// js/os_tabelas.js

// ========================= Configurações =========================
const ITENS_POR_PAGINA = 10;

// ====================== Estado e Dados Locais ====================
let dadosPendentes = [];
let dadosAndamento = [];
let dadosEncerradas = [];

let estadoPaginacao = {
    paginaPendentes: 1,
    paginaAndamento: 1,
    paginaEncerradas: 1
};

// =========================== Helpers =============================
function selecionar(seletor) {
    return document.querySelector(seletor);
}

function escaparHtml(texto) {
    const mapa = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
    return String(texto ?? '').replace(/[&<>"']/g, c => mapa[c]);
}

function formatarData(dataISO) {
    if (!dataISO) return '—';
    const data = new Date(dataISO + 'T00:00:00');
    if (isNaN(data)) return '—';
    return data.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function definirParametroUrl(chave, valor) {
    const url = new URL(location.href);
    url.searchParams.set(chave, String(valor));
    history.replaceState({}, '', url.toString()); // não recarrega
}

function lerParametroUrlInteiro(chave, padrao) {
    const url = new URL(location.href);
    const valor = parseInt(url.searchParams.get(chave), 10);
    return Number.isInteger(valor) && valor > 0 ? valor : padrao;
}

// =================== Função de Inicialização =====================
function iniciarListasOS(listaPendentes, listaAndamento, listaEncerradas) {
    // guarda os dados recebidos no onload
    dadosPendentes = Array.isArray(listaPendentes) ? listaPendentes : [];
    dadosAndamento = Array.isArray(listaAndamento) ? listaAndamento : [];
    dadosEncerradas = Array.isArray(listaEncerradas) ? listaEncerradas : [];

    // lê páginas iniciais da URL (sem defaults mágicos)
    estadoPaginacao.paginaPendentes = lerParametroUrlInteiro('pendente_p', 1);
    estadoPaginacao.paginaAndamento = lerParametroUrlInteiro('andamento_p', 1);
    estadoPaginacao.paginaEncerradas = lerParametroUrlInteiro('encerrada_p', 1);

    // monta as três tabelas
    preencherTabelaPendentes();
    preencherTabelaAndamento();
    preencherTabelaEncerradas();
}

// ===================== 1) Tabela: Pendentes ======================
function preencherTabelaPendentes() {
    const totalRegistros = dadosPendentes.length;
    const totalPaginas = Math.max(1, Math.ceil(totalRegistros / ITENS_POR_PAGINA));

    if (estadoPaginacao.paginaPendentes > totalPaginas) estadoPaginacao.paginaPendentes = totalPaginas;
    if (estadoPaginacao.paginaPendentes < 1) estadoPaginacao.paginaPendentes = 1;

    const indiceInicial = (estadoPaginacao.paginaPendentes - 1) * ITENS_POR_PAGINA;
    const itensDaPagina = dadosPendentes.slice(indiceInicial, indiceInicial + ITENS_POR_PAGINA);

    const corpoTabela = selecionar('#tbody-os-pendentes');
    if (!corpoTabela) return;

    if (itensDaPagina.length === 0) {
        corpoTabela.innerHTML = `<tr><td colspan="3" class="text-center text-muted fst-italic py-4">Nenhuma OS</td></tr>`;
    } else {
        corpoTabela.innerHTML = itensDaPagina.map(item => `
      <tr>
        <td class="fw-bold text-primary">#${item.id}</td>
        <td>${escaparHtml(item.proxima_etapa)}</td>
        <td>${formatarData(item.criado_em)}</td>
      </tr>
    `).join('');
    }

    const barraPaginacao = selecionar('#paginacao-os-pendentes');
    if (!barraPaginacao) return;
    barraPaginacao.innerHTML = '';
    if (totalPaginas <= 1) return;

    const botaoAnterior = document.createElement('button');
    botaoAnterior.className = 'btn btn-sm btn-outline-primary';
    botaoAnterior.textContent = '← Anterior';
    botaoAnterior.disabled = estadoPaginacao.paginaPendentes <= 1;
    botaoAnterior.onclick = function () {
        if (estadoPaginacao.paginaPendentes > 1) {
            estadoPaginacao.paginaPendentes--;
            definirParametroUrl('pendente_p', estadoPaginacao.paginaPendentes);
            preencherTabelaPendentes();
        }
    };

    const infoPagina = document.createElement('span');
    infoPagina.className = 'text-muted';
    infoPagina.textContent = `Página ${estadoPaginacao.paginaPendentes} de ${totalPaginas} (máx. ${ITENS_POR_PAGINA}/página)`;

    const botaoProxima = document.createElement('button');
    botaoProxima.className = 'btn btn-sm btn-outline-primary';
    botaoProxima.textContent = 'Próxima →';
    botaoProxima.disabled = estadoPaginacao.paginaPendentes >= totalPaginas;
    botaoProxima.onclick = function () {
        if (estadoPaginacao.paginaPendentes < totalPaginas) {
            estadoPaginacao.paginaPendentes++;
            definirParametroUrl('pendente_p', estadoPaginacao.paginaPendentes);
            preencherTabelaPendentes();
        }
    };

    barraPaginacao.appendChild(botaoAnterior);
    barraPaginacao.appendChild(infoPagina);
    barraPaginacao.appendChild(botaoProxima);
}

// =================== 2) Tabela: Em Andamento =====================
function preencherTabelaAndamento() {
    const totalRegistros = dadosAndamento.length;
    const totalPaginas = Math.max(1, Math.ceil(totalRegistros / ITENS_POR_PAGINA));

    if (estadoPaginacao.paginaAndamento > totalPaginas) estadoPaginacao.paginaAndamento = totalPaginas;
    if (estadoPaginacao.paginaAndamento < 1) estadoPaginacao.paginaAndamento = 1;

    const indiceInicial = (estadoPaginacao.paginaAndamento - 1) * ITENS_POR_PAGINA;
    const itensDaPagina = dadosAndamento.slice(indiceInicial, indiceInicial + ITENS_POR_PAGINA);

    const corpoTabela = selecionar('#tbody-os-andamento');
    if (!corpoTabela) return;

    if (itensDaPagina.length === 0) {
        corpoTabela.innerHTML = `<tr><td colspan="3" class="text-center text-muted fst-italic py-4">Nenhuma OS</td></tr>`;
    } else {
        corpoTabela.innerHTML = itensDaPagina.map(item => `
      <tr>
        <td class="fw-bold text-primary">#${item.id}</td>
        <td>${escaparHtml(item.etapa_atual)}</td>
        <td>${formatarData(item.data_programada)}</td>
      </tr>
    `).join('');
    }

    const barraPaginacao = selecionar('#paginacao-os-andamento');
    if (!barraPaginacao) return;
    barraPaginacao.innerHTML = '';
    if (totalPaginas <= 1) return;

    const botaoAnterior = document.createElement('button');
    botaoAnterior.className = 'btn btn-sm btn-outline-primary';
    botaoAnterior.textContent = '← Anterior';
    botaoAnterior.disabled = estadoPaginacao.paginaAndamento <= 1;
    botaoAnterior.onclick = function () {
        if (estadoPaginacao.paginaAndamento > 1) {
            estadoPaginacao.paginaAndamento--;
            definirParametroUrl('andamento_p', estadoPaginacao.paginaAndamento);
            preencherTabelaAndamento();
        }
    };

    const infoPagina = document.createElement('span');
    infoPagina.className = 'text-muted';
    infoPagina.textContent = `Página ${estadoPaginacao.paginaAndamento} de ${totalPaginas} (máx. ${ITENS_POR_PAGINA}/página)`;

    const botaoProxima = document.createElement('button');
    botaoProxima.className = 'btn btn-sm btn-outline-primary';
    botaoProxima.textContent = 'Próxima →';
    botaoProxima.disabled = estadoPaginacao.paginaAndamento >= totalPaginas;
    botaoProxima.onclick = function () {
        if (estadoPaginacao.paginaAndamento < totalPaginas) {
            estadoPaginacao.paginaAndamento++;
            definirParametroUrl('andamento_p', estadoPaginacao.paginaAndamento);
            preencherTabelaAndamento();
        }
    };

    barraPaginacao.appendChild(botaoAnterior);
    barraPaginacao.appendChild(infoPagina);
    barraPaginacao.appendChild(botaoProxima);
}

// ==================== 3) Tabela: Encerradas ======================
function preencherTabelaEncerradas() {

    // Total de OS encerradas
    const totalRegistros = dadosEncerradas.length;

    // Total de páginas
    const totalPaginas = Math.max(1, Math.ceil(totalRegistros / ITENS_POR_PAGINA));

    // Ajusta a página atual se estiver fora do intervalo
    if (estadoPaginacao.paginaEncerradas > totalPaginas) estadoPaginacao.paginaEncerradas = totalPaginas;

    // Garante que a página atual seja pelo menos 1
    if (estadoPaginacao.paginaEncerradas < 1) estadoPaginacao.paginaEncerradas = 1;

    // Calcula o índice inicial e os itens da página atual
    const indiceInicial = (estadoPaginacao.paginaEncerradas - 1) * ITENS_POR_PAGINA;

    // Itens a serem exibidos na página atual exemplo: slice(0, 10), slice(10, 20), etc.
    const itensDaPagina = dadosEncerradas.slice(indiceInicial, indiceInicial + ITENS_POR_PAGINA);

    // Seleciona o corpo da tabela onde os dados serão inseridos
    const corpoTabela = selecionar('#tbody-os-encerradas');

    // Se o corpo da tabela não for encontrado, sai da função
    if (!corpoTabela) return;

    if (itensDaPagina.length === 0) {
        corpoTabela.innerHTML = `<tr><td colspan="3" class="text-center text-muted fst-italic py-4">Nenhuma OS</td></tr>`;
    } else {
        corpoTabela.innerHTML = itensDaPagina.map(item => `
      <tr>
        <td class="fw-bold text-primary">#${item.id}</td>
        <td>${escaparHtml(item.ultima_etapa)}</td>
        <td>${formatarData(item.data_encerramento)}</td>
      </tr>
    `).join('');
    }

    const barraPaginacao = selecionar('#paginacao-os-encerradas');
    if (!barraPaginacao) return;
    barraPaginacao.innerHTML = '';
    if (totalPaginas <= 1) return;

    const botaoAnterior = document.createElement('button');
    botaoAnterior.className = 'btn btn-sm btn-outline-primary';
    botaoAnterior.textContent = '← Anterior';
    botaoAnterior.disabled = estadoPaginacao.paginaEncerradas <= 1;
    botaoAnterior.onclick = function () {
        if (estadoPaginacao.paginaEncerradas > 1) {
            estadoPaginacao.paginaEncerradas--;
            definirParametroUrl('encerrada_p', estadoPaginacao.paginaEncerradas);
            preencherTabelaEncerradas();
        }
    };

    const infoPagina = document.createElement('span');
    infoPagina.className = 'text-muted';
    infoPagina.textContent = `Página ${estadoPaginacao.paginaEncerradas} de ${totalPaginas} (máx. ${ITENS_POR_PAGINA}/página)`;

    const botaoProxima = document.createElement('button');
    botaoProxima.className = 'btn btn-sm btn-outline-primary';
    botaoProxima.textContent = 'Próxima →';
    botaoProxima.disabled = estadoPaginacao.paginaEncerradas >= totalPaginas;
    botaoProxima.onclick = function () {
        if (estadoPaginacao.paginaEncerradas < totalPaginas) {
            estadoPaginacao.paginaEncerradas++;
            definirParametroUrl('encerrada_p', estadoPaginacao.paginaEncerradas);
            preencherTabelaEncerradas();
        }
    };

    barraPaginacao.appendChild(botaoAnterior);
    barraPaginacao.appendChild(infoPagina);
    barraPaginacao.appendChild(botaoProxima);
}
