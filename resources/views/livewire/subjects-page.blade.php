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
      <h2 class="h4 mb-1">Assuntos</h2>
      <small class="text-muted">Gerenciar assuntos e categorias de livros</small>
    </div>
    <button class="btn btn-primary d-inline-flex align-items-center gap-2" wire:click="openCreate">
      <span class="fw-semibold">Novo assunto</span>
    </button>
  </div>

  {{-- Search --}}
  <div class="d-flex gap-2">
    <div class="position-relative" style="max-width: 340px;">
      <input type="text" class="form-control" placeholder="Pesquisar..."
             wire:model.live.debounce.400ms="search">
    </div>
  </div>

    @php
        // Para exibir o indicador de sort no cabeçalho
        $sortMap = ['description' => 'descricao'];
        $currentSort = $sort ?? 'descricao';
    @endphp

  {{-- Table --}}
  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th style="width:60%;">
                <a class="link-dark p-0 text-decoration-none"
                      wire:click="setSort('description')"
                      wire:loading.attr="disabled">
                Assunto
                @if(($sortMap['description'] ?? 'description') === $currentSort)
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
        <tbody>
          @forelse($this->rows['items'] as $row)
            <tr>
              <td>{{ $row['description'] }}</td>
              <td class="text-end">
                <button class="btn btn-outline-secondary btn-sm" wire:click="openEdit('{{ $row['id'] }}')">Editar</button>
                <button class="btn btn-outline-danger btn-sm" wire:click="confirmDelete('{{ $row['id'] }}')">Excluir</button>
              </td>
            </tr>
          @empty
            <tr><td colspan="2" class="text-center text-muted py-4">Sem resultados.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

    {{-- Pagination --}}
    @php
        $items       = $rows['items'] ?? [];
        $total       = $rows['total'] ?? 0;
        $pages       = $rows['pages'] ?? 0;
        $currentPage = $rows['currentPage'] ?? 1;
        $count       = count($items);
        $from        = $total ? (($currentPage - 1) * $perPage) + 1 : 0;
        $to          = $from ? $from + $count - 1 : 0;
    @endphp

    @if($pages > 1)
    <div class="d-flex justify-content-between align-items-center">
    <small class="text-muted">
        Mostrando {{ $from }}–{{ $to }} de {{ $total }} resultados
    </small>

    <div class="btn-group">
        <button class="btn btn-outline-secondary btn-sm"
                wire:click="previousPage('{{ $this->pageName }}')"
                @if($currentPage === 1) disabled @endif>
        Anterior
        </button>

        @for($p=1; $p <= $pages; $p++)
        <button class="btn btn-sm {{ $p === $currentPage ? 'btn-primary' : 'btn-outline-secondary' }}"
                wire:click="gotoPage({{ $p }}, '{{ $this->pageName }}')"
                wire:key="subject-page-{{ $p }}">
            {{ $p }}
        </button>
        @endfor

        <button class="btn btn-outline-secondary btn-sm"
                wire:click="nextPage('{{ $this->pageName }}')"
                @if($currentPage === $pages) disabled @endif>
        Próxima
        </button>
    </div>
    </div>
    @endif

  {{-- Modal Create/Edit --}}
  <div class="modal" tabindex="-1" id="subjectModal" wire:ignore.self>
    <div class="modal-dialog">
      <form class="modal-content" wire:submit.prevent="save" novalidate>
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">{{ $editingId ? 'Editar Assunto' : 'Cadastrar Assunto' }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                  onclick="bootstrap.Modal.getInstance(document.getElementById('subjectModal'))?.hide()"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="description" class="form-label">Assunto <span class="text-danger">*</span></label>
            <input id="description" type="text" maxlength="255" class="form-control @error('description') is-invalid @enderror"
                   wire:model.defer="description" placeholder="Ficção Científica">
            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline-secondary" type="button"
                  onclick="bootstrap.Modal.getInstance(document.getElementById('subjectModal'))?.hide()"
                  >Cancelar</button>
          <button class="btn btn-primary" type="submit">
            <span wire:loading.remove wire:target="save">Salvar</span>
            <span wire:loading wire:target="save">Salvando...</span>
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- Modal Delete --}}
  <div class="modal" tabindex="-1" id="subjectDeleteModal" wire:ignore.self>
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Excluir assunto</h5></div>
        <div class="modal-body"><p class="mb-0">Esta ação não pode ser desfeita.</p></div>
        <div class="modal-footer">
          <button class="btn btn-outline-secondary" type="button"
                  onclick="bootstrap.Modal.getInstance(document.getElementById('subjectDeleteModal'))?.hide()">Cancelar</button>
          <button class="btn btn-danger" wire:click="delete">Excluir</button>
        </div>
      </div>
    </div>
  </div>
</div>
