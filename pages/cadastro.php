<?php
include 'backend/cadastro/usuarios.php'; // define $USERS_JSON, $total, $acessosNiveis
?>

<!-- Container de Notificações -->
<div id="notificacao-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>

<script>
    function mostrarNotificacao(mensagem, tipo) {
        const container = document.getElementById('notificacao-container');
        const corClass = tipo === 'sucesso' ? 'bg-success' : 'bg-danger';
        const iconClass = tipo === 'sucesso' ? 'bi-check-circle' : 'bi-exclamation-circle';
        
        const notif = document.createElement('div');
        notif.className = `alert alert-${tipo === 'sucesso' ? 'success' : 'danger'} alert-dismissible fade show shadow-lg`;
        notif.style.minWidth = '300px';
        notif.innerHTML = `
            <i class="bi ${iconClass} me-2"></i>
            ${mensagem}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        `;
        
        container.appendChild(notif);
        
        // Auto remover após 5 segundos
        setTimeout(() => {
            notif.remove();
        }, 5000);
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Exibir mensagens de sucesso ou erro
        <?php if (isset($_SESSION['mensagem'])): ?>
            mostrarNotificacao('<?= htmlspecialchars($_SESSION['mensagem'], ENT_QUOTES, 'UTF-8') ?>', 'sucesso');
            <?php unset($_SESSION['mensagem']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['erro'])): ?>
            mostrarNotificacao('<?= htmlspecialchars($_SESSION['erro'], ENT_QUOTES, 'UTF-8') ?>', 'erro');
            <?php unset($_SESSION['erro']); ?>
        <?php endif; ?>
        
        myFunction(<?= $USERS_JSON ?>);
    });
</script>

<!-- Conteúdo Principal -->
<main class="flex-grow-1 d-flex flex-column bg-white">

    <!-- Header -->
    <header class="d-flex justify-content-between align-items-center border-bottom shadow-sm py-3 px-4">
        <h1 class="h3 m-0 text-dark">Cadastros de Usuários</h1>
        <button type="button" class="btn btn-success" onclick="abrirModalCadastro()">+ Novo Usuário</button>
    </header>

    <!-- Área de conteúdo -->
    <div class="px-4 pt-4">

        <div class="card shadow-sm rounded-3">
            <div class="card-header d-flex justify-content-between align-items-center bg-light">
                <h2 class="h5 m-0 text-dark">Usuários Cadastrados</h2>
                <span class="text-muted small">Total: <span id="total-usuarios"><?= $total ?></span> usuários</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tabela-usuarios">
                    <thead class="table-light text-uppercase">
                        <tr>
                            <th class="small">Matrícula</th>
                            <th class="small">Nome</th>
                            <th class="small">Nível</th>
                            <th class="small">Data de Cadastro</th>
                            <th class="small">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-usuarios">
                        <!-- preenchido pelo JS -->
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center align-items-center gap-3 bg-light border-top py-3" id="paginacao">
                <!-- preenchido pelo JS -->
            </div>
        </div>

    </div>
</main>

<!-- Modal Novo Usuário -->
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
                        <label for="novo_cpf" class="form-label">CPF</label>
                        <input type="text" class="form-control" id="novo_cpf" name="cpf" maxlength="11" required>
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

<!-- Modal Resetar Senha -->
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

<!-- Modal Alterar Usuário -->
<div class="modal fade" id="modalAlterarUsuario" tabindex="-1" aria-labelledby="modalAlterarUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalAlterarUsuarioLabel">Editar Usuário</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body">
                <form id="formAlterar" action="backend/cadastro/atualizarUsers.php" method="POST">

                    <input type="hidden" name="user_id" id="editar_user_id">

                    <div class="mb-3">
                        <label for="editar_matricula" class="form-label">Matrícula</label>
                        <input type="text" class="form-control" id="editar_matricula" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="editar_nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="editar_nome" name="nome" required>
                    </div>

                    <div class="mb-3">
                        <label for="editar_cpf" class="form-label">CPF</label>
                        <input type="text" class="form-control" id="editar_cpf" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="editar_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="editar_email" name="email">
                    </div>

                    <div class="mb-3">
                        <label for="editar_nivel" class="form-label">Nível</label>
                        <select class="form-select" id="editar_nivel" name="nivel" required>

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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="formAlterar" class="btn btn-info">Salvar Alterações</button>
            </div>

        </div>
    </div>
</div>

<!-- Modal Deletar Usuário -->
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

<!-- Scripts -->
<script src="assets/js/cadastros.js"></script>