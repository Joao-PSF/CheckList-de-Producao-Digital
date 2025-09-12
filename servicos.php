<?php
session_start();
if (empty($_SESSION['logado'])) {
    header("Location: index.php");
    exit;
}
include 'backend/servicos/listar.php'; // define $SERVICOS_JSON

try {
    $stmtTipos = $conexao->query("SELECT id, tipo FROM servicos_tipos WHERE status = 'Ativo' ORDER BY tipo ASC");
    $tiposDeServico = $stmtTipos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $tiposDeServico = []; // Em caso de erro, o select ficará vazio
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordens de Serviço - Sistema Metalma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body onload='renderServicos(<?php echo $SERVICOS_JSON; ?>)'>
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
                    <a href="estoque.php" class="nav-link text-light">Estoque</a>
                    <a href="servicos.php" class="nav-link active">Ordens de Serviços</a>
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
                    <h1 class="h3 m-0 text-dark">Ordens de Serviço</h1>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNovaOS">
                        + Nova Ordem de Serviço
                    </button>
                </header>

                <!-- Área de conteúdo -->
                <div class="flex-grow-1 p-4 overflow-auto">
                    <div class="card shadow-sm rounded-3">
                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                            <h2 id="titulo-card" class="h5 m-0 text-dark">Ordens de Serviço</h2>
                            <span class="text-muted small" id="total-label">—</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="tabela-servicos">
                                <thead class="table-light text-uppercase" id="thead-servicos"></thead>
                                <tbody id="tbody-servicos"><!-- preenchido pelo JS --></tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center align-items-center gap-3 bg-light border-top py-3"
                            id="paginacao">
                            <!-- preenchido pelo JS -->
                        </div>
                    </div>
                </div>
            </main>
            <!-- /Conteúdo Principal -->

        </div>
    </div>

    <div class="modal fade" id="modalNovaOS" tabindex="-1" aria-labelledby="modalNovaOSLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">

                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalNovaOSLabel">Nova Ordem de Serviço</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Fechar"></button>
                </div>

                <div class="modal-body">
                    <form id="formNovaOS" action="backend/servicos/cadastrar.php" method="POST">

                        <div class="mb-3">
                            <label for="servico_tipo_id" class="form-label">Tipo de Serviço</label>
                            <select class="form-select" id="servico_tipo_id" name="servico_tipo_id" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($tiposDeServico as $tipo): ?>
                                    <option value="<?= (int) $tipo['id'] ?>">
                                        <?= htmlspecialchars($tipo['tipo'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nome_cliente" class="form-label">Nome do Cliente</label>
                                <input type="text" class="form-control" id="nome_cliente" name="nome_cliente"
                                    placeholder="(Opcional)">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="numero_cliente" class="form-label">Contato do Cliente</label>
                                <input type="text" class="form-control" id="numero_cliente" name="numero_cliente"
                                    placeholder="(Opcional)">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="data_programada" class="form-label">Data Programada para Conclusão</label>
                            <input type="date" class="form-control" id="data_programada" name="data_programada">
                        </div>

                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formNovaOS" class="btn btn-success">Salvar Ordem de Serviço</button>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/servicos.js"></script>
</body>

</html>