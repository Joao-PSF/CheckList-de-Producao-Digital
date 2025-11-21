<?php

// Verificar se está logado
if (empty($_SESSION['logado'])) {
    header("Location: ../index.php");
    exit();
}

include_once __DIR__ . '/../backend/servicos/RelatoriosServicos.php';
include_once __DIR__ . '/../backend/conexao.php';

// Processar filtros de serviços
$filtrosServicos = [];
if (isset($_GET['filtro_servicos'])) {
    if (!empty($_GET['situacao'])) $filtrosServicos['situacao'] = $_GET['situacao'];
    if (!empty($_GET['tipo_servico'])) $filtrosServicos['tipo_servico'] = $_GET['tipo_servico'];
    if (!empty($_GET['data_inicio'])) $filtrosServicos['data_inicio'] = $_GET['data_inicio'];
    if (!empty($_GET['data_fim'])) $filtrosServicos['data_fim'] = $_GET['data_fim'];
    if (!empty($_GET['ordenacao'])) $filtrosServicos['ordenacao'] = $_GET['ordenacao'];
}

// Processar filtros de logs
$filtrosLogs = [];
$tipoLog = $_GET['tipo_log'] ?? 'servicos';
if (isset($_GET['filtro_logs'])) {
    if (!empty($_GET['acao'])) $filtrosLogs['acao'] = $_GET['acao'];
    if (!empty($_GET['status'])) $filtrosLogs['status'] = $_GET['status'];
    if (!empty($_GET['data_inicio_log'])) $filtrosLogs['data_inicio'] = $_GET['data_inicio_log'];
    if (!empty($_GET['data_fim_log'])) $filtrosLogs['data_fim'] = $_GET['data_fim_log'];
    if (!empty($_GET['usuario_matricula'])) $filtrosLogs['usuario_matricula'] = $_GET['usuario_matricula'];
}

// Determinar aba ativa
$abaAtiva = 'servicos';
if (isset($_GET['filtro_logs']) || isset($_GET['aba'])) {
    $abaAtiva = $_GET['aba'] ?? 'logs';
}

// Obter dados
$estatisticas = obterEstatisticasOS($conexao);
$osComEtapas = obterOSComEtapas($conexao, $filtrosServicos);
$mediaTempo = obterMediaTempoPorTipo($conexao);
$tiposServico = obterTiposServico($conexao);
$estatisticasLogin = obterEstatisticasLogin($conexao);
$historicoLogin = obterHistoricoLogin($conexao);

// Obter logs conforme tipo selecionado
switch ($tipoLog) {
    case 'cadastro':
        $logs = obterLogsCadastro($conexao, $filtrosLogs);
        break;
    case 'login':
        $logs = obterLogsLogin($conexao, $filtrosLogs);
        break;
    case 'todos':
        $logs = obterTodosLogs($conexao, $filtrosLogs);
        break;
    default:
        $logs = obterLogsServicos($conexao, $filtrosLogs);
}
?>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<style>
        @media print {
            .no-print {
                display: none !important;
            }

            .sidebar {
                display: none !important;
            }

            .navbar {
                display: none !important;
            }

            body {
                font-size: 12px;
            }
        }

        .stat-card {
            border-left: 4px solid;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card.total {
            border-left-color: #0d6efd;
        }

        .stat-card.abertas {
            border-left-color: #ffc107;
        }

        .stat-card.atrasadas {
            border-left-color: #dc3545;
        }

        .stat-card.encerradas {
            border-left-color: #198754;
        }

        .badge-atrasada {
            background-color: #dc3545;
        }

        .badge-no-prazo {
            background-color: #198754;
        }

        .toggle-table {
            transition: transform 0.3s ease;
        }

        .toggle-table:hover {
            transform: scale(1.1);
        }

        .collapse {
            transition: height 0.35s ease;
        }

        /* Responsividade adicional */
        @media (max-width: 768px) {
            .stat-card .card-body {
                padding: 1rem;
            }

            .stat-card h2 {
                font-size: 1.5rem;
            }

            .stat-card .fs-1 {
                font-size: 2rem !important;
            }

            .table-responsive {
                font-size: 0.85rem;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                max-width: 100%;
            }

            .table-responsive table {
                min-width: 800px;
                width: max-content;
            }

            .nav-tabs .nav-link {
                font-size: 0.9rem;
                padding: 0.5rem 0.75rem;
            }
        }

        @media (max-width: 576px) {
            .stat-card h2 {
                font-size: 1.25rem;
            }

            .stat-card h6 {
                font-size: 0.75rem;
            }

            .stat-card .fs-1 {
                font-size: 1.5rem !important;
            }

            .table-responsive table {
                min-width: 900px;
            }
        }

        /* Scrollbar personalizado para tabelas */
        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Indicador visual de scroll */
        .table-container {
            position: relative;
            width: 100%;
            overflow: visible;
        }
    </style>

    <div class="container-fluid px-2 px-md-4 py-3 py-md-4" style="max-width: 100%; overflow-x: hidden;">

        <!-- Cabeçalho -->
        <div class="row mb-3 mb-md-4 no-print">
            <div class="col-12">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 gap-md-0">
                    <div>
                        <h2 class="h3 h2-md mb-1"><i class="bi bi-file-earmark-text"></i> Relatórios do Sistema</h2>
                    </div>
                    <button class="btn btn-primary btn-sm btn-md-md" onclick="imprimirRelatorio()">
                        <i class="bi bi-printer"></i> <span class="d-none d-sm-inline">Imprimir</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Navegação entre Serviços e Logs -->
        <ul class="nav nav-tabs mb-4 no-print" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $abaAtiva === 'servicos' ? 'active' : '' ?>" data-tab="servicos" type="button">
                    <i class="bi bi-clipboard-check"></i> Serviços
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $abaAtiva === 'logs' ? 'active' : '' ?>" data-tab="logs" type="button">
                    <i class="bi bi-journal-text"></i> Logs do Sistema
                </button>
            </li>
        </ul>

        <!-- Conteúdo das Abas -->
        <div class="tab-content">

            <!-- ============ ABA SERVIÇOS ============ -->
            <div class="tab-pane fade <?= $abaAtiva === 'servicos' ? 'show active' : '' ?>" data-tab-content="servicos">

                <!-- Estatísticas Gerais -->
                <div class="row g-2 g-md-3 mb-3 mb-md-4">
                    <div class="col-6 col-md-3">
                        <div class="card stat-card total shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1 small">Total de OS</h6>
                                        <h2 class="mb-0"><?= number_format($estatisticas['total_os']) ?></h2>
                                    </div>
                                    <i class="bi bi-clipboard-data fs-1 text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card stat-card abertas shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1 small">OS Abertas</h6>
                                        <h2 class="mb-0"><?= number_format($estatisticas['abertas']) ?></h2>
                                    </div>
                                    <i class="bi bi-hourglass-split fs-1 text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card stat-card atrasadas shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1 small">OS em Atraso</h6>
                                        <h2 class="mb-0"><?= number_format($estatisticas['atrasadas']) ?></h2>
                                    </div>
                                    <i class="bi bi-exclamation-triangle fs-1 text-danger"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card stat-card encerradas shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1 small">OS Encerradas</h6>
                                        <h2 class="mb-0"><?= number_format($estatisticas['encerradas']) ?></h2>
                                    </div>
                                    <i class="bi bi-check-circle fs-1 text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card mb-3 mb-md-4 no-print">
                    <div class="card-header">
                        <h5 class="mb-0 h6 h5-md"><i class="bi bi-funnel"></i> Filtros</h5>
                    </div>
                    <div class="card-body">
                        <form id="formFiltroServicos" method="GET">
                            <input type="hidden" name="page" value="relatorios">
                            <input type="hidden" name="filtro_servicos" value="1">
                            <div class="row g-2 g-md-3">
                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="form-label small">Situação</label>
                                    <select name="situacao" class="form-select form-select-sm">
                                        <option value="">Todas</option>
                                        <option value="PENDENTE" <?= ($filtrosServicos['situacao'] ?? '') === 'PENDENTE' ? 'selected' : '' ?>>Pendente</option>
                                        <option value="ANDAMENTO" <?= ($filtrosServicos['situacao'] ?? '') === 'ANDAMENTO' ? 'selected' : '' ?>>Em Andamento</option>
                                        <option value="ENCERRADA" <?= ($filtrosServicos['situacao'] ?? '') === 'ENCERRADA' ? 'selected' : '' ?>>Encerrada</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="form-label small">Tipo de Serviço</label>
                                    <select name="tipo_servico" class="form-select form-select-sm">
                                        <option value="">Todos</option>
                                        <?php foreach ($tiposServico as $tipo): ?>
                                            <option value="<?= $tipo['id'] ?>" <?= ($filtrosServicos['tipo_servico'] ?? '') == $tipo['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($tipo['tipo']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label small">Data Início</label>
                                    <input type="date" name="data_inicio" class="form-control form-control-sm" value="<?= $filtrosServicos['data_inicio'] ?? '' ?>">
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label small">Data Fim</label>
                                    <input type="date" name="data_fim" class="form-control form-control-sm" value="<?= $filtrosServicos['data_fim'] ?? '' ?>">
                                </div>
                                <div class="col-12 col-md-4 col-lg-2">
                                    <label class="form-label small">Ordenação</label>
                                    <select name="ordenacao" class="form-select form-select-sm">
                                        <option value="">Padrão</option>
                                        <option value="mais_atrasadas" <?= ($filtrosServicos['ordenacao'] ?? '') === 'mais_atrasadas' ? 'selected' : '' ?>>Mais Atrasadas</option>
                                        <option value="mais_recentes" <?= ($filtrosServicos['ordenacao'] ?? '') === 'mais_recentes' ? 'selected' : '' ?>>Mais Recentes</option>
                                        <option value="mais_antigas" <?= ($filtrosServicos['ordenacao'] ?? '') === 'mais_antigas' ? 'selected' : '' ?>>Mais Antigas</option>
                                        <option value="data_programada" <?= ($filtrosServicos['ordenacao'] ?? '') === 'data_programada' ? 'selected' : '' ?>>Data Programada</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="bi bi-search"></i> Aplicar
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-sm" onclick="limparFiltros('formFiltroServicos')">
                                            <i class="bi bi-x-circle"></i> Limpar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Lista de OS por Etapas -->
                <div class="card mb-3 mb-md-4">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 h6 h5-md"><i class="bi bi-list-check"></i> <span class="d-none d-sm-inline">Ordens de Serviço por Etapas</span><span class="d-inline d-sm-none">OS por Etapas</span></h5>
                            <button class="btn btn-sm btn-outline-secondary toggle-table" data-target="tabelaOSEtapas">
                                <i class="bi bi-chevron-up"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body collapse show" id="tabelaOSEtapas">
                        <div class="table-container position-relative">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Cliente</th>
                                            <th>Tipo</th>
                                            <th>Criada em</th>
                                            <th>Data Programada</th>
                                            <th>Situação</th>
                                            <th>Etapas</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                <tbody>
                                    <?php if (empty($osComEtapas)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                                Nenhuma OS encontrada com os filtros aplicados
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($osComEtapas as $os): ?>
                                            <tr>
                                                <td><strong>#<?= $os['id'] ?></strong></td>
                                                <td>
                                                    <?= htmlspecialchars($os['nome_cliente']) ?><br>
                                                    <small class="text-muted"><?= htmlspecialchars($os['numero_cliente']) ?></small>
                                                </td>
                                                <td><?= htmlspecialchars($os['tipo_servico']) ?></td>
                                                <td><?= date('d/m/Y', strtotime($os['criado_em'])) ?></td>
                                                <td>
                                                    <?php if ($os['data_programada']): ?>
                                                        <?= date('d/m/Y', strtotime($os['data_programada'])) ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $badgeClass = 'secondary';
                                                    switch ($os['situacao']) {
                                                        case 'PENDENTE':
                                                            $badgeClass = 'warning';
                                                            break;
                                                        case 'ANDAMENTO':
                                                            $badgeClass = 'info';
                                                            break;
                                                        case 'ENCERRADA':
                                                            $badgeClass = 'success';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge bg-<?= $badgeClass ?>"><?= $os['situacao'] ?></span>
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <?php
                                                        $percentual = $os['total_etapas'] > 0
                                                            ? round(($os['etapas_concluidas'] / $os['total_etapas']) * 100)
                                                            : 0;
                                                        ?>
                                                        <div class="progress-bar" role="progressbar"
                                                            style="width: <?= $percentual ?>%"
                                                            aria-valuenow="<?= $percentual ?>"
                                                            aria-valuemin="0"
                                                            aria-valuemax="100">
                                                            <?= $os['etapas_concluidas'] ?>/<?= $os['total_etapas'] ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if ($os['atrasada']): ?>
                                                        <span class="badge badge-atrasada">
                                                            <i class="bi bi-exclamation-circle"></i> Atrasada
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge badge-no-prazo">
                                                            <i class="bi bi-check-circle"></i> No Prazo
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        </div>
                    </div>
                </div>

                <!-- Média de Tempo por Tipo -->
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 h6 h5-md"><i class="bi bi-clock-history"></i> <span class="d-none d-sm-inline">Média de Tempo por Tipo de Serviço</span><span class="d-inline d-sm-none">Média de Tempo</span></h5>
                            <button class="btn btn-sm btn-outline-secondary toggle-table" data-target="tabelaMediaTempo">
                                <i class="bi bi-chevron-up"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body collapse show" id="tabelaMediaTempo">
                        <div class="table-container position-relative">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tipo de Serviço</th>
                                            <th>Total de OS</th>
                                            <th>Média de Dias</th>
                                            <th>Mínimo de Dias</th>
                                            <th>Máximo de Dias</th>
                                        </tr>
                                    </thead>
                                <tbody>
                                    <?php if (empty($mediaTempo)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                Nenhum dado disponível
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($mediaTempo as $tipo): ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($tipo['tipo_servico']) ?></strong></td>
                                                <td><?= number_format($tipo['total_os']) ?></td>
                                                <td><?= number_format($tipo['media_dias'], 1) ?> dias</td>
                                                <td><?= number_format($tipo['min_dias']) ?> dias</td>
                                                <td><?= number_format($tipo['max_dias']) ?> dias</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ============ ABA LOGS ============ -->
            <div class="tab-pane fade <?= $abaAtiva === 'logs' ? 'show active' : '' ?>" data-tab-content="logs">

                <!-- Filtros de Logs -->
                <div class="card mb-3 mb-md-4 no-print">
                    <div class="card-header">
                        <h5 class="mb-0 h6 h5-md"><i class="bi bi-funnel"></i> Filtros de Logs</h5>
                    </div>
                    <div class="card-body">
                        <form id="formFiltroLogs" method="GET">
                            <input type="hidden" name="page" value="relatorios">
                            <input type="hidden" name="filtro_logs" value="1">
                            <input type="hidden" name="aba" value="logs">
                            <div class="row g-2 g-md-3">
                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="form-label small">Tipo de Log</label>
                                    <select name="tipo_log" class="form-select form-select-sm">
                                        <option value="todos" <?= $tipoLog === 'todos' ? 'selected' : '' ?>>Todos</option>
                                        <option value="servicos" <?= $tipoLog === 'servicos' ? 'selected' : '' ?>>Serviços</option>
                                        <option value="cadastro" <?= $tipoLog === 'cadastro' ? 'selected' : '' ?>>Cadastros</option>
                                        <option value="login" <?= $tipoLog === 'login' ? 'selected' : '' ?>>Login/Logout</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-6 col-lg-2">
                                    <label class="form-label small">Status</label>
                                    <select name="status" class="form-select form-select-sm">
                                        <option value="">Todos</option>
                                        <option value="sucesso" <?= ($filtrosLogs['status'] ?? '') === 'sucesso' ? 'selected' : '' ?>>Sucesso</option>
                                        <option value="falha" <?= ($filtrosLogs['status'] ?? '') === 'falha' ? 'selected' : '' ?>>Falha</option>
                                        <option value="erro" <?= ($filtrosLogs['status'] ?? '') === 'erro' ? 'selected' : '' ?>>Erro</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label small">Data Início</label>
                                    <input type="date" name="data_inicio_log" class="form-control form-control-sm" value="<?= $filtrosLogs['data_inicio'] ?? '' ?>">
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label small">Data Fim</label>
                                    <input type="date" name="data_fim_log" class="form-control form-control-sm" value="<?= $filtrosLogs['data_fim'] ?? '' ?>">
                                </div>
                                <div class="col-6 col-md-4 col-lg-3">
                                    <label class="form-label small">Matrícula</label>
                                    <input type="text" name="usuario_matricula" class="form-control form-control-sm" value="<?= $filtrosLogs['usuario_matricula'] ?? '' ?>" placeholder="Ex: 1234">
                                </div>
                                <div class="col-12">
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="bi bi-search"></i> Aplicar
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-sm" onclick="limparFiltros('formFiltroLogs')">
                                            <i class="bi bi-x-circle"></i> Limpar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Gráfico de Estatísticas de Login -->
                <div class="card mb-3 mb-md-4">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 h6 h5-md">
                                <i class="bi bi-bar-chart-fill"></i> 
                                <span class="d-none d-sm-inline">Estatísticas de Tentativas de Login</span>
                                <span class="d-inline d-sm-none">Estatísticas Login</span>
                            </h5>
                            <button class="btn btn-sm btn-outline-secondary toggle-table" data-target="graficoLogin">
                                <i class="bi bi-chevron-up"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body collapse show" id="graficoLogin">
                        <div class="row g-3">
                            <!-- Cards de Resumo -->
                            <div class="col-6 col-md-3">
                                <div class="card border-primary h-100">
                                    <div class="card-body text-center">
                                        <i class="bi bi-arrow-repeat fs-1 text-primary mb-2"></i>
                                        <h6 class="text-muted mb-1 small">Total de Tentativas</h6>
                                        <h3 class="mb-0 text-primary"><?= number_format($estatisticasLogin['total_tentativas'] ?? 0) ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="card border-success h-100">
                                    <div class="card-body text-center">
                                        <i class="bi bi-check-circle-fill fs-1 text-success mb-2"></i>
                                        <h6 class="text-muted mb-1 small">Sucesso</h6>
                                        <h3 class="mb-0 text-success"><?= number_format($estatisticasLogin['login_sucesso'] ?? 0) ?></h3>
                                        <?php 
                                        $totalTentativas = $estatisticasLogin['total_tentativas'] ?? 1;
                                        $percentualSucesso = $totalTentativas > 0 ? round(($estatisticasLogin['login_sucesso'] / $totalTentativas) * 100, 1) : 0;
                                        ?>
                                        <small class="text-muted"><?= $percentualSucesso ?>%</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="card border-warning h-100">
                                    <div class="card-body text-center">
                                        <i class="bi bi-exclamation-circle-fill fs-1 text-warning mb-2"></i>
                                        <h6 class="text-muted mb-1 small">Falhas</h6>
                                        <h3 class="mb-0 text-warning"><?= number_format($estatisticasLogin['login_falha'] ?? 0) ?></h3>
                                        <?php 
                                        $percentualFalha = $totalTentativas > 0 ? round(($estatisticasLogin['login_falha'] / $totalTentativas) * 100, 1) : 0;
                                        ?>
                                        <small class="text-muted"><?= $percentualFalha ?>%</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="card border-danger h-100">
                                    <div class="card-body text-center">
                                        <i class="bi bi-x-circle-fill fs-1 text-danger mb-2"></i>
                                        <h6 class="text-muted mb-1 small">Erros</h6>
                                        <h3 class="mb-0 text-danger"><?= number_format($estatisticasLogin['login_erro'] ?? 0) ?></h3>
                                        <?php 
                                        $percentualErro = $totalTentativas > 0 ? round(($estatisticasLogin['login_erro'] / $totalTentativas) * 100, 1) : 0;
                                        ?>
                                        <small class="text-muted"><?= $percentualErro ?>%</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Gráfico -->
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div id="chartLogin" style="width: 100%; height: 400px;"></div>
                                        <?php if (empty($historicoLogin)): ?>
                                        <div class="alert alert-warning mt-3 mb-0 text-center">
                                            <i class="bi bi-exclamation-triangle"></i> 
                                            Nenhum dado de login encontrado nos últimos 30 dias.
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabela de Logs -->
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 h6 h5-md">
                                <i class="bi bi-journal-text"></i>
                                <span class="d-none d-sm-inline">Logs de <?= ucfirst($tipoLog) ?></span>
                                <span class="d-inline d-sm-none">Logs</span>
                            </h5>
                            <button class="btn btn-sm btn-outline-secondary toggle-table" data-target="tabelaLogs">
                                <i class="bi bi-chevron-up"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body collapse show" id="tabelaLogs">
                        <div class="table-container position-relative">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 60px;">ID</th>
                                            <?php if ($tipoLog === 'todos'): ?>
                                            <th style="width: 100px;">Tipo</th>
                                            <?php endif; ?>
                                            <th>Ação</th>
                                            <th>Usuário</th>
                                            <th>Descrição</th>
                                            <th>Status</th>
                                            <th>Data/Hora</th>
                                        </tr>
                                    </thead>
                                <tbody>
                                    <?php if (empty($logs)): ?>
                                        <tr>
                                            <td colspan="<?= $tipoLog === 'todos' ? '7' : '6' ?>" class="text-center text-muted py-4">
                                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                                Nenhum log encontrado
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($logs as $log): ?>
                                            <tr>
                                                <td>#<?= $log['id'] ?></td>
                                                <?php if ($tipoLog === 'todos'): ?>
                                                <td>
                                                    <?php
                                                    $tipoBadge = 'secondary';
                                                    switch ($log['tipo_log']) {
                                                        case 'Serviços':
                                                            $tipoBadge = 'primary';
                                                            break;
                                                        case 'Cadastro':
                                                            $tipoBadge = 'info';
                                                            break;
                                                        case 'Login':
                                                            $tipoBadge = 'warning';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge bg-<?= $tipoBadge ?>"><?= htmlspecialchars($log['tipo_log'] ?? 'N/A') ?></span>
                                                </td>
                                                <?php endif; ?>
                                                <td><code><?= htmlspecialchars($log['acao']) ?></code></td>
                                                <td>
                                                    <?= htmlspecialchars($log['usuario_nome'] ?? 'N/A') ?><br>
                                                    <small class="text-muted">Mat: <?= $log['usuario_matricula'] ?? 'N/A' ?></small>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($log['descricao'] ?? '—') ?>
                                                    <?php if (!empty($log['mensagem_erro'])): ?>
                                                        <br><small class="text-danger">Erro: <?= htmlspecialchars($log['mensagem_erro']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $statusBadge = 'secondary';
                                                    switch ($log['status']) {
                                                        case 'sucesso':
                                                            $statusBadge = 'success';
                                                            break;
                                                        case 'falha':
                                                            $statusBadge = 'warning';
                                                            break;
                                                        case 'erro':
                                                            $statusBadge = 'danger';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge bg-<?= $statusBadge ?>"><?= strtoupper($log['status']) ?></span>
                                                </td>
                                                <td><?= date('d/m/Y H:i:s', strtotime($log['criado_em'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        </div>
                        <?php if (count($logs) >= 500): ?>
                            <div class="alert alert-info mt-3 mb-0">
                                <i class="bi bi-info-circle"></i> Mostrando os 500 registros mais recentes. Use filtros para refinar a busca.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/relatorios.js"></script>
    <script type="text/javascript">
        // Gráfico de Login com Google Charts
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(drawLoginChart);

        function drawLoginChart() {
            // Verificar se o elemento existe antes de desenhar o gráfico
            var chartElement = document.getElementById('chartLogin');
            if (!chartElement) {
                // Se o elemento não existir ainda, tentar novamente após um pequeno delay
                setTimeout(drawLoginChart, 100);
                return;
            }

            var data = new google.visualization.DataTable();
            data.addColumn('datetime', 'Data/Hora');
            data.addColumn('number', 'Sucesso');
            data.addColumn('number', 'Falhas');
            data.addColumn('number', 'Erros');
            data.addColumn('number', 'Total Tentativas');

            // Adicionar dados do PHP
            data.addRows([
                <?php 
                if (!empty($historicoLogin)) {
                    foreach ($historicoLogin as $index => $registro) {
                        $dataDia = $registro['data_dia'];
                        $horaAgrupada = (int)$registro['hora_agrupada'];
                        
                        // Parse da data
                        $timestamp = strtotime($dataDia);
                        $ano = date('Y', $timestamp);
                        $mes = date('n', $timestamp) - 1; // JavaScript mês começa em 0
                        $dia = date('j', $timestamp);
                        
                        $sucesso = (int)$registro['login_sucesso'];
                        $falha = (int)$registro['login_falha'];
                        $erro = (int)$registro['login_erro'];
                        $total = (int)$registro['total_tentativas'];
                        
                        echo "[new Date($ano, $mes, $dia, $horaAgrupada, 0, 0), ";
                        echo "$sucesso, ";
                        echo "$falha, ";
                        echo "$erro, ";
                        echo "$total";
                        echo "]";
                        
                        if ($index < count($historicoLogin) - 1) {
                            echo ",\n                ";
                        }
                    }
                } else {
                    // Dados vazios para evitar erro no gráfico
                    echo "[new Date(), 0, 0, 0, 0]";
                }
                ?>
            ]);

            var options = {
                title: 'Histórico de Tentativas de Login',
                titleTextStyle: {
                    fontSize: 16,
                    bold: true
                },
                curveType: 'function',
                legend: { 
                    position: 'bottom',
                    textStyle: { fontSize: 12 }
                },
                hAxis: {
                    title: 'Data e Hora',
                    format: 'dd/MM HH:mm',
                    gridlines: { count: 15 },
                    textStyle: { fontSize: 11 }
                },
                vAxis: {
                    title: 'Número de Tentativas',
                    minValue: 0,
                    viewWindow: {
                        min: 0
                    },
                    format: '0',
                    gridlines: { color: '#f0f0f0' },
                    textStyle: { fontSize: 11 }
                },
                series: {
                    0: { color: '#198754', lineWidth: 3 }, // Verde - Sucesso
                    1: { color: '#ffc107', lineWidth: 3 }, // Amarelo - Falhas
                    2: { color: '#dc3545', lineWidth: 3 }, // Vermelho - Erros
                    3: { color: '#0d6efd', lineWidth: 3, lineDashStyle: [5, 5] } // Azul tracejado - Total
                },
                chartArea: {
                    width: '85%',
                    height: '70%'
                },
                backgroundColor: '#f8f9fa',
                focusTarget: 'category',
                animation: {
                    startup: true,
                    duration: 1000,
                    easing: 'out'
                },
                pointSize: 5,
                interpolateNulls: false
            };

            var chart = new google.visualization.LineChart(chartElement);
            chart.draw(data, options);

            // Redesenhar gráfico ao redimensionar janela
            var resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    if (chartElement && chart) {
                        chart.draw(data, options);
                    }
                }, 250); // Debounce de 250ms
            });
        }
    </script>
