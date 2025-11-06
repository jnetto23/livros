<div>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <ul class="nav nav-tabs mb-0">
      <li class="nav-item">
        <a href="#"
           class="nav-link {{ $currentPage==='subjects' ? 'active' : '' }}"
           wire:click.prevent="setPage('subjects')">
          Assuntos
        </a>
      </li>
      <li class="nav-item">
        <a href="#"
           class="nav-link {{ $currentPage==='authors' ? 'active' : '' }}"
           wire:click.prevent="setPage('authors')">
          Autores
        </a>
      </li>
      <li class="nav-item">
        <a href="#"
           class="nav-link {{ $currentPage==='books' ? 'active' : '' }}"
           wire:click.prevent="setPage('books')">
          Livros
        </a>
      </li>
    </ul>

    <a href="{{ route('report.livros-por-autor', ['inline' => 1]) }}" target="_blank" class="btn btn-secondary">
    {{-- ícone --}}
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
         fill="currentColor" viewBox="0 0 16 16">
      <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h3V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
      <path d="M10.5 8a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0m-2-3a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0M5.5 10a.5.5 0 1 1 0-1 .5.5 0 0 1 0 1m3 0a.5.5 0 1 1 0-1 .5.5 0 0 1 0 1"/>
    </svg>

    Relatório
    </a>
  </div>

  @if($currentPage==='subjects')
    @livewire('subjects-page')
  @elseif($currentPage==='authors')
    @livewire('authors-page')
  @else
    @livewire('books-page')
  @endif
</div>
