<?php

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
  <title>Detalhes da OS #<?= (int)$os['id'] ?> - Sistema Metalma</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

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

<body style="background-color: #00897B;">
  <div class="container py-4">
    <div class="row align-items-center g-2 mb-3">
      <div class="col-12 col-md">
        <h1 class="h4 m-0 text-white">Detalhes da OS #<?= (int)$os['id'] ?></h1>
      </div>

      <div class="col-12 col-md-auto">
        <!-- Mobile: d-grid (botões 100%) | ≥sm: d-flex (largura natural, à direita) -->
        <div class="d-grid d-sm-flex gap-2 justify-content-sm-end">
          <a href="../../home.php" class="btn bg-white text-dark">← Voltar</a>

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
      <input type="hidden" name="os_id" value="<?= (int)$os['id'] ?>">

      <!-- Resumo da OS -->
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

      <!-- Responsáveis Gerais -->
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

      <!-- Etapas (Accordion) -->
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
                    <!-- Campos hidden obrigatórios para POST -->
                    <input type="hidden" name="etapas[<?= $etapaId ?>][status]" value="<?= htmlspecialchars($e['status'] ?? 'Ativo', ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="etapas[<?= $etapaId ?>][execucao]" value="<?= (int)($e['execucao'] ?? 0) ?>">
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
                        <div class="fw-semibold"><?= $isExec ? 'Sim' : 'Não' ?></div>
                      </div>
                      <div class="col-md-3">
                        <div class="text-muted small">Status</div>
                        <div class="fw-semibold"><?= htmlspecialchars($e['status'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
                      </div>
                    </div>

                    <!-- Botões de Ação da Etapa -->
                    <div class="row mt-3">
                      <div class="col-12 d-flex gap-2">
                        <button type="button" 
                                class="btn btn-<?= $isExec ? 'warning' : 'success' ?> btn-sm btn-concluir-etapa" 
                                data-etapa-id="<?= $etapaId ?>"
                                data-os-id="<?= (int)$os['id'] ?>"
                                data-executada="<?= $isExec ? '1' : '0' ?>">
                          <i class="bi bi-<?= $isExec ? 'arrow-counterclockwise' : 'check-circle' ?>"></i>
                          <?= $isExec ? 'Reverter Conclusão' : 'Concluir Etapa' ?>
                        </button>
                        <button type="button" 
                                class="btn btn-danger btn-sm btn-inativar-etapa" 
                                data-etapa-id="<?= $etapaId ?>"
                                data-os-id="<?= (int)$os['id'] ?>"
                                data-etapa-nome="<?= htmlspecialchars($e['etapa'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                          <i class="bi bi-trash"></i>
                          Excluir Etapa
                        </button>
                      </div>
                    </div>

                    <hr class="my-3">

                    <div class="mb-2"><strong>Responsáveis da Etapa</strong></div>
                    <div class="view-mode">
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
                    </div>
                    <div class="edit-mode">
                      <div class="mb-3">
                        <label class="form-label small">Selecionar Responsáveis</label>
                        <div id="multiSelectResponsaveis_<?= $etapaId ?>"></div>
                        <script>
                          document.addEventListener('DOMContentLoaded', function() {
                            const container = document.getElementById('multiSelectResponsaveis_<?= $etapaId ?>');
                            if (container && !container.hasAttribute('data-initialized')) {
                              const usuarios = <?= $USERS_JSON ?? "[]" ?>;
                              const responsaveisSelecionados = [<?php 
                                echo implode(',', array_map(function($r) { 
                                  return "'" . htmlspecialchars($r['matricula'], ENT_QUOTES, 'UTF-8') . "'"; 
                                }, $respEtapa)); 
                              ?>];
                              
                              window.initMultiSelectEtapa(
                                container, 
                                usuarios.map(u => ({ value: u.matricula, label: u.nome })),
                                'etapas[<?= $etapaId ?>][responsaveis][]',
                                responsaveisSelecionados
                              );
                              container.setAttribute('data-initialized', 'true');
                            }
                          });
                        </script>
                      </div>
                    </div>

                    <hr class="my-3">

                    <div class="mb-2"><strong>Observações</strong></div>
                    <div class="view-mode">
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
                    <div class="edit-mode">
                      <textarea class="form-control form-control-sm" 
                                name="etapas[<?= $etapaId ?>][observacao]" 
                                rows="3" 
                                placeholder="Digite uma observação para esta etapa..."><?= htmlspecialchars($e['observacao'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                      <small class="text-muted">Observação: A observação será atualizada ao salvar. Observações antigas serão mantidas no histórico.</small>
                    </div>

                    <hr class="my-3">

                    <!-- Anexos -->
                    <div class="mb-2"><strong>Anexos</strong></div>
                    <div id="anexos-etapa-<?= $etapaId ?>">
                      <?php 
                        $anexosEtapa = $anexosPorEtapa[$etapaId] ?? [];
                        if (empty($anexosEtapa)): 
                      ?>
                        <div class="text-muted fst-italic mb-2">Nenhum anexo</div>
                      <?php else: ?>
                        <div class="list-group mb-2">
                          <?php foreach ($anexosEtapa as $anexo): 
                            $tipoArquivo = strpos($anexo['tipo_mime'], 'image/') === 0 ? 'imagem' : 'pdf';
                            $icone = $tipoArquivo === 'pdf' ? 'bi-file-pdf text-danger' : 'bi-file-image text-primary';
                            $tamanhoFormatado = $anexo['tamanho'] >= 1048576 
                              ? number_format($anexo['tamanho'] / 1048576, 2) . ' MB' 
                              : number_format($anexo['tamanho'] / 1024, 2) . ' KB';
                          ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center p-2" id="anexo-<?= $anexo['id'] ?>">
                              <div class="d-flex align-items-center gap-2 flex-grow-1">
                                <i class="bi <?= $icone ?> fs-4"></i>
                                <div class="flex-grow-1">
                                  <div class="fw-semibold"><?= htmlspecialchars($anexo['nome_original'], ENT_QUOTES, 'UTF-8') ?></div>
                                  <small class="text-muted">
                                    <?= $tamanhoFormatado ?> • 
                                    <?= htmlspecialchars($anexo['criado_por_nome'], ENT_QUOTES, 'UTF-8') ?> • 
                                    <?= fmtDate($anexo['criado_em']) ?>
                                  </small>
                                </div>
                              </div>
                              <div class="d-flex gap-1">
                                <a href="../../backend/servicos/DownloadAnexo.php?id=<?= $anexo['id'] ?>" 
                                   class="btn btn-sm btn-primary" 
                                   title="Baixar"
                                   download>
                                  <i class="bi bi-download"></i>
                                </a>
                                <button type="button" 
                                        class="btn btn-sm btn-danger" 
                                        onclick="deletarAnexo(<?= $anexo['id'] ?>, <?= $etapaId ?>)"
                                        title="Excluir">
                                  <i class="bi bi-trash"></i>
                                </button>
                              </div>
                            </div>
                          <?php endforeach; ?>
                        </div>
                      <?php endif; ?>
                      
                      <!-- Formulário de Upload -->
                      <div class="card bg-light">
                        <div class="card-body p-2">
                          <form id="formUploadAnexo-<?= $etapaId ?>" class="upload-anexo-form" data-etapa-id="<?= $etapaId ?>" onsubmit="return false;">
                            <div class="input-group input-group-sm">
                              <input type="file" 
                                     class="form-control" 
                                     name="arquivo" 
                                     accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.bmp"
                                     required>
                              <button type="button" class="btn btn-success btn-upload-anexo">
                                <i class="bi bi-upload"></i> Enviar
                              </button>
                            </div>
                            <small class="text-muted d-block mt-1">
                              Formatos: PDF, JPG, PNG, GIF, WebP, BMP • Máximo: 5MB
                            </small>
                          </form>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Botão Salvar -->
      <div class="d-grid gap-2 mb-4">
        <button type="submit" class="btn btn-success btn-lg" id="btnSalvar">
          <i class="bi bi-save"></i> Salvar Alterações
        </button>
      </div>
    </form>

  </div>

  <!-- Modal Nova Etapa -->
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

  <!-- Modal Confirmar Exclusão de Etapa -->
  <div class="modal fade" id="modalConfirmarExclusao" tabindex="-1" aria-labelledby="modalConfirmarExclusaoLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="modalConfirmarExclusaoLabel">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            Confirmar Exclusão
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-2">Tem certeza que deseja excluir a etapa:</p>
          <p class="fw-bold text-center fs-5" id="nomeEtapaExcluir"></p>
          <div class="alert alert-warning mb-0">
            <i class="bi bi-info-circle me-1"></i>
            <strong>Atenção:</strong> Esta ação não pode ser desfeita. A etapa será marcada como inativa.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-lg"></i> Cancelar
          </button>
          <button type="button" class="btn btn-danger" id="btnConfirmarExclusao">
            <i class="bi bi-trash"></i> Sim, Excluir
          </button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

  <script>
    // Upload de Anexos
    document.addEventListener('DOMContentLoaded', function() {
      const uploadButtons = document.querySelectorAll('.btn-upload-anexo');
      
      uploadButtons.forEach(button => {
        button.addEventListener('click', async function(e) {
          e.preventDefault();
          e.stopPropagation();
          
          // Buscar o form pai mais próximo
          const form = this.closest('form');
          if (!form) {
            alert('Erro: Formulário não encontrado.');
            return;
          }
          
          const etapaId = form.dataset.etapaId;
          const fileInput = form.querySelector('input[type="file"]');
          
          // Validar se arquivo foi selecionado
          if (!fileInput || !fileInput.files || !fileInput.files[0]) {
            alert('Por favor, selecione um arquivo.');
            return;
          }
          
          const formData = new FormData();
          formData.append('servico_etapa_id', etapaId);
          formData.append('arquivo', fileInput.files[0]);
          
          const originalText = this.innerHTML;
          this.disabled = true;
          this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Enviando...';
          
          try {
            const response = await fetch('../../backend/servicos/UploadAnexo.php', {
              method: 'POST',
              body: formData
            });
            
            const result = await response.json();
            
            if (result.sucesso) {
              alert('Arquivo anexado com sucesso!');
              location.reload();
            } else {
              alert('Erro: ' + result.mensagem);
            }
          } catch (error) {
            alert('Erro ao enviar arquivo: ' + error.message);
          } finally {
            this.disabled = false;
            this.innerHTML = originalText;
          }
        });
      });
    });

    // Deletar Anexo
    async function deletarAnexo(anexoId, etapaId) {
      if (!confirm('Deseja realmente excluir este anexo?')) return;
      
      const formData = new FormData();
      formData.append('id', anexoId);
      
      try {
        const response = await fetch('../../backend/servicos/DeletarAnexo.php', {
          method: 'POST',
          body: formData
        });
        
        const result = await response.json();
        
        if (result.sucesso) {
          alert('Anexo removido com sucesso!');
          // Remove o elemento da lista
          const anexoElement = document.getElementById('anexo-' + anexoId);
          if (anexoElement) {
            anexoElement.remove();
          }
          
          // Verifica se não há mais anexos e mostra mensagem
          const container = document.getElementById('anexos-etapa-' + etapaId);
          const listGroup = container.querySelector('.list-group');
          if (listGroup && listGroup.children.length === 0) {
            listGroup.remove();
            const noAnexosMsg = document.createElement('div');
            noAnexosMsg.className = 'text-muted fst-italic mb-2';
            noAnexosMsg.textContent = 'Nenhum anexo';
            container.insertBefore(noAnexosMsg, container.querySelector('.card'));
          }
        } else {
          alert('Erro: ' + result.mensagem);
        }
      } catch (error) {
        alert('Erro ao excluir anexo: ' + error.message);
      }
    }

    // Garantir que o formulário funcione corretamente
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('formOS');
      if (form) {
        console.log('Formulário encontrado e pronto');
      }
    });
  </script>

</body>

</html>