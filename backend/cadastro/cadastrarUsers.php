<?php
// Incluir conexão (deve expor $conexao como um PDO com ERRMODE_EXCEPTION)
include __DIR__ . '/../conexao.php';

session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verifica existência dos campos
    if (!isset($_POST['matricula'], $_POST['nome'], $_POST['nivel'])) {
        echo 'Campos não enviados';
        exit;
    }

    // Normaliza/valida entrada
    $matricula = trim($_POST['matricula']);
    $nome      = trim($_POST['nome']);
    $nivel     = trim($_POST['nivel']); // se for numérico no DB, pode usar (int)$_POST['nivel']
    $senhaPadrao = '123456';

    // E-mail: opcional; valida se vier preenchido
    $email = '';
    if (isset($_POST['email']) && $_POST['email'] !== '') {
        $emailInformado = trim($_POST['email']);
        if (filter_var($emailInformado, FILTER_VALIDATE_EMAIL)) {
            $email = $emailInformado;
        } else {
            echo 'E-mail inválido';
            exit;
        }
    }

    // Checa vazios obrigatórios
    if ($matricula === '' || $nome === '' || $nivel === '') {
        echo 'Campos estão vazios';
        exit;
    }

    try {
        // 1) Verificar se a matrícula já existe
        $stmt = $conexao->prepare('SELECT id FROM users WHERE matricula = :matricula LIMIT 1');
        $stmt->execute([':matricula' => $matricula]);
        if ($stmt->fetch()) {
            echo 'Matrícula já cadastrada';
            exit;
        }

        // 2) Validar nível
        $stmt = $conexao->prepare('SELECT 1 FROM acessosniveis WHERE nivel = :nivel LIMIT 1');
        $stmt->execute([':nivel' => $nivel]);
        if (!$stmt->fetchColumn()) {
            echo 'Nível inválido';
            exit;
        }

        // 3) Inserir usuário
        $senhaHash = password_hash($senhaPadrao, PASSWORD_DEFAULT);

        // Antes de usar datas
        date_default_timezone_set('America/Sao_Paulo');

        $dataCadastro = date('Y-m-d H:i:s'); // formato compatível com DATETIME no MySQL
        $status = 'Ativo';

        $sql = 'INSERT INTO users (matricula, nome, email, senha, nivel, status, datadecadastro)
                VALUES (:matricula, :nome, :email, :senha, :nivel, :status, :datadecadastro)';

        $stmt = $conexao->prepare($sql);
        $stmt->execute([
            ':matricula' => $matricula,
            ':nome'      => $nome,
            ':email'     => $email,        // string vazia se não informado
            ':senha'     => $senhaHash,
            ':nivel'     => $nivel,
            ':status'     => $status,
            ':datadecadastro' => $dataCadastro
        ]);

        // (To do) enviar e-mail ao usuário com a senha padrão (opcional)
        // (To do) Mensagem de sucesso

        header('Location: ../cadastro.php');
        exit;
    } catch (PDOException $e) {
        // Em produção, registre $e->getMessage() em log
        echo 'Erro ao cadastrar usuário.';
        exit;
    }
} else {
    echo 'Método de requisição inválido';
    exit;
}

// Encerrar conexão (opcional)
$conexao = null;
