<?php
// Iniciar sessão
session_start();

// Verificar se o usuário está logado
if (empty($_SESSION['logado'])) {
    header("Location: index.php");
    exit();
}

include_once __DIR__ . '/backend/config-style.php';

$is_admin_or_supervisor = isset($_SESSION['nivel']) && in_array($_SESSION['nivel'], [2, 3]);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema Metalma</title>
    <script src="assets/js/novoservico.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="assets/css/custom.css">

    <style>
        :root {
            --color-primary:
                <?= defined('COLOR_PRIMARY') ? COLOR_PRIMARY : '#343a40' ?>;
            --color-secondary:
                <?= defined('COLOR_SECONDARY') ? COLOR_SECONDARY : '#198754' ?>;
            --sidebar-text-color:
                <?= (defined('SIDEBAR_THEME') && SIDEBAR_THEME === 'light') ? '#000000' : '#ffffff' ?>;
        }

        
    </style>
</head>

<body>

    <div class="container-fluid p-0">

        <div class="d-lg-flex vh-100">

            <?php if ($is_admin_or_supervisor) { ?>

                <aside class="sidebar-themed p-3 offcanvas-lg offcanvas-start" tabindex="-1"
                    id="sidebarMenu" aria-labelledby="sidebarMenuLabel">

                    <div class="offcanvas-header">
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
                            data-bs-target="#sidebarMenu" aria-label="Close"></button>
                    </div>

                    <div class="offcanvas-body d-flex flex-column p-0">
                        <div class="mb-4 border-bottom pb-3">
                            <img src="<?= defined('LOGO_PATH') ? LOGO_PATH : '' ?>" alt="Logo Empresa"
                                style="width: <?= defined('LOGO_WIDTH') ? LOGO_WIDTH : 'auto' ?>; height: <?= defined('LOGO_HEIGHT') ? LOGO_HEIGHT : 'auto' ?>;">

                            <p class="small mb-0 text-white">Olá, <?php echo htmlspecialchars($_SESSION['nome']); ?>!
                            </p>
                        </div>

                        <nav class="nav nav-pills flex-column mb-auto">
                            <a href="home.php?page=dashboard" class="nav-link text-white">
                                <i class="bi bi-house-door me-2"></i>Inicial
                            </a>
                            <a href="home.php?page=estoque" class="nav-link text-white">
                                <i class="bi bi-box-seam me-2"></i>Estoque
                            </a>
                            <a href="home.php?page=servicos" class="nav-link text-white">
                                <i class="bi bi-clipboard-check me-2"></i>Ordens de Serviços
                            </a>
                            <a href="home.php?page=cadastro" class="nav-link text-white">
                                <i class="bi bi-people me-2"></i>Cadastros Usuários
                            </a>
                            <a href="home.php?page=relatorios" class="nav-link text-white">
                                <i class="bi bi-graph-up me-2"></i>Relatórios
                            </a>
                        </nav>

                        <div class="mt-auto border-top pt-3">
                            <a href="./backend/logout.php" class="btn btn-danger w-100">Sair</a>
                        </div>
                    </div>
                </aside>
            <?php } ?>

            <main class="flex-grow-1 d-flex flex-column bg-light overflow-auto">

                <?php if ($is_admin_or_supervisor) { ?>
                    <header
                        class="d-lg-none d-flex align-items-center justify-content-between bg-white border-bottom shadow-sm p-3">
                        <h5 class="m-0">Sistema Metalma</h5>
                        <button class="btn btn-dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu"
                            aria-controls="sidebarMenu">
                            <i class="bi bi-list"></i> </button>
                    </header>
                <?php } ?>


                <?php
                // Roteador de páginas
                if (isset($_GET['page'])) {
                    $page = $_GET['page'];
                    // Proteção para evitar inclusão de arquivos indesejados
                    if (in_array($page, ['estoque', 'servicos', 'cadastro', 'dashboard', 'relatorios'])) {
                        include "pages/{$page}.php";
                    } else {
                        include 'pages/dashboard.php'; // Página padrão
                    }
                } else {
                    include 'pages/dashboard.php'; // Página inicial
                }
                ?>

            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q"
        crossorigin="anonymous"></script>
</body>

</html>