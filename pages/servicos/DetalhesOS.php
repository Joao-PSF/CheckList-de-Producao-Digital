<?php
// Incluir arquivo de configuração de estilo (caminho relativo)
include '../../backend/config-style.php';

include '../../backend/servicos/DetalharOS.php';

include '../../backend/servicos/AtualizarOS.php';

/* -------- Helpers -------- */
function fmtDate($d)
{
  if (empty($d)) return '—';
  $t = strtotime($d);
  if ($t === false) return '—';
  return date('d/m/Y', $t);
}

function dateToInput($d)
{
  if (empty($d)) return '';
  $t = strtotime($d);
  if ($t === false) return '';
  return date('Y-m-d', $t);
}

function badgeSituacao($sit)
{
  $sit = strtoupper((string)$sit);
  $cls = 'bg-secondary';
  if ($sit === 'ANDAMENTO') $cls = 'bg-success';
  elseif ($sit === 'PENDENTE') $cls = 'bg-warning';
  elseif ($sit === 'ENCERRADA') $cls = 'bg-dark';
  return ['text' => $sit ?: '—', 'class' => $cls];
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <title>Detalhes da OS #<?= (int)$os['id'] ?> - <?php echo APP_TITLE; ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  
  <link rel="icon" href="../../<?php echo FAVICON_PATH; ?>" type="image/x-icon">

  <script src="../../assets/js/DetalheOS.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      renderDetalhesOS({
        usuarios: <?= $USERS_JSON ?? "[]" ?>,
        totalEtapas: <?= count($etapas) ?>
      });
    });
  </script>

  <style>
    .view-mode {
      display: block;
    }

    .edit-mode {
      display: none;
    }

    body.editing .view-mode {
      display: none;
    }

    body.editing .edit-mode {
      display: block;
    }

    body.editing #btnEditar {
      display: none;
    }

    body.editing #btnCancelar {
      display: inline-block;
    }

    body.editing #btnSalvar {
      display: inline-block;
    }

    #btnCancelar,
    #btnSalvar {
      display: none;
    }

    /* Responsividade para botões em mobile */
    @media (max-width: 468px) {
      .card-header .d-flex {
        flex-direction: column !important;
        gap: 0.5rem !important;
      }

      .card-header .d-flex .d-flex {
        width: 100%;
      }

      .card-header .d-flex button {
        width: 100%;
      }
    }
  </style>
</head>

<body class="bg-light">
  <div class="container py-4">
    <div class="row align-items-center g-2 mb-3">
      <div class="col-12 col-md">
        <h1 class="h4 m-0">Detalhes da OS #<?= (int)$os['id'] ?></h1>
      </div>

      <div class="col-12 col-md-auto">
        <div class="d-grid d-sm-flex gap-2 justify-content-sm-end">
          <a href="../../home.php" class="btn btn-outline-secondary">← Voltar</a>

          <button type="button" class="btn btn-primary" id="btnEditar">
            <i class="bi bi-pencil-fill"></i> Editar
          </button>

          <button type="button" class="btn btn-secondary" id="btnCancelar">
            <i class="bi bi-x-lg"></i> Cancelar
          </button>
        </div>
      </div>
    </div>



    <?php
    if (isset($_SESSION['mensagem'])) {
      echo '<div class="alert alert-success alert-dismissible fade show" role="alert">'
        . htmlspecialchars($_SESSION['mensagem']) .
        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
      unset($_SESSION['mensagem']);
    }
    if (isset($_SESSION['erro'])) {
      echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">'
        . htmlspecialchars($_SESSION['erro']) .
        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
      unset($_SESSION['erro']);
    }
    ?>

    <form method="POST" id="formOS">
      <input type="hidden" name="action" value="atualizar_os">

      <div class="card mb-4 shadow-sm">
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-3">
              <div class="text-muted small">Tipo de Serviço</div>
              <div class="fw-semibold"><?= htmlspecialchars($os['tipo_servico'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
            </div>

            <div class="col-md-3">
              <div class="text-muted small">Situação</div>
              <div class="view-mode">
                <?php $b = badgeSituacao($os['situacao'] ?? null); ?>
                <span class="badge <?= $b['class'] ?>"><?= htmlspecialchars($b['text'], ENT_QUOTES, 'UTF-8') ?></span>
              </div>
              <div class="edit-mode">
                <select class="form-select form-select-sm" name="situacao">
                  <option value="ANDAMENTO" <?= ($os['situacao'] === 'ANDAMENTO') ? 'selected' : '' ?>>ANDAMENTO</option>
                  <option value="PENDENTE" <?= ($os['situacao'] === 'PENDENTE') ? 'selected' : '' ?>>PENDENTE</option>
                  <option value="ENCERRADA" <?= ($os['situacao'] === 'ENCERRADA') ? 'selected' : '' ?>>ENCERRADA</option>
                </select>
              </div>
            </div>

            <div class="col-md-3">
              <div class="text-muted small">Data Programada</div>
              <div class="view-mode fw-semibold"><?= fmtDate($os['data_programada'] ?? null) ?></div>
              <div class="edit-mode">
                <input type="date" class="form-control form-control-sm" name="data_programada"
                  value="<?= dateToInput($os['data_programada'] ?? null) ?>">
              </div>
            </div>

            <div class="col-md-3">
              <div class="text-muted small">Status (registro)</div>
              <div class="view-mode fw-semibold"><?= htmlspecialchars($os['status'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
              <div class="edit-mode">
                <select class="form-select form-select-sm" name="status">
                  <option value="Ativo" <?= ($os['status'] === 'Ativo') ? 'selected' : '' ?>>Ativo</option>
                  <option value="Inativo" <?= ($os['status'] === 'Inativo') ? 'selected' : '' ?>>Inativo</option>
                </select>
              </div>
            </div>

            <div class="col-md-3">
              <div class="text-muted small">Data Início</div>
              <div class="view-mode fw-semibold"><?= fmtDate($os['data_inicio'] ?? null) ?></div>
              <div class="edit-mode">
                <input type="date" class="form-control form-control-sm" name="data_inicio"
                  value="<?= dateToInput($os['data_inicio'] ?? null) ?>">
              </div>
            </div>

            <div class="col-md-3">
              <div class="text-muted small">Data Encerramento</div>
              <div class="view-mode fw-semibold"><?= fmtDate($os['data_encerramento'] ?? null) ?></div>
              <div class="edit-mode">
                <input type="date" class="form-control form-control-sm" name="data_encerramento"
                  value="<?= dateToInput($os['data_encerramento'] ?? null) ?>">
              </div>
            </div>

            <div class="col-md-3">
              <div class="text-muted small">Criado em</div>
              <div class="fw-semibold"><?= fmtDate($os['criado_em'] ?? null) ?></div>
            </div>

            <div class="col-md-3">
              <div class="text-muted small">Cliente</div>
              <div class="view-mode fw-semibold">
                <?php
                $cli = trim(($os['nome_cliente'] ?? '') . ' ' . ($os['numero_cliente'] ?? ''));
                echo $cli ? htmlspecialchars($cli, ENT_QUOTES, 'UTF-8') : '—';
                ?>
              </div>
              <div class="edit-mode">
                <input type="text" class="form-control form-control-sm mb-1" name="nome_cliente"
                  placeholder="Nome do cliente" value="<?= htmlspecialchars($os['nome_cliente'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <input type="text" class="form-control form-control-sm" name="numero_cliente"
                  placeholder="Contato" value="<?= htmlspecialchars($os['numero_cliente'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white"><strong>Responsáveis Gerais</strong></div>
        <div class="card-body p-0">
          <?php if (!$responsaveisGerais): ?>
            <div class="p-3 text-muted fst-italic">Nenhum responsável geral cadastrado</div>
          <?php else: ?>
            <ul class="list-group list-group-flush">
              <?php foreach ($responsaveisGerais as $r): ?>
                <li class="list-group-item d-flex justify-content-between">
                  <span><?= htmlspecialchars($r['nome'] ?? '—', ENT_QUOTES, 'UTF-8') ?></span>
                  <span class="text-muted small">Matrícula: <?= htmlspecialchars($r['matricula'] ?? '—', ENT_QUOTES, 'UTF-8') ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </div>

      <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <strong>Etapas</strong>
          <div class="d-flex gap-2">
            <button class="btn btn-sm btn-success" type="button" data-bs-toggle="modal" data-bs-target="#modalNovaEtapa">
              <i class="bi bi-plus-lg"></i> Nova Etapa
            </button>
            <button class="btn btn-sm btn-outline-primary" id="btnExpandAll" type="button">Expandir tudo</button>
            <button class="btn btn-sm btn-outline-secondary" id="btnCollapseAll" type="button">Recolher tudo</button>
          </div>
        </div>

        <?php if (!$etapas): ?>
          <div class="p-3 text-muted fst-italic">Nenhuma etapa cadastrada</div>
        <?php else: ?>
          <div class="accordion" id="etapasAccordion">
            <?php foreach ($etapas as $e):
              $etapaId    = (int)$e['id'];
              $headingId  = "heading{$etapaId}";
              $collapseId = "collapse{$etapaId}";
              $isExec     = !empty($e['execucao']) && (int)$e['execucao'] === 1;

              $badgeExec = $isExec
                ? '<span class="badge bg-success">Executada</span>'
                : '<span class="badge bg-secondary">Pendente</span>';

              $badgeStatus = '<span class="badge bg-light text-dark">' . htmlspecialchars($e['status'] ?? '—', ENT_QUOTES, 'UTF-8') . '</span>';

              $respEtapa = $respPorEtapa[$etapaId] ?? [];
              $obsEtapa = $obsPorEtapa[$etapaId] ?? [];
            ?>
              <div class="accordion-item">
                <h2 class="accordion-header" id="<?= $headingId ?>">
                  <button class="accordion-button collapsed d-flex align-items-center gap-2" type="button"
                    data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>" aria-expanded="false"
                    aria-controls="<?= $collapseId ?>">
                    <span class="text-muted view-mode">#<?= (int)$e['ordem'] ?></span>
                    <span class="text-muted edit-mode">
                      <input type="number" class="form-control form-control-sm d-inline-block"
                        style="width: 60px;" name="etapas[<?= $etapaId ?>][ordem]"
                        value="<?= (int)$e['ordem'] ?>" min="1">
                    </span>
                    <span class="fw-semibold view-mode"><?= htmlspecialchars($e['etapa'] ?? '—', ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="fw-semibold edit-mode">
                      <input type="text" class="form-control form-control-sm d-inline-block"
                        style="width: 250px;" name="etapas[<?= $etapaId ?>][etapa]"
                        value="<?= htmlspecialchars($e['etapa'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </span>
                    <span class="ms-2"><?= $badgeExec ?></span>
                    <span class="ms-1"><?= $badgeStatus ?></span>
                  </button>
                </h2>
                <div id="<?= $collapseId ?>" class="accordion-collapse collapse" aria-labelledby="<?= $headingId ?>" data-bs-parent="#etapasAccordion">
                  <div class="accordion-body">
                    <div class="row g-3">
                      <div class="col-md-3">
                        <div class="text-muted small">Criada em</div>
                        <div class="fw-semibold"><?= fmtDate($e['criada_em'] ?? null) ?></div>
                      </div>
                      <div class="col-md-3">
                        <div class="text-muted small">Executada em</div>
                        <div class="view-mode fw-semibold"><?= fmtDate($e['executada_em'] ?? null) ?></div>
                        <div class="edit-mode">
                          <input type="date" class="form-control form-control-sm"
                            name="etapas[<?= $etapaId ?>][executada_em]"
                            value="<?= dateToInput($e['executada_em'] ?? null) ?>">
                        </div>
                      </div>
                      <div class="col-md-3">
                        <div class="text-muted small">Executada?</div>
                        <div class="view-mode fw-semibold"><?= $isExec ? 'Sim' : 'Não' ?></div>
                        <div class="edit-mode">
                          <select class="form-select form-select-sm" name="etapas[<?= $etapaId ?>][execucao]">
                            <option value="0" <?= !$isExec ? 'selected' : '' ?>>Não</option>
                            <option value="1" <?= $isExec ? 'selected' : '' ?>>Sim</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-3">
                        <div class="text-muted small">Status</div>
                        <div class="view-mode fw-semibold"><?= htmlspecialchars($e['status'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="edit-mode">
                          <select class="form-select form-select-sm" name="etapas[<?= $etapaId ?>][status]">
                            <option value="Ativo" <?= ($e['status'] === 'Ativo') ? 'selected' : '' ?>>Ativo</option>
                            <option value="Inativo" <?= ($e['status'] === 'Inativo') ? 'selected' : '' ?>>Inativo</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <hr class="my-3">

                    <div class="mb-2"><strong>Responsáveis da Etapa</strong></div>
                    <?php if (!$respEtapa): ?>
                      <div class="text-muted fst-italic">Nenhum responsável atribuído para esta etapa</div>
                    <?php else: ?>
                      <ul class="list-group mb-3">
                        <?php foreach ($respEtapa as $rr): ?>
                          <li class="list-group-item d-flex justify-content-between">
                            <span><?= htmlspecialchars($rr['nome'] ?? '—', ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="text-muted small">Matrícula: <?= htmlspecialchars($rr['matricula'] ?? '—', ENT_QUOTES, 'UTF-8') ?></span>
                          </li>
                        <?php endforeach; ?>
                      </ul>
                    <?php endif; ?>

                    <hr class="my-3">

                    <div class="mb-2"><strong>Observações</strong></div>
                    <?php if (!$obsEtapa): ?>
                      <div class="text-muted fst-italic">Nenhuma observação registrada</div>
                    <?php else: ?>
                      <?php foreach ($obsEtapa as $obs): ?>
                        <div class="card mb-2">
                          <div class="card-body p-2">
                            <div class="small text-muted mb-1">
                              <?= htmlspecialchars($obs['criado_por'], ENT_QUOTES, 'UTF-8') ?> - <?= fmtDate($obs['criado_em']) ?>
                            </div>
                            <div><?= nl2br(htmlspecialchars($obs['observacao'], ENT_QUOTES, 'UTF-8')) ?></div>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    <?php endif; ?>

                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="d-grid gap-2 mb-4">
        <button type="submit" class="btn btn-success btn-lg" id="btnSalvar">
          <i class="bi bi-save"></i> Salvar Alterações
        </button>
      </div>
    </form>

  </div>

  <div class="modal fade" id="modalNovaEtapa" tabindex="-1" aria-labelledby="modalNovaEtapaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form method="POST" id="formNovaEtapa">
          <input type="hidden" name="action" value="criar_etapa">

          <div class="modal-header">
            <h5 class="modal-title" id="modalNovaEtapaLabel">Nova Etapa</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body">
            <div class="mb-3">
              <label for="nome_etapa" class="form-label">Nome da Etapa <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="nome_etapa" name="nome_etapa" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Responsáveis (opcional)</label>
              <div id="multiSelectResponsaveis"></div>
            </div>

            <div class="mb-3">
              <label for="observacao_etapa" class="form-label">Observação (opcional)</label>
              <textarea class="form-control" id="observacao_etapa" name="observacao_etapa" rows="3"></textarea>
            </div>

            <div class="alert alert-info small mb-0">
              <i class="bi bi-info-circle"></i> A ordem será atribuída automaticamente como a próxima disponível.
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success">
              <i class="bi bi-plus-lg"></i> Criar Etapa
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>

</body>

</html>