<?php
//Iniciar sessão
session_start();

// Incluir sistema de logs
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/AutenticacaoLogs.php';

// REGISTRAR LOG DE LOGOUT
if (isset($_SESSION['logado']) && $_SESSION['logado'] === true) {
    $nome_usuario = isset($_SESSION['nome']) ? $_SESSION['nome'] : 'Desconhecido';
    registrarLogout($conexao, $nome_usuario);
}

//Destruir todas as variáveis de sessão
session_unset();

//Destruir a sessão
session_destroy();

header("Location: ../index.php");
exit();
?>