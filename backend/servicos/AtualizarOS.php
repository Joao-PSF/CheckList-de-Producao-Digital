<?php

include_once __DIR__ . '../../../backend/conexao.php';
include_once __DIR__ . '/LogsServicos.php';

// Obter o ID da OS
$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['os_id']) ? (int)$_POST['os_id'] : 0);
if ($id <= 0) {
    http_response_code(400);
    $_SESSION['erro'] = 'ID da OS inválido';
    exit;
}

// Processar atualização se for POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    try {

        $conexao->beginTransaction();

        if ($_POST['action'] === 'atualizar_os') {

            // Buscar dados antes da atualização (para log)
            $stmtBefore = $conexao->prepare("SELECT * FROM servicos_os WHERE id = :id");
            $stmtBefore->execute([':id' => $id]);
            $dadosAntes = $stmtBefore->fetch(PDO::FETCH_ASSOC);

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

                    // Capturar dados antes da atualização
                    $sqlEtapaAntes = "SELECT etapa, ordem, execucao, executada_em, status FROM servico_etapas WHERE id = :id AND servico_os_id = :os_id";
                    $stmtAntes = $conexao->prepare($sqlEtapaAntes);
                    $stmtAntes->bindValue(':id', (int)$etapaId, PDO::PARAM_INT);
                    $stmtAntes->bindValue(':os_id', $id, PDO::PARAM_INT);
                    $stmtAntes->execute();
                    $etapaAntes = $stmtAntes->fetch(PDO::FETCH_ASSOC);

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

                    // Se a etapa foi inativada, reorganizar as ordens
                    if ($etapaAntes['status'] === 'Ativo' && $etapaData['status'] === 'Inativo') {
                        $ordemInativada = (int)$etapaAntes['ordem'];
                        
                        // Reorganizar as ordens das etapas restantes (ATIVAS)
                        $sqlReorganizar = "UPDATE servico_etapas 
                                           SET ordem = ordem - 1
                                           WHERE servico_os_id = :os_id 
                                           AND status = 'Ativo'
                                           AND ordem > :ordem_inativada";
                        
                        $stmtReorganizar = $conexao->prepare($sqlReorganizar);
                        $stmtReorganizar->bindValue(':os_id', $id, PDO::PARAM_INT);
                        $stmtReorganizar->bindValue(':ordem_inativada', $ordemInativada, PDO::PARAM_INT);
                        $stmtReorganizar->execute();
                    }

                    // REGISTRAR LOG DE ATUALIZAÇÃO DE ETAPA
                    // Se houve mudança na execução (marcou como executada)
                    if ($etapaAntes['execucao'] == 0 && (int)$etapaData['execucao'] == 1) {

                        registrarAtualizarEtapa(
                            $conexao,
                            [
                                'id' => $_SESSION['id'],
                                'cpf' => $_SESSION['cpf'],
                                'matricula' => $_SESSION['matricula']
                            ],
                            $id,
                            $etapaId,
                            $etapaAntes,
                            $etapaData,
                            true
                        );
                    }

                    // Se houve outras mudanças (nome, ordem, status)
                    elseif (
                        $etapaAntes['etapa'] !== $etapaData['etapa'] ||
                        $etapaAntes['ordem'] != $etapaData['ordem'] ||
                        $etapaAntes['status'] !== $etapaData['status']
                    ) {
                        registrarAtualizarEtapa(
                            $conexao,
                            [
                                'id' => $_SESSION['id'],
                                'cpf' => $_SESSION['cpf'],
                                'matricula' => $_SESSION['matricula']
                            ],
                            $id,
                            $etapaId,
                            $etapaAntes,
                            $etapaData,
                            true
                        );
                    }

                    // Atualizar observações da etapa
                    if (!empty($etapaData['observacao'])) {
                        $sqlObsAtualizar = "
                            INSERT INTO servico_etapas_observacao (observacao, criado_por, criado_em, servico_etapa_id, status)
                            VALUES (:obs, :criado_por, CURDATE(), :etapa_id, 'Ativo')
                        ";
                        $stmtObsAtualizar = $conexao->prepare($sqlObsAtualizar);
                        $stmtObsAtualizar->bindValue(':obs', $etapaData['observacao'], PDO::PARAM_STR);
                        $stmtObsAtualizar->bindValue(':criado_por', $_SESSION['matricula'] ?? '0', PDO::PARAM_STR);
                        $stmtObsAtualizar->bindValue(':etapa_id', (int)$etapaId, PDO::PARAM_INT);
                        $stmtObsAtualizar->execute();
                    }

                    // Atualizar responsáveis da etapa
                    if (isset($etapaData['responsaveis']) && is_array($etapaData['responsaveis'])) {
                        // Deletar responsáveis atuais (soft delete)
                        $sqlDelResp = "UPDATE servico_etapas_responsavel SET status = 'Inativo' WHERE servico_etapa_id = :etapa_id";
                        $stmtDelResp = $conexao->prepare($sqlDelResp);
                        $stmtDelResp->bindValue(':etapa_id', (int)$etapaId, PDO::PARAM_INT);
                        $stmtDelResp->execute();

                        // Inserir novos responsáveis
                        $sqlInsResp = "INSERT INTO servico_etapas_responsavel (responsavel, servico_etapa_id, status) VALUES (:resp, :etapa_id, 'Ativo')";
                        $stmtInsResp = $conexao->prepare($sqlInsResp);
                        foreach ($etapaData['responsaveis'] as $matricula) {
                            $matricula = trim($matricula);
                            if (!empty($matricula)) {
                                $stmtInsResp->bindValue(':resp', $matricula, PDO::PARAM_STR);
                                $stmtInsResp->bindValue(':etapa_id', (int)$etapaId, PDO::PARAM_INT);
                                $stmtInsResp->execute();
                            }
                        }
                    }
                } // Fim foreach etapas

            } // Fim Atualizar etapas

            // REGISTRAR LOG DE ATUALIZAÇÃO DE OS
            $dadosDepois = [
                'nome_cliente' => $_POST['nome_cliente'],
                'numero_cliente' => $_POST['numero_cliente'],
                'data_programada' => $_POST['data_programada'],
                'data_inicio' => $_POST['data_inicio'],
                'data_encerramento' => $_POST['data_encerramento'],
                'situacao' => $_POST['situacao'],
                'status' => $_POST['status']
            ];

            registrarAtualizarOS($conexao, [
                'cpf' => $_SESSION['cpf'],
                'matricula' => $_SESSION['matricula']
            ], $id, $dadosAntes, $dadosDepois, true);

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

            // REGISTRAR LOG DE CRIAÇÃO DE ETAPA
            $dadosDepois = [
                'etapa' => $_POST['nome_etapa'],
                'ordem' => $novaOrdem,
                'execucao' => 0,
                'executada_em' => null,
                'status' => 'Ativo',
                'observacao' => $_POST['observacao_etapa'] ?? null,
                'responsaveis' => $_POST['responsaveis'] ?? []
            ];
            registrarCriarEtapa(
                $conexao,
                [
                    'id' => $_SESSION['id'],
                    'cpf' => $_SESSION['cpf'],
                    'matricula' => $_SESSION['matricula']
                ],
                $id,
                $etapaId,
                [],
                $dadosDepois
            );

            $_SESSION['mensagem'] = 'Etapa criada com sucesso!';
        }

        $conexao->commit();
        
        // Retornar sucesso e redirecionar
        $_SESSION['mensagem'] = $_POST['action'] === 'atualizar_os' 
            ? 'OS e etapas atualizadas com sucesso!' 
            : 'Etapa criada com sucesso!';
        
        // Para requisições AJAX, retornar JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => true, 'mensagem' => $_SESSION['mensagem'], 'redirect' => "?id=" . $id]);
        } else {
            // Para submissão normal do formulário, redirecionar
            header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id);
        }
        exit;
    } catch (Exception $e) {
        $conexao->rollBack();

        $dadosPost = $_POST;

        // REGISTRAR LOG DE FALHA NA ATUALIZAÇÃO
        if (function_exists('registrarAtualizarOS')) {
            registrarAtualizarOS($conexao, [
                'cpf' => $_SESSION['cpf'],
                'matricula' => $_SESSION['matricula']
            ], $id ?? 0, [], $dadosPost, false, $e->getMessage());
        }

        $mensagem_erro = 'Erro: ' . $e->getMessage();
        
        // Para requisições AJAX, retornar JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['sucesso' => false, 'mensagem' => $mensagem_erro]);
        } else {
            $_SESSION['erro'] = $mensagem_erro;
            header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id);
        }
        exit;
    }
}
