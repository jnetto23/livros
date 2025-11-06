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
    <button class="btn btn-primary d-inline-flex align-items-center gap-2"
            wire:click="openCreate"
            wire:loading.attr="disabled">
      <span class="fw-semibold">Novo livro</span>
    </button>
  </div>

  {{-- Search --}}
  <div class="d-flex gap-2">
    <div class="position-relative" style="max-width:340px;">
      <input type="text"
             class="form-control"
             placeholder="Pesquisar por titulo, autor ou assunto..."
             wire:model.live.debounce.400ms="search"
             wire:keydown.enter.prevent>
    </div>
  </div>

  {{-- Tabela --}}
  @php
    // UI -> coluna válida no backend (apenas para exibir Indicador de sort)
    $sortMap = [
      'title'     => 'titulo',
      'publisher' => 'editora',
      'edition'   => 'edicao',
      'year'      => 'anopublicacao',
      'autores'   => 'autor',
      'price'     => 'valor',
    ];
    $currentSort = $sort ?? 'titulo';
  @endphp

  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" style="table-layout: fixed; width: 100%;">
        <thead>
          <tr>
            @foreach(['title'=>'Título','publisher'=>'Editora','edition'=>'Edição','year'=>'Ano','autores'=>'Autores','price'=>'Preço'] as $key => $label)
                <th style="
                    @if($key==='title') width:28%;
                    @elseif($key==='publisher') width:15%;
                    @elseif($key==='edition') width:8%;
                    @elseif($key==='year') width:8%;
                    @elseif($key==='autores') width:25%;
                    @elseif($key==='price') width:8%;
                    @endif
                ">
                @if($key !== 'edition')
                {{-- Colunas ordenáveis --}}
                <a class="link-dark p-0 text-decoration-none"
                    wire:click="setSort('{{ $key }}')"
                    wire:loading.attr="disabled"
                    role="button">
                    {{ $label }}
                    @if(($sortMap[$key] ?? 'titulo') === $currentSort)
                    <span style="font-size: 12px; display:inline-block; width: 12px;">
                        @if(strtoupper($dir) === 'ASC') &#8595;
                        @elseif(strtoupper($dir) === 'DESC') &#8593;
                        @endif
                    </span>
                    @endif
                </a>
                @else
                {{-- Coluna não ordenável --}}
                <span class="link-dark p-0 text-decoration-none">{{ $label }}</span>
                @endif
              </th>
            @endforeach
            <th class="text-end" style="width:140px;"></th>
          </tr>
        </thead>

        <tbody wire:loading.class="opacity-50">
          <tr wire:loading wire:target="search,setSort,previousPage,nextPage,gotoPage,openCreate,openEdit,confirmDelete,save,delete">
            <td colspan="7" class="py-4 text-center text-muted">Carregando…</td>
          </tr>

          <?php $items = $rows['items'] ?? []; ?>
          @forelse($items as $row)
            <tr wire:key="row-{{ $row['id'] }}">
              <td class="text-break">
                {{ $row['title'] }} <br>
                <small class="text-muted">{{ $row['subject'] }}</small>
              </td>
              <td>{{ $row['publisher'] }}</td>
              <td class="text-nowrap">{{ $row['edition'] }}</td>
              <td class="text-nowrap">{{ $row['year'] }}</td>
              <td>{{ $row['autores'] }}</td>
              <td class="text-end text-nowrap">{{ $row['price'] }}</td>
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
              <td colspan="7" class="text-center text-muted py-4">Sem resultados.</td>
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
                  wire:key="p-{{ $p }}"
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

  {{-- Modal Create/Edit --}}
  <div class="modal" tabindex="-1" id="bookModal" wire:ignore.self>
    <div class="modal-dialog modal-lg">
      <form class="modal-content" wire:submit.prevent="save" novalidate>
        <div class="modal-header">
          <h5 class="modal-title">{{ $editingId ? 'Editar Livro' : 'Cadastrar Livro' }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="row g-3">
            <div class="col-sm-6">
              <label for="title" class="form-label">Título <span class="text-danger">*</span></label>
              <input id="title" type="text" maxlength="40"
                     class="form-control @error('title') is-invalid @enderror"
                     wire:model.defer="title"
                     placeholder="Digite o título do livro">
              @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-sm-6">
              <label for="publisher" class="form-label">Editora <span class="text-danger">*</span></label>
              <input id="publisher" type="text" maxlength="40"
                     class="form-control @error('publisher') is-invalid @enderror"
                     wire:model.defer="publisher"
                     placeholder="Digite o nome da editora">
              @error('publisher') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-sm-4">
              <label for="edition" class="form-label">Edição</label>
              <input id="edition" type="number" min="1"
                     class="form-control @error('edition') is-invalid @enderror"
                     wire:model.defer="edition">
              @error('edition') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-sm-4">
              <label for="year" class="form-label">Ano de Publicação</label>
              <input id="year" type="text" maxlength="4"
                     class="form-control @error('year') is-invalid @enderror"
                     wire:model.defer="year"
                     placeholder="2025" inputmode="numeric">
              @error('year') <div class="invalid-feedback">{{ $message }}</div> @enderror
              <small class="text-muted">Digite o ano com 4 dígitos (ex: 2025)</small>
            </div>

            <div class="col-sm-4">
              <label for="price" class="form-label">Preço <span class="text-danger">*</span></label>
              <div class="input-group" wire:ignore>
                <span class="input-group-text">R$</span>
                <input id="price" type="text"
                       class="form-control @error('price') is-invalid @enderror"
                       wire:model.defer="price"
                       placeholder="24,99">
              </div>
              @error('price') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              <small class="text-muted">Formato: 24,99</small>
            </div>

            <div class="col-12">
              <label for="autoresIds" class="form-label">Autores <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-people"></i></span>
                <select id="autoresIds"
                        class="form-control @error('autoresIds') is-invalid @enderror"
                        multiple size="5"
                        wire:model.defer="autoresIds">
                  @foreach(($autores ?? []) as $autor)
                    <option wire:key="autor-opt-{{ $autor['id'] }}" value="{{ $autor['id'] }}">
                      {{ $autor['name'] }}
                    </option>
                  @endforeach
                </select>
              </div>
              @error('autoresIds') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
              <label for="assuntosIds" class="form-label">Assuntos <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                <select id="assuntosIds"
                        class="form-control @error('assuntosIds') is-invalid @enderror"
                        multiple size="5"
                        wire:model.defer="assuntosIds">
                  @foreach(($assuntos ?? []) as $assunto)
                    <option wire:key="assunto-opt-{{ $assunto['id'] }}" value="{{ $assunto['id'] }}">
                      {{ $assunto['description'] }}
                    </option>
                  @endforeach
                </select>
              </div>
              @error('assuntosIds') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
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

  {{-- Modal Delete --}}
  <div class="modal" tabindex="-1" id="bookDeleteModal" wire:ignore.self>
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Excluir livro</h5></div>
        <div class="modal-body"><p class="mb-0">Esta ação não pode ser desfeita.</p></div>
        <div class="modal-footer">
          <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-danger" wire:click="delete" wire:loading.attr="disabled">Excluir</button>
        </div>
      </div>
    </div>
  </div>

  {{-- Scripts: eventos e máscara de preço --}}
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

        // Reaplica Inputmask após morph (Livewire v3)
        Livewire.hook('morph.updated', () => initPriceMask());
      });

      function initPriceMask() {
        const el = document.getElementById('price');
        if (!el || typeof Inputmask === 'undefined') return;
        if (el.inputmask) Inputmask.remove(el);
        Inputmask("currency", {
          prefix: "R$ ",
          groupSeparator: ".",
          radixPoint: ",",
          autoGroup: true,
          rightAlign: false
        }).mask(el);
      }

      // aplica quando o modal é mostrado
      document.getElementById('bookModal')?.addEventListener('shown.bs.modal', () => {
        setTimeout(initPriceMask, 50);
      });

      // DOM ready
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPriceMask);
      } else {
        initPriceMask();
      }
    })();
  </script>
</div>
