<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['logado'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../conexao.php';

$limite = 15;
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina - 1) * $limite;

$data = [];
$total = 0;
$totalPaginas = 1;

try {
    // Tipos de serviço ativos (para selects no front)
    $stmtTipos = $conexao->query("
        SELECT id, tipo
        FROM servicos_tipos
        WHERE status = 'Ativo'
        ORDER BY tipo ASC
    ");
    $tiposDeServico = $stmtTipos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $tiposDeServico = [];
}

try {
    // Buscar OS com apenas os campos necessários para o front
    $sql = "
        SELECT 
            os.id,
            st.tipo AS tipo_servico,
            COALESCE(GROUP_CONCAT(DISTINCT u.nome ORDER BY u.nome SEPARATOR ', '), '') AS responsaveis,
            os.data_programada,
            os.situacao
        FROM servicos_os os
        LEFT JOIN servicos_tipos st 
               ON st.id = os.servico_tipo_id
        LEFT JOIN servico_etapas se 
               ON se.servico_os_id = os.id
              AND se.status = 'Ativo'
        LEFT JOIN servico_etapas_responsavel ser 
               ON ser.servico_etapa_id = se.id
              AND ser.status = 'Ativo'
        LEFT JOIN users u 
               ON u.matricula = ser.responsavel
              AND u.status = 'Ativo'
        WHERE os.status = 'Ativo'
        GROUP BY os.id, st.tipo, os.data_programada, os.situacao
        ORDER BY os.id DESC
        LIMIT :limite OFFSET :offset
    ";

    $stmt = $conexao->prepare($sql);
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Contagem de OS ativas
    $total = (int)$conexao->query("
        SELECT COUNT(*) FROM servicos_os WHERE status = 'Ativo'
    ")->fetchColumn();
    $totalPaginas = max(1, (int)ceil($total / $limite));
} catch (PDOException $e) {
    $data = [];
    $total = 0;
    $totalPaginas = 1;
}

// Saída JSON
$SERVICOS_JSON = json_encode(
    [
        'data' => $data,
        'meta' => [
            'pagina'       => $pagina,
            'limite'       => $limite,
            'total'        => $total,
            'totalPaginas' => $totalPaginas
        ]
    ],
    JSON_UNESCAPED_UNICODE
    | JSON_UNESCAPED_SLASHES
    | JSON_HEX_TAG
    | JSON_HEX_AMP
    | JSON_HEX_APOS
    | JSON_HEX_QUOT
);

