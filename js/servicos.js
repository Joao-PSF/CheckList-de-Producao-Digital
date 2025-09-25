// js/servicos.js

function renderServicos(payload) {
  const { data = [], meta = {} } = payload || {};
  const { total = 0, pagina = 1, totalPaginas = 1 } = meta;

  const titulo = document.getElementById('titulo-card');
  const totalLabel = document.getElementById('total-label');
  if (titulo) titulo.textContent = 'Ordens de Serviço';
  if (totalLabel) {
    totalLabel.textContent =
      `Total: ${Number(total)} ordem${Number(total) !== 1 ? 's' : ''} de serviço`;
  }

  const thead = document.getElementById('thead-servicos');
  const tbody = document.getElementById('tbody-servicos');
  if (!thead || !tbody) return;

  renderHead(thead);
  renderBody(tbody, data);

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
      <th class="small">Situação</th>
    </tr>
  `;
}

/* ---------- CORPO DA TABELA ---------- */
function renderBody(tbody, rows) {
  tbody.innerHTML = '';
  if (!rows || !rows.length) {
    tbody.innerHTML = `
      <tr>
        <td colspan="5" class="text-center text-muted fst-italic py-4">
          Nenhuma ordem de serviço encontrada
        </td>
      </tr>`;
    return;
  }

  for (const os of rows) {
    const id = Number(os.id);
    const tipoServico = os.tipo_servico || 'Não definido';
    const responsaveis = os.responsaveis || 'Não atribuído';
    const dataProgramada = formatDate(os.data_programada);

    // pega situacao (com fallbacks) e normaliza
    let situacao = (os.situacao ?? os.status_os ?? os.status ?? '').toString().trim();
    if (!situacao) situacao = 'Não definida';


    const tr = document.createElement('tr');

    // #OS
    const tdId = document.createElement('td');
    tdId.className = 'fw-bold';

    // cria o link para a página de detalhes
    const aId = document.createElement('a');
    aId.href = `DetalhesOS.php?id=${id}`;
    aId.className = 'link-primary text-decoration-none';
    aId.title = `Ver detalhes da OS #${id}`;
    aId.textContent = `#${id}`;

    tdId.appendChild(aId);
    tr.appendChild(tdId);

    // Tipo de serviço
    const tdTipo = document.createElement('td');
    const spanTipo = document.createElement('span');
    spanTipo.className = 'badge bg-secondary';
    spanTipo.textContent = tipoServico;
    tdTipo.appendChild(spanTipo);
    tr.appendChild(tdTipo);

    // Responsáveis
    const tdResp = document.createElement('td');
    tdResp.className = 'small';
    tdResp.textContent = responsaveis;
    tr.appendChild(tdResp);

    // Data programada
    const tdData = document.createElement('td');
    tdData.textContent = dataProgramada;
    tr.appendChild(tdData);

    // Situação
    const tdSit = document.createElement('td');
    const spanSit = document.createElement('span');
    spanSit.className = `badge ${getSituacaoClass(situacao)}`;
    spanSit.textContent = situacao; // <- garante texto, sem HTML
    tdSit.appendChild(spanSit);
    tr.appendChild(tdSit);

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
    return date.toLocaleDateString('pt-BR', { year: 'numeric', month: '2-digit', day: '2-digit' });
  } catch {
    return 'Data inválida';
  }
}

function getSituacaoClass(s) {
  const up = s.toUpperCase();
  if (up === 'ANDAMENTO') return 'bg-success';
  if (up === 'PENDENTE') return 'bg-warning';
  if (up === 'ENCERRADA') return 'bg-dark';
  return 'bg-secondary';
}
