<?php

include_once __DIR__ . '/../conexao.php';

// Puxa todas as OS ativas (campos mínimos)
$sqlOs = "
    SELECT
        id,
        criado_em,
        data_programada,
        data_encerramento,
        UPPER(situacao) AS situacao
    FROM servicos_os
    WHERE status = 'Ativo'
    ORDER BY id DESC
";

$os = $conexao->query($sqlOs)->fetchAll(PDO::FETCH_ASSOC);

//Separa por situacao
$pendentesIds  = [];
$andamentoIds  = [];
$encerradasIds = [];

$pendentes  = [];
$andamento  = [];
$encerradas = [];

foreach ($os as $row) {

    $base = [
        'id'              => (int)$row['id'],
        'criado_em'       => $row['criado_em'] ?: null,
        'data_programada' => $row['data_programada'] ?: null,
        'data_encerramento' => $row['data_encerramento'] ?: null
    ];

    switch ($row['situacao']) {

        case 'PENDENTE':

            $pendentesIds[] = (int)$row['id'];
            $pendentes[(int)$row['id']] = $base + ['proxima_etapa' => '—'];
            break;

        case 'ANDAMENTO':

            $andamentoIds[] = (int)$row['id'];
            $andamento[(int)$row['id']] = $base + ['etapa_atual' => '—'];
            break;

        case 'ENCERRADA':

            $encerradasIds[] = (int)$row['id'];
            $encerradas[(int)$row['id']] = $base + ['ultima_etapa' => '—'];
            break;
    }
}

/* Helper para IN (...) seguro quando array pode estar vazio */
function buildInPlaceholders(array $ids, string $prefix = ':id'): array
{
    $params = [];
    $phs = [];
    foreach ($ids as $k => $v) {
        $name = $prefix . $k;
        $phs[] = $name;
        $params[$name] = $v;
    }
    return [$phs, $params];
}


if (!empty($pendentesIds)) {

    [$phs, $params] = buildInPlaceholders($pendentesIds);

    $sqlProx = "
        SELECT 
            servico_os_id,
            etapa
        FROM (
        SELECT 
            se.servico_os_id,
            se.etapa,
            se.ordem,
            ROW_NUMBER() OVER (
                PARTITION BY 
                se.servico_os_id ORDER BY se.ordem ASC
            ) AS rn
        FROM 
            servico_etapas se
        WHERE 
            (se.status = 'Ativo')
            AND (se.execucao = 'PENDENTE')
            AND se.servico_os_id IN (" . implode(',', $phs) . ")
        ) x
        WHERE x.rn = 1
    ";

    $stmt = $conexao->prepare($sqlProx);

    foreach ($params as $k => $v) $stmt->bindValue($k, $v, PDO::PARAM_INT);

    $stmt->execute();

    foreach ($stmt->fetchAll() as $r) {

        $sid = (int)$r['servico_os_id'];

        if (isset($pendentes[$sid])) {

            $pendentes[$sid]['proxima_etapa'] = $r['etapa'] ?: '—';
        }
    }
}

/* 3b) Para ANDAMENTO: Etapa Atual = menor 'ordem' com execucao PENDENTE */
if (!empty($andamentoIds)) {
    [$phs, $params] = buildInPlaceholders($andamentoIds);
    $sqlAtual = "
    SELECT servico_os_id, etapa
    FROM (
      SELECT se.servico_os_id, se.etapa, se.ordem,
             ROW_NUMBER() OVER (PARTITION BY se.servico_os_id ORDER BY se.ordem ASC) AS rn
      FROM servico_etapas se
      WHERE (se.status IS NULL OR se.status = 'Ativo')
        AND (se.execucao = 'PENDENTE' OR se.execucao = 0)
        AND se.servico_os_id IN (" . implode(',', $phs) . ")
    ) x
    WHERE x.rn = 1
  ";
    $stmt = $conexao->prepare($sqlAtual);
    foreach ($params as $k => $v) $stmt->bindValue($k, $v, PDO::PARAM_INT);
    $stmt->execute();
    foreach ($stmt->fetchAll() as $r) {
        $sid = (int)$r['servico_os_id'];
        if (isset($andamento[$sid])) {
            $andamento[$sid]['etapa_atual'] = $r['etapa'] ?: '—';
        }
    }
}

/* 3c) Para ENCERRADAS: Última Etapa = maior 'ordem' (ou última executada) com execucao ENCERRADA */
if (!empty($encerradasIds)) {
    [$phs, $params] = buildInPlaceholders($encerradasIds);
    $sqlUlt = "
    SELECT servico_os_id, etapa
    FROM (
      SELECT se.servico_os_id, se.etapa, se.ordem, se.executada_em,
             ROW_NUMBER() OVER (PARTITION BY se.servico_os_id ORDER BY se.executada_em DESC, se.ordem DESC) AS rn
      FROM servico_etapas se
      WHERE (se.status IS NULL OR se.status = 'Ativo')
        AND (se.execucao = 'ENCERRADA' OR se.execucao = 1)
        AND se.servico_os_id IN (" . implode(',', $phs) . ")
    ) x
    WHERE x.rn = 1
  ";
    $stmt = $conexao->prepare($sqlUlt);
    foreach ($params as $k => $v) $stmt->bindValue($k, $v, PDO::PARAM_INT);
    $stmt->execute();
    foreach ($stmt->fetchAll() as $r) {
        $sid = (int)$r['servico_os_id'];
        if (isset($encerradas[$sid])) {
            $encerradas[$sid]['ultima_etapa'] = $r['etapa'] ?: '—';
        }
    }
}

/* 4) JSONs por grupo (apenas os campos necessários para renderizar) */
$PENDENTES_JSON  = json_encode(array_values($pendentes),  JSON_UNESCAPED_UNICODE);
$ANDAMENTO_JSON  = json_encode(array_values($andamento),  JSON_UNESCAPED_UNICODE);
$ENCERRADAS_JSON = json_encode(array_values($encerradas), JSON_UNESCAPED_UNICODE);
