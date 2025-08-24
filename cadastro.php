<?php
session_start();

if (empty($_SESSION['logado'])) {
    header('Location: index.php');
    exit;
}

include 'backend/cadastro/usuarios.php'; // define $USERS_JSON, $total e $acessosNiveis
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastros - Sistema Metalma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
</head>

<body onload='myFunction(<?php echo $USERS_JSON; ?>)'>

    <div class="container-fluid p-0">
        <div class="d-flex vh-100">

            <!-- Sidebar -->
            <aside class="bg-dark text-light d-flex flex-column p-3" style="width:250px;">
                <div class="mb-4 border-bottom pb-3">
                    <h3 class="h5 mb-1">Sistema Metalma</h3>
                    <p class="small text-secondary mb-0">Olá, <?php echo htmlspecialchars($_SESSION['nome']); ?>!</p>
                </div>

                <nav class="nav nav-pills flex-column mb-auto">
                    <a href="dashboard.php" class="nav-link text-light">Inicial</a>
                    <a href="estoque.php" class="nav-link text-light">Estoque</a>
                    <a href="#" onclick="alert('Em desenvolvimento')" class="nav-link text-light">Ordens de Serviços</a>
                    <a href="cadastros.php" class="nav-link active">Cadastros</a>
                    <a href="#" onclick="alert('Em desenvolvimento')" class="nav-link text-light">Relatórios</a>
                </nav>

                <div class="mt-auto border-top pt-3">
                    <a href="backend/logout.php" class="btn btn-danger w-100">Sair</a>
                </div>
            </aside>
            <!-- /Sidebar -->

            <!-- Conteúdo Principal -->
            <main class="flex-grow-1 d-flex flex-column bg-white">

                <!-- Header -->
                <header class="d-flex justify-content-between align-items-center border-bottom shadow-sm py-3 px-4">
                    <h1 class="h3 m-0 text-dark">Cadastros de Usuários</h1>
                    <button type="button" class="btn btn-success" onclick="abrirModalCadastro()">+ Novo Usuário</button>
                </header>

                <!-- Área de conteúdo -->
                <div class="flex-grow-1 p-4 overflow-auto">

                    <!-- Card Usuários -->
                    <div class="card shadow-sm rounded-3">
                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                            <h2 class="h5 m-0 text-dark">Usuários Cadastrados</h2>
                            <span class="text-muted small">Total: <span id="total-usuarios"><?php echo $total; ?></span> usuários</span>
                        </div>

                        <!-- Tabela -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="tabela-usuarios">
                                <thead class="table-light text-uppercase">
                                    <tr>
                                        <th scope="col" class="small">Matrícula</th>
                                        <th scope="col" class="small">Nome</th>
                                        <th scope="col" class="small">Nível</th>
                                        <th scope="col" class="small">Data de Cadastro</th>
                                        <th scope="col" class="small">Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-usuarios"><!-- preenchido pelo JS --></tbody>
                            </table>
                        </div>

                        <!-- Paginação -->
                        <div class="d-flex justify-content-center align-items-center gap-3 bg-light border-top py-3" id="paginacao">
                            <!-- preenchido pelo JS -->
                        </div>
                    </div>
                    <!-- /Card Usuários -->

                </div>
                <!-- /Área de conteúdo -->

            </main>
            <!-- /Conteúdo Principal -->

        </div>
    </div>

    <!-- Modal Novo Usuário-->
    <div class="modal fade" id="modalCadastro" tabindex="-1" aria-labelledby="modalCadastroLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalCadastroLabel">Novo Usuário</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body">
                    <form id="formCadastro" action="backend/cadastro/cadastrarUsers.php" method="POST">

                        <div class="mb-3">
                            <label for="nova_matricula" class="form-label">Matrícula</label>
                            <input type="text" class="form-control" id="nova_matricula" name="matricula" required>
                        </div>

                        <div class="mb-3">
                            <label for="novo_nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="novo_nome" name="nome" required>
                        </div>

                        <div class="mb-3">
                            <label for="novo_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="novo_email" name="email">
                        </div>

                        <div class="mb-3">
                            <label for="novo_nivel" class="form-label">Nível</label>
                            <select class="form-select" id="novo_nivel" name="nivel" required>
                                <?php foreach ($acessosNiveis as $n): ?>
                                    <option value="<?= (int)$n['nivel'] ?>">
                                        <?= htmlspecialchars($n['descricao'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="fecharModal()">Cancelar</button>
                    <button type="submit" form="formCadastro" class="btn btn-success">Salvar</button>
                </div>

            </div>
        </div>
    </div>
    <!-- /Modal Cadastro -->

    <!-- Modal: Resetar Senha -->
    <div class="modal fade" id="modalResetSenha" tabindex="-1" aria-labelledby="modalResetSenhaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" action="backend/cadastro/resetar.php" method="POST">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="modalResetSenhaLabel">Resetar senha</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="user_id" id="reset_user_id">
                    <p class="mb-0">
                        Você tem certeza que deseja resetar a senha do usuário
                        <strong id="reset_user_nome"></strong>?
                    </p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Não</button>
                    <button type="submit" class="btn btn-warning">Sim, resetar</button>
                </div>
            </form>
        </div>
    </div>
    <!-- /Modal Reset -->

    <!-- Modal: Deletar (Inativar) Usuário -->
    <div class="modal fade" id="modalDeletarUsuario" tabindex="-1" aria-labelledby="modalDeletarUsuarioLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" action="backend/cadastro/deletar.php" method="POST">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalDeletarUsuarioLabel">Inativar usuário</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="user_id" id="delete_user_id">

                    <p>
                        Você tem certeza que deseja inativar o usuário
                        <strong id="delete_user_nome"></strong>?
                    </p>

                    <div class="mb-2">
                        <label for="senha_confirmacao" class="form-label">Digite sua senha para confirmar</label>
                        <input type="password" class="form-control" id="senha_confirmacao" name="senha_confirmacao" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
    <!-- /Modal Deletar -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <script src="js/cadastros.js"></script>
</body>

</html>