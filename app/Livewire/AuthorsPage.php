<?php

namespace App\Livewire;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\{
    Url,
    Computed,
};

use App\Application\Usecases\Queries\{
    ListAutoresQuery, ListAutoresInputDTO,
    FindAutorByIdQuery, FindAutorByIdInputDTO
};
use App\Application\Usecases\Commands\{
    CreateAutorCommand, CreateAutorInputDTO,
    UpdateAutorCommand, UpdateAutorInputDTO,
    DeleteAutorCommand, DeleteAutorInputDTO
};

final class AuthorsPage extends Component
{
    use WithPagination;

    protected string $pageName = 'author_page';

    // Filtros/ordenação
    #[Url(history: true)] public string $search = '';
    #[Url(history: true)] public ?string $sort = 'nome'; // coluna válida no backend
    #[Url(history: true)] public string $dir = 'asc';

    // Tamanho da página
    #[Url(history: true)] public int $perPage = 10;

    // Edição
    public ?string $editingId = null;
    public string $name = '';
    public bool $saving = false;

    // Dependências
    private ListAutoresQuery $listAutores;
    private FindAutorByIdQuery $findAutor;
    private CreateAutorCommand $createAutor;
    private UpdateAutorCommand $updateAutor;
    private DeleteAutorCommand $deleteAutor;

    public function boot(
        ListAutoresQuery $listAutores,
        FindAutorByIdQuery $findAutor,
        CreateAutorCommand $createAutor,
        UpdateAutorCommand $updateAutor,
        DeleteAutorCommand $deleteAutor,
    ): void {
        $this->listAutores = $listAutores;
        $this->findAutor   = $findAutor;
        $this->createAutor = $createAutor;
        $this->updateAutor = $updateAutor;
        $this->deleteAutor = $deleteAutor;
    }

    /** ================= AÇÕES DE UI ================= */

    public function setSort(string $column): void
    {
        // UI -> coluna válida no backend
        $map = [
            'name' => 'nome',
        ];
        $col = $map[$column] ?? 'nome';

        if ($this->sort === $col) {
            $this->dir = $this->dir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $col;
            $this->dir  = 'asc';
        }
        $this->resetPage($this->pageName);
    }

    public function updatedSearch(): void
    {
        $this->resetPage($this->pageName);
    }

    public function updatedPerPage(): void
    {
        $this->resetPage($this->pageName);
    }

    public function openCreate(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->saving = false;
        $this->dispatch('modal:open', id: 'authorModal');
    }

    public function openEdit(string $id): void
    {
        try {
            $output = $this->findAutor->execute(new FindAutorByIdInputDTO((int) $id));
            if ($output->autor === null) {
                $this->dispatch('show-error', message: 'Autor não encontrado');
                return;
            }

            $this->editingId = (string) $output->autor->codau();
            $this->name      = $output->autor->nome()->value();
            $this->saving    = false;

            $this->dispatch('modal:open', id: 'authorModal');
        } catch (\Throwable $e) {
            $this->dispatch('show-error', message: $e->getMessage());
        }
    }

    public function confirmDelete(string $id): void
    {
        $this->editingId = $id;
        $this->dispatch('modal:open', id: 'authorDeleteModal');
    }

    public function delete(): void
    {
        try {
            $this->deleteAutor->execute(new DeleteAutorInputDTO((int) $this->editingId));
            $this->dispatch('show-success', message: 'Autor excluído com sucesso');
        } catch (\DomainException $e) {
            $this->dispatch('show-error', message: $e->getMessage());
        } catch (\Throwable $e) {
            $this->dispatch('show-error', message: 'Erro ao excluir autor: ' . $e->getMessage());
        } finally {
            $this->dispatch('modal:close', id: 'authorDeleteModal');
            $this->editingId = null;
        }
    }

    /** ================= SALVAR ================= */

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    public function save(): void
    {
        $this->saving = true;
        $this->validate();

        try {
            if ($this->editingId) {
                $this->updateAutor->execute(new UpdateAutorInputDTO(
                    codau: (int) $this->editingId,
                    nome: $this->name
                ));
                $this->dispatch('show-success', message: 'Autor atualizado com sucesso');
            } else {
                $this->createAutor->execute(new CreateAutorInputDTO(
                    nome: $this->name
                ));
                $this->dispatch('show-success', message: 'Autor criado com sucesso');
            }

            $this->dispatch('modal:close', id: 'authorModal');
            $this->name = '';
            $this->editingId = null;
            $this->resetPage($this->pageName);
        } catch (\DomainException $e) {
            $this->addError('name', $e->getMessage());
        } catch (\Throwable $e) {
            $this->dispatch('show-error', message: 'Erro ao salvar autor: ' . $e->getMessage());
        } finally {
            $this->saving = false;
        }
    }

    /** ================= COMPUTED ================= */

    #[Computed]
    public function rows(): array
    {
        $currentPage = $this->getPage($this->pageName);

        $output = $this->listAutores->execute(new ListAutoresInputDTO(
            search: $this->search ?: null,
            sort:   $this->sort ?: 'nome',
            dir:    $this->dir,
            page:   $currentPage,
            limit:  $this->perPage
        ));

        $items = array_map(function ($autor) {
            return [
                'id'   => (string) $autor->codau(),
                'name' => $autor->nome()->value(),
            ];
        }, $output->autores());

        return [
            'items'       => $items,
            'total'       => $output->total,
            'pages'       => $output->totalPages,
            'currentPage' => $currentPage,
        ];
    }

    public function render()
    {
        return view('livewire.authors-page', [
            'rows' => $this->rows,
        ]);
    }
}
