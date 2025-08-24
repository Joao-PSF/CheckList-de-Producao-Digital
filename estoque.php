<?php
session_start();
if (empty($_SESSION['logado'])) {
    header("Location: index.php");
    exit;
}
include 'backend/estoque/listar.php'; // define $ESTOQUE_JSON
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estoque - Sistema Metalma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body onload='renderEstoque(<?php echo $ESTOQUE_JSON; ?>)'>
    <div class="container-fluid p-0">
        <div class="d-flex vh-100">

            <!-- Sidebar -->
            <aside class="bg-dark text-light d-flex flex-column p-3" style="width:250px;">
                <div class="mb-4 border-bottom pb-3">
                    <h3 class="h5 mb-1">Sistema Metalma</h3>
                    <p class="small text-secondary mb-0">
                        Olá, <?php echo htmlspecialchars($_SESSION['nome'] ?? '', ENT_QUOTES, 'UTF-8'); ?>!
                    </p>
                </div>

                <nav class="nav nav-pills flex-column mb-auto">
                    <a href="dashboard.php" class="nav-link text-light">Inicial</a>
                    <a href="estoque.php" class="nav-link active">Estoque</a>
                    <a href="#" onclick="alert('Em desenvolvimento')" class="nav-link text-light">Ordens de Serviços</a>
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
                <header class="d-flex justify-content-between align-items-center border-bottom shadow-sm py-3 px-4">
                    <h1 class="h3 m-0 text-dark">Controle de Estoque</h1>
                    <button type="button" class="btn btn-success" onclick="alert('Funcionalidade em desenvolvimento')">
                        + Nova Movimentação
                    </button>
                </header>

                <!-- Abas -->
                <div class="px-4 pt-3">
                    <ul class="nav nav-pills bg-light p-2 rounded-3 shadow-sm">
                        <li class="nav-item">
                            <a id="tab-saldo" class="nav-link" href="?aba=saldo">Saldo</a>
                        </li>
                        <li class="nav-item">
                            <a id="tab-mov" class="nav-link" href="?aba=movimentacoes">Movimentações</a>
                        </li>
                        <li class="nav-item ms-auto">
                        </li>
                    </ul>
                </div>

                <!-- Área de conteúdo -->
                <div class="flex-grow-1 p-4 overflow-auto">
                    <div class="card shadow-sm rounded-3">
                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                            <h2 id="titulo-card" class="h5 m-0 text-dark">—</h2>
                            <span class="text-muted small" id="total-label">—</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="tabela-estoque">
                                <thead class="table-light text-uppercase" id="thead-estoque"></thead>
                                <tbody id="tbody-estoque"><!-- preenchido pelo JS --></tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center align-items-center gap-3 bg-light border-top py-3" id="paginacao">
                            <!-- preenchido pelo JS -->
                        </div>
                    </div>
                </div>
            </main>
            <!-- /Conteúdo Principal -->

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/estoques.js"></script>
</body>
</html>
