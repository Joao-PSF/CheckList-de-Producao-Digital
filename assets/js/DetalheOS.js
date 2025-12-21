// assets/js/detalhes-os.js

window.renderDetalhesOS = function renderDetalhesOS(config) {
  const USUARIOS = Array.isArray(config?.usuarios) ? config.usuarios : [];
  const TOTAL_ETAPAS = Number(config?.totalEtapas || 0);

  // ---- helpers
  function qs(sel, root = document) { return root.querySelector(sel); }
  function qsa(sel, root = document) { return Array.from(root.querySelectorAll(sel)); }

  // ---- modo edição
  function habilitarEdicao() {
    document.body.classList.add('editing');
    setupOrdemHandlers();
  }
  function cancelarEdicao() {
    if (confirm('Deseja cancelar as alterações?')) location.reload();
  }

  // ---- listeners nos botões editar/cancelar
  const btnEditar = qs('#btnEditar');
  const btnCancelar = qs('#btnCancelar');
  btnEditar && btnEditar.addEventListener('click', habilitarEdicao);
  btnCancelar && btnCancelar.addEventListener('click', cancelarEdicao);

  // ---- sistema de ajuste automático de ordem
  function setupOrdemHandlers() {
    const ordemInputs = qsa('input[name^="etapas["][name$="][ordem]"]');
    ordemInputs.forEach(input => {
      input.setAttribute('max', TOTAL_ETAPAS || input.getAttribute('max') || '');
      input.addEventListener('change', function () { ajustarOrdens(this); });
    });
  }

  function ajustarOrdens(changedInput) {
    const novaOrdem = parseInt(changedInput.value, 10);

    if (novaOrdem < 1) {
      alert('A ordem mínima é 1');
      changedInput.value = 1; return;
    }
    if (TOTAL_ETAPAS && novaOrdem > TOTAL_ETAPAS) {
      alert(`A ordem máxima é ${TOTAL_ETAPAS} (total de etapas)`);
      changedInput.value = TOTAL_ETAPAS; return;
    }

    const ordemInputs = qsa('input[name^="etapas["][name$="][ordem]"]');
    const etapasMap = [];
    ordemInputs.forEach(input => {
      const m = input.name.match(/etapas\[(\d+)\]\[ordem\]/);
      if (m) {
        etapasMap.push({
          id: m[1],
          input,
          ordemOriginal: parseInt(input.getAttribute('data-ordem-original') || input.value, 10)
        });
      }
    });

    const mChanged = changedInput.name.match(/etapas\[(\d+)\]\[ordem\]/);
    if (!mChanged) return;
    const changedId = mChanged[1];
    const changedEtapa = etapasMap.find(e => e.id === changedId);
    const ordemAntiga = changedEtapa.ordemOriginal;

    if (novaOrdem === ordemAntiga) return;

    etapasMap.forEach(etapa => {
      if (etapa.id === changedId) {
        etapa.input.setAttribute('data-ordem-original', novaOrdem);
        return;
      }
      const ordemAtual = parseInt(etapa.input.value, 10);
      if (novaOrdem > ordemAntiga) {
        if (ordemAtual > ordemAntiga && ordemAtual <= novaOrdem) {
          etapa.input.value = ordemAtual - 1;
          etapa.input.setAttribute('data-ordem-original', ordemAtual - 1);
        }
      } else {
        if (ordemAtual >= novaOrdem && ordemAtual < ordemAntiga) {
          etapa.input.value = ordemAtual + 1;
          etapa.input.setAttribute('data-ordem-original', ordemAtual + 1);
        }
      }
    });
  }

  // ---- expandir/recolher tudo do accordion
  (function wireAccordionBulkControls() {
    const accordion = qs('#etapasAccordion');
    if (!accordion) return;

    const btnExpand = qs('#btnExpandAll');
    const btnCollapse = qs('#btnCollapseAll');

    function getCollapses() { return qsa('.accordion-collapse', accordion); }

    btnExpand && btnExpand.addEventListener('click', () => {
      getCollapses().forEach(el => {
        const c = window.bootstrap?.Collapse.getOrCreateInstance(el, { toggle: false });
        c && c.show();
      });
    });

    btnCollapse && btnCollapse.addEventListener('click', () => {
      getCollapses().forEach(el => {
        const c = window.bootstrap?.Collapse.getOrCreateInstance(el, { toggle: false });
        c && c.hide();
      });
    });
  })();

  // ---- validação do submit
  (function wireFormValidation() {
    const form = qs('#formOS');
    const btnSalvar = qs('#btnSalvar');
    
    if (!form) {
      console.error('Formulário #formOS não encontrado');
      return;
    }

    form.addEventListener('submit', function (e) {
      console.log('Submit iniciado');
      
      // Pegar inputs de ordem
      const ordemInputs = qsa('input[name^="etapas["][name$="][ordem]"]');
      console.log('Inputs de ordem encontrados:', ordemInputs.length);
      
      // Se não há etapas, permitir o submit
      if (ordemInputs.length === 0) {
        console.log('Nenhuma etapa encontrada, permitindo submit');
        if (!confirm('Deseja salvar todas as alterações?')) {
          e.preventDefault();
          return;
        }
        // Se confirmou, mostrar indicador de carregamento
        if (btnSalvar) {
          btnSalvar.disabled = true;
          btnSalvar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Salvando...';
        }
        return;
      }
      
      // Se há etapas, validar ordem
      const ordens = ordemInputs.map(i => parseInt(i.value, 10)).sort((a, b) => a - b);
      console.log('Ordens encontradas:', ordens);

      // Verificar duplicatas
      const temDup = ordens.some((v, idx) => ordens.indexOf(v) !== idx);
      if (temDup) { 
        e.preventDefault(); 
        alert('Erro: Existem etapas com a mesma ordem.'); 
        return; 
      }

      // Verificar sequência
      for (let i = 0; i < ordens.length; i++) {
        if (ordens[i] !== (i + 1)) {
          e.preventDefault();
          alert('Erro: As ordens devem ser sequenciais (1, 2, 3...).');
          return;
        }
      }

      // Pedir confirmação final
      if (!confirm('Deseja salvar todas as alterações?')) {
        e.preventDefault();
        return;
      }
      
      // Se confirmou, mostrar indicador de carregamento
      if (btnSalvar) {
        btnSalvar.disabled = true;
        btnSalvar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Salvando...';
      }
    });
  })();

  // ---- multiselect do modal "Nova Etapa"
  function usersToItems(arr) { return (arr || []).map(u => ({ value: u.id, label: u.nome })); }

  function initMultiSelect(containerEl, usuarios, name, selectedValues = []) {
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
    const list = containerEl.querySelector('[data-ms-list]');
    const label = containerEl.querySelector('.ms-label');
    const hiddenBox = containerEl.querySelector('[data-ms-values]');
    const inputBusca = containerEl.querySelector('input[aria-label="Pesquisar"]');

    usuarios.forEach(u => {
      const isChecked = selectedValues.includes(String(u.value));
      const row = document.createElement('label');
      row.className = 'd-flex align-items-center gap-2';
      row.innerHTML = `<input class="form-check-input" type="checkbox" value="${u.value}" ${isChecked ? 'checked' : ''}><span>${u.label}</span>`;
      list.appendChild(row);
    });

    function update() {
      hiddenBox.innerHTML = '';
      const names = [];
      list.querySelectorAll('input:checked').forEach(chk => {
        const txt = chk.parentElement.querySelector('span').textContent;
        names.push(txt);
        const h = document.createElement('input');
        h.type = 'hidden'; h.name = name; h.value = chk.value;
        hiddenBox.appendChild(h);
      });
      if (names.length === 0) label.textContent = placeholder;
      else if (names.length <= 2) label.textContent = names.join(', ');
      else label.textContent = `${names.length} selecionados`;
    }

    inputBusca.addEventListener('input', () => {
      const q = inputBusca.value.toLowerCase();
      list.querySelectorAll('label').forEach(lab => {
        lab.classList.toggle('d-none', !lab.innerText.toLowerCase().includes(q));
      });
    });

    list.addEventListener('change', update);
    update();
  }

  // Função global para inicializar multiselect em etapas
  window.initMultiSelectEtapa = function(containerEl, usuarios, name, selectedValues = []) {
    initMultiSelect(containerEl, usuarios, name, selectedValues);
  };

  (function wireNovaEtapaModal() {
    const modalEl = qs('#modalNovaEtapa');
    if (!modalEl) return;

    modalEl.addEventListener('shown.bs.modal', function () {
      const container = qs('#multiSelectResponsaveis');
      if (container && !container.hasAttribute('data-initialized')) {
        initMultiSelect(container, usersToItems(USUARIOS), 'responsaveis[]');
        container.setAttribute('data-initialized', 'true');
      }
    });

    modalEl.addEventListener('hidden.bs.modal', function () {
      const form = qs('#formNovaEtapa'); form && form.reset();
      const container = qs('#multiSelectResponsaveis');
      container && container.removeAttribute('data-initialized');
    });
  })();

  // ---- ao carregar: salvar ordem original
  qsa('input[name^="etapas["][name$="][ordem]"]').forEach(input => {
    input.setAttribute('data-ordem-original', input.value);
  });

  // ---- botão de concluir etapa
  function setupConcluirEtapaButtons() {
    const buttons = qsa('.btn-concluir-etapa');
    
    buttons.forEach(button => {
      button.addEventListener('click', function() {
        const etapaId = this.getAttribute('data-etapa-id');
        const osId = this.getAttribute('data-os-id');
        const executada = this.getAttribute('data-executada') === '1';
        
        const acao = executada ? 'reverter a conclusão' : 'concluir';
        const confirmMsg = `Deseja realmente ${acao} esta etapa?`;
        
        if (!confirm(confirmMsg)) return;
        
        // Desabilitar botão durante processamento
        const originalContent = this.innerHTML;
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processando...';
        
        // Enviar requisição
        fetch('../../backend/servicos/ConcluirEtapa.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            etapa_id: parseInt(etapaId),
            os_id: parseInt(osId),
            executar: !executada
          })
        })
        .then(response => {
          // Verificar se a resposta é JSON válido
          const contentType = response.headers.get('content-type');
          if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
              throw new Error('Resposta inválida do servidor: ' + text.substring(0, 100));
            });
          }
          return response.json();
        })
        .then(data => {
          if (data.success) {
            // Mostrar mensagem de sucesso
            alert(data.message);
            
            // Recarregar página para atualizar todos os dados
            location.reload();
          } else {
            alert('Erro: ' + data.message);
            this.disabled = false;
            this.innerHTML = originalContent;
          }
        })
        .catch(error => {
          console.error('Erro:', error);
          alert('Erro ao processar solicitação: ' + error.message);
          this.disabled = false;
          this.innerHTML = originalContent;
        });
      });
    });
  }

  // Chamar função ao carregar
  setupConcluirEtapaButtons();

  // ---- botão de inativar/excluir etapa
  function setupInativarEtapaButtons() {
    const buttons = qsa('.btn-inativar-etapa');
    let etapaIdParaExcluir = null;
    let osIdParaExcluir = null;
    
    const modal = new window.bootstrap.Modal(document.getElementById('modalConfirmarExclusao'));
    const nomeEtapaElement = qs('#nomeEtapaExcluir');
    const btnConfirmar = qs('#btnConfirmarExclusao');
    
    buttons.forEach(button => {
      button.addEventListener('click', function() {
        etapaIdParaExcluir = this.getAttribute('data-etapa-id');
        osIdParaExcluir = this.getAttribute('data-os-id');
        const nomeEtapa = this.getAttribute('data-etapa-nome');
        
        // Atualizar nome da etapa no modal
        nomeEtapaElement.textContent = nomeEtapa;
        
        // Mostrar modal
        modal.show();
      });
    });
    
    // Confirmar exclusão
    btnConfirmar && btnConfirmar.addEventListener('click', function() {
      if (!etapaIdParaExcluir || !osIdParaExcluir) return;
      
      // Desabilitar botão durante processamento
      const originalContent = this.innerHTML;
      this.disabled = true;
      this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Excluindo...';
      
      // Enviar requisição
      fetch('../../backend/servicos/InativarEtapa.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          etapa_id: parseInt(etapaIdParaExcluir),
          os_id: parseInt(osIdParaExcluir)
        })
      })
      .then(response => {
        // Verificar se a resposta é JSON válido
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
          return response.text().then(text => {
            throw new Error('Resposta inválida do servidor: ' + text.substring(0, 100));
          });
        }
        return response.json();
      })
      .then(data => {
        if (data.success) {
          // Fechar modal
          modal.hide();
          
          // Mostrar mensagem de sucesso
          alert(data.message);
          
          // Recarregar página para atualizar todos os dados
          location.reload();
        } else {
          alert('Erro: ' + data.message);
          this.disabled = false;
          this.innerHTML = originalContent;
        }
      })
      .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao processar solicitação: ' + error.message);
        this.disabled = false;
        this.innerHTML = originalContent;
      });
    });
  }

  // Chamar função ao carregar
  setupInativarEtapaButtons();
};

