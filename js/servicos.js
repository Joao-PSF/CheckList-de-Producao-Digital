// js/servicos.js

function renderServicos(payload) {
    // payload = { data: [...], meta: { pagina, limite, total, totalPaginas } }
    const { data = [], meta = {} } = payload || {};
    const { total = 0, pagina = 1, totalPaginas = 1 } = meta;

    // Título/Total
    const titulo = document.getElementById('titulo-card');
    const totalLabel = document.getElementById('total-label');
    if (titulo) titulo.textContent = 'Ordens de Serviço';
    if (totalLabel) totalLabel.textContent = `Total: ${Number(total)} ordem${Number(total) !== 1 ? 's' : ''} de serviço`;

    // Monta head e body
    const thead = document.getElementById('thead-servicos');
    const tbody = document.getElementById('tbody-servicos');
    if (!thead || !tbody) return;

    renderHead(thead);
    renderBody(tbody, data);

    // Paginação
    const pag = document.getElementById('paginacao');
    renderPaginacao(pag, { pagina, totalPaginas });
}

/* ---------- CABEÇALHO ---------- */
function renderHead(thead) {
    thead.innerHTML = `
    <tr>
      <th class="small"># OS</th>
      <th class="small">Tipo de Serviço</th>
      <th class="small">Responsáveis</th>
      <th class="small">Data Programada</th>
      <th class="small">Status</th>
      <th class="small">Ações</th>
    </tr>
  `;
}

/* ---------- CORPO DA TABELA ---------- */
function renderBody(tbody, rows) {
    tbody.innerHTML = '';
    if (!rows || !rows.length) {
        tbody.innerHTML = `<tr>
      <td colspan="8" class="text-center text-muted fst-italic py-4">Nenhuma ordem de serviço encontrada</td>
    </tr>`;
        return;
    }

    for (const os of rows) {
        const id = Number(os.id);
        const tipoServico = os.tipo_servico || 'Não definido';
        const responsaveis = os.responsaveis || 'Não atribuído';
        const dataProgramada = formatDate(os.data_programada);
        const statusOS = os.status_os ?? '';
        const statusClass = getStatusClass(statusOS);

        const tr = document.createElement('tr');
        tr.innerHTML = `
      <td class="fw-bold text-primary">#${id}</td>
      <td>
        <span class="badge bg-secondary">${tipoServico}</span>
      </td>
      <td class="small">${responsaveis}</td>
      <td>${dataProgramada}</td>
      <td>
        <span class="badge ${statusClass}">${statusOS}</span>
      </td>
      <td>
        <button class="btn btn-sm btn-primary" onclick="visualizarOS(${id})">
          Visualizar
        </button>
      </td>
    `;
        tbody.appendChild(tr);
    }
}

/* ---------- PAGINAÇÃO ---------- */
function renderPaginacao(container, meta) {
    if (!container) return;
    const pagina = Number(meta.pagina || 1);
    const totalPaginas = Number(meta.totalPaginas || 1);

    container.innerHTML = '';
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

    container.appendChild(frag);
}

function btnPagina(text, destinoPagina) {
    const a = document.createElement('a');
    a.href = `?pagina=${destinoPagina}`;
    a.className = 'btn btn-sm btn-primary';
    a.textContent = text;
    return a;
}

/* ---------- Helpers ---------- */
function formatDate(dateStr) {
    if (!dateStr) return 'Não definida';

    try {
        const date = new Date(dateStr + 'T00:00:00');
        if (isNaN(date)) return 'Data inválida';

        return date.toLocaleDateString('pt-BR', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        });
    } catch (e) {
        return 'Data inválida';
    }
}

function getStatusClass(status) {
    const statusMap = {
        'Atrasado': 'bg-danger',
        'Em andamento': 'bg-success',
        'Sem data definida': 'bg-secondary'
    };
    return statusMap[status] || 'bg-secondary';
}

/* ---------- Ações dos botões ---------- */
function visualizarOS(id) {
    alert('Visualizando OS #' + id + ' - Funcionalidade em desenvolvimento');
}