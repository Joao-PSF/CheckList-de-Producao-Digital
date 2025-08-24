<?php

session_start();

if (empty($_SESSION['logado'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../conexao.php'; // expõe $conexao (PDO, ERRMODE_EXCEPTION)

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo 'Método inválido.';
    exit;
}

$userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
if ($userId <= 0) {
    echo 'Parâmetro inválido.';
    exit;
}

try {

    $novaSenhaHash = password_hash('12345', PASSWORD_DEFAULT);

    $stmt = $conexao->prepare('UPDATE users SET senha = :senha WHERE id = :id LIMIT 1');
    $stmt->execute([
        ':senha' => $novaSenhaHash,
        ':id'    => $userId,
    ]);

    // (To do) enviar e-mail ao usuário com a senha padrão (opcional)
    // (To do) Mensagem de sucesso

    header('Location: ../../cadastro.php');
    exit;
} catch (PDOException $e) {

    echo 'Erro ao resetar a senha.';
    exit;
}
