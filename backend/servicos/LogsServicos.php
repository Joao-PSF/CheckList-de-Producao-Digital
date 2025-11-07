<?php

/**
 * Registra o cadastro de uma nova OS na tabela servicos_log
 */
function registrarCadastroOS($conexao, $usuarioLogado, $osId, $dadosOS, $sucesso = true, $mensagem_erro = '') {
    $acao = 'CADASTRO_OS';
    $status = $sucesso ? 'sucesso' : 'falha';

    $dados = [
        'descricao' => $sucesso
            ? "Nova OS cadastrada: ID {$osId}, Cliente: {$dadosOS['nome_cliente']}"
            : "Falha ao cadastrar OS para o cliente: {$dadosOS['nome_cliente']}",
        'dados_depois' => $sucesso ? $dadosOS : null,
        'mensagem_erro' => $mensagem_erro
    ];

    $sql = "INSERT INTO servicos_log (acao, usuario_cpf, usuario_matricula, descricao, dados_depois, status, mensagem_erro, criado_em)
            VALUES (:acao, :usuario_cpf, :usuario_matricula, :descricao, :dados_depois, :status, :mensagem_erro, NOW())";

    $stmt = $conexao->prepare($sql);
    $stmt->bindValue(':acao', $acao, PDO::PARAM_STR);
    $stmt->bindValue(':usuario_cpf', $usuarioLogado['cpf'], PDO::PARAM_STR);
    $stmt->bindValue(':usuario_matricula', $usuarioLogado['matricula'], PDO::PARAM_INT);
    $stmt->bindValue(':descricao', $dados['descricao'], PDO::PARAM_STR);
    $stmt->bindValue(':dados_depois', json_encode($dadosOS), PDO::PARAM_STR);
    $stmt->bindValue(':status', $status, PDO::PARAM_STR);
    $stmt->bindValue(':mensagem_erro', $mensagem_erro, PDO::PARAM_STR);

    return $stmt->execute();
}

/**
 * Registra a atualização de uma OS na tabela servicos_log
 */
function registrarAtualizarOS($conexao, $usuarioLogado, $osId, $dadosAntes, $dadosDepois, $sucesso = true, $mensagem_erro = '') {
    $acao = 'ATUALIZAR_OS';
    $status = $sucesso ? 'sucesso' : 'falha';

    $dados = [
        'descricao' => $sucesso
            ? "OS atualizada: ID {$osId}, Cliente: {$dadosDepois['nome_cliente']}"
            : "Falha ao atualizar OS ID: {$osId}",
        'dados_antes' => $dadosAntes,
        'dados_depois' => $sucesso ? $dadosDepois : null,
        'mensagem_erro' => $mensagem_erro
    ];

    $sql = "INSERT INTO servicos_log (acao, usuario_cpf, usuario_matricula, descricao, dados_antes, dados_depois, status, mensagem_erro, criado_em)
            VALUES (:acao, :usuario_cpf, :usuario_matricula, :descricao, :dados_antes, :dados_depois, :status, :mensagem_erro, NOW())";

    $stmt = $conexao->prepare($sql);
    $stmt->bindValue(':acao', $acao, PDO::PARAM_STR);
    $stmt->bindValue(':usuario_cpf', $usuarioLogado['cpf'], PDO::PARAM_STR);
    $stmt->bindValue(':usuario_matricula', $usuarioLogado['matricula'], PDO::PARAM_INT);
    $stmt->bindValue(':descricao', $dados['descricao'], PDO::PARAM_STR);
    $stmt->bindValue(':dados_antes', json_encode($dadosAntes), PDO::PARAM_STR);
    $stmt->bindValue(':dados_depois', json_encode($dadosDepois), PDO::PARAM_STR);
    $stmt->bindValue(':status', $status, PDO::PARAM_STR);
    $stmt->bindValue(':mensagem_erro', $mensagem_erro, PDO::PARAM_STR);

    return $stmt->execute();
}

/**
 * Registra a criação de uma nova etapa na tabela servicos_log
 */
function registrarCriarEtapa($conexao, $usuarioLogado, $osId, $etapaId, $dadosAntes, $dadosDepois, $sucesso = true, $mensagem_erro = '') {
    $acao = 'CRIAR_ETAPA';
    $status = $sucesso ? 'sucesso' : 'falha';

    $dados = [
        'descricao' => $sucesso
            ? "Nova etapa criada na OS ID {$osId}: {$dadosDepois['etapa']}"
            : "Falha ao criar etapa na OS ID: {$osId}",
        'dados_antes' => $dadosAntes,
        'dados_depois' => $sucesso ? $dadosDepois : null,
        'mensagem_erro' => $mensagem_erro
    ];

    $sql = "INSERT INTO servicos_log (acao, usuario_cpf, usuario_matricula, descricao, dados_antes, dados_depois, status, mensagem_erro, criado_em)
            VALUES (:acao, :usuario_cpf, :usuario_matricula, :descricao, :dados_antes, :dados_depois, :status, :mensagem_erro, NOW())";

    $stmt = $conexao->prepare($sql);
    $stmt->bindValue(':acao', $acao, PDO::PARAM_STR);
    $stmt->bindValue(':usuario_cpf', $usuarioLogado['cpf'], PDO::PARAM_STR);
    $stmt->bindValue(':usuario_matricula', $usuarioLogado['matricula'], PDO::PARAM_INT);
    $stmt->bindValue(':descricao', $dados['descricao'], PDO::PARAM_STR);
    $stmt->bindValue(':dados_antes', json_encode($dadosAntes), PDO::PARAM_STR);
    $stmt->bindValue(':dados_depois', json_encode($dadosDepois), PDO::PARAM_STR);
    $stmt->bindValue(':status', $status, PDO::PARAM_STR);
    $stmt->bindValue(':mensagem_erro', $mensagem_erro, PDO::PARAM_STR);

    return $stmt->execute();
}

/**
 * Registra a atualização de uma etapa na tabela servicos_log
 */
function registrarAtualizarEtapa($conexao, $usuarioLogado, $osId, $etapaId, $dadosAntes, $dadosDepois, $sucesso = true, $mensagem_erro = '') {
    $acao = 'ATUALIZAR_ETAPA';
    $status = $sucesso ? 'sucesso' : 'falha';

    $dados = [
        'descricao' => $sucesso
            ? "Etapa atualizada na OS ID {$osId}: {$dadosDepois['etapa']}"
            : "Falha ao atualizar etapa ID {$etapaId} na OS ID: {$osId}",
        'dados_antes' => $dadosAntes,
        'dados_depois' => $sucesso ? $dadosDepois : null,
        'mensagem_erro' => $mensagem_erro
    ];

    $sql = "INSERT INTO servicos_log (acao, usuario_cpf, usuario_matricula, descricao, dados_antes, dados_depois, status, mensagem_erro, criado_em)
            VALUES (:acao, :usuario_cpf, :usuario_matricula, :descricao, :dados_antes, :dados_depois, :status, :mensagem_erro, NOW())";

    $stmt = $conexao->prepare($sql);
    $stmt->bindValue(':acao', $acao, PDO::PARAM_STR);
    $stmt->bindValue(':usuario_cpf', $usuarioLogado['cpf'], PDO::PARAM_STR);
    $stmt->bindValue(':usuario_matricula', $usuarioLogado['matricula'], PDO::PARAM_INT);
    $stmt->bindValue(':descricao', $dados['descricao'], PDO::PARAM_STR);
    $stmt->bindValue(':dados_antes', json_encode($dadosAntes), PDO::PARAM_STR);
    $stmt->bindValue(':dados_depois', json_encode($dadosDepois), PDO::PARAM_STR);
    $stmt->bindValue(':status', $status, PDO::PARAM_STR);
    $stmt->bindValue(':mensagem_erro', $mensagem_erro, PDO::PARAM_STR);

    return $stmt->execute();
}