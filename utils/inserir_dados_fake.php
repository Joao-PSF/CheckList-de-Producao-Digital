<?php
// backend/dev/seed_fake_data.php

declare(strict_types=1);

// Fuso horário São Paulo p/ datas coerentes
date_default_timezone_set('America/Sao_Paulo');

// Conexão PDO (deve expor $conexao = new PDO(...), ERRMODE_EXCEPTION)
include ('/backend/conexao.php');

// Opção: se chamar com ?truncate=1, limpa as tabelas antes de popular
$doTruncate = isset($_GET['truncate']) && $_GET['truncate'] == '1';

try {
    $conexao->beginTransaction();

    if ($doTruncate) {
        // cuidado: TRUNCATE zera AUTO_INCREMENT
        $conexao->exec("SET FOREIGN_KEY_CHECKS=0");
        $tables = [
            'estoque_movimentos',
            'estoque_saldo',
            'itens',
            'unidades',
            'almoxarifados',
            'acessosniveis',
            'estoque_movimentos_tipos',
            'users',
        ];
        foreach ($tables as $t) {
            $conexao->exec("TRUNCATE TABLE {$t}");
        }
        $conexao->exec("SET FOREIGN_KEY_CHECKS=1");
    }

    // ---------- Helpers ----------
    $countTable = function (PDO $pdo, string $table): int {
        return (int)$pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
    };

    $insertAndGetId = function (PDO $pdo, string $sql, array $params = []): int {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$pdo->lastInsertId();
    };

    $ensureInserted = function (PDO $pdo, string $table, callable $insertFn) use ($countTable) {
        if ($countTable($pdo, $table) === 0) {
            $insertFn();
        }
    };

    // ---------- 1) unidades ----------
    $ensureInserted($conexao, 'unidades', function () use ($conexao, $insertAndGetId) {
        $unidades = [
            ['UN', 'Unidade'],
            ['KG', 'Quilograma'],
            ['M',  'Metro'],
            ['CX', 'Caixa'],
            ['L',  'Litro'],
        ];
        $sql = "INSERT INTO unidades (unidade, nome) VALUES (:unidade, :nome)";
        $stmt = $conexao->prepare($sql);
        foreach ($unidades as [$sigla, $nome]) {
            $stmt->execute([':unidade' => $sigla, ':nome' => $nome]);
        }
    });

    // Map de unidades (sigla => id)
    $mapUnidades = [];
    $stmt = $conexao->query("SELECT id, unidade FROM unidades");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $u) {
        $mapUnidades[$u['unidade']] = (int)$u['id'];
    }

    // ---------- 2) acessosniveis ----------
    $ensureInserted($conexao, 'acessosniveis', function () use ($conexao) {
        $rows = [
            [1, 'Administrador'],
            [2, 'Supervisor'],
            [3, 'Usuário'],
        ];
        $stmt = $conexao->prepare("INSERT INTO acessosniveis (nivel, descricao) VALUES (:nivel, :descricao)");
        foreach ($rows as [$nivel, $desc]) {
            $stmt->execute([':nivel' => $nivel, ':descricao' => $desc]);
        }
    });

    // ---------- 3) almoxarifados ----------
    $ensureInserted($conexao, 'almoxarifados', function () use ($conexao) {
        $rows = [
            ['Almoxarifado Central', 1],
            ['Ferramentaria', 1],
            ['Almoxarifado Externo', 1],
        ];
        $stmt = $conexao->prepare("INSERT INTO almoxarifados (nome, ativo) VALUES (:nome, :ativo)");
        foreach ($rows as [$nome, $ativo]) {
            $stmt->execute([':nome' => $nome, ':ativo' => $ativo]);
        }
    });

    // Pega IDs de almoxarifados
    $almoxIds = [];
    $stmt = $conexao->query("SELECT id, nome FROM almoxarifados");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $a) {
        $almoxIds[] = (int)$a['id'];
    }

    // ---------- 4) estoque_movimentos_tipos ----------
    $ensureInserted($conexao, 'estoque_movimentos_tipos', function () use ($conexao) {
        $rows = [
            ['Entrada', 'IN'],
            ['Saída',   'OUT'],
            ['Ajuste',  'ADJ'],
        ];
        $stmt = $conexao->prepare("INSERT INTO estoque_movimentos_tipos (tipo, direcao) VALUES (:tipo, :direcao)");
        foreach ($rows as [$tipo, $dir]) {
            $stmt->execute([':tipo' => $tipo, ':direcao' => $dir]);
        }
    });

    // Map de tipos (tipo => id) e (direcao => id) se necessário
    $mapTipos = [];
    $stmt = $conexao->query("SELECT id, tipo, direcao FROM estoque_movimentos_tipos");
    $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($tipos as $t) {
        $mapTipos[$t['tipo']] = (int)$t['id'];
    }

    // ---------- 5) itens ----------
    $ensureInserted($conexao, 'itens', function () use ($conexao, $mapUnidades) {
        $itens = [
            ['Chapa de Aço 2mm',     $mapUnidades['UN'] ?? null],
            ['Tubo 1" 6m',          $mapUnidades['M']  ?? null],
            ['Parafuso M8',         $mapUnidades['UN'] ?? null],
            ['Eletrodo 6013 2,5mm', $mapUnidades['KG'] ?? null],
            ['Óleo Lubrificante',   $mapUnidades['L']  ?? null],
            ['Abraçadeira 1/2"',    $mapUnidades['UN'] ?? null],
            ['Cinta de Carga',      $mapUnidades['UN'] ?? null],
            ['Cabo 4mm',            $mapUnidades['M']  ?? null],
            ['Caixa Plástica',      $mapUnidades['CX'] ?? null],
            ['Tinta Epóxi',         $mapUnidades['L']  ?? null],
        ];
        $stmt = $conexao->prepare("INSERT INTO itens (item, unidade_padrao) VALUES (:item, :unidade)");
        foreach ($itens as [$nome, $uniId]) {
            $stmt->execute([':item' => $nome, ':unidade' => $uniId]);
        }
    });

    // Pega itens
    $itens = $conexao->query("SELECT id, item, unidade_padrao FROM itens")->fetchAll(PDO::FETCH_ASSOC);

    // ---------- 6) users ----------
    $ensureInserted($conexao, 'users', function () use ($conexao) {
        $agora = date('Y-m-d H:i:s');
        $senhaHash = password_hash('123456', PASSWORD_DEFAULT);
        $users = [
            [1001, 'Ana Silva',   'ana.silva@metalma.test',    1, 'Ativo'],
            [1002, 'Bruno Souza', 'bruno.souza@metalma.test',  2, 'Ativo'],
            [1003, 'Carlos Lima', 'carlos.lima@metalma.test',  3, 'Ativo'],
            [1004, 'Diana Reis',  'diana.reis@metalma.test',   3, 'Ativo'],
            [1005, 'Edu Santos',  'edu.santos@metalma.test',   2, 'Ativo'],
        ];
        $sql = "INSERT INTO users (matricula, nome, email, senha, nivel, status, datadecadastro)
                VALUES (:matricula, :nome, :email, :senha, :nivel, :status, :data)";
        $stmt = $conexao->prepare($sql);
        foreach ($users as [$mat, $nome, $email, $nivel, $status]) {
            $stmt->execute([
                ':matricula' => $mat,
                ':nome'      => $nome,
                ':email'     => $email,
                ':senha'     => $senhaHash,
                ':nivel'     => $nivel,
                ':status'    => $status,
                ':data'      => $agora,
            ]);
        }
    });

    // Quem será "criado_por" nas movimentações (pega 1 id existente)
    $userCriador = (int)$conexao->query("SELECT id FROM users LIMIT 1")->fetchColumn();
    if ($userCriador <= 0) { $userCriador = 1; } // fallback

    // ---------- 7) estoque_saldo ----------
    $ensureInserted($conexao, 'estoque_saldo', function () use ($conexao, $itens, $almoxIds) {
        if (!$itens || !$almoxIds) return;
        $agora = date('Y-m-d H:i:s');

        $sql = "INSERT INTO estoque_saldo (item, almoxarifado, quantidade, custo_medio, data)
                VALUES (:item, :almox, :quantidade, :custo_medio, :data)";

        $stmt = $conexao->prepare($sql);
        foreach ($itens as $row) {
            $itemId = (int)$row['id'];

            // 1 almox por item (p/ simplificar). Você pode variar mais
            $almox = $almoxIds[array_rand($almoxIds)];

            // valores fake
            $quantidade = rand(5, 150);                 // int
            $custoMedio = rand(500, 5000) / 100;        // 5.00 a 50.00

            $stmt->execute([
                ':item'        => $itemId,
                ':almox'       => $almox,
                ':quantidade'  => $quantidade,
                ':custo_medio' => $custoMedio,
                ':data'        => $agora,
            ]);
        }
    });

    // ---------- 8) estoque_movimentos ----------
    $ensureInserted($conexao, 'estoque_movimentos', function () use ($conexao, $itens, $almoxIds, $mapTipos, $userCriador) {
        if (!$itens || !$almoxIds || !$mapTipos) return;

        $sql = "INSERT INTO estoque_movimentos
                (data, tipo_movimentacao, almoxarifado, item, quantidade, custo_medio, doc_ref, observacao, criado_por, criado_em, os)
                VALUES
                (:data, :tipo, :almox, :item, :quantidade, :custo_medio, :doc_ref, :observacao, :criado_por, :criado_em, :os)";
        $stmt = $conexao->prepare($sql);

        $hoje = new DateTimeImmutable('now');
        $docs = ['NF-1001', 'NF-1002', 'REQ-2001', 'AJ-01', 'OS-778', null];

        // cria ~20 movimentos distribuídos
        for ($i = 0; $i < 20; $i++) {
            $item = $itens[array_rand($itens)];
            $itemId = (int)$item['id'];
            $almox = $almoxIds[array_rand($almoxIds)];

            // tipo aleatório
            $tipoNome = array_rand($mapTipos);
            $tipoId = $mapTipos[$tipoNome];

            // data aleatória nos últimos 15 dias
            $dataMov = $hoje->sub(new DateInterval('P' . rand(0, 15) . 'D'))->format('Y-m-d');
            $criadoEm = $hoje->format('Y-m-d H:i:s');

            // quantidade/custo fake
            $quant = rand(1, 50) / 1.0;
            $custo = rand(800, 6000) / 100; // 8.00 a 60.00
            $docRef = $docs[array_rand($docs)];
            $obs = 'Movimento gerado para seed';

            $stmt->execute([
                ':data'         => $dataMov,
                ':tipo'         => $tipoId,
                ':almox'        => $almox,
                ':item'         => $itemId,
                ':quantidade'   => $quant,
                ':custo_medio'  => $custo,
                ':doc_ref'      => $docRef,
                ':observacao'   => $obs,
                ':criado_por'   => $userCriador,
                ':criado_em'    => $criadoEm,
                ':os'           => rand(0,1) ? rand(700, 999) : null,
            ]);
        }
    });

    $conexao->commit();

    // Saída simples
    $summary = [
        'unidades'                  => (int)$conexao->query("SELECT COUNT(*) FROM unidades")->fetchColumn(),
        'acessosniveis'             => (int)$conexao->query("SELECT COUNT(*) FROM acessosniveis")->fetchColumn(),
        'almoxarifados'             => (int)$conexao->query("SELECT COUNT(*) FROM almoxarifados")->fetchColumn(),
        'estoque_movimentos_tipos'  => (int)$conexao->query("SELECT COUNT(*) FROM estoque_movimentos_tipos")->fetchColumn(),
        'itens'                     => (int)$conexao->query("SELECT COUNT(*) FROM itens")->fetchColumn(),
        'users'                     => (int)$conexao->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'estoque_saldo'             => (int)$conexao->query("SELECT COUNT(*) FROM estoque_saldo")->fetchColumn(),
        'estoque_movimentos'        => (int)$conexao->query("SELECT COUNT(*) FROM estoque_movimentos")->fetchColumn(),
    ];

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => true, 'truncate' => $doTruncate, 'summary' => $summary], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    if ($conexao->inTransaction()) {
        $conexao->rollBack();
    }
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Erro ao popular banco: " . $e->getMessage();
}
