/* ---------Modal Cadastro--------- */
let modalCadastroInstance = null;

function getModalCadastro() {
    if (!modalCadastroInstance) {
        const el = document.getElementById('modalCadastro');
        modalCadastroInstance = new bootstrap.Modal(el, { backdrop: 'static' });
    }
    return modalCadastroInstance;
}

function abrirModalCadastro() {
    getModalCadastro().show();
}

function fecharModal() {
    getModalCadastro().hide();
}
/* ------------------------------------------- */


/* --------- Modais: Resetar Senha / Deletar (Inativar) --------- */
let modalResetInstance = null;
let modalDeleteInstance = null;

function getModalReset() {
    if (!modalResetInstance) {
        const el = document.getElementById('modalResetSenha');
        if (el) modalResetInstance = new bootstrap.Modal(el, { backdrop: 'static' });
    }
    return modalResetInstance;
}

function getModalDelete() {
    if (!modalDeleteInstance) {
        const el = document.getElementById('modalDeletarUsuario');
        if (el) modalDeleteInstance = new bootstrap.Modal(el, { backdrop: 'static' });
    }
    return modalDeleteInstance;
}

function abrirModalReset(id, nome) {
    const modal = getModalReset();
    if (!modal) return;
    const inId = document.getElementById('reset_user_id');
    const spanNm = document.getElementById('reset_user_nome');
    if (inId) inId.value = id;
    if (spanNm) spanNm.textContent = nome || '';
    modal.show();
}

function abrirModalDeletar(id, nome) {
    const modal = getModalDelete();
    if (!modal) return;
    const inId = document.getElementById('delete_user_id');
    const spanNm = document.getElementById('delete_user_nome');
    const inPwd = document.getElementById('senha_confirmacao');
    if (inId) inId.value = id;
    if (spanNm) spanNm.textContent = nome || '';
    if (inPwd) inPwd.value = '';
    modal.show();
}
/* --------------------------------------------------------------- */


/* --------- Renderização da tabela e paginação --------- */
function myFunction(payload) {
    // payload = { data: [...], meta: {pagina, limite, total, totalPaginas} }
    const tbody = document.getElementById('tbody-usuarios');
    const pag = document.getElementById('paginacao');

    renderTabela(tbody, payload.data || []);
    renderPaginacao(pag, payload.meta || { pagina: 1, totalPaginas: 1 });

    // Atualiza total (se houver)
    const totalSpan = document.getElementById('total-usuarios');
    if (totalSpan && payload.meta) {
        const total = payload.meta.totalUsuarios ?? payload.meta.total;
        if (typeof total !== 'undefined') totalSpan.textContent = total;
    }
}

// Renderiza a tabela de usuários
function renderTabela(tbody, usuarios) {
    tbody.innerHTML = '';

    // Se não houver usuários, exibe uma mensagem
    if (!usuarios.length) {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td colspan="5" class="text-center text-muted fst-italic">Nenhum usuário encontrado</td>`;
        tbody.appendChild(tr);
        return;
    }

    for (const u of usuarios) {
        const id = Number(u.id);
        const matricula = u.matricula ?? '';
        const nome = u.nome ?? '';
        const nivel = String(u.nivel ?? '');
        const dataFmt = formatarData(u.datadecadastro);

        const tr = document.createElement('tr');
        tr.innerHTML = `
      <td>${matricula}</td>
      <td>${nome}</td>
      <td>
            <select class="form-select form-select-sm" style="width:auto;">
                <option ${nivel === '1' ? 'selected' : ''} value="1">Operador</option>
                <option ${nivel === '2' ? 'selected' : ''} value="2">Supervisor</option> 
                <option ${nivel === '3' ? 'selected' : ''} value="3">Gestor</option>    
            </select>
        </td>
      <td>${dataFmt}</td>
      <td class="d-flex gap-2 flex-wrap">
        <button type="button" class="btn btn-sm btn-primary" data-id="${id}" data-nome="${nome}">Alterar</button>
        <button type="button" class="btn btn-sm btn-warning" data-id="${id}" data-nome="${nome}">Reset Senha</button>
        <button type="button" class="btn btn-sm btn-danger"  data-id="${id}" data-nome="${nome}">Excluir</button>
      </td>
    `;
        tbody.appendChild(tr);
    }
}

function formatarData(dataBD) {
    if (!dataBD) return '';
    const d = new Date(String(dataBD).replace(' ', 'T'));
    if (isNaN(d)) return '';

    return d.toLocaleString('pt-BR', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function renderPaginacao(container, meta) {
    container.innerHTML = '';

    const pagina = Number(meta.pagina || 1);
    const totalPaginas = Number(meta.totalPaginas || 1);
    if (totalPaginas <= 1) return;

    const frag = document.createDocumentFragment();

    if (pagina > 1) {
        frag.appendChild(btnPagina('← Anterior', pagina - 1));
    }

    const span = document.createElement('span');
    span.className = 'text-muted';
    span.textContent = `Página ${pagina} de ${totalPaginas}`;
    frag.appendChild(span);

    if (pagina < totalPaginas) {
        frag.appendChild(btnPagina('Próxima →', pagina + 1));
    }

    const div = document.createElement('div');
    div.className = 'd-flex justify-content-center align-items-center gap-3 py-3';
    div.appendChild(frag);

    container.appendChild(div);
}

function btnPagina(text, destinoPagina) {
    const a = document.createElement('a');
    a.href = `?pagina=${destinoPagina}`;
    a.className = 'btn btn-sm btn-primary';
    a.textContent = text;
    return a;
}
/* ------------------------------------------------------ */


/* --------- Delegação para botões de ação (abrir modais) --------- */
document.addEventListener('DOMContentLoaded', () => {
    // cria instâncias dos modais (se existirem na página)
    getModalReset();
    getModalDelete();

    const tbody = document.getElementById('tbody-usuarios');
    if (!tbody) return;

    tbody.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-id]');
        if (!btn) return;

        const id = btn.getAttribute('data-id');
        const nome = btn.getAttribute('data-nome') || '';

        if (btn.classList.contains('btn-warning')) {
            // Reset senha
            abrirModalReset(id, nome);
            return;
        }

        if (btn.classList.contains('btn-danger')) {
            // Deletar (inativar)
            abrirModalDeletar(id, nome);
            return;
        }

        if (btn.classList.contains('btn-primary')) {
            // Alterar (implemente se desejar abrir outro modal)
            // abrirModalAlterar(id, nome);
        }
    });
});
