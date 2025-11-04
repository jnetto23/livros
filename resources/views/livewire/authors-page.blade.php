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
    <button class="btn btn-primary d-inline-flex align-items-center gap-2" wire:click="openCreate">
      <span class="fw-semibold">Novo autor</span>
    </button>
  </div>

  {{-- Search --}}
  <div class="d-flex gap-2">
    <div class="position-relative" style="max-width: 340px;">
      <input type="text" class="form-control" placeholder="Pesquisar..."
             wire:model.live.debounce.400ms="search">
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-sm table-hover align-middle mb-0">
        <thead>
        <tr>
          <th style="width:60%;">
            <button class="btn btn-link p-0 text-decoration-none" wire:click="setSort('name')">
              Nome @if($sort==='name') <small>{{ strtoupper($dir) }}</small> @endif
            </button>
          </th>
          <th class="text-end" style="width:140px;"></th>
        </tr>
        </thead>
        <tbody>
          @forelse($this->rows['items'] as $row)
            <tr>
              <td>{{ $row['name'] }}</td>
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
  @php($total=$this->rows['total']) @php($pages=$this->rows['pages'])
  @if($pages>1)
    <div class="d-flex justify-content-between align-items-center">
      <small class="text-muted">
        Mostrando {{ ($page-1)*$perPage + 1 }} de {{ min($page*$perPage, $total) }} total {{ $total }} resultados
      </small>
      <div class="btn-group">
        <button class="btn btn-outline-secondary btn-sm" @disabled($page===1) wire:click="goto({{ $page-1 }})">Anterior</button>
        @for($p=1;$p<=$pages;$p++)
          <button class="btn btn-sm {{ $p===$page ? 'btn-primary' : 'btn-outline-secondary' }}" wire:click="goto({{ $p }})">
            {{ $p }}
          </button>
        @endfor
        <button class="btn btn-outline-secondary btn-sm" @disabled($page===$pages) wire:click="goto({{ $page+1 }})">Próxima</button>
      </div>
    </div>
  @endif

  {{-- Modal Create/Edit --}}
  <div class="modal" tabindex="-1" id="authorModal" wire:ignore.self>
    <div class="modal-dialog">
      <form class="modal-content" wire:submit.prevent="save" novalidate>
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">{{ $editingId ? 'Editar Autor' : 'Cadastrar Autor' }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                  onclick="bootstrap.Modal.getInstance(document.getElementById('authorModal'))?.hide()"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="name" class="form-label">Nome <span class="text-danger">*</span></label>
            <input id="name" type="text" maxlength="255" class="form-control @error('name') is-invalid @enderror"
                   wire:model.defer="name" placeholder="Ex: Robert C. Martin">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline-secondary" type="button"
                  onclick="bootstrap.Modal.getInstance(document.getElementById('authorModal'))?.hide()"
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
  <div class="modal" tabindex="-1" id="authorDeleteModal" wire:ignore.self>
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Excluir autor</h5></div>
        <div class="modal-body"><p class="mb-0">Esta ação não pode ser desfeita.</p></div>
        <div class="modal-footer">
          <button class="btn btn-outline-secondary" type="button"
                  onclick="bootstrap.Modal.getInstance(document.getElementById('authorDeleteModal'))?.hide()">Cancelar</button>
          <button class="btn btn-danger" wire:click="delete">Excluir</button>
        </div>
      </div>
    </div>
  </div>
</div>
