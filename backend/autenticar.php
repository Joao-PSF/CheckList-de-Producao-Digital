<?php

include 'conexao.php';
include 'AutenticacaoLogs.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['matricula'], $_POST['senha'])) {
        $_SESSION['mensagem'] = 'Campos de matrícula e senha não foram recebidos.';
        exit;
    }

    $matricula = trim($_POST['matricula']);
    $senhaDigitada = $_POST['senha'];

    if ($matricula === '' || $senhaDigitada === '') {
        $_SESSION['mensagem'] = 'Campos de matrícula e senha não podem estar vazios.';
        exit;
    }

    try {
        $stmt = $conexao->prepare('SELECT id, matricula, nome, cpf, senha, nivel FROM users WHERE matricula = :matricula and status = "Ativo" LIMIT 1');
        $stmt->bindValue(':matricula', $matricula, PDO::PARAM_STR);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($senhaDigitada, $usuario['senha'])) {

            // Prevenir fixação de sessão
            session_regenerate_id(true);

            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['matricula']  = $usuario['matricula'];
            $_SESSION['nome']       = $usuario['nome'];
            $_SESSION['cpf']       = $usuario['cpf'];
            $_SESSION['nivel']       = $usuario['nivel'];
            $_SESSION['logado']     = true;

            // REGISTRAR LOG DE LOGIN BEM-SUCEDIDO
            registrarLogin($conexao, $matricula, 'sucesso', "Login feito com sucesso");

            header('Location: ../home.php');
            exit;

        } else {
            // REGISTRAR LOG DE TENTATIVA DE LOGIN FALHA
            registrarLogin($conexao, $matricula, 'falha', 'Matrícula ou senha incorretos');

            $_SESSION['mensagem'] = 'Matrícula ou senha incorretos';
            header('Location: ../index.php');
            exit;
        }

    } catch (PDOException $e) {

        registrarLogin($conexao, $matricula, 'erro', 'Erro ao processar login', $e->getMessage());
        $_SESSION['mensagem'] ='Erro ao processar login: ' . $e->getMessage();
        exit;
    }
}


$conexao = null;
?>
