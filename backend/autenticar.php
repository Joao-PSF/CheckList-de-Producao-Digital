<?php
// Incluir arquivo de conexão (deve criar um objeto PDO em $conexao)
include 'conexao.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['matricula'], $_POST['senha'])) {
        echo 'Campos de matrícula e senha não foram enviados.';
        exit;
    }

    $matricula = trim($_POST['matricula']);
    $senhaDigitada = $_POST['senha'];

    if ($matricula === '' || $senhaDigitada === '') {
        echo 'Campos de matrícula e senha não podem estar vazios.';
        exit;
    }

    try {
        $stmt = $conexao->prepare('SELECT id, matricula, nome, senha FROM users WHERE matricula = :matricula and status = "Ativo" LIMIT 1');
        $stmt->bindValue(':matricula', $matricula, PDO::PARAM_STR);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($senhaDigitada, $usuario['senha'])) {
            // Prevenir fixação de sessão
            session_regenerate_id(true);

            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['matricula']  = $usuario['matricula'];
            $_SESSION['nome']       = $usuario['nome'];
            $_SESSION['logado']     = true;

            header('Location: ../dashboard.php');
            exit;
        } else {
            $_SESSION['mensagem'] = 'Matrícula ou senha incorretos';
            header('Location: ../index.php');
            exit;
        }
    } catch (PDOException $e) {
        // Em produção, registre o erro e mostre mensagem genérica
        echo 'Erro ao processar login.';
        exit;
    }
}

// Encerrar conexão (opcional; PDO fecha ao fim do script)
$conexao = null;
?>
