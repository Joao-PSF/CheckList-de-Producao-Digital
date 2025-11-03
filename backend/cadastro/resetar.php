<?php

session_start();

if (empty($_SESSION['logado'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../conexao.php'; // expõe $conexao (PDO, ERRMODE_EXCEPTION)
require_once __DIR__ . '/../logs.php'; // Sistema de logs

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

    // Buscar dados do usuário (para log)
    $stmt = $conexao->prepare('SELECT nome FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $userId]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    $nomeUsuario = $usuario ? $usuario['nome'] : 'Desconhecido';

    $novaSenhaHash = password_hash('12345', PASSWORD_DEFAULT);

    $stmt = $conexao->prepare('UPDATE users SET senha = :senha WHERE id = :id LIMIT 1');
    $stmt->execute([
        ':senha' => $novaSenhaHash,
        ':id'    => $userId,
    ]);

    // REGISTRAR LOG DE RESET DE SENHA BEM-SUCEDIDO
    registrarResetarSenha($conexao, $userId, $nomeUsuario, true);

    // (To do) enviar e-mail ao usuário com a senha padrão (opcional)
    // (To do) Mensagem de sucesso

    header('Location: ../../cadastro.php');
    exit;
} catch (PDOException $e) {

    // REGISTRAR LOG DE FALHA NO RESET DE SENHA
    registrarResetarSenha($conexao, $userId, 'Desconhecido', false, $e->getMessage());

    echo 'Erro ao resetar a senha.';
    exit;
}
