// js/estoque_onload.js

function renderEstoque(payload) {
  // payload = { data: [...], meta: { aba, pagina, limite, total, totalPaginas } }
  const { data = [], meta = {} } = payload || {};
  const { aba = 'saldo', total = 0, pagina = 1, totalPaginas = 1 } = meta;

  // Abas ativas
  const tabSaldo = document.getElementById('tab-saldo');
  const tabMov   = document.getElementById('tab-mov');
  if (tabSaldo && tabMov) {
    tabSaldo.classList.toggle('active', aba === 'saldo');
    tabMov.classList.toggle('active', aba === 'movimentacoes');
  }

  // Títulos/Total
  const titulo = document.getElementById('titulo-card');
  const totalLabel = document.getElementById('total-label');
  if (aba === 'saldo') {
    if (titulo) titulo.textContent = 'Saldo Atual do Estoque';
    if (totalLabel) totalLabel.textContent = `Total: ${Number(total)} itens em estoque`;
  } else {
    if (titulo) titulo.textContent = 'Movimentações do Estoque';
    if (totalLabel) totalLabel.textContent = `Total: ${Number(total)} movimentações`;
  }

  // Monta head e body
  const thead = document.getElementById('thead-estoque');
  const tbody = document.getElementById('tbody-estoque');
  if (!thead || !tbody) return;

  if (aba === 'saldo') {
    renderHeadSaldo(thead);
    renderBodySaldo(tbody, data);
  } else {
    renderHeadMov(thead);
    renderBodyMov(tbody, data);
  }

  // Paginação
  const pag = document.getElementById('paginacao');
  renderPaginacao(pag, { pagina, totalPaginas, aba });
}

/* ---------- SALDO ---------- */
function renderHeadSaldo(thead) {
  thead.innerHTML = `
    <tr>
      <th class="small">Item</th>
      <th class="small text-end">Quantidade</th>
      <th class="small">Unidade</th>
      <th class="small text-end">Custo Médio</th>
      <th class="small">Almoxarifado</th>
      <th class="small">Ações</th>
    </tr>
  `;
}

function renderBodySaldo(tbody, rows) {
  tbody.innerHTML = '';
  if (!rows || !rows.length) {
    tbody.innerHTML = `<tr>
      <td colspan="6" class="text-center text-muted fst-italic py-4">Nenhum item em estoque encontrado</td>
    </tr>`;
    return;
  }

  for (const r of rows) {
    const idItem   = Number(r.item);
    const produto  = r.produto_nome ?? '';
    const unidade  = r.unidade_codigo ?? '';
    const qtd      = toNumber(r.quantidade);
    const custo    = toNumber(r.custo_medio);
    const almox    = r.almoxarifado ?? '';

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${produto}</td>
      <td class="text-end fw-semibold font-monospace">${formatNumber(qtd)}</td>
      <td>${unidade}</td>
      <td class="text-end fw-semibold text-success font-monospace">R$ ${formatNumber(custo)}</td>
      <td>${almox}</td>
      <td>
        <button class="btn btn-sm btn-primary" onclick="verDetalhes(${idItem})">Detalhes</button>
      </td>
    `;
    tbody.appendChild(tr);
  }
}

/* ---------- MOVIMENTAÇÕES ---------- */
function renderHeadMov(thead) {
  thead.innerHTML = `
    <tr>
      <th class="small">Data</th>
      <th class="small">Tipo</th>
      <th class="small">Item</th>
      <th class="small text-end">Quantidade</th>
      <th class="small">Unidade</th>
      <th class="small text-end">Custo Médio</th>
      <th class="small">Documento</th>
      <th class="small">Ações</th>
    </tr>
  `;
}

function renderBodyMov(tbody, rows) {
  tbody.innerHTML = '';
  if (!rows || !rows.length) {
    tbody.innerHTML = `<tr>
      <td colspan="8" class="text-center text-muted fst-italic py-4">Nenhuma movimentação encontrada</td>
    </tr>`;
    return;
  }

  for (const m of rows) {
    const id      = Number(m.id);
    const tipo    = m.tipo_movimentacao ?? '';
    const dir     = m.direction; // IN | OUT | (ajuste?)
    const sinal   = dir === 'OUT' ? '-' : (dir === 'IN' ? '+' : '±');
    const produto = m.produto_nome ?? '';
    const qtd     = toNumber(m.quantidade);
    const unid    = m.unidade_codigo ?? '';
    const custo   = toNumber(m.custo_medio);
    const doc     = m.doc_ref || (m.os ? `OS: ${m.os}` : '-');
    const data    = formatDate(m.movement_date);

    // Badges e cores usando utilitários bootstrap
    const badgeClass =
      dir === 'OUT' ? 'bg-danger' : (dir === 'IN' ? 'bg-success' : 'bg-warning text-dark');
    const qtyClass =
      dir === 'OUT' ? 'text-danger' : (dir === 'IN' ? 'text-success' : 'text-warning');

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${data}</td>
      <td><span class="badge ${badgeClass}">${tipo}</span></td>
      <td>${produto}</td>
      <td class="text-end fw-semibold font-monospace ${qtyClass}">${sinal}${formatNumber(qtd)}</td>
      <td>${unid}</td>
      <td class="text-end fw-semibold font-monospace">R$ ${formatNumber(custo)}</td>
      <td>${doc}</td>
      <td>
        <button class="btn btn-sm btn-secondary" onclick="verMovimentacao(${id})">Mais</button>
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
  const aba = meta.aba || 'saldo';

  container.innerHTML = '';
  if (totalPaginas <= 1) return;

  const frag = document.createDocumentFragment();

  if (pagina > 1) {
    frag.appendChild(btnPagina('← Anterior', aba, pagina - 1));
  }

  const span = document.createElement('span');
  span.className = 'text-muted';
  span.textContent = `Página ${pagina} de ${totalPaginas}`;
  frag.appendChild(span);

  if (pagina < totalPaginas) {
    frag.appendChild(btnPagina('Próxima →', aba, pagina + 1));
  }

  container.appendChild(frag);
}

function btnPagina(text, aba, destinoPagina) {
  const a = document.createElement('a');
  a.href = `?aba=${encodeURIComponent(aba)}&pagina=${destinoPagina}`;
  a.className = 'btn btn-sm btn-primary';
  a.textContent = text;
  return a;
}

/* ---------- Helpers ---------- */
function toNumber(v) {
  const n = Number(v);
  return isNaN(n) ? 0 : n;
}

function formatNumber(n) {
  return toNumber(n).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(s) {
  if (!s) return '';
  const d = new Date(String(s).replace(' ', 'T'));
  if (isNaN(d)) return '';
  return d.toLocaleString('pt-BR', {
    year: 'numeric', month: '2-digit', day: '2-digit',
    hour: '2-digit', minute: '2-digit'
  });
}

/* ---------- Ações dos botões ---------- */
function verDetalhes(id)      { alert('Detalhes do item #' + id); }
function verMovimentacao(id)  { alert('Detalhes da movimentação #' + id); }
