<?php
session_start();

// Incluir arquivo de conexão (deve criar um objeto PDO em $conexao)
include 'conexao.php';

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

            header('Location: ../home.php');
            exit;

        } else {
            $_SESSION['mensagem'] = 'Matrícula ou senha incorretos';
            header('Location: ../index.php');
            exit;
        }

    } catch (PDOException $e) {

        $_SESSION['mensagem'] ='Erro ao processar login: ' . $e->getMessage();
        exit;
    }
}

// Encerrar conexão (opcional; PDO fecha ao fim do script)
$conexao = null;
?>
