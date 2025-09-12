<?php
// backend/servicos/cadastrar.php

session_start();
include __DIR__ . '/../conexao.php'; // Inclui a conexão

// Proteção: Somente usuários logados podem acessar
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    $_SESSION['mensagem_erro'] = "Acesso negado. Faça login primeiro.";
    header('Location: ../../index.php');
    exit;
}

// Proteção: Somente via método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['mensagem_erro'] = "Método de requisição inválido.";
    header('Location: ../../servicos.php');
    exit;
}

// --- Validação dos dados recebidos do formulário ---
$servico_tipo_id = filter_input(INPUT_POST, 'servico_tipo_id', FILTER_VALIDATE_INT);
$nome_cliente = trim(filter_input(INPUT_POST, 'nome_cliente', FILTER_SANITIZE_STRING));
$numero_cliente = trim(filter_input(INPUT_POST, 'numero_cliente', FILTER_SANITIZE_STRING));
$data_programada_str = trim(filter_input(INPUT_POST, 'data_programada', FILTER_SANITIZE_STRING));

if (!$servico_tipo_id) {
    $_SESSION['mensagem_erro'] = 'Erro: O tipo de serviço é obrigatório.';
    header('Location: ../../servicos.php');
    exit;
}

$data_programada = null;
if (!empty($data_programada_str)) {
    $d = DateTime::createFromFormat('Y-m-d', $data_programada_str);
    if (!$d || $d->format('Y-m-d') !== $data_programada_str) {
        $_SESSION['mensagem_erro'] = 'Erro: Formato de data programada inválido.';
        header('Location: ../../servicos.php');
        exit;
    }
    $data_programada = $data_programada_str;
}

// --- Inserção no Banco ---
try {
    $sql = "INSERT INTO servicos_os 
                (servico_tipo_id, nome_cliente, numero_cliente, criado_por, criado_em, data_programada, status)
            VALUES 
                (:servico_tipo_id, :nome_cliente, :numero_cliente, :criado_por, NOW(), :data_programada, :status)";

    $stmt = $conexao->prepare($sql);
    $stmt->bindValue(':servico_tipo_id', $servico_tipo_id, PDO::PARAM_INT);
    $stmt->bindValue(':nome_cliente', $nome_cliente ?: null, PDO::PARAM_STR);
    $stmt->bindValue(':numero_cliente', $numero_cliente ?: null, PDO::PARAM_STR);
    $stmt->bindValue(':criado_por', $_SESSION['matricula'], PDO::PARAM_INT);
    $stmt->bindValue(':data_programada', $data_programada, PDO::PARAM_STR);
    $stmt->bindValue(':status', 'Pendente', PDO::PARAM_STR);

    $stmt->execute();

    // MENSAGEM DE SUCESSO
    $_SESSION['mensagem_sucesso'] = "Ordem de Serviço cadastrada com sucesso!";

} catch (PDOException $e) {
    // MENSAGEM DE ERRO
    $_SESSION['mensagem_erro'] = "Erro ao cadastrar a Ordem de Serviço. Tente novamente.";
}

// Redireciona de volta para a página de serviços
header('Location: ../../servicos.php');
exit;