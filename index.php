<?php
//Incluir conexão
include('backend/conexao.php');

//Iniciar sessão
session_start();

//Verificar se o usuário está logado, se sim, redirecionar para o dashboard
if (isset($_SESSION['logado']) && $_SESSION['logado'] === true) {
    header("Location: home.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema Metalma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
</head>

<body class="d-flex align-items-center justify-content-center vh-100 bg-dark bg-gradient">

    <div class="card shadow-lg p-4" style="max-width: 400px; width:100%;">
        <h2 class="text-center mb-4">Login</h2>

        <?php if (isset($_SESSION['mensagem']) && !empty($_SESSION['mensagem'])): ?>
            <div class="alert alert-danger text-center">
                <?= $_SESSION['mensagem']; ?>
            </div>
            <?php unset($_SESSION['mensagem']); ?>
        <?php endif; ?>

        <form action="backend/autenticar.php" method="POST">

            <div class="mb-3">
                <label for="matricula" class="form-label">Matrícula</label>
                <input
                    type="text"
                    class="form-control"
                    id="matricula"
                    name="matricula"
                    placeholder="Digite sua matrícula"
                    required>
            </div>

            <div class="mb-3">
                <label for="senha" class="form-label">Senha</label>
                <input
                    type="password"
                    class="form-control"
                    id="senha"
                    name="senha"
                    placeholder="Digite sua senha"
                    required>
            </div>

            <div class="d-grid gap-2 mt-3">
                <button type="submit" class="btn btn-primary">Entrar</button>
            </div>
        </form>

        <div class="text-center mt-3">
            <a href="esqueci.php" class="text-decoration-none">Esqueci minha senha</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
</body>

</html>
