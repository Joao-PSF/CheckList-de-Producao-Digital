<?php

/*
 * logs.php
 * 
 * 
*/


require_once __DIR__ . '/conexao.php';

/**
 * Registra cadastro de novo usuário na tabela cadastro_log
 */
function registrarCadastroUsuario($conexao, $usuarioLogado, $novoUsuario, $sucesso = true, $mensagem_erro = '') {
    $acao = 'CADASTRO_USUARIO';
    $status = $sucesso ? 'sucesso' : 'falha';

    $dados = [
        'descricao' => $sucesso 
            ? "Novo usuário cadastrado: {$novoUsuario['nome']} (Matrícula: {$novoUsuario['matricula']}, Nível: {$novoUsuario['nivel']})"
            : "Falha ao cadastrar usuário {$novoUsuario['nome']} (Matrícula: {$novoUsuario['matricula']})",
        'dados_depois' => $sucesso ? $novoUsuario : null,
        'mensagem_erro' => $mensagem_erro
    ];

    $sql = "INSERT INTO cadastro_log (acao, usuario_id, usuario_cpf, usuario_matricula, descricao, dados_depois, status, mensagem_erro, criado_em) 
            VALUES (:acao, :usuario_id, :usuario_cpf, :usuario_matricula, :descricao, :dados_depois, :status, :mensagem_erro, NOW())";

    $stmt = $conexao->prepare($sql);
    $stmt->bindValue(':acao', $acao, PDO::PARAM_STR);
    $stmt->bindValue(':usuario_id', $usuarioLogado['id'], PDO::PARAM_INT);
    $stmt->bindValue(':usuario_cpf', $usuarioLogado['cpf'], PDO::PARAM_STR);
    $stmt->bindValue(':usuario_matricula', $usuarioLogado['matricula'], PDO::PARAM_INT);
    $stmt->bindValue(':descricao', $dados['descricao'], PDO::PARAM_STR);
    $stmt->bindValue(':dados_depois', json_encode($dados['dados_depois']), PDO::PARAM_STR);
    $stmt->bindValue(':status', $status, PDO::PARAM_STR);
    $stmt->bindValue(':mensagem_erro', $mensagem_erro, PDO::PARAM_STR);

    return $stmt->execute();
}

/**
 * Registra inativação de usuário na tabela cadastro_log
 */
function registrarInativarUsuario($conexao, $usuarioLogado, $usuarioAlvo, $sucesso = true, $mensagem_erro = '') {
    $acao = 'INATIVAR_USUARIO';
    $status = $sucesso ? 'sucesso' : 'falha';

    $dados = [
        'descricao' => $sucesso 
            ? "Usuário inativado: {$usuarioAlvo['nome']} (ID: {$usuarioAlvo['id']})"
            : "Falha ao inativar usuário ID: {$usuarioAlvo['id']}",
        'dados_antes' => ['status' => 'Ativo'],
        'dados_depois' => ['status' => 'Inativo'],
        'mensagem_erro' => $mensagem_erro
    ];

    $sql = "INSERT INTO cadastro_log (acao, usuario_id, usuario_cpf, usuario_matricula, descricao, dados_antes, dados_depois, status, mensagem_erro, criado_em) 
            VALUES (:acao, :usuario_id, :usuario_cpf, :usuario_matricula, :descricao, :dados_antes, :dados_depois, :status, :mensagem_erro, NOW())";

    $stmt = $conexao->prepare($sql);
    $stmt->bindValue(':acao', $acao, PDO::PARAM_STR);
    $stmt->bindValue(':usuario_id', $usuarioLogado['id'], PDO::PARAM_INT);
    $stmt->bindValue(':usuario_cpf', $usuarioLogado['cpf'], PDO::PARAM_STR);
    $stmt->bindValue(':usuario_matricula', $usuarioLogado['matricula'], PDO::PARAM_INT);
    $stmt->bindValue(':descricao', $dados['descricao'], PDO::PARAM_STR);
    $stmt->bindValue(':dados_antes', json_encode($dados['dados_antes']), PDO::PARAM_STR);
    $stmt->bindValue(':dados_depois', json_encode($dados['dados_depois']), PDO::PARAM_STR);
    $stmt->bindValue(':status', $status, PDO::PARAM_STR);
    $stmt->bindValue(':mensagem_erro', $mensagem_erro, PDO::PARAM_STR);

    return $stmt->execute();
}

/**
 * Registra reset de senha na tabela cadastro_log
 */
function registrarResetarSenha($conexao, $usuarioLogado, $usuarioAlvo, $sucesso = true, $mensagem_erro = '') {
    $acao = 'RESETAR_SENHA';
    $status = $sucesso ? 'sucesso' : 'falha';

    $dados = [
        'descricao' => $sucesso 
            ? "Senha resetada para usuário: {$usuarioAlvo['nome']} (ID: {$usuarioAlvo['id']})"
            : "Falha ao resetar senha do usuário ID: {$usuarioAlvo['id']}",
        'mensagem_erro' => $mensagem_erro
    ];

    $sql = "INSERT INTO cadastro_log (acao, usuario_id, usuario_cpf, usuario_matricula, descricao, status, mensagem_erro, criado_em) 
            VALUES (:acao, :usuario_id, :usuario_cpf, :usuario_matricula, :descricao, :status, :mensagem_erro, NOW())";

    $stmt = $conexao->prepare($sql);
    $stmt->bindValue(':acao', $acao, PDO::PARAM_STR);
    $stmt->bindValue(':usuario_id', $usuarioLogado['id'], PDO::PARAM_INT);
    $stmt->bindValue(':usuario_cpf', $usuarioLogado['cpf'], PDO::PARAM_STR);
    $stmt->bindValue(':usuario_matricula', $usuarioLogado['matricula'], PDO::PARAM_INT);
    $stmt->bindValue(':descricao', $dados['descricao'], PDO::PARAM_STR);
    $stmt->bindValue(':status', $status, PDO::PARAM_STR);
    $stmt->bindValue(':mensagem_erro', $mensagem_erro, PDO::PARAM_STR);

    return $stmt->execute();
}