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
    //Buscar ordens de serviço com informações relacionadas
    $sql = "SELECT 
                os.id,
                os.nome_cliente,
                os.numero_cliente,
                os.data_programada,
                st.tipo AS tipo_servico,
                st.status AS tipo_status,
                GROUP_CONCAT(DISTINCT u.nome ORDER BY u.nome SEPARATOR ', ') AS responsaveis,
                COUNT(DISTINCT sr.responsavel) AS total_responsaveis,
                CASE 
                    WHEN os.data_programada IS NULL THEN 'Sem data definida'
                    WHEN os.data_programada < CURDATE() THEN 'Atrasado'
                    ELSE 'Em andamento'
                END AS status_os,
                os.criado_em
            FROM servicos_os os
            LEFT JOIN servicos_tipos st ON os.servico_tipo_id = st.id
            LEFT JOIN servico_responsavel sr ON os.id = sr.servico_os_id AND sr.status = 'ativo'
            LEFT JOIN users u ON sr.responsavel = u.matricula AND u.status = 'ativo'
            GROUP BY os.id, os.nome_cliente, os.numero_cliente, os.data_programada, st.tipo, st.status, os.criado_em
            ORDER BY os.id DESC
            LIMIT :limite OFFSET :offset";

    $stmt = $conexao->prepare($sql);
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //Contar total de ordens de serviço
    $total = (int)$conexao->query("SELECT COUNT(*) FROM servicos_os")->fetchColumn();
    $totalPaginas = max(1, (int)ceil($total / $limite));
} catch (PDOException $e) {
    $data = [];
    $total = 0;
    $totalPaginas = 1;
}

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
