<?php
// Carrega dados necessários (já validou sessão no home.php)
include 'backend/servicos/listarPorSituacao.php'; // $PENDENTES_JSON, $ANDAMENTO_JSON, $ENCERRADAS_JSON
include 'backend/cadastro/usuarios.php';          // $USERS_JSON
include 'backend/servicos/listar.php';            // $tiposDeServico
?>

<!-- Script de carregamento das OS -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    renderServicos(<?= $USERS_JSON ?? "[]" ?>);
    iniciarListasOS(
        <?= $PENDENTES_JSON ?? "[]" ?>,
        <?= $ANDAMENTO_JSON ?? "[]" ?>,
        <?= $ENCERRADAS_JSON ?? "[]" ?>
    );
});
</script>

<!-- Header -->
<header class="d-flex justify-content-between align-items-center border-bottom shadow-sm py-3 px-4">
    <div>
        <h4 class="h4 m-0 text-dark">Acompanhamento</h4>
        <small class="text-muted">matrícula: <?= htmlspecialchars($_SESSION['matricula']) ?></small>
    </div>
    <a href="backend/logout.php" class="btn btn-danger">Sair</a>
</header>

<!-- Área de conteúdo -->
<div class="flex-grow-1 p-4 overflow-auto">

    <?php if (isset($_SESSION['mensagem'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['mensagem']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['mensagem']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['erro'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['erro']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php unset($_SESSION['erro']); ?>
    <?php endif; ?>

    <?php if ($_SESSION['nivel'] === 2 || $_SESSION['nivel'] === 3): ?>
        <button type="button" class="btn btn-success mb-2" data-bs-toggle="modal" data-bs-target="#modalNovaOS">
            + Nova Ordem de Serviço
        </button>
    <?php endif; ?>

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

<!-- Modal Nova Ordem de Serviço -->
<div class="modal fade" id="modalNovaOS" tabindex="-1" aria-labelledby="modalNovaOSLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalNovaOSLabel">Nova Ordem de Serviço</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body">
                <form id="formNovaOS" action="backend/servicos/CadastrarOS.php" method="POST">

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
                            <input type="text" class="form-control" id="nome_cliente" name="nome_cliente" placeholder="(Opcional)">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="numero_cliente" class="form-label">Contato do Cliente</label>
                            <input type="text" class="form-control" id="numero_cliente" name="numero_cliente" placeholder="(Opcional)">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="data_programada_inicio" class="form-label">Data Programada para Início</label>
                        <input type="date" class="form-control" id="data_programada_inicio" name="data_programada_inicio">
                    </div>

                    <div class="mb-3">
                        <label for="data_programada" class="form-label">Data Programada para Conclusão</label>
                        <input type="date" class="form-control" id="data_programada" name="data_programada">
                    </div>

                    <div class="col-12 col-lg-6">
                        <label class="form-label">Responsáveis Gerais (multi)</label>
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

<!-- Scripts -->
<script src="assets/js/tabelaOS.js"></script>
