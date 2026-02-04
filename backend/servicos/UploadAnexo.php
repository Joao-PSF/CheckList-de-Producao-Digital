<?php

session_start();
require_once __DIR__ . '/../conexao.php';

// Validar autenticação
if (empty($_SESSION['logado'])) {
    http_response_code(401);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Não autenticado']);
    exit;
}

try {
    // Validar dados
    $etapa_id = isset($_POST['servico_etapa_id']) ? (int)$_POST['servico_etapa_id'] : 0;
    if ($etapa_id <= 0) {
        throw new Exception('ID da etapa inválido');
    }

    // Validar arquivo
    if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Nenhum arquivo foi enviado ou ocorreu um erro');
    }

    $arquivo = $_FILES['arquivo'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    // Validar tamanho
    if ($arquivo['size'] > $maxSize) {
        throw new Exception('Arquivo muito grande (máximo: 5MB)');
    }

    // Validar tipo MIME
    $mimes_permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'application/pdf'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $arquivo['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $mimes_permitidos)) {
        throw new Exception('Tipo de arquivo não permitido');
    }

    // Criar diretório se não existir
    $upload_dir = __DIR__ . '/../../assets/uploads/anexos/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Gerar nome único para o arquivo
    $extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
    $nome_arquivo = uniqid('anexo_', true) . '.' . $extensao;
    $caminho_arquivo = $upload_dir . $nome_arquivo;

    // Mover arquivo
    if (!move_uploaded_file($arquivo['tmp_name'], $caminho_arquivo)) {
        throw new Exception('Erro ao salvar o arquivo');
    }

    // Inserir no banco de dados
    $sql = "
        INSERT INTO servico_anexos 
        (servico_etapa_id, nome_original, nome_arquivo, tipo_mime, tamanho, criado_por, criado_em, status)
        VALUES (:etapa_id, :nome_original, :nome_arquivo, :tipo_mime, :tamanho, :criado_por, NOW(), 'Ativo')
    ";

    $stmt = $conexao->prepare($sql);
    $stmt->bindValue(':etapa_id', $etapa_id, PDO::PARAM_INT);
    $stmt->bindValue(':nome_original', $arquivo['name'], PDO::PARAM_STR);
    $stmt->bindValue(':nome_arquivo', $nome_arquivo, PDO::PARAM_STR);
    $stmt->bindValue(':tipo_mime', $mime_type, PDO::PARAM_STR);
    $stmt->bindValue(':tamanho', $arquivo['size'], PDO::PARAM_INT);
    $stmt->bindValue(':criado_por', $_SESSION['id'] ?? 0, PDO::PARAM_INT);
    
    if (!$stmt->execute()) {
        @unlink($caminho_arquivo);
        throw new Exception('Erro ao registrar o arquivo no banco de dados');
    }

    http_response_code(200);
    echo json_encode(['sucesso' => true, 'mensagem' => 'Arquivo enviado com sucesso']);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
}
