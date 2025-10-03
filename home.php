<?php
// Iniciar sessão
session_start();

// Verificar se o usuário está logado
if (empty($_SESSION['logado'])) {
    header("Location: index.php");
    exit();
}

$is_admin_or_supervisor = isset($_SESSION['nivel']) && in_array($_SESSION['nivel'], [2, 3]);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema Metalma</title>
    <script src="js/novoservico.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>

    <div class="container-fluid p-0">

        <div class="d-flex vh-100">

            <?php if ($is_admin_or_supervisor) { ?>

                <aside class="bg-dark text-light d-flex flex-column p-3 offcanvas-lg offcanvas-start" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel" style="width:250px;">
                    
                    <div class="offcanvas-header">
                        <h5 class="offcanvas-title" id="sidebarMenuLabel">Sistema Metalma</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Close"></button>
                    </div>

                    <div class="offcanvas-body d-flex flex-column p-0">
                        <div class="mb-4 border-bottom pb-3">
                            <h3 class="h5 mb-1">Sistema Metalma</h3>
                            <p class="small text-secondary mb-0">Olá, <?php echo htmlspecialchars($_SESSION['nome']); ?>!</p>
                        </div>

                        <nav class="nav nav-pills flex-column mb-auto">
                            <a href="home.php?page=dashboard" class="nav-link text-light">Inicial</a>
                            <a href="home.php?page=estoque" class="nav-link text-light">Estoque</a>
                            <a href="home.php?page=servicos" class="nav-link text-light">Ordens de Serviços</a>
                            <a href="home.php?page=cadastro" class="nav-link text-light">Cadastros Usuários</a>
                            <a href="#" onclick="alert('Em desenvolvimento')" class="nav-link text-light">Relatórios</a>
                        </nav>

                        <div class="mt-auto border-top pt-3">
                            <a href="backend/logout.php" class="btn btn-danger w-100">Sair</a>
                        </div>
                    </div>
                </aside>
                <?php } ?>

            <main class="flex-grow-1 d-flex flex-column bg-light">

                <?php if ($is_admin_or_supervisor) { ?>
                    <header class="d-lg-none d-flex align-items-center justify-content-between bg-white border-bottom shadow-sm p-3">
                        <h5 class="m-0">Sistema Metalma</h5>
                        <button class="btn btn-dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
                            <i class="bi bi-list"></i> </button>
                    </header>
                <?php } ?>


                <?php
                    // Roteador de páginas
                    if (isset($_GET['page'])) {
                        $page = $_GET['page'];
                        // Proteção para evitar inclusão de arquivos indesejados
                        if (in_array($page, ['estoque', 'servicos', 'cadastro', 'dashboard'])) {
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
</body>

</html>