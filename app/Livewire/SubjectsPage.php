<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\{
    Url,
    Computed,
};

use App\Application\Usecases\Commands\{
    CreateAssuntoCommand, CreateAssuntoInputDTO,
    UpdateAssuntoCommand, UpdateAssuntoInputDTO,
    DeleteAssuntoCommand, DeleteAssuntoInputDTO
};
use App\Application\Usecases\Queries\{
    ListAssuntosQuery, ListAssuntosInputDTO,
    FindAssuntoByIdQuery, FindAssuntoByIdInputDTO
};

final class SubjectsPage extends Component
{
    use WithPagination;

    protected string $pageName = 'page';

    #[Url(history: true)] public string $search = '';
    #[Url(history: true)] public ?string $sort = 'descricao'; // coluna do backend
    #[Url(history: true)] public string $dir = 'asc';
    #[Url(history: true)] public int $perPage = 10;

    public ?string $editingId = null;
    public string $description = '';
    public bool $saving = false;

    private ListAssuntosQuery $listAssuntos;
    private FindAssuntoByIdQuery $findAssunto;
    private CreateAssuntoCommand $createAssunto;
    private UpdateAssuntoCommand $updateAssunto;
    private DeleteAssuntoCommand $deleteAssunto;

    public function boot(
        ListAssuntosQuery $listAssuntos,
        FindAssuntoByIdQuery $findAssunto,
        CreateAssuntoCommand $createAssunto,
        UpdateAssuntoCommand $updateAssunto,
        DeleteAssuntoCommand $deleteAssunto,
    ): void {
        $this->listAssuntos = $listAssuntos;
        $this->findAssunto = $findAssunto;
        $this->createAssunto = $createAssunto;
        $this->updateAssunto = $updateAssunto;
        $this->deleteAssunto = $deleteAssunto;
    }

    /* ---------- UI ---------- */

    public function setSort(string $column): void
    {
        $map = [
            'description' => 'descricao'
        ];
        $col = $map[$column] ?? 'descricao';

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
        $this->description = '';
        $this->saving = false;

        $this->dispatch('modal:open', id: 'subjectModal');
    }

    public function openEdit(string $id): void
    {
        $output = $this->findAssunto->execute(new FindAssuntoByIdInputDTO((int) $id));

        if (!$output->assunto) {
            $this->dispatch('show-error', message: 'Assunto não encontrado');
            return;
        }

        $this->editingId = (string) $output->assunto->codas();
        $this->description = $output->assunto->descricao()->value();
        $this->saving = false;

        $this->dispatch('modal:open', id: 'subjectModal');
    }

    public function confirmDelete(string $id): void
    {
        $this->editingId = $id;
        $this->dispatch('modal:open', id: 'subjectDeleteModal');
    }

    public function delete(): void
    {
        $this->deleteAssunto->execute(new DeleteAssuntoInputDTO((int) $this->editingId));

        $this->dispatch('show-success', message: 'Assunto excluído com sucesso');
        $this->dispatch('modal:close', id: 'subjectDeleteModal');
        $this->editingId = null;
        $this->resetPage($this->pageName);
    }

    /* ---------- Save ---------- */

    protected function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:255'],
        ];
    }

    public function save(): void
    {
        $this->saving = true;
        $this->validate();

        if ($this->editingId) {
            $this->updateAssunto->execute(
                new UpdateAssuntoInputDTO(
                    (int) $this->editingId,
                    $this->description
                )
            );
            $msg = 'Assunto atualizado com sucesso';
        } else {
            $this->createAssunto->execute(
                new CreateAssuntoInputDTO($this->description)
            );
            $msg = 'Assunto criado com sucesso';
        }

        $this->dispatch('show-success', message: $msg);
        $this->dispatch('modal:close', id: 'subjectModal');

        $this->editingId = null;
        $this->description = '';
        $this->resetPage($this->pageName);
        $this->saving = false;
    }

    /* ---------- Computed ---------- */

    #[Computed]
    public function rows(): array
    {
        $page = $this->getPage($this->pageName);

        $output = $this->listAssuntos->execute(new ListAssuntosInputDTO(
            search: $this->search ?: null,
            sort: $this->sort ?: 'descricao',
            dir:  $this->dir,
            page: $page,
            limit: $this->perPage
        ));

        return [
            'items'       => array_map(fn($a) => [
                'id' => (string) $a->codas(),
                'description' => $a->descricao()->value(),
            ], $output->assuntos()),
            'total'       => $output->total,
            'pages'       => $output->totalPages,
            'currentPage' => $page,
        ];
    }

    public function render()
    {
        return view('livewire.subjects-page', [
            'rows' => $this->rows,
        ]);
    }
}
