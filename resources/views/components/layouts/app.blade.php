<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Livros</title>

  {{-- Bootstrap 5 --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" />
  {{-- Select2 CSS --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
  @vite(['resources/sass/app.scss', 'resources/js/app.js'])
  @livewireStyles
</head>
<body>

  <div class="app-header border-bottom">
    <div class="container py-3 d-flex align-items-center gap-3">
      <div class="d-inline-flex align-items-center justify-content-center rounded" style="width:40px;height:40px;background-color:rgba(255,255,255,0.2);">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"><path d="M19 2H8a3 3 0 0 0-3 3v13a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2Zm0 16H7V5a1 1 0 0 1 1-1h11Z"/><path d="M5 6H4a2 2 0 0 0-2 2v12a1 1 0 0 0 1 1h12a2 2 0 0 0 2-2v-1H5Z"/></svg>
      </div>
      <div>
        <h1 class="h5 mb-0">Livros</h1>
        <small class="">Gerencie seu estoque de livros</small>
      </div>
    </div>
  </div>

  <div class="container py-4">
    {{-- Toast Container para notificações --}}
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1060;">
      <div id="toastNotification" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
          <strong class="me-auto" id="toastTitle">Notificação</strong>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toastMessage"></div>
      </div>
    </div>

    {{ $slot }}
  </div>

  {{-- Scripts --}}
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.0/dist/jquery.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  {{-- Inputmask para máscaras --}}
  <script src="https://cdn.jsdelivr.net/npm/inputmask@5.0.7/dist/inputmask.min.js"></script>

  @livewireScripts

  {{-- Eventos globais para abrir/fechar modais Bootstrap a partir do Livewire --}}
  <script>

    window.addEventListener('modal:open', e => {
      const el = document.getElementById(e.detail.id);
      if (!el) return;
      const modal = bootstrap.Modal.getOrCreateInstance(el);
      modal.show();
    });
    window.addEventListener('modal:close', e => {
      const el = document.getElementById(e.detail.id);
      if (!el) return;
      const modal = bootstrap.Modal.getOrCreateInstance(el);
      modal.hide();
    });

    // Notificações de sucesso/erro
    window.addEventListener('show-success', e => {
      const toast = document.getElementById('toastNotification');
      const title = document.getElementById('toastTitle');
      const message = document.getElementById('toastMessage');

      title.textContent = 'Sucesso';
      title.className = 'me-auto text-success';
      message.textContent = e.detail.message || 'Operação realizada com sucesso';
      toast.className = 'toast show';

      const bsToast = new bootstrap.Toast(toast);
      bsToast.show();
    });

    window.addEventListener('show-error', e => {
      const toast = document.getElementById('toastNotification');
      const title = document.getElementById('toastTitle');
      const message = document.getElementById('toastMessage');

      title.textContent = 'Erro';
      title.className = 'me-auto text-danger';
      message.textContent = e.detail.message || 'Ocorreu um erro';
      toast.className = 'toast show';

      const bsToast = new bootstrap.Toast(toast);
      bsToast.show();
    });

    // Restrição simples para campo de ano (aceitar apenas números)
    document.addEventListener('DOMContentLoaded', function() {
      // Restringe campo de ano para aceitar apenas números
      document.addEventListener('input', function(e) {
        if (e.target.id === 'year' && e.target.type === 'text') {
          e.target.value = e.target.value.replace(/[^0-9]/g, '');
        }
      });

      // Restringe campo de ano no keypress
      document.addEventListener('keypress', function(e) {
        if (e.target.id === 'year' && e.target.type === 'text') {
          const char = String.fromCharCode(e.which);
          if (!/[0-9]/.test(char)) {
            e.preventDefault();
          }
        }
      });
    });

    // Remove erros do Livewire quando o campo é preenchido corretamente
    if (window.Livewire) {
      function removeError(input) {
        // Ignora selects (multi-select nativo)
        if (!input || input.tagName === 'SELECT') return;
        if (!input.classList.contains('is-invalid')) return;

        const value = input.value ? String(input.value).trim() : '';
        if (!value) return;

        // Para campo de preço, valida se tem valor numérico válido
        if (input.id === 'price') {
          let cleanValue = value.replace(/[R$\s]/g, '');
          if (cleanValue.includes('.') && cleanValue.includes(',')) {
            cleanValue = cleanValue.replace(/\./g, '').replace(',', '.');
          } else if (cleanValue.includes(',')) {
            cleanValue = cleanValue.replace(',', '.');
          }
          const priceValue = parseFloat(cleanValue);
          if (isNaN(priceValue) || priceValue <= 0) {
            return; // Não remove erro se o valor não é válido
          }
        }

        // Remove classe de erro e mensagem
        setTimeout(() => {
          if (input.tagName === 'SELECT') return;
          if (input.value && input.value.trim()) {
            input.classList.remove('is-invalid');

            // Para campos dentro de input-group, pega o container pai
            let container = input.closest('.input-group');
            if (container) {
              container = container.parentElement;
            } else {
              container = input.closest('.mb-3, .col-sm-6, .col-sm-4, .col-12');
            }

            if (!container) {
              container = input.parentElement;
            }

            if (container) {
              // Remove mensagens de erro
              const errorMsgs = container.querySelectorAll('.invalid-feedback');
              errorMsgs.forEach(msg => msg.remove());
            }
          }
        }, 400); // Delay maior para dar tempo do inputmask formatar
      }

      document.addEventListener('input', function(e) {
        const input = e.target;
        // Ignora selects (multi-select nativo)
        if (input.tagName !== 'SELECT') {
          removeError(input);
        }
      });

      // Também escuta eventos do inputmask (blur)
      document.addEventListener('blur', function(e) {
        const input = e.target;
        if (input.id === 'price' && input.tagName !== 'SELECT') {
          setTimeout(() => removeError(input), 200);
        }
      }, true);
    }
  </script>
</body>
</html>

