<?php


session_start();
if (empty($_SESSION['logado'])) {
    header("Location: ../index.php");
    exit;
}

// Conexão com o banco de dados
require_once __DIR__ . '/../conexao.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo "Parâmetro 'id' inválido.";
    exit;
}


/* -------- Buscar usuários ativos -------- */
$sqlUsers = "SELECT matricula as id, nome FROM users WHERE status = 'Ativo' ORDER BY nome";
$stmtUsers = $conexao->prepare($sqlUsers);
$stmtUsers->execute();
$usuarios = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);
$USERS_JSON = json_encode($usuarios);

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

/* -------- Responsáveis Gerais (servico_os_responsavel) -------- */
$sqlRespGerais = "
    SELECT DISTINCT
        u.matricula,
        u.nome,
        u.cpf
    FROM servico_os_responsavel sor
    JOIN users u
      ON u.cpf = sor.responsavel
    WHERE sor.servico_os_id = :id
      AND sor.status = 'Ativo'
      AND u.status = 'Ativo'
    ORDER BY u.nome
";
$stmtRespGerais = $conexao->prepare($sqlRespGerais);
$stmtRespGerais->bindValue(':id', $id, PDO::PARAM_INT);
$stmtRespGerais->execute();
$responsaveisGerais = $stmtRespGerais->fetchAll(PDO::FETCH_ASSOC);

/* -------- Etapas -------- */
$sqlEtapas = "
    SELECT
        se.id,
        se.etapa,
        se.ordem,
        se.execucao,
        se.criada_em,
        se.executada_em,
        se.status
    FROM servico_etapas se
    WHERE se.servico_os_id = :id
      AND se.status = 'Ativo'
    ORDER BY se.ordem ASC, se.id ASC
";
$stmtEtapas = $conexao->prepare($sqlEtapas);
$stmtEtapas->bindValue(':id', $id, PDO::PARAM_INT);
$stmtEtapas->execute();
$etapas = $stmtEtapas->fetchAll(PDO::FETCH_ASSOC);

/* -------- Responsáveis por etapa -------- */
$respPorEtapa = [];
if ($etapas) {
    $etapaIds = array_column($etapas, 'id');
    $in = implode(',', array_fill(0, count($etapaIds), '?'));
    $sqlRespEtapas = "
      SELECT
          ser.servico_etapa_id,
          u.matricula,
          u.nome
      FROM servico_etapas_responsavel ser
      JOIN users u
        ON u.matricula = ser.responsavel
       AND u.status = 'Ativo'
      WHERE ser.status = 'Ativo'
        AND ser.servico_etapa_id IN ($in)
      ORDER BY u.nome
  ";
    $stmtRE = $conexao->prepare($sqlRespEtapas);
    foreach ($etapaIds as $k => $val) {
        $stmtRE->bindValue($k + 1, (int)$val, PDO::PARAM_INT);
    }
    $stmtRE->execute();
    $rows = $stmtRE->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $r) {
        $sid = (int)$r['servico_etapa_id'];
        if (!isset($respPorEtapa[$sid])) $respPorEtapa[$sid] = [];
        $respPorEtapa[$sid][] = [
            'matricula' => $r['matricula'],
            'nome'      => $r['nome'],
        ];
    }
}

/* -------- Observações por etapa -------- */
$obsPorEtapa = [];
if ($etapas) {
    $etapaIds = array_column($etapas, 'id');
    $in = implode(',', array_fill(0, count($etapaIds), '?'));
    $sqlObs = "
      SELECT
          seo.servico_etapa_id,
          seo.observacao,
          seo.criado_em,
          u.nome as criado_por_nome
      FROM servico_etapas_observacao seo
      LEFT JOIN users u ON u.cpf = seo.criado_por
      WHERE seo.status = 'Ativo'
        AND seo.servico_etapa_id IN ($in)
      ORDER BY seo.criado_em DESC
  ";
    $stmtObs = $conexao->prepare($sqlObs);
    foreach ($etapaIds as $k => $val) {
        $stmtObs->bindValue($k + 1, (int)$val, PDO::PARAM_INT);
    }
    $stmtObs->execute();
    $rowsObs = $stmtObs->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rowsObs as $o) {
        $sid = (int)$o['servico_etapa_id'];
        if (!isset($obsPorEtapa[$sid])) $obsPorEtapa[$sid] = [];
        $obsPorEtapa[$sid][] = [
            'observacao' => $o['observacao'],
            'criado_em' => $o['criado_em'],
            'criado_por' => $o['criado_por_nome'] ?? 'Sistema',
        ];
    }
}

/* -------- Anexos por etapa -------- */
$anexosPorEtapa = [];
if ($etapas) {
    try {
        $etapaIds = array_column($etapas, 'id');
        $in = implode(',', array_fill(0, count($etapaIds), '?'));
        $sqlAnexos = "
          SELECT
              sa.id,
              sa.servico_etapa_id,
              sa.nome_original,
              sa.nome_arquivo,
              sa.tipo_mime,
              sa.tamanho,
              sa.criado_em,
              u.nome as criado_por_nome
          FROM servico_anexos sa
          LEFT JOIN users u ON u.id = sa.criado_por
          WHERE sa.status = 'Ativo'
            AND sa.servico_etapa_id IN ($in)
          ORDER BY sa.criado_em DESC
      ";
        $stmtAnexos = $conexao->prepare($sqlAnexos);
        foreach ($etapaIds as $k => $val) {
            $stmtAnexos->bindValue($k + 1, (int)$val, PDO::PARAM_INT);
        }
        $stmtAnexos->execute();
        $rowsAnexos = $stmtAnexos->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rowsAnexos as $a) {
            $sid = (int)$a['servico_etapa_id'];
            if (!isset($anexosPorEtapa[$sid])) $anexosPorEtapa[$sid] = [];
            $anexosPorEtapa[$sid][] = [
                'id' => $a['id'],
                'nome_original' => $a['nome_original'],
                'nome_arquivo' => $a['nome_arquivo'],
                'tipo_mime' => $a['tipo_mime'],
                'tamanho' => $a['tamanho'],
                'criado_em' => $a['criado_em'],
                'criado_por_nome' => $a['criado_por_nome'] ?? 'Sistema',
            ];
        }
    } catch (PDOException $e) {
        // Tabela pode não existir ainda, ignorar
        $anexosPorEtapa = [];
    }
}

