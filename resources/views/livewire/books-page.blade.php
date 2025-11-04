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
      <h2 class="h4 mb-1">Livros</h2>
      <small class="text-muted">Gerenciar seu estoque de livros</small>
    </div>
    <button class="btn btn-primary d-inline-flex align-items-center gap-2" wire:click="openCreate">
      <span class="fw-semibold">Novo livro</span>
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
            @foreach(['title'=>'Título','publisher'=>'Editora','edition'=>'Edição','year'=>'Ano','autores'=>'Autores','price'=>'Preço'] as $key=>$label)
              <th>
                <button class="btn btn-link p-0 text-decoration-none" wire:click="setSort('{{ $key }}')">
                  {{ $label }} @if($sort===$key) <small>{{ strtoupper($dir) }}</small> @endif
                </button>
              </th>
            @endforeach
            <th class="text-end" style="width:140px;"></th>
          </tr>
        </thead>
        <tbody>
          @forelse($this->rows['items'] as $row)
            <tr>
              <td>
                {{ $row['title'] }}
                <br>
                <small class="text-muted">{{ $row['subject'] }}</small>
              </td>
              <td>{{ $row['publisher'] }}</td>
              <td>{{ $row['edition'] }}</td>
              <td>{{ $row['year'] }}</td>
              <td>{{ $row['autores'] }}</td>
              <td style="text-align: right;">{{ $row['price'] }}</td>
              <td class="text-end">
                <button class="btn btn-outline-secondary btn-sm" wire:click="openEdit('{{ $row['id'] }}')">Editar</button>
                <button class="btn btn-outline-danger btn-sm" wire:click="confirmDelete('{{ $row['id'] }}')">Excluir</button>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted py-4">Sem resultados.</td></tr>
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
  <div class="modal" tabindex="-1" id="bookModal" wire:ignore.self
       data-component="books-page"
       data-autores-ids="{{ json_encode($autoresIds ?? []) }}"
       data-assuntos-ids="{{ json_encode($assuntosIds ?? []) }}">
    <div class="modal-dialog modal-lg">
      <form class="modal-content" wire:submit.prevent="save" novalidate>
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">{{ $editingId ? 'Editar Livro' : 'Cadastrar Livro' }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                  onclick="bootstrap.Modal.getInstance(document.getElementById('bookModal'))?.hide()"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-sm-6">
              <label for="title" class="form-label">Título <span class="text-danger">*</span></label>
              <input id="title" type="text" maxlength="40" class="form-control @error('title') is-invalid @enderror" wire:model.defer="title" placeholder="Digite o título do livro">
              @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-sm-6">
              <label for="publisher" class="form-label">Editora <span class="text-danger">*</span></label>
              <input id="publisher" type="text" maxlength="40" class="form-control @error('publisher') is-invalid @enderror" wire:model.defer="publisher" placeholder="Digite o nome da editora">
              @error('publisher') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-sm-4">
              <label for="edition" class="form-label">Edição</label>
              <input id="edition" type="number" min="1" class="form-control @error('edition') is-invalid @enderror" wire:model.defer="edition">
              @error('edition') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-sm-4">
              <label for="year" class="form-label">Ano de Publicação</label>
              <input id="year" type="text" maxlength="4" class="form-control @error('year') is-invalid @enderror" wire:model.defer="year" placeholder="2024" inputmode="numeric">
              @error('year') <div class="invalid-feedback">{{ $message }}</div> @enderror
              <small class="text-muted">Digite o ano com 4 dígitos (ex: 2024)</small>
            </div>
            <div class="col-sm-4">
              <label for="price" class="form-label">Preço <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V1.5c-.587 0-1.18.103-1.706.25C9.09 1.85 8.5 2.5 8.5 3.5c0 .375.085.716.25.978l.5.896c.5.5 1.5.5 2 .5h.5v-1h-1c-1.5 0-2.5-1-2.5-2.5 0-1.5 1-2.5 2.5-2.5h.5V0h1v1.5c1.5 0 2.5 1 2.5 2.5 0 1.5-1 2.5-2.5 2.5h-.5V8h1v-.5c1.5 0 2.5 1 2.5 2.5 0 1.5-1 2.5-2.5 2.5h-.5V15h-1v-1.5c-1.5 0-2.5-1-2.5-2.5 0-1.5 1-2.5 2.5-2.5h.5V8h-1v.5c-1.5 0-2.5-1-2.5-2.5 0-1.5 1-2.5 2.5-2.5h.5V3h-1v1.5c-1.5 0-2.5 1-2.5 2.5 0 1.5 1 2.5 2.5 2.5h.5V9h1v-.5c1.5 0 2.5 1 2.5 2.5 0 1.5-1 2.5-2.5 2.5h-.5V15h-1v-1.5z"/>
                  </svg>
                </span>
                <input id="price" type="text" class="form-control @error('price') is-invalid @enderror" wire:model.defer="price" placeholder="R$ 24,99">
              </div>
              @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
              <small class="text-muted">Formato: R$ 24,99</small>
            </div>
            <div class="col-12">
              <label for="autoresIds" class="form-label">Autores <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664z"/>
                  </svg>
                </span>
                <select id="autoresIds" class="form-control @error('autoresIds') is-invalid @enderror" multiple="multiple" size="5" wire:model.defer="autoresIds">
                  @foreach($this->autores as $autor)
                    <option value="{{ $autor['id'] }}" {{ in_array($autor['id'], $autoresIds ?? []) ? 'selected' : '' }}>{{ $autor['name'] }}</option>
                  @endforeach
                </select>
              </div>
              @error('autoresIds') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12">
              <label for="assuntosIds" class="form-label">Assuntos <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M7.5 1.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3.5 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm-4 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3.5 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm-4 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3.5 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5z"/>
                    <path d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-12a2 2 0 0 1 2-2m1 2v11a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2z"/>
                  </svg>
                </span>
                <select id="assuntosIds" class="form-control @error('assuntosIds') is-invalid @enderror" multiple="multiple" size="5" wire:model.defer="assuntosIds">
                  @foreach($this->assuntos as $assunto)
                    <option value="{{ $assunto['id'] }}" {{ in_array($assunto['id'], $assuntosIds ?? []) ? 'selected' : '' }}>{{ $assunto['description'] }}</option>
                  @endforeach
                </select>
              </div>
              @error('assuntosIds') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline-secondary" type="button"
                  onclick="bootstrap.Modal.getInstance(document.getElementById('bookModal'))?.hide()">Cancelar</button>
          <button class="btn btn-primary" type="submit">
            <span wire:loading.remove wire:target="save">Salvar</span>
            <span wire:loading wire:target="save">Salvando...</span>
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- Scripts para Inputmask --}}
  <script>
    (function() {
      // Máscara para preço
      function initInputmask() {
        const priceInput = document.getElementById('price');
        if (priceInput && typeof Inputmask !== 'undefined') {
          // Remove máscara anterior se existir
          if (priceInput.inputmask) {
            Inputmask.remove(priceInput);
          }
          Inputmask("currency", {
            prefix: "R$ ",
            groupSeparator: ".",
            radixPoint: ",",
            autoGroup: true,
            rightAlign: false
          }).mask(priceInput);
        }
      }

      // Eventos do modal
      const bookModal = document.getElementById('bookModal');
      if (bookModal) {
        bookModal.addEventListener('shown.bs.modal', function() {
          setTimeout(function() {
            initInputmask();
          }, 100);
        });
      }

      // Inicialização quando DOM carregar
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
          initInputmask();
        });
      } else {
        initInputmask();
      }
    })();
  </script>

  {{-- Modal Delete --}}
  <div class="modal" tabindex="-1" id="bookDeleteModal" wire:ignore.self>
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Excluir livro</h5></div>
        <div class="modal-body"><p class="mb-0">Esta ação não pode ser desfeita.</p></div>
        <div class="modal-footer">
          <button class="btn btn-outline-secondary" type="button"
                  onclick="bootstrap.Modal.getInstance(document.getElementById('bookDeleteModal'))?.hide()">Cancelar</button>
          <button class="btn btn-danger" wire:click="delete">Excluir</button>
        </div>
      </div>
    </div>
  </div>
</div>
