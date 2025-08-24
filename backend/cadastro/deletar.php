<?php

session_start();

if (empty($_SESSION['logado'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../conexao.php'; // expõe $conexao (PDO)

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo 'Método inválido.';
    exit;
}

$userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$senha  = isset($_POST['senha_confirmacao']) ? $_POST['senha_confirmacao'] : '';

if ($userId <= 0 || $senha === '') {
    echo 'Parâmetros inválidos.';
    exit;
}

// Id do usuário logado (ajuste o nome da chave conforme seu login)
$usuarioLogadoId = isset($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : 0;
if ($usuarioLogadoId <= 0) {
    echo 'Sessão inválida.';
    exit;
}

try {
    // 1) Busca a senha do usuário logado
    $stmt = $conexao->prepare('SELECT senha FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $usuarioLogadoId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || !password_verify($senha, $row['senha'])) {
        echo 'Senha de confirmação inválida.';
        exit;
    }

    // 2) Inativar o usuário alvo
    $stmt = $conexao->prepare("UPDATE users SET status = 'Inativo' WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $userId]);

    header('Location: ../../cadastro.php');
    exit;
} catch (PDOException $e) {
    
    echo 'Erro ao inativar usuário.';
    exit;
}
