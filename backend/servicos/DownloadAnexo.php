<?php

session_start();
require_once __DIR__ . '/../conexao.php';

// Validar autenticação
if (empty($_SESSION['logado'])) {
    http_response_code(401);
    exit;
}

try {
    $anexo_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($anexo_id <= 0) {
        throw new Exception('ID inválido');
    }

    // Buscar arquivo
    $sql = "SELECT nome_arquivo, nome_original, tipo_mime FROM servico_anexos WHERE id = :id AND status = 'Ativo'";
    $stmt = $conexao->prepare($sql);
    $stmt->bindValue(':id', $anexo_id, PDO::PARAM_INT);
    $stmt->execute();
    $anexo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$anexo) {
        throw new Exception('Arquivo não encontrado');
    }

    $arquivo_path = __DIR__ . '/../../assets/uploads/anexos/' . $anexo['nome_arquivo'];
    
    if (!file_exists($arquivo_path)) {
        throw new Exception('Arquivo não existe no servidor');
    }

    // Enviar arquivo
    header('Content-Type: ' . $anexo['tipo_mime']);
    header('Content-Disposition: attachment; filename="' . $anexo['nome_original'] . '"');
    header('Content-Length: ' . filesize($arquivo_path));
    readfile($arquivo_path);

} catch (Exception $e) {
    http_response_code(404);
    echo 'Erro: ' . $e->getMessage();
}
