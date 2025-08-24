<?php
// backend/estoque/listar.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['logado'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../conexao.php'; // expõe $conexao (PDO)

$limite = 15;
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina - 1) * $limite;

$aba = $_GET['aba'] ?? 'saldo';
$aba = in_array($aba, ['saldo', 'movimentacoes'], true) ? $aba : 'saldo';

$data = [];
$total = 0;
$totalPaginas = 1;

try {
    if ($aba === 'saldo') {
        $sql = "SELECT 
                    es.id,
                    es.item,
                    i.item AS produto_nome,
                    u.unidade AS unidade_codigo,
                    u.nome AS unidade_nome,
                    es.quantidade,
                    es.custo_medio,
                    a.nome AS almoxarifado
                FROM estoque_saldo es
                INNER JOIN itens i ON es.item = i.id
                INNER JOIN unidades u ON i.unidade_padrao = u.id
                INNER JOIN almoxarifados a ON es.almoxarifado = a.id
                WHERE es.quantidade > 0 AND a.ativo = 1
                ORDER BY i.item ASC
                LIMIT :limite OFFSET :offset";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $total = (int)$conexao->query(
            "SELECT COUNT(*)
             FROM estoque_saldo es
             INNER JOIN almoxarifados a ON es.almoxarifado = a.id
             WHERE es.quantidade > 0 AND a.ativo = 1"
        )->fetchColumn();

    } else { // movimentacoes
        $sql = "SELECT 
                    em.id,
                    em.data AS movement_date,
                    emt.tipo AS tipo_movimentacao,
                    emt.direcao AS direction,
                    i.item AS produto_nome,
                    em.quantidade,
                    u.unidade AS unidade_codigo,
                    em.custo_medio,
                    a.nome AS almoxarifado,
                    em.doc_ref,
                    em.observacao AS notes,
                    em.os
                FROM estoque_movimentos em
                INNER JOIN estoque_movimentos_tipos emt ON em.tipo_movimentacao = emt.id
                INNER JOIN itens i ON em.item = i.id
                INNER JOIN unidades u ON i.unidade_padrao = u.id
                INNER JOIN almoxarifados a ON em.almoxarifado = a.id
                ORDER BY em.data DESC, em.id DESC
                LIMIT :limite OFFSET :offset";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $total = (int)$conexao->query("SELECT COUNT(*) FROM estoque_movimentos")->fetchColumn();
    }

    $totalPaginas = max(1, (int)ceil($total / $limite));
} catch (PDOException $e) {
    $data = [];
    $total = 0;
    $totalPaginas = 1;
}

$ESTOQUE_JSON = json_encode(
    [
        'data' => $data,
        'meta' => [
            'aba'          => $aba,
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
?>