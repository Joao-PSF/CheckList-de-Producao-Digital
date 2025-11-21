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
$executar = isset($input['executar']) ? (bool)$input['executar'] : false;

// Validar dados
if ($etapaId <= 0 || $osId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit();
}

try {
    $conexao->beginTransaction();

    // Buscar dados da etapa ANTES da atualização
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

    // Preparar dados para atualização
    $novaExecucao = $executar ? 1 : 0;
    $novaDataExecutada = $executar ? date('Y-m-d') : null;

    // Atualizar a etapa
    $sqlUpdate = "UPDATE servico_etapas 
                  SET execucao = :execucao,
                      executada_em = :executada_em
                  WHERE id = :etapa_id AND servico_os_id = :os_id";

    $stmtUpdate = $conexao->prepare($sqlUpdate);
    $stmtUpdate->bindValue(':execucao', $novaExecucao, PDO::PARAM_INT);
    $stmtUpdate->bindValue(':executada_em', $novaDataExecutada, PDO::PARAM_STR);
    $stmtUpdate->bindValue(':etapa_id', $etapaId, PDO::PARAM_INT);
    $stmtUpdate->bindValue(':os_id', $osId, PDO::PARAM_INT);
    $stmtUpdate->execute();

    // Dados DEPOIS da atualização
    $dadosDepois = [
        'etapa' => $dadosAntes['etapa'],
        'ordem' => $dadosAntes['ordem'],
        'execucao' => $novaExecucao,
        'executada_em' => $novaDataExecutada,
        'status' => $dadosAntes['status']
    ];

    // Registrar log da conclusão/reversão da etapa
    $acao = $executar ? 'CONCLUIR_ETAPA' : 'REVERTER_CONCLUSAO_ETAPA';
    registrarConcluirEtapa(
        $conexao,
        [
            'id' => $_SESSION['id'],
            'cpf' => $_SESSION['cpf'],
            'matricula' => $_SESSION['matricula']
        ],
        $osId,
        $etapaId,
        $dadosAntes,
        $dadosDepois,
        $acao
    );

    $conexao->commit();

    // Retornar sucesso com dados atualizados
    echo json_encode([
        'success' => true,
        'message' => $executar ? 'Etapa concluída com sucesso!' : 'Conclusão da etapa revertida com sucesso!',
        'data' => [
            'execucao' => $novaExecucao,
            'executada_em' => $novaDataExecutada,
            'executada_em_formatada' => $novaDataExecutada ? date('d/m/Y', strtotime($novaDataExecutada)) : '—'
        ]
    ]);

} catch (Exception $e) {
    if (isset($conexao) && $conexao->inTransaction()) {
        $conexao->rollBack();
    }

    // Registrar log de erro
    if (isset($conexao)) {
        try {
            registrarConcluirEtapa(
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
                'CONCLUIR_ETAPA',
                false,
                $e->getMessage()
            );
        } catch (Exception $logError) {
            // Ignorar erro ao registrar log
        }
    }

    echo json_encode([
        'success' => false,
        'message' => 'Erro ao atualizar etapa: ' . $e->getMessage()
    ]);
}
