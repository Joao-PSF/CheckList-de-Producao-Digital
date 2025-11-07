<?php
include 'backend/servicos/listar.php'; // $SERVICOS_JSON
include 'backend/conexao.php';

try {
    $stmtTipos = $conexao->query("SELECT id, tipo FROM servicos_tipos WHERE status = 'Ativo' ORDER BY tipo ASC");
    $tiposDeServico = $stmtTipos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $tiposDeServico = [];
}
?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    renderServicos(<?= $SERVICOS_JSON ?>);
});
</script>

<!-- Conteúdo Principal -->
<main class="flex-grow-1 d-flex flex-column bg-white">

    <!-- Header -->
    <header class="d-flex justify-content-between align-items-center border-bottom shadow-sm py-3 px-4">
        <h1 class="h3 m-0 text-dark">Ordens de Serviço</h1>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNovaOS">
            + Nova Ordem de Serviço
        </button>
    </header>

    <!-- Conteúdo -->
    <div class="px-4 pt-4">

        <div class="card shadow-sm rounded-3">
            <div class="card-header d-flex justify-content-between align-items-center bg-light">
                <h2 id="titulo-card" class="h5 m-0 text-dark">Ordens de Serviço</h2>
                <span class="text-muted small" id="total-label">—</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tabela-servicos">
                    <thead class="table-light text-uppercase" id="thead-servicos">
                        <tr>
                            <th class="small"># OS</th>
                            <th class="small">Tipo de Serviço</th>
                            <th class="small">Responsáveis</th>
                            <th class="small">Data Programada</th>
                            <th class="small">Situação</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-servicos">
                        <!-- preenchido pelo JS -->
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center align-items-center gap-3 bg-light border-top py-3" id="paginacao">
                <!-- preenchido pelo JS -->
            </div>
        </div>

    </div>
</main>

<!-- Modal Nova OS -->
<div class="modal fade" id="modalNovaOS" tabindex="-1" aria-labelledby="modalNovaOSLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalNovaOSLabel">Nova Ordem de Serviço</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body">
                <form id="formNovaOS" action="/./backend/servicos/CadastrarOS.php" method="POST">

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
                        <label for="data_programada_inicio" class="form-label">Data Programada para Inicio</label>
                        <input type="date" class="form-control" id="data_programada_inicio" name="data_programada_inicio">
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

<!-- Scripts específicos -->
<script src="assets/js/servicos.js"></script>
