<?php
// Iniciar sessão se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Desabilitar exibição de erros e definir header JSON antes de qualquer saída
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

// Verificar se o usuário está logado
if (empty($_SESSION['logado'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit();
}

try {
    include_once __DIR__ . '/../conexao.php';
    include_once __DIR__ . '/LogsServicos.php';
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao carregar dependências: ' . $e->getMessage()]);
    exit();
}

// Verificar se a conexão com o banco foi estabelecida
if (!isset($conexao)) {
    echo json_encode(['success' => false, 'message' => 'Erro: Conexão com o banco de dados não estabelecida']);
    exit();
}

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

// Receber dados JSON
$input = json_decode(file_get_contents('php://input'), true);

$etapaId = isset($input['etapa_id']) ? (int)$input['etapa_id'] : 0;
$osId = isset($input['os_id']) ? (int)$input['os_id'] : 0;

// Validar dados
if ($etapaId <= 0 || $osId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit();
}

try {
    $conexao->beginTransaction();

    // Buscar dados da etapa ANTES da inativação
    $sqlAntes = "SELECT etapa, ordem, execucao, executada_em, status 
                 FROM servico_etapas 
                 WHERE id = :etapa_id AND servico_os_id = :os_id";
    $stmtAntes = $conexao->prepare($sqlAntes);
    $stmtAntes->bindValue(':etapa_id', $etapaId, PDO::PARAM_INT);
    $stmtAntes->bindValue(':os_id', $osId, PDO::PARAM_INT);
    $stmtAntes->execute();
    $dadosAntes = $stmtAntes->fetch(PDO::FETCH_ASSOC);

    if (!$dadosAntes) {
        throw new Exception('Etapa não encontrada');
    }

    // Verificar se já está inativa
    if ($dadosAntes['status'] === 'Inativo') {
        throw new Exception('Esta etapa já está inativa');
    }

    // Guardar a ordem da etapa que será inativada
    $ordemInativada = (int)$dadosAntes['ordem'];

    // Inativar a etapa (soft delete)
    $sqlUpdate = "UPDATE servico_etapas 
                  SET status = 'Inativo'
                  WHERE id = :etapa_id AND servico_os_id = :os_id";

    $stmtUpdate = $conexao->prepare($sqlUpdate);
    $stmtUpdate->bindValue(':etapa_id', $etapaId, PDO::PARAM_INT);
    $stmtUpdate->bindValue(':os_id', $osId, PDO::PARAM_INT);
    $stmtUpdate->execute();

    // Reorganizar as ordens das etapas restantes (ATIVAS)
    // Todas as etapas com ordem maior que a inativada devem ter sua ordem reduzida em 1
    $sqlReorganizar = "UPDATE servico_etapas 
                       SET ordem = ordem - 1
                       WHERE servico_os_id = :os_id 
                       AND status = 'Ativo'
                       AND ordem > :ordem_inativada";
    
    $stmtReorganizar = $conexao->prepare($sqlReorganizar);
    $stmtReorganizar->bindValue(':os_id', $osId, PDO::PARAM_INT);
    $stmtReorganizar->bindValue(':ordem_inativada', $ordemInativada, PDO::PARAM_INT);
    $stmtReorganizar->execute();

    // Dados DEPOIS da inativação
    $dadosDepois = [
        'etapa' => $dadosAntes['etapa'],
        'ordem' => $dadosAntes['ordem'],
        'execucao' => $dadosAntes['execucao'],
        'executada_em' => $dadosAntes['executada_em'],
        'status' => 'Inativo'
    ];

    // Registrar log da inativação
    registrarInativarEtapa(
        $conexao,
        [
            'id' => $_SESSION['id'],
            'cpf' => $_SESSION['cpf'],
            'matricula' => $_SESSION['matricula']
        ],
        $osId,
        $etapaId,
        $dadosAntes,
        $dadosDepois
    );

    $conexao->commit();

    // Retornar sucesso
    echo json_encode([
        'success' => true,
        'message' => 'Etapa inativada com sucesso!'
    ]);

} catch (Exception $e) {
    if (isset($conexao) && $conexao->inTransaction()) {
        $conexao->rollBack();
    }

    // Registrar log de erro
    if (isset($conexao)) {
        try {
            registrarInativarEtapa(
                $conexao,
                [
                    'id' => $_SESSION['id'] ?? 0,
                    'cpf' => $_SESSION['cpf'] ?? '',
                    'matricula' => $_SESSION['matricula'] ?? 0
                ],
                $osId ?? 0,
                $etapaId ?? 0,
                [],
                [],
                false,
                $e->getMessage()
            );
        } catch (Exception $logError) {
            // Ignorar erro ao registrar log
        }
    }

    echo json_encode([
        'success' => false,
        'message' => 'Erro ao inativar etapa: ' . $e->getMessage()
    ]);
}
