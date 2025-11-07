<?php

function getIPaddress()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {

        return $_SERVER['HTTP_CLIENT_IP'];

    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {

        return $_SERVER['HTTP_X_FORWARDED_FOR'];

    } else {

        return $_SERVER['REMOTE_ADDR'];
    }
}

function getUserAgent()
{
    return $_SERVER['HTTP_USER_AGENT'] ?? 'Desconhecido';
}


/**
 * Registra as tentivas de logins
 */
function registrarLogin($conexao, $usuario, $sucesso, $descricao, $mensagem_erro = '')
{   
    $ip_address = getIPaddress();
    $user_agent = getUserAgent();

    $sql = "INSERT INTO login_log (acao, usuario_matricula, ip_address, user_agent, descricao, status, mensagem_erro, criado_em)
            VALUES (:acao, :usuario_matricula, :ip_address, :user_agent, :descricao, :status, :mensagem_erro, NOW())";

    $stmt = $conexao->prepare($sql);
    $stmt->bindValue(':acao', 'Tentativa_Login', PDO::PARAM_STR);
    $stmt->bindValue(':usuario_matricula', $usuario, PDO::PARAM_INT);
    $stmt->bindValue(':ip_address', $ip_address, PDO::PARAM_STR);
    $stmt->bindValue(':user_agent', $user_agent, PDO::PARAM_INT);
    $stmt->bindValue(':descricao', $descricao, PDO::PARAM_STR);
    $stmt->bindValue(':status', $sucesso, PDO::PARAM_STR);
    $stmt->bindValue(':mensagem_erro', $mensagem_erro, PDO::PARAM_STR);

    return $stmt->execute();
}

/**
 * Registrar logout do usuário
 */
function registrarLogout($conexao, $usuario)
{
    $ip_address = getIPaddress();
    $user_agent = getUserAgent();
    
    $sql = "INSERT INTO login_log (acao, usuario_matricula, ip_address, user_agent, descricao, status, criado_em)
            VALUES (:acao, :usuario_matricula, :ip_address, :user_agent, :descricao, :status, NOW())";

    $stmt = $conexao->prepare($sql);
    $stmt->bindValue(':acao', 'Logout', PDO::PARAM_STR);
    $stmt->bindValue(':usuario_matricula', $usuario, PDO::PARAM_INT);
    $stmt->bindValue(':ip_address', $ip_address, PDO::PARAM_STR);
    $stmt->bindValue(':user_agent', $user_agent, PDO::PARAM_INT);
    $stmt->bindValue(':descricao', 'Usuário efetuou logout com sucesso.', PDO::PARAM_STR);
    $stmt->bindValue(':status', 'Sucesso', PDO::PARAM_STR);

    return $stmt->execute();
}
