<?php
// DetalhesOS.php
session_start();
if (empty($_SESSION['logado'])) {
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/backend/conexao.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo "Parâmetro 'id' inválido.";
    exit;
}

/* -------- OS + Tipo do Serviço -------- */
$sqlOS = "
    SELECT
        os.id,
        os.servico_tipo_id,
        os.nome_cliente,
        os.numero_cliente,
        os.data_programada,
        os.data_inicio,
        os.data_encerramento,
        os.situacao,
        os.status,
        os.criado_em,
        st.tipo AS tipo_servico
    FROM servicos_os os
    LEFT JOIN servicos_tipos st
           ON st.id = os.servico_tipo_id
    WHERE os.id = :id
    LIMIT 1
";
$stmtOS = $conexao->prepare($sqlOS);
$stmtOS->bindValue(':id', $id, PDO::PARAM_INT);
$stmtOS->execute();
$os = $stmtOS->fetch(PDO::FETCH_ASSOC);

if (!$os) {
    http_response_code(404);
    echo "OS #{$id} não encontrada.";
    exit;
}

/* -------- Responsáveis (via etapas) -------- */
$sqlResp = "
    SELECT DISTINCT
        u.matricula,
        u.nome
    FROM servico_etapas se
    JOIN servico_etapas_responsavel ser
      ON ser.servico_etapa_id = se.id
     AND ser.status = 'Ativo'
    JOIN users u
      ON u.matricula = ser.responsavel
     AND u.status = 'Ativo'
    WHERE se.servico_os_id = :id
      AND se.status = 'Ativo'
    ORDER BY u.nome
";
$stmtResp = $conexao->prepare($sqlResp);
$stmtResp->bindValue(':id', $id, PDO::PARAM_INT);
$stmtResp->execute();
$responsaveis = $stmtResp->fetchAll(PDO::FETCH_ASSOC);

/* -------- Etapas (nomes certos das colunas) -------- */
$sqlEtapas = "
    SELECT
        se.id,
        se.etapa,
        se.ordem,
        se.execucao,        -- tinyint(1) 0/1
        se.criada_em,       -- date
        se.executada_em,    -- date (nullable)
        se.status
    FROM servico_etapas se
    WHERE se.servico_os_id = :id
    ORDER BY se.ordem ASC, se.id ASC
";
$stmtEtapas = $conexao->prepare($sqlEtapas);
$stmtEtapas->bindValue(':id', $id, PDO::PARAM_INT);
$stmtEtapas->execute();
$etapas = $stmtEtapas->fetchAll(PDO::FETCH_ASSOC);

/* -------- Helpers -------- */
function fmtDate($d) {
    if (empty($d)) return '—';
    $t = strtotime($d);
    if ($t === false) return '—';
    return date('d/m/Y', $t);
}
function badgeSituacao($sit) {
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
</head>
<body class="bg-light">
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="h4 m-0">Detalhes da OS #<?= (int)$os['id'] ?></h1>
      <div class="d-flex gap-2">
        <a href="servicos.php" class="btn btn-outline-secondary">← Voltar</a>
      </div>
    </div>

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
            <?php $b = badgeSituacao($os['situacao'] ?? null); ?>
            <span class="badge <?= $b['class'] ?>"><?= htmlspecialchars($b['text'], ENT_QUOTES, 'UTF-8') ?></span>
          </div>
          <div class="col-md-3">
            <div class="text-muted small">Data Programada</div>
            <div class="fw-semibold"><?= fmtDate($os['data_programada'] ?? null) ?></div>
          </div>
          <div class="col-md-3">
            <div class="text-muted small">Status (registro)</div>
            <div class="fw-semibold"><?= htmlspecialchars($os['status'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
          </div>

          <div class="col-md-3">
            <div class="text-muted small">Data Início</div>
            <div class="fw-semibold"><?= fmtDate($os['data_inicio'] ?? null) ?></div>
          </div>
          <div class="col-md-3">
            <div class="text-muted small">Data Encerramento</div>
            <div class="fw-semibold"><?= fmtDate($os['data_encerramento'] ?? null) ?></div>
          </div>
          <div class="col-md-3">
            <div class="text-muted small">Criado em</div>
            <div class="fw-semibold"><?= fmtDate($os['criado_em'] ?? null) ?></div>
          </div>
          <div class="col-md-3">
            <div class="text-muted small">Cliente</div>
            <div class="fw-semibold">
              <?php
                $cli = trim(($os['nome_cliente'] ?? '') . ' ' . ($os['numero_cliente'] ?? ''));
                echo $cli ? htmlspecialchars($cli, ENT_QUOTES, 'UTF-8') : '—';
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Responsáveis -->
    <div class="card mb-4 shadow-sm">
      <div class="card-header bg-white"><strong>Responsáveis</strong></div>
      <div class="card-body p-0">
        <?php if (!$responsaveis): ?>
          <div class="p-3 text-muted fst-italic">Nenhum responsável atribuído</div>
        <?php else: ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($responsaveis as $r): ?>
              <li class="list-group-item d-flex justify-content-between">
                <span><?= htmlspecialchars($r['nome'] ?? '—', ENT_QUOTES, 'UTF-8') ?></span>
                <span class="text-muted small">Matrícula: <?= htmlspecialchars($r['matricula'] ?? '—', ENT_QUOTES, 'UTF-8') ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>

    <!-- Etapas -->
    <div class="card mb-4 shadow-sm">
      <div class="card-header bg-white"><strong>Etapas</strong></div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Etapa</th>
              <th>Ordem</th>
              <th>Executada?</th>
              <th>Criada em</th>
              <th>Executada em</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$etapas): ?>
              <tr>
                <td colspan="7" class="text-muted fst-italic text-center py-3">Nenhuma etapa cadastrada</td>
              </tr>
            <?php else: ?>
              <?php foreach ($etapas as $e): ?>
                <tr>
                  <td><?= (int)$e['id'] ?></td>
                  <td><?= htmlspecialchars($e['etapa'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($e['ordem'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    <?php
                      $isExec = !empty($e['execucao']) && (int)$e['execucao'] === 1;
                      echo $isExec ? '<span class="badge bg-success">Sim</span>' : '<span class="badge bg-secondary">Não</span>';
                    ?>
                  </td>
                  <td><?= fmtDate($e['criada_em'] ?? null) ?></td>
                  <td><?= fmtDate($e['executada_em'] ?? null) ?></td>
                  <td><?= htmlspecialchars($e['status'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
