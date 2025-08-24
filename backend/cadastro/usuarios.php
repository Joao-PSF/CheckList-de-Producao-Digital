<?php

if (empty($_SESSION['logado'])) {
    header('Location: index.php');
    exit;
}

include __DIR__ . '/../conexao.php';

$limite = 15;
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina - 1) * $limite;

$sql = "SELECT id, matricula, nome, nivel, datadecadastro
        FROM users
        WHERE status = 'Ativo'
        ORDER BY datadecadastro DESC
        LIMIT :limite OFFSET :offset";
$stmt = $conexao->prepare($sql);
$stmt->bindValue(':limite',  $limite, PDO::PARAM_INT);
$stmt->bindValue(':offset',  $offset, PDO::PARAM_INT);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = (int)$conexao->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalPaginas = max(1, (int)ceil($total / $limite));

// Prepara para o JS
$USERS_JSON = json_encode(
    [
        'data' => $usuarios,
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

// Carregar níveis de acesso
$stmtN = $conexao->query("SELECT nivel, descricao FROM acessosniveis ORDER BY descricao ASC");
$acessosNiveis = $stmtN->fetchAll(PDO::FETCH_ASSOC);

?>