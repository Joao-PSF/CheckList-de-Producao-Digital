<?php

session_start();
require_once __DIR__ . '/../conexao.php';

// Validar se o usuário está autenticado
if (empty($_SESSION['logado'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Não autenticado']);
    exit;
}

// Validar ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['erro' => 'ID inválido']);
    exit;
}

try {
    $sql = "SELECT id, matricula, nome, cpf, email, nivel FROM users WHERE id = ? AND status = 'Ativo'";
    $stmt = $conexao->prepare($sql);
    $stmt->execute([$id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        http_response_code(404);
        echo json_encode(['erro' => 'Usuário não encontrado']);
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode($usuario);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao buscar usuário']);
    exit;
}
?>
