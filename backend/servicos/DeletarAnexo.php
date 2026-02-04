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
    $anexo_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ($anexo_id <= 0) {
        throw new Exception('ID do anexo inválido');
    }

    // Buscar arquivo para deletar do disco
    $sql = "SELECT nome_arquivo FROM servico_anexos WHERE id = :id AND status = 'Ativo'";
    $stmt = $conexao->prepare($sql);
    $stmt->bindValue(':id', $anexo_id, PDO::PARAM_INT);
    $stmt->execute();
    $anexo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$anexo) {
        throw new Exception('Anexo não encontrado');
    }

    // Marcar como inativo (soft delete)
    $sqlDelete = "UPDATE servico_anexos SET status = 'Inativo' WHERE id = :id";
    $stmtDelete = $conexao->prepare($sqlDelete);
    $stmtDelete->bindValue(':id', $anexo_id, PDO::PARAM_INT);
    
    if (!$stmtDelete->execute()) {
        throw new Exception('Erro ao deletar o anexo');
    }

    // Tentar deletar o arquivo do disco (opcional, não causa erro se falhar)
    $arquivo_path = __DIR__ . '/../../assets/uploads/anexos/' . $anexo['nome_arquivo'];
    if (file_exists($arquivo_path)) {
        @unlink($arquivo_path);
    }

    http_response_code(200);
    echo json_encode(['sucesso' => true, 'mensagem' => 'Anexo removido com sucesso']);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
}
