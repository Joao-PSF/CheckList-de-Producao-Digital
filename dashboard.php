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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
</head>

<body>
    <div class="container-fluid p-0">
        <div class="d-flex vh-100">

            <!-- Sidebar -->
            <aside class="bg-dark text-light d-flex flex-column p-3" style="width:250px;">
                <div class="mb-4 border-bottom pb-3">
                    <h3 class="h5 mb-1">Sistema Metalma</h3>
                    <p class="small text-secondary mb-0">Olá, <?php echo htmlspecialchars($_SESSION['nome']); ?>!</p>
                </div>

                <nav class="nav nav-pills flex-column mb-auto">
                    <a href="dashboard.php" class="nav-link active">Inicial</a>
                    <a href="estoque.php" class="nav-link text-light">Estoque</a>
                    <a href="servicos.php" class="nav-link text-light">Ordens de Serviços</a>
                    <a href="cadastro.php" class="nav-link text-light">Cadastros</a>
                    <a href="#" onclick="alert('Em desenvolvimento')" class="nav-link text-light">Relatórios</a>
                </nav>

                <div class="mt-auto border-top pt-3">
                    <a href="backend/logout.php" class="btn btn-danger w-100">Sair</a>
                </div>
            </aside>
            <!-- /Sidebar -->

            <!-- Conteúdo Principal -->
            <main class="flex-grow-1 d-flex flex-column bg-white">

                <!-- Header -->
                <header class="d-flex justify-content-between align-items-center border-bottom shadow-sm py-3 px-4">
                    <h1 class="h3 m-0 text-dark">Página Inicial</h1>
                </header>

                <!-- Área de conteúdo -->
                <div class="flex-grow-1 p-4 overflow-auto">
                    <div class="card shadow-sm rounded-3 border-0">
                        <div class="card-body p-4 border-start border-4 border-primary">
                            <h2 class="h5 text-dark mb-3">Bem-vindo ao Sistema!</h2>
                            <p class="text-muted mb-2">Matrícula: <?php echo htmlspecialchars($_SESSION['matricula']); ?></p>
                            <p class="text-muted mb-0">Use o menu lateral para navegar pelas funcionalidades do sistema.</p>
                        </div>
                    </div>
                </div>
                <!-- /Área de conteúdo -->

            </main>
            <!-- /Conteúdo Principal -->

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
</body>
</html>
