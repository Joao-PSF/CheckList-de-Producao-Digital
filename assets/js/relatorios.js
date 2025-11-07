// assets/js/relatorios.js

(function() {
  'use strict';

  // Alternar entre abas
  const tabButtons = document.querySelectorAll('[data-tab]');
  const tabContents = document.querySelectorAll('[data-tab-content]');

  tabButtons.forEach(btn => {
    btn.addEventListener('click', function() {
      const targetTab = this.getAttribute('data-tab');
      
      // Remover active de todos
      tabButtons.forEach(b => b.classList.remove('active'));
      tabContents.forEach(c => c.classList.remove('active', 'show'));
      
      // Adicionar active ao selecionado
      this.classList.add('active');
      const targetContent = document.querySelector(`[data-tab-content="${targetTab}"]`);
      if (targetContent) {
        targetContent.classList.add('active', 'show');
      }
      
      // Atualizar URL para manter a aba ativa ao recarregar
      const url = new URL(window.location);
      url.searchParams.set('aba', targetTab);
      window.history.replaceState({}, '', url);
    });
  });

  // Função de impressão
  window.imprimirRelatorio = function() {
    // Encontrar a aba ativa
    const activeTab = document.querySelector('.tab-content .tab-pane.active');
    if (!activeTab) {
      alert('Nenhum relatório para imprimir');
      return;
    }

    // Criar nova janela para impressão
    const printWindow = window.open('', '_blank');
    
    const html = `
      <!DOCTYPE html>
      <html lang="pt-br">
      <head>
        <meta charset="UTF-8">
        <title>Relatório - Sistema Metalma</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
          @media print {
            .no-print { display: none !important; }
            body { font-size: 12px; }
            .table { font-size: 11px; }
            .card { break-inside: avoid; }
          }
          body { padding: 20px; }
          .header-print { 
            text-align: center; 
            margin-bottom: 30px; 
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
          }
        </style>
      </head>
      <body>
        <div class="header-print">
          <h2>Sistema Metalma - Relatórios</h2>
          <p class="mb-0">Gerado em: ${new Date().toLocaleString('pt-BR')}</p>
        </div>
        ${activeTab.innerHTML}
        <script>
          window.onload = function() {
            window.print();
            setTimeout(function() { window.close(); }, 100);
          }
        </script>
      </body>
      </html>
    `;
    
    printWindow.document.write(html);
    printWindow.document.close();
  };

  // Aplicar filtros de serviços
  const formFiltroServicos = document.getElementById('formFiltroServicos');
  if (formFiltroServicos) {
    formFiltroServicos.addEventListener('submit', function(e) {
      e.preventDefault();
      this.submit();
    });
  }

  // Aplicar filtros de logs
  const formFiltroLogs = document.getElementById('formFiltroLogs');
  if (formFiltroLogs) {
    formFiltroLogs.addEventListener('submit', function(e) {
      e.preventDefault();
      this.submit();
    });
  }

  // Limpar filtros
  window.limparFiltros = function(formId) {
    const form = document.getElementById(formId);
    if (form) {
      // Determinar qual aba está ativa
      let abaAtual = 'servicos';
      if (formId === 'formFiltroLogs') {
        abaAtual = 'logs';
      }
      
      // Redirecionar para a página limpa, mantendo apenas page e aba
      const url = new URL(window.location.origin + window.location.pathname);
      url.searchParams.set('page', 'relatorios');
      url.searchParams.set('aba', abaAtual);
      window.location.href = url.toString();
    }
  };

  // Toggle de tabelas (recolher/expandir)
  const toggleButtons = document.querySelectorAll('.toggle-table');
  toggleButtons.forEach(btn => {
    btn.addEventListener('click', function() {
      const targetId = this.getAttribute('data-target');
      const target = document.getElementById(targetId);
      const icon = this.querySelector('i');
      
      if (target) {
        if (target.classList.contains('show')) {
          target.classList.remove('show');
          icon.classList.remove('bi-chevron-up');
          icon.classList.add('bi-chevron-down');
        } else {
          target.classList.add('show');
          icon.classList.remove('bi-chevron-down');
          icon.classList.add('bi-chevron-up');
        }
      }
    });
  });

})();
