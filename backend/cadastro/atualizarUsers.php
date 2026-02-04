<?php

session_start();
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/LogsCadastro.php';

// Validar se o usuário está autenticado e é admin
if (empty($_SESSION['logado'])) {
    $_SESSION['erro'] = 'Não autenticado';
    header('Location: ../../home.php?page=cadastro');
    exit;
}

// Apenas usuários de nível diferente de 1 podem atualizar
if ((int)$_SESSION['nivel'] === 1) {
    $_SESSION['erro'] = 'Acesso negado';
    header('Location: ../../home.php?page=cadastro');
    exit;
}

// Validar dados
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$nivel = isset($_POST['nivel']) ? (int)$_POST['nivel'] : 1;

if ($user_id <= 0) {
    $_SESSION['erro'] = 'Erro: Dados inválidos';
    header('Location: ../../home.php?page=cadastro');
    exit;
}

if (empty($nome)) {
    $_SESSION['erro'] = 'Erro: Nome é obrigatório';
    header('Location: ../../home.php?page=cadastro');
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
        error_log("ERROR atualizarUsers.php - Usuário não encontrado ou inativo: $user_id");
        throw new Exception('Usuário não encontrado');
    }

    // Atualizar usuário
    $sql = "UPDATE users SET nome = ?, email = ?, nivel = ? WHERE id = ?";
    $stmt = $conexao->prepare($sql);
    $sucesso = $stmt->execute([$nome, $email, $nivel, $user_id]);

    if (!$sucesso) {
        error_log("ERROR atualizarUsers.php - Falha ao executar UPDATE para user_id: $user_id");
        throw new Exception('Erro ao atualizar usuário no banco de dados');
    }

    // Verificar se houve linhas afetadas
    if ($stmt->rowCount() === 0) {
        error_log("WARNING atualizarUsers.php - Nenhuma linha afetada para user_id: $user_id");
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

    error_log("SUCCESS atualizarUsers.php - Usuário $user_id atualizado com sucesso");
    $_SESSION['mensagem'] = 'Usuário atualizado com sucesso';
    header('Location: ../../home.php?page=cadastro');
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

    $_SESSION['erro'] = 'Erro: ' . $e->getMessage();
    header('Location: ../../home.php?page=cadastro');
    exit;
}
?>
