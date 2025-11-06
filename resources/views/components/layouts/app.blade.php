<!doctype html>
<html lang="pt-BR" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Livros')</title>

  {{-- Bootstrap 5 --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css"/>

  {{-- Select2 (opcional) --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"/>

  {{-- Ícones Bootstrap (opcional) --}}
  {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css"> --}}

  {{-- Seus assets Vite --}}
  @vite(['resources/sass/app.scss', 'resources/js/app.js'])

  @stack('styles')
  @livewireStyles
</head>
<body>

  {{-- Header --}}
  <div class="app-header border-bottom">
    <div class="container py-3 d-flex align-items-center gap-3">
      <div class="d-inline-flex align-items-center justify-content-center rounded"
           style="width:40px;height:40px;background-color:rgba(0,0,0,0.03);">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24">
          <path d="M19 2H8a3 3 0 0 0-3 3v13a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2Zm0 16H7V5a1 1 0 0 1 1-1h11Z"/>
          <path d="M5 6H4a2 2 0 0 0-2 2v12a1 1 0 0 0 1 1h12a2 2 0 0 0 2-2v-1H5Z"/>
        </svg>
      </div>
      <div>
        <h1 class="h5 mb-0">@yield('page_title', 'Livros')</h1>
        <small class="text-muted">@yield('page_subtitle', 'Gerencie seu estoque de livros')</small>
      </div>
    </div>
  </div>

  {{-- Conteúdo --}}
  <div class="container py-4">
    {{-- Toast Container para notificações --}}
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1060;">
      <div id="toastNotification" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="2500">
        <div class="toast-header">
          <strong class="me-auto" id="toastTitle">Notificação</strong>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toastMessage"></div>
      </div>
    </div>

    {{ $slot }}
  </div>

  {{-- Scripts (jQuery full, não slim) --}}
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/inputmask@5.0.7/dist/inputmask.min.js"></script>

  @livewireScripts
  @stack('scripts')

  <script>
    // ===== Ajuste navegacao =====
    window.addEventListener('clear-querystring', () => {
        history.replaceState({}, '', location.pathname);
    });
    // ===== Modais: abrir/fechar via Browser Events do Livewire =====
    window.addEventListener('modal:open', (e) => {
      const el = document.getElementById(e.detail.id);
      if (!el) return;
      bootstrap.Modal.getOrCreateInstance(el).show();
    });

    window.addEventListener('modal:close', (e) => {
      const el = document.getElementById(e.detail.id);
      if (!el) return;
      bootstrap.Modal.getOrCreateInstance(el).hide();
    });

    // ===== Toasts globais =====
    function showToast(kind, message) {
      const toastEl = document.getElementById('toastNotification');
      const titleEl = document.getElementById('toastTitle');
      const msgEl   = document.getElementById('toastMessage');

      titleEl.textContent = kind === 'success' ? 'Sucesso' : 'Erro';
      titleEl.className   = 'me-auto ' + (kind === 'success' ? 'text-success' : 'text-danger');
      msgEl.textContent   = message || (kind === 'success' ? 'Operação realizada com sucesso' : 'Ocorreu um erro');

      const toast = bootstrap.Toast.getOrCreateInstance(toastEl);
      toast.show();
    }

    window.addEventListener('show-success', (e) => {
      showToast('success', e.detail?.message);
    });

    window.addEventListener('show-error', (e) => {
      showToast('error', e.detail?.message);
    });

    // ===== Restrições simples de campo (year) =====
    document.addEventListener('input', function (e) {
      if (e.target.id === 'year' && e.target.type === 'text') {
        e.target.value = e.target.value.replace(/[^0-9]/g, '');
      }
    });

    document.addEventListener('keypress', function (e) {
      if (e.target.id === 'year' && e.target.type === 'text') {
        const ch = String.fromCharCode(e.which || e.keyCode);
        if (!/[0-9]/.test(ch)) e.preventDefault();
      }
    });

    // ===== Remoção de erros Livewire quando input fica válido =====
    function removeErrorIfValid(input) {
      if (!input || input.tagName === 'SELECT') return;
      if (!input.classList.contains('is-invalid')) return;

      const raw = input.value ? String(input.value).trim() : '';
      if (!raw) return;

      if (input.id === 'price') {
        let clean = raw.replace(/[R$\s]/g, '');
        if (clean.includes('.') && clean.includes(',')) clean = clean.replace(/\./g, '').replace(',', '.');
        else if (clean.includes(',')) clean = clean.replace(',', '.');
        const n = parseFloat(clean);
        if (isNaN(n) || n <= 0) return;
      }

      setTimeout(() => {
        input.classList.remove('is-invalid');

        let container = input.closest('.input-group')?.parentElement
          || input.closest('.mb-3, .col-sm-6, .col-sm-4, .col-12')
          || input.parentElement;

        if (container) {
          container.querySelectorAll('.invalid-feedback')?.forEach(n => n.remove());
        }
      }, 300);
    }

    document.addEventListener('input', (e) => {
      const el = e.target;
      if (el.tagName !== 'SELECT') removeErrorIfValid(el);
    });

    document.addEventListener('blur', (e) => {
      const el = e.target;
      if (el.id === 'price' && el.tagName !== 'SELECT') {
        setTimeout(() => removeErrorIfValid(el), 200);
      }
    }, true);

    // ===== Select2 init opcional (apenas se houver .js-select2) =====
    document.addEventListener('livewire:navigated', initSelect2IfNeeded);
    document.addEventListener('DOMContentLoaded', initSelect2IfNeeded);

    function initSelect2IfNeeded() {
      if (!window.jQuery || !jQuery.fn.select2) return;
      const $ = jQuery;
      $('.js-select2').each(function () {
        const $el = $(this);
        if ($el.data('select2')) return; // já inicializado
        $el.select2({
          theme: 'bootstrap-5',
          width: '100%',
          placeholder: $el.attr('placeholder') || '',
        }).on('change', function () {
          // Empurra valor para Livewire (quando usado com wire:model.defer)
          const name = this.getAttribute('wire:model') || this.getAttribute('wire:model.defer');
          if (name && window.Livewire) {
            const comp = window.Livewire.find(document.querySelector('[wire\\:id]')?.getAttribute('wire:id'));
            if (comp) comp.set(name.replace('this.', ''), $el.val());
          }
        });
      });
    }
  </script>
</body>
</html>
