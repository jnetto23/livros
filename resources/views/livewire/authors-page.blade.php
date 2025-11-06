<div class="vstack gap-3">
  {{-- Flash Messages --}}
  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <strong>Sucesso!</strong> {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <strong>Erro!</strong> {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <div class="d-flex justify-content-between align-items-end gap-2">
    <div>
      <h2 class="h4 mb-1">Autores</h2>
      <small class="text-muted">Gerenciar autores de livros</small>
    </div>
    <button class="btn btn-primary d-inline-flex align-items-center gap-2"
            wire:click="openCreate"
            wire:loading.attr="disabled">
      <span class="fw-semibold">Novo autor</span>
    </button>
  </div>

  {{-- Search --}}
  <div class="d-flex gap-2">
    <div class="position-relative" style="max-width:340px;">
      <input type="text"
             class="form-control"
             placeholder="Pesquisar..."
             wire:model.live.debounce.400ms="search"
             wire:keydown.enter.prevent>
    </div>
  </div>

  @php
    // Para exibir o indicador de sort no cabeçalho
    $sortMap = ['name' => 'nome'];
    $currentSort = $sort ?? 'nome';
  @endphp

  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th style="width:60%;">
              <a class="link-dark p-0 text-decoration-none"
                      wire:click="setSort('name')"
                      wire:loading.attr="disabled">
                Nome
                @if(($sortMap['name'] ?? 'nome') === $currentSort)
                  <span style="font-size: 12px; display:inline-block; width: 12px;">
                        @if(strtoupper($dir) === 'ASC') &#8595;
                        @elseif(strtoupper($dir) === 'DESC') &#8593;
                        @endif
                    </span>
                @endif
                </a>
            </th>
            <th class="text-end" style="width:140px;"></th>
          </tr>
        </thead>

        <tbody wire:loading.class="opacity-50">
          <tr wire:loading
              wire:target="search,setSort,previousPage,nextPage,gotoPage,openCreate,openEdit,confirmDelete,save,delete">
            <td colspan="2" class="py-4 text-center text-muted">Carregando…</td>
          </tr>

          <?php $items = $rows['items'] ?? []; ?>
          @forelse($items as $row)
            <tr wire:key="author-{{ $row['id'] }}">
              <td>{{ $row['name'] }}</td>
              <td class="text-end">
                <div class="btn-group">
                  <button class="btn btn-outline-secondary btn-sm"
                          wire:click="openEdit('{{ $row['id'] }}')"
                          wire:loading.attr="disabled">
                    Editar
                  </button>
                  <button class="btn btn-outline-danger btn-sm"
                          wire:click="confirmDelete('{{ $row['id'] }}')"
                          wire:loading.attr="disabled">
                    Excluir
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="2" class="text-center text-muted py-4">Sem resultados.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Paginação --}}
  <?php
    $total       = (int) ($rows['total'] ?? 0);
    $pages       = (int) ($rows['pages'] ?? 0);
    $currentPage = (int) ($rows['currentPage'] ?? 1);
    $perPage     = (int) ($perPage ?? 10);
    $countItems  = is_countable($items) ? count($items) : 0;
    $from        = $total ? (($currentPage - 1) * $perPage) + 1 : 0;
    $to          = $from ? $from + $countItems - 1 : 0;
  ?>

  @if($pages > 1)
    <div class="d-flex justify-content-between align-items-center">
      <small class="text-muted">
        Mostrando {{ $from }}–{{ $to }} de {{ $total }} resultados
      </small>

      <div class="btn-group">
        <button class="btn btn-outline-secondary btn-sm"
                wire:click="previousPage('{{ $this->pageName }}')"
                @if($currentPage === 1) disabled @endif
                wire:loading.attr="disabled">
          Anterior
        </button>

        @for($p = 1; $p <= $pages; $p++)
          <button class="btn btn-sm {{ $p === $currentPage ? 'btn-primary' : 'btn-outline-secondary' }}"
                  wire:click="gotoPage({{ $p }}, '{{ $this->pageName }}')"
                  wire:key="authors-page-{{ $p }}"
                  wire:loading.attr="disabled">
            {{ $p }}
          </button>
        @endfor

        <button class="btn btn-outline-secondary btn-sm"
                wire:click="nextPage('{{ $this->pageName }}')"
                @if($currentPage === $pages) disabled @endif
                wire:loading.attr="disabled">
          Próxima
        </button>
      </div>
    </div>
  @endif

  {{-- Modais --}}
  <div class="modal" tabindex="-1" id="authorModal" wire:ignore.self>
    <div class="modal-dialog">
      <form class="modal-content" wire:submit.prevent="save" novalidate>
        <div class="modal-header">
          <h5 class="modal-title">{{ $editingId ? 'Editar Autor' : 'Cadastrar Autor' }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label for="name" class="form-label">Nome <span class="text-danger">*</span></label>
            <input id="name" type="text" maxlength="20"
                   class="form-control @error('name') is-invalid @enderror"
                   wire:model.defer="name" placeholder="Ex: Robert C. Martin">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-primary" type="submit" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="save">Salvar</span>
            <span wire:loading wire:target="save">Salvando...</span>
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="modal" tabindex="-1" id="authorDeleteModal" wire:ignore.self>
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Excluir autor</h5></div>
        <div class="modal-body"><p class="mb-0">Esta ação não pode ser desfeita.</p></div>
        <div class="modal-footer">
          <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-danger" wire:click="delete" wire:loading.attr="disabled">Excluir</button>
        </div>
      </div>
    </div>
  </div>

  {{-- Scripts de modais --}}
  <script>
    (function() {
      document.addEventListener('livewire:init', () => {
        Livewire.on('modal:open', ({ id }) => {
          const el = document.getElementById(id);
          if (!el) return;
          bootstrap.Modal.getOrCreateInstance(el).show();
        });
        Livewire.on('modal:close', ({ id }) => {
          const el = document.getElementById(id);
          if (!el) return;
          bootstrap.Modal.getOrCreateInstance(el).hide();
        });
      });
    })();
  </script>
</div>
