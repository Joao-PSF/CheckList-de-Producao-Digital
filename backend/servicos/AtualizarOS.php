<?php

include_once __DIR__ . '../../../backend/conexao.php';


// Processar atualização se for POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    try {
        $conexao->beginTransaction();

        if ($_POST['action'] === 'atualizar_os') {
            // Atualizar OS principal
            $sqlUpdate = "
        UPDATE servicos_os 
        SET nome_cliente = :nome_cliente,
            numero_cliente = :numero_cliente,
            data_programada = :data_programada,
            data_inicio = :data_inicio,
            data_encerramento = :data_encerramento,
            situacao = :situacao,
            status = :status
        WHERE id = :id
      ";

            $stmtUpdate = $conexao->prepare($sqlUpdate);
            $stmtUpdate->bindValue(':nome_cliente', $_POST['nome_cliente'], PDO::PARAM_STR);
            $stmtUpdate->bindValue(':numero_cliente', $_POST['numero_cliente'], PDO::PARAM_STR);
            $stmtUpdate->bindValue(':data_programada', $_POST['data_programada'] ?: null, PDO::PARAM_STR);
            $stmtUpdate->bindValue(':data_inicio', $_POST['data_inicio'] ?: null, PDO::PARAM_STR);
            $stmtUpdate->bindValue(':data_encerramento', $_POST['data_encerramento'] ?: null, PDO::PARAM_STR);
            $stmtUpdate->bindValue(':situacao', $_POST['situacao'], PDO::PARAM_STR);
            $stmtUpdate->bindValue(':status', $_POST['status'], PDO::PARAM_STR);
            $stmtUpdate->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtUpdate->execute();

            // Atualizar etapas
            if (isset($_POST['etapas']) && is_array($_POST['etapas'])) {
                // Verificar se há ordens duplicadas
                $ordens = [];
                foreach ($_POST['etapas'] as $etapaId => $etapaData) {
                    $ordem = (int)$etapaData['ordem'];
                    if (in_array($ordem, $ordens)) {
                        throw new Exception("Erro: Existem etapas com a mesma ordem. Cada etapa deve ter uma ordem única.");
                    }
                    $ordens[] = $ordem;
                }

                // Verificar se as ordens são sequenciais (1, 2, 3, ...)
                sort($ordens);
                $totalEtapas = count($ordens);
                for ($i = 0; $i < $totalEtapas; $i++) {
                    if ($ordens[$i] !== ($i + 1)) {
                        throw new Exception("Erro: As ordens devem ser sequenciais (1, 2, 3...) sem pular números.");
                    }
                }

                foreach ($_POST['etapas'] as $etapaId => $etapaData) {
                    $sqlUpdateEtapa = "
            UPDATE servico_etapas 
            SET etapa = :etapa,
                ordem = :ordem,
                execucao = :execucao,
                executada_em = :executada_em,
                status = :status
            WHERE id = :id AND servico_os_id = :os_id
          ";

                    $stmtEtapa = $conexao->prepare($sqlUpdateEtapa);
                    $stmtEtapa->bindValue(':etapa', $etapaData['etapa'], PDO::PARAM_STR);
                    $stmtEtapa->bindValue(':ordem', (int)$etapaData['ordem'], PDO::PARAM_INT);
                    $stmtEtapa->bindValue(':execucao', (int)$etapaData['execucao'], PDO::PARAM_INT);
                    $stmtEtapa->bindValue(':executada_em', $etapaData['executada_em'] ?: null, PDO::PARAM_STR);
                    $stmtEtapa->bindValue(':status', $etapaData['status'], PDO::PARAM_STR);
                    $stmtEtapa->bindValue(':id', (int)$etapaId, PDO::PARAM_INT);
                    $stmtEtapa->bindValue(':os_id', $id, PDO::PARAM_INT);
                    $stmtEtapa->execute();
                }
            }

            $_SESSION['mensagem'] = 'OS e etapas atualizadas com sucesso!';
        } elseif ($_POST['action'] === 'criar_etapa') {
            // Buscar maior ordem atual
            $sqlMaxOrdem = "SELECT COALESCE(MAX(ordem), 0) as max_ordem FROM servico_etapas WHERE servico_os_id = :os_id";
            $stmtMax = $conexao->prepare($sqlMaxOrdem);
            $stmtMax->bindValue(':os_id', $id, PDO::PARAM_INT);
            $stmtMax->execute();
            $novaOrdem = $stmtMax->fetch(PDO::FETCH_ASSOC)['max_ordem'] + 1;

            // Inserir nova etapa
            $sqlInsertEtapa = "
        INSERT INTO servico_etapas (etapa, ordem, execucao, criada_em, servico_os_id, status)
        VALUES (:etapa, :ordem, 0, CURDATE(), :os_id, 'Ativo')
      ";

            $stmtInsert = $conexao->prepare($sqlInsertEtapa);
            $stmtInsert->bindValue(':etapa', $_POST['nome_etapa'], PDO::PARAM_STR);
            $stmtInsert->bindValue(':ordem', $novaOrdem, PDO::PARAM_INT);
            $stmtInsert->bindValue(':os_id', $id, PDO::PARAM_INT);
            $stmtInsert->execute();

            $etapaId = $conexao->lastInsertId();

            // Inserir responsáveis se houver
            if (!empty($_POST['responsaveis']) && is_array($_POST['responsaveis'])) {
                $sqlResp = "INSERT INTO servico_etapas_responsavel (responsavel, servico_etapa_id, status) VALUES (:resp, :etapa_id, 'Ativo')";
                $stmtResp = $conexao->prepare($sqlResp);
                foreach ($_POST['responsaveis'] as $matricula) {
                    $stmtResp->bindValue(':resp', $matricula, PDO::PARAM_STR);
                    $stmtResp->bindValue(':etapa_id', $etapaId, PDO::PARAM_INT);
                    $stmtResp->execute();
                }
            }

            // Inserir observação se houver
            if (!empty($_POST['observacao_etapa'])) {
                $sqlObs = "
          INSERT INTO servico_etapas_observacao (observacao, criado_por, criado_em, servico_etapa_id, status)
          VALUES (:obs, :criado_por, CURDATE(), :etapa_id, 'Ativo')
        ";
                $stmtObs = $conexao->prepare($sqlObs);
                $stmtObs->bindValue(':obs', $_POST['observacao_etapa'], PDO::PARAM_STR);
                $stmtObs->bindValue(':criado_por', $_SESSION['matricula'] ?? '0', PDO::PARAM_STR);
                $stmtObs->bindValue(':etapa_id', $etapaId, PDO::PARAM_INT);
                $stmtObs->execute();
            }

            $_SESSION['mensagem'] = 'Etapa criada com sucesso!';
        }

        $conexao->commit();
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id);
        exit;
    } catch (Exception $e) {
        $conexao->rollBack();
        $_SESSION['erro'] = 'Erro: ' . $e->getMessage();
    }
}
