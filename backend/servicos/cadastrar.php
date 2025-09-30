<?php

session_start();

include __DIR__ . '/../conexao.php'; // Inclui a conexão

// Proteção: Somente usuários logados podem acessar
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    $_SESSION['mensagem'] = "Acesso negado. Faça login primeiro.";
    header('Location: ../../index.php');
    exit;
}

// Proteção: Somente via método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['erro'] = "Método de requisição inválido.";
    header('Location: ../../home.php');
    exit;
}

// Verifica o nível de acesso do usuário
if (!isset($_SESSION['nivel']) || $_SESSION['nivel'] === 2) {
    $_SESSION['erro'] = "Acesso negado. Você não tem permissão para cadastrar ordens de serviço.";
    header('Location: ../../home.php');
    exit;
}

// --- Validação dos dados recebidos do formulário ---
$servico_tipo_id = filter_input(INPUT_POST, 'servico_tipo_id', FILTER_VALIDATE_INT);
$nome_cliente = trim(filter_input(INPUT_POST, 'nome_cliente', FILTER_SANITIZE_STRING));
$numero_cliente = trim(filter_input(INPUT_POST, 'numero_cliente', FILTER_SANITIZE_STRING));
$data_programada_inicio_str = trim(filter_input(INPUT_POST, 'data_programada_inicio', FILTER_SANITIZE_STRING));
$data_programada_str = trim(filter_input(INPUT_POST, 'data_programada', FILTER_SANITIZE_STRING));

if (!$servico_tipo_id) {
    $_SESSION['erro'] = 'Erro: O tipo de serviço é obrigatório.';
    header('Location: .../../home.php');
    exit;
}


$data_inicio = null;
if (!empty($data_programada_inicio_str)) {
    $d_inicio = DateTime::createFromFormat('Y-m-d', $data_programada_inicio_str);
    if (!$d_inicio || $d_inicio->format('Y-m-d') !== $data_programada_inicio_str) {
        $_SESSION['erro'] = 'Erro: Formato de data programada para início inválido.';
        header('Location: ../../home.php');
        exit;
    }
    $data_inicio = $data_programada_inicio_str;
}


$data_programada = null;
if (!empty($data_programada_str)) {
    $d = DateTime::createFromFormat('Y-m-d', $data_programada_str);
    if (!$d || $d->format('Y-m-d') !== $data_programada_str) {
        $_SESSION['erro'] = 'Erro: Formato de data programada inválido.';
        header('Location: ../../home.php');
        exit;
    }
    $data_programada = $data_programada_str;
}

$criado_por_cpf = $_SESSION['cpf'];
$data_encerramento = null;
$situacao          = 'PENDENTE';
$status            = 'Ativo';

// --- Inserção no Banco ---
try {

    $sql = "INSERT INTO servicos_os
        (servico_tipo_id, nome_cliente, numero_cliente, criado_por_cpf, criado_em,
         data_inicio, data_programada, data_encerramento, situacao, status)
        VALUES
        (:servico_tipo_id, :nome_cliente, :numero_cliente, :criado_por_cpf, NOW(),
         :data_inicio, :data_programada, :data_encerramento, :situacao, :status)";

    $stmt = $conexao->prepare($sql);

    $stmt->bindValue(':servico_tipo_id', $servico_tipo_id, PDO::PARAM_INT);
    $stmt->bindValue(':nome_cliente', $nome_cliente ?: null, is_null($nome_cliente) ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':numero_cliente', $numero_cliente ?: null, is_null($numero_cliente) ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':criado_por_cpf', $criado_por_cpf, PDO::PARAM_STR);
    $stmt->bindValue(':data_inicio', $data_inicio, PDO::PARAM_NULL);
    $stmt->bindValue(':data_programada', $data_programada, PDO::PARAM_STR);
    $stmt->bindValue(':data_encerramento', $data_encerramento, PDO::PARAM_NULL);
    $stmt->bindValue(':situacao', $situacao, PDO::PARAM_STR);
    $stmt->bindValue(':status', $status, PDO::PARAM_STR);

    $stmt->execute();

    $id = $conexao->lastInsertId();


    // MENSAGEM DE SUCESSO
    $_SESSION['mensagem'] = "Ordem de Serviço cadastrada com sucesso!";
    header('Location: ../../pages/servicos/DetalhesOS.php?id=' . $id);
    exit;

} catch (PDOException $e) {

    // MENSAGEM DE ERRO
    $_SESSION['erro'] = "Erro ao cadastrar a Ordem de Serviço. Tente novamente." . $e->getMessage();
    header('Location: ../../home.php');
    exit;
}
