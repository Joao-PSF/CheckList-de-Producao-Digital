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

            <?php if ($_SESSION['nivel'] === 2 || $_SESSION['nivel'] === 3) { ?>
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
                </aside>
                <!-- /Sidebar -->
            <?php } ?>

            <!-- Conteúdo Principal -->
            <main class="flex-grow-1 d-flex flex-column bg-white">

                <!-- Header -->
                <header class="d-flex justify-content-between align-items-center border-bottom shadow-sm py-3 px-4">
                    <div>
                        <h4 class="h4 m-0 text-dark">Acompanhamento</h4>
                        <small class="text-muted">matrícula: <?php echo htmlspecialchars($_SESSION['matricula']); ?></small>
                    </div>
                    <a href="backend/logout.php" class="btn btn-danger">Sair</a>
                </header>

                <!-- Área de conteúdo -->
                <div class="flex-grow-1 p-4 overflow-auto">

                    <!-- OS em Andamento -->
                    <div class="card shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-info text-white fw-bold">
                            Ordens de Serviço em Andamento
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-uppercase">
                                    <tr>
                                        <th class="small">Nº OS</th>
                                        <th class="small">Etapa</th>
                                        <th class="small">Previsão</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>005</td>
                                        <td>João</td>
                                        <td>05/09/2025</td>
                                    </tr>
                                    <tr>
                                        <td>006</td>
                                        <td>Maria</td>
                                        <td>07/09/2025</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- OS Pendentes -->
                    <div class="card shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-warning text-dark fw-bold">
                            Ordens de Serviço Pendentes
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-uppercase">
                                    <tr>
                                        <th class="small">Nº OS</th>
                                        <th class="small">Etapa</th>
                                        <th class="small">Data de Geração</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>001</td>
                                        <td>Empresa X</td>
                                        <td>01/09/2025</td>
                                    </tr>
                                    <tr>
                                        <td>002</td>
                                        <td>Cliente Y</td>
                                        <td>02/09/2025</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- OS Encerradas -->
                    <div class="card shadow-sm rounded-3">
                        <div class="card-header bg-success text-white fw-bold">
                            Ordens de Serviço Encerradas
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-uppercase">
                                    <tr>
                                        <th class="small">Nº OS</th>
                                        <th class="small">Etapa</th>
                                        <th class="small">Data Encerramento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>010</td>
                                        <td>Etapa1</td>
                                        <td>28/08/2025</td>
                                    </tr>
                                    <tr>
                                        <td>011</td>
                                        <td>Etapa1</td>
                                        <td>30/08/2025</td>
                                    </tr>
                                </tbody>
                            </table>
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