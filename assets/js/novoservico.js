//-----------Validação do Formulário-----------//
(function () {
  'use strict';
  const forms = document.querySelectorAll('.needs-validation');
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });
})();


//------------Carregamento de dados e manipulação do formulário-------//
let USUARIOS = [];     // payload.data
let TIPOS = [];        // se um dia vier do servidor


// ---------- Multiselect ----------
function initMultiSelect(containerEl, usuarios, name) {

  const placeholder = 'Selecionar...';

  containerEl.innerHTML = `
    <div class="dropdown w-100" data-ms>
      <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start" type="button"
              data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
        <span class="ms-label">Selecionar...</span>
      </button>
      <div class="dropdown-menu p-2 w-100" style="max-height:260px; overflow:auto;">
        <input type="text" class="form-control form-control-sm mb-2" placeholder="Pesquisar..." aria-label="Pesquisar">
        <div class="vstack gap-1" data-ms-list></div>
      </div>
    </div>
    <div data-ms-values></div>
  `;

  // cada linha (checkbox + label) vai dentro de list
  const list = containerEl.querySelector('[data-ms-list]');

  //texto exibido no botão do dropdown
  const textoExibidoLista = containerEl.querySelector('.ms-label');

  // container dos inputs hidden
  const hiddenBox = containerEl.querySelector('[data-ms-values]');

  // input de busca
  const inputBusca = containerEl.querySelector('input[aria-label="Pesquisar"]');

  // monta as linhas (checkbox + label)
  usuarios.forEach(u => {

    //cria a label
    const row = document.createElement('label');
    row.className = 'd-flex align-items-center gap-2';

    // adiciona o checkbox e o span dentro da label
    row.innerHTML = `
      <input class="form-check-input" type="checkbox" value="${u.value}">
      <span>${u.label}</span>
    `;

    console.log(u);

    // adiciona a linha à lista
    list.appendChild(row);
  });

  function update() {

    // limpa hidden inputs
    hiddenBox.innerHTML = '';

    // Array temporário para armazenar os nomes dos itens selecionados (para exibir no botão).
    const names = [];

    // Seleciona todos os checkboxes marcados dentro da lista.
    list.querySelectorAll('input:checked').forEach(chk => {

      // Pega o texto da label associada ao checkbox
      const nameLabel = chk.parentElement.querySelector('span').textContent;

      // Adiciona o nome ao array
      names.push(nameLabel);

      // Cria um input hidden para cada item selecionado
      const h = document.createElement('input');

      // configura o input hidden
      h.type = 'hidden';

      // o name deve ser um array (name[])
      h.name = name;

      // valor do checkbox
      h.value = chk.value;

      // adiciona o input hidden ao container
      hiddenBox.appendChild(h);
    });

    // Atualiza o texto do botão baseado na quantidade de itens selecionados
    if (names.length === 0) textoExibidoLista.textContent = placeholder;

    // 1 ou 2 nomes, exibe os nomes
    else if (names.length <= 2) textoExibidoLista.textContent = names.join(', ');

    // mais de 2 nomes, exibe a quantidade
    else textoExibidoLista.textContent = `${names.length} selecionados`;
  }

  // Busca
  inputBusca.addEventListener('input', () => {

    // texto da busca
    const textoBusca = inputBusca.value.toLowerCase();

    // esconde/mostra linhas
    list.querySelectorAll('label').forEach(lab => {

      // texto da label
      const textoUser = lab.innerText.toLowerCase();

      // se contém o texto da busca, mostra; senão, esconde
      if (textoUser.includes(textoBusca)) {
        lab.classList.remove('d-none');
      } else {
        lab.classList.add('d-none');
      }
    });
  });

  list.addEventListener('change', update);
  update(); // inicial
}

// ---------- Usuários -> itens p/ multiselect ----------
function usersToItems(arr) {
  return (arr || []).map(u => ({
    value: u.id,
    label: u.nome
  }));
}

// ---------- Tipos de tarefa ----------

// Popula o select de tipos de tarefa
function fillTiposTarefa() {

  //Pega o elemento select do tipo de tarefa
  const sel = document.getElementById('tipo_tarefa');

  // Popula com as opções
  let html = '<option value="" disabled selected>Selecione...</option>';
  html += `
    <option value="manutencao">Manutenção</option>
    <option value="projeto">Projeto</option>
    <option value="documentacao">Documentação</option>
    <option value="compras">Compras</option>
    <option value="outro">Outro (especificar)</option>
  `;

  // Insere as opções no select
  sel.innerHTML = html;
}

// Configura o campo "Outro" do tipo de tarefa
function TipoTarefaOutro() {

  // Pega o elemento select do tipo de tarefa
  const sel = document.getElementById('tipo_tarefa');

  // Pega o grupo do campo "Outro"
  const group = document.getElementById('tipo_tarefa_outro_group');

  // Pega o input do campo "Outro"
  const input = document.getElementById('tipo_tarefa_outro');

  // Sincroniza a visibilidade do campo "Outro" com o valor do select
  function sync() {

    // Se o valor for "outro", mostra o campo e torna obrigatório
    if (sel.value === 'outro') {
      group.classList.remove('d-none');
      input.required = true;

    } else {

      // Caso contrário, esconde o campo e limpa o valor
      group.classList.add('d-none');
      input.required = false;
      input.value = '';
    }
  }

  // Escuta mudanças no select e sincroniza
  sel.addEventListener('change', sync);

  // Sincroniza inicialmente
  sync();
}

// ---------- Etapas ----------
const COLOR_CYCLE = [
  { bg: 'bg-primary', border: 'border-primary', text: 'text-white' },
  { bg: 'bg-success', border: 'border-success', text: 'text-white' },
  { bg: 'bg-info', border: 'border-info', text: 'text-dark' },
  { bg: 'bg-warning', border: 'border-warning', text: 'text-dark' },
  { bg: 'bg-danger', border: 'border-danger', text: 'text-white' },
  { bg: 'bg-secondary', border: 'border-secondary', text: 'text-white' },
  { bg: 'bg-dark', border: 'border-dark', text: 'text-white' }
];

let etapaUID = 0;

function applyColorToEtapa(card, idxZeroBased) {

  // Seleciona a cor baseada no índice da etapa
  const color = COLOR_CYCLE[idxZeroBased % COLOR_CYCLE.length];

  // Seleciona o header do card
  const header = card.querySelector('.card-header');

  // Aplica classes de Alinhamento e bordas
  card.className = 'card border';
  header.className = 'card-header d-flex justify-content-between align-items-center';

  // Aplica as classes de cor
  card.classList.add(color.border);
  header.classList.add(color.bg, color.text);

  // Aplica a classe do botão de remover etapa
  header.querySelector('.btn-remover-etapa').className = 'btn btn-sm btn-outline-light btn-remover-etapa';
}

function setNamesForCard(card, usuariosItems, idx) {
  // campos simples
  const titulo = card.querySelector('[data-key="titulo"]');
  const prazo = card.querySelector('[data-key="prazo"]');
  const obs = card.querySelector('[data-key="observacao"]');

  // Se os campos existem, define o name correto
  if (titulo) titulo.name = `etapas[${idx}][titulo]`;
  if (prazo) prazo.name = `etapas[${idx}][prazo]`;
  if (obs) obs.name = `etapas[${idx}][observacao]`;

  // multiselect da etapa
  const msBox = card.querySelector('[data-key="responsaveis"]');

  if (msBox) {

    // re-inicializa o multiselect com o name correto
    msBox.innerHTML = ''; // limpa conteúdo anterior se houver
    initMultiSelect(msBox, usuariosItems,`etapas[${idx}][responsaveis][]`);
  }
}

function addEtapa() {

  // Pega o container de etapas
  const container = document.getElementById('etapas-container');

  // Pega o template de etapa
  const templateDaEtapa = document.getElementById('tpl-etapa');

  // Clona o objeto do template <div class="card border">
  const node = templateDaEtapa.content.firstElementChild.cloneNode(true);

  // Define o número da etapa 
  // (baseado na quantidade atual de card dentro de etapas-container)
  const idx = container.querySelectorAll('.card').length; // zero-based

  // Atualiza o número da etapa no card
  node.querySelector('.etapa-num').textContent = idx + 1;

  // Atualiza os IDs dos campos da etapa
  etapaUID++;

  // Base para os IDs dos campos
  const idBase = 'etp-' + etapaUID + '-';

  // Atualiza os IDs dos campos dentro do card
  node.querySelector('.input-titulo').id = idBase + 'titulo';
  node.querySelector('.input-prazo').id = idBase + 'prazo';
  node.querySelector('.textarea-obs').id = idBase + 'obs';

  // multiselect dos responsáveis da etapa
  const users = usersToItems(USUARIOS);

  // define os name=etapas[idx][...]
  setNamesForCard(node, users, idx);

  // Aplica as cores à etapa
  applyColorToEtapa(node, idx);

  // Adiciona evento ao botão de remover etapa
  node.querySelector('.btn-remover-etapa').addEventListener('click', () => {
    node.remove();
    renumerarEtapas();
  });

  // Adiciona o card ao container de etapas
  container.appendChild(node);
}


function renumerarEtapas() {

  // Pega todos os cards dentro do container de etapas
  const cards = document.querySelectorAll('#etapas-container .card');

  // multiselect dos responsáveis da etapa
  const users = usersToItems(USUARIOS);
  
  // Atualiza o número e as cores de cada card
  cards.forEach((card, idx) => {

    // Atualiza o número da etapa (idx é zero-based)
    const numEl = card.querySelector('.etapa-num');
    if (numEl) numEl.textContent = idx + 1;

    // Reaplica as cores
    applyColorToEtapa(card, idx);

    // redefine os names para o novo índice
    setNamesForCard(card, users, idx);
  });
}

//-----------Renderização inicial (onload do body)----------//
function renderServicos(payload) {

  // Verifica se payload é um array, caso contrário, define como array vazio
  USUARIOS = Array.isArray(payload?.data) ? payload.data : [];

  console.log('USUARIOS:', payload);

  // Responsáveis gerais (multi)
  initMultiSelect(
    document.getElementById('responsavel_geral_ms'),
    usersToItems(USUARIOS),
    'responsavel_geral[]'
  );

  /*Tipos de tarefa
  fillTiposTarefa();
  TipoTarefaOutro();

  // Etapa inicial
  addEtapa();*/
}

//--------Eventos Globais--------//
document.addEventListener("input", function (e) {

  // Se o evento contiver a classe textarea-obs, atualiza o contador
  if (e.target.classList.contains("textarea-obs")) {

    // Seleciona o elemento pai do textarea
    const small = e.target.parentElement.querySelector(".contador");

    // Pega o limite do maxlength do textarea
    const limite = e.target.maxLength;

    // Atualiza o contador
    small.textContent = `${e.target.value.length} / ${limite}`;
  }
});