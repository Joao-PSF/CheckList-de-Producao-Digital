<?php

session_start();
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/LogsCadastro.php';

// Validar se o usuário está autenticado e é admin
if (empty($_SESSION['logado']) || $_SESSION['nivel'] != 1) {
    $_SESSION['erro'] = 'Acesso negado';
    header('Location: ../../pages/cadastro.php');
    exit;
}

// Validar dados
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$nivel = isset($_POST['nivel']) ? (int)$_POST['nivel'] : 1;

if ($user_id <= 0 || empty($nome)) {
    $_SESSION['erro'] = 'Dados inválidos';
    header('Location: ../../pages/cadastro.php');
    exit;
}

try {
    // Inicia transação
    $conexao->beginTransaction();

    // Buscar dados antigos do usuário
    $sqlBusca = "SELECT id, nome, email, nivel FROM users WHERE id = ? AND status = 'Ativo'";
    $stmtBusca = $conexao->prepare($sqlBusca);
    $stmtBusca->execute([$user_id]);
    $usuarioAntigo = $stmtBusca->fetch(PDO::FETCH_ASSOC);

    if (!$usuarioAntigo) {
        throw new Exception('Usuário não encontrado');
    }

    // Atualizar usuário
    $sql = "UPDATE users SET nome = ?, email = ?, nivel = ? WHERE id = ?";
    $stmt = $conexao->prepare($sql);
    $sucesso = $stmt->execute([$nome, $email, $nivel, $user_id]);

    if (!$sucesso) {
        throw new Exception('Erro ao atualizar usuário');
    }

    // Buscar dados novos para o log
    $stmtBusca = $conexao->prepare($sqlBusca);
    $stmtBusca->execute([$user_id]);
    $usuarioNovo = $stmtBusca->fetch(PDO::FETCH_ASSOC);

    // Registrar log
    $usuarioLogado = [
        'id' => $_SESSION['id'],
        'cpf' => $_SESSION['cpf'],
        'matricula' => $_SESSION['matricula']
    ];

    registrarAlteracaoUsuario(
        $conexao,
        $usuarioLogado,
        $usuarioAntigo,
        $usuarioNovo,
        true
    );

    // Confirma transação
    $conexao->commit();

    $_SESSION['mensagem'] = 'Usuário atualizado com sucesso';
    header('Location: ../../pages/cadastro.php');
    exit;

} catch (Exception $e) {
    $conexao->rollBack();

    // Registrar falha no log
    $usuarioLogado = [
        'id' => $_SESSION['id'],
        'cpf' => $_SESSION['cpf'],
        'matricula' => $_SESSION['matricula']
    ];

    registrarAlteracaoUsuario(
        $conexao,
        $usuarioLogado,
        [],
        ['user_id' => $user_id],
        false,
        $e->getMessage()
    );

    $_SESSION['erro'] = 'Erro ao atualizar usuário: ' . $e->getMessage();
    header('Location: ../../pages/cadastro.php');
    exit;
}
?>
