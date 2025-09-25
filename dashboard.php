<?php
// Iniciar sessão
session_start();

// Verificar se o usuário está logado
if (empty($_SESSION['logado'])) {
    header("Location: index.php");
    exit();
}

include 'backend/servicos/listarPorSituacao.php'; // define $SERVICOS_JSON
include 'backend/cadastro/usuarios.php'; // define $USERS_JSON
include 'backend/cadastro/usuarios.php';
include 'backend/servicos/listar.php';

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

<body onload='renderServicos(<?php echo $USERS_JSON ?? "[]"; ?>);
  iniciarListasOS(
    <?php echo $PENDENTES_JSON  ?? "[]"; ?>,
    <?php echo $ANDAMENTO_JSON  ?? "[]"; ?>,
    <?php echo $ENCERRADAS_JSON ?? "[]"; ?>
  );'>
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
                    <?php if ($_SESSION['nivel'] === 2 || $_SESSION['nivel'] === 3) { ?>
                        <button type="button" class="btn btn-success mb-2" data-bs-toggle="modal" data-bs-target="#modalNovaOS">
                            + Nova Ordem de Serviço
                        </button>
                    <?php } ?>
                    <!-- OS Pendentes -->
                    <div class="card shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-danger text-white fw-bold">
                            Ordens de Serviço Pendentes
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-uppercase">
                                    <tr>
                                        <th class="small">Nº OS</th>
                                        <th class="small">Próxima Etapa</th>
                                        <th class="small">Data de Geração</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-os-pendentes"></tbody>
                            </table>
                        </div>
                        <div id="paginacao-os-pendentes" class="d-flex justify-content-between align-items-center p-2 border-top small"></div>
                    </div>

                    <!-- OS em Andamento -->
                    <div class="card shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-warning text-dark fw-bold">
                            Ordens de Serviço em Andamento
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-uppercase">
                                    <tr>
                                        <th class="small">Nº OS</th>
                                        <th class="small">Etapa Atual</th>
                                        <th class="small">Previsão</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-os-andamento"></tbody>
                            </table>
                        </div>
                        <div id="paginacao-os-andamento" class="d-flex justify-content-between align-items-center p-2 border-top small"></div>
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
                                        <th class="small">Última Etapa</th>
                                        <th class="small">Data Encerramento</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-os-encerradas"></tbody>
                            </table>
                        </div>
                        <div id="paginacao-os-encerradas" class="d-flex justify-content-between align-items-center p-2 border-top small"></div>
                    </div>

                </div>
                <!-- /Área de conteúdo -->

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
                            <label for="data_programada_inicio" class="form-label">Data Programada para Inicio</label>
                            <input type="date" class="form-control" id="data_programada_inicio" name="data_programada_inicio">
                        </div>

                        <div class="mb-3">
                            <label for="data_programada" class="form-label">Data Programada para Conclusão</label>
                            <input type="date" class="form-control" id="data_programada" name="data_programada">
                        </div>

                        <div class="col-12 col-lg-6">
                            <label class="form-label">Responsáveis Gerais (multi)</label>
                            <!-- componente multiseleção -->
                            <div id="responsavel_geral_ms"></div>
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


    <script src="js/tabelaOS.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
</body>

</html>