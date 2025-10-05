<?php
include 'backend/estoque/listar.php'; // define $ESTOQUE_JSON
?>

<!-- Script para renderizar a tabela ao carregar -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    renderEstoque(<?= $ESTOQUE_JSON ?>);
});
</script>

<!-- Conteúdo Principal -->
<main class="flex-grow-1 d-flex flex-column bg-white">

    <!-- Header -->
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
                <!-- espaço reservado para filtros/opções -->
            </li>
        </ul>
    </div>

    <!-- Tabela Estoque -->
    <div class="px-4 pt-3">
        <div class="card shadow-sm rounded-3">
            <div class="card-header d-flex justify-content-between align-items-center bg-light">
                <h2 id="titulo-card" class="h5 m-0 text-dark">—</h2>
                <span class="text-muted small" id="total-label">—</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tabela-estoque">
                    <thead class="table-light text-uppercase" id="thead-estoque"></thead>
                    <tbody id="tbody-estoque">
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

<!-- Scripts específicos da página -->
<script src="assets/js/estoques.js"></script>
