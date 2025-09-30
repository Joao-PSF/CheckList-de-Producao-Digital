<?php
// Iniciar sessão
session_start();

// Verificar se o usuário está logado
if (empty($_SESSION['logado'])) {
    header("Location: index.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema Metalma</title>
    <script src="js/novoservico.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
</head>

<body>

    <div class="container-fluid p-0">

        <div class="d-flex vh-100">

            <?php if ($_SESSION['nivel'] === 2 || $_SESSION['nivel'] === 3) { ?>

                <!-- Sidebar -->
                <aside class="bg-dark text-light d-flex flex-column p-3" style="width:250px;">
                    <div class="mb-4 border-bottom pb-3">
                        <h3 class="h5 mb-1">Sistema Metalma</h3>
                        <p class="small text-secondary mb-0">Olá, <?php echo htmlspecialchars($_SESSION['nome']); ?>!</p>
                    </div>

                    <nav class="nav nav-pills flex-column mb-auto">
                        <a href="home.php?page=dasboard" class="nav-link active">Inicial</a>
                        <a href="home.php?page=estoque" class="nav-link text-light">Estoque</a>
                        <a href="home.php?page=servicos" class="nav-link text-light">Ordens de Serviços</a>
                        <a href="home.php?page=cadastro" class="nav-link text-light">Cadastros Usuários</a>
                        <a href="#" onclick="alert('Em desenvolvimento')" class="nav-link text-light">Relatórios</a>
                    </nav>

                    <div class="mt-auto border-top pt-3">
                        <a href="backend/logout.php" class="btn btn-danger w-100">Sair</a>
                    </div>
                </aside>
                <!-- /Sidebar -->

            <?php } ?>

            <!-- Conteúdo Principal -->
            <main class="flex-grow-1 d-flex flex-column bg-white">

                <?php

                    if (isset($_GET['page'])) {

                        switch ($_GET['page']) {
                            case 'estoque':
                                include 'pages/estoque.php';
                                break;
                            case 'servicos':
                                include 'pages/servicos.php';
                                break;
                            case 'cadastro':
                                include 'pages/cadastro.php';
                                break;
                            default:
                                include 'pages/dashboard.php';
                        }
                    } else {

                        include 'pages/dashboard.php';
                    }
                ?>

            </main>
            <!-- /Conteúdo Principal -->

        </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
</body>

</html>