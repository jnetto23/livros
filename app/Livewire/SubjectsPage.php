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
        $this->resetValidation();
        $this->editingId = null;
        $this->description = '';
        $this->saving = false;

        $this->dispatch('modal:open', id: 'subjectModal');
    }

    public function openEdit(string $id): void
    {
        try {
            $output = $this->findAssunto->execute(new FindAssuntoByIdInputDTO((int) $id));
            if (!$output->assunto) {
                $this->dispatch('show-error', message: 'Assunto não encontrado');
                return;
            }

            $this->resetValidation();
            $this->editingId = (string) $out->assunto->codas();
            $this->description = $out->assunto->descricao()->value();
            $this->saving = false;

            $this->dispatch('modal:open', id: 'subjectModal');
        } catch (\Throwable $e) {
            $this->dispatch('show-error', message: $e->getMessage());
        }
    }

    public function confirmDelete(string $id): void
    {
        $this->editingId = $id;
        $this->dispatch('modal:open', id: 'subjectDeleteModal');
    }

    public function delete(): void
    {
        try {
            $this->deleteAssunto->execute(new DeleteAssuntoInputDTO((int) $this->editingId));
            $this->dispatch('show-success', message: 'Assunto excluído com sucesso');
            $this->resetPage($this->pageName);
        } catch (\DomainException $e) {
            $this->dispatch('show-error', message: $e->getMessage());
        } catch (\Throwable $e) {
            $this->dispatch('show-error', message: 'Erro ao excluir assunto: ' . $e->getMessage());
        } finally {
            $this->dispatch('modal:close', id: 'subjectDeleteModal');
            $this->editingId = null;
        }
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

        try {
            if ($this->editingId) {
                $this->updateAssunto->execute(
                    new UpdateAssuntoInputDTO(
                        (int) $this->editingId,
                        $this->description
                    )
                );
                $this->dispatch('show-success', message: 'Assunto atualizado com sucesso');
            } else {
                $this->createAssunto->execute(
                    new CreateAssuntoInputDTO($this->description)
                );
                $this->dispatch('show-success', message: 'Assunto criado com sucesso');
                $this->resetPage($this->pageName);
            }

            $this->dispatch('modal:close', id: 'subjectModal');
            $this->resetValidation();
            $this->reset(['editingId', 'description']);
        } catch (\DomainException $e) {
            // Ex.: “Já existe um assunto com a descrição ‘X’.”
            $this->addError('description', $e->getMessage());
            $this->dispatch('show-error', message: $e->getMessage());
        } catch (\Throwable $e) {
            $this->dispatch('show-error', message: 'Erro ao salvar assunto: ' . $e->getMessage());
        } finally {
            $this->saving = false;
        }
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
