<?php

namespace App\Livewire;

use App\Application\Usecases\Commands\CreateAssuntoCommand;
use App\Application\Usecases\Commands\DeleteAssuntoCommand;
use App\Application\Usecases\Commands\UpdateAssuntoCommand;
use App\Application\Usecases\Queries\FindAssuntoByIdQuery;
use App\Application\Usecases\Queries\ListAssuntosQuery;
use Livewire\Component;

class SubjectsPage extends Component
{
    public string $search = '';
    public string $sort = 'description';
    public string $dir = 'asc';

    public int $page = 1;
    public int $perPage = 10;

    public ?string $editingId = null;
    public string $description = '';
    public bool $saving = false;

    public function mount(): void
    {
        // Carrega dados iniciais
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
        try {
            $query = app(FindAssuntoByIdQuery::class);
            $input = new \App\Application\Usecases\Queries\FindAssuntoByIdInputDTO(codas: (int) $id);
            $output = $query->execute($input);
            assert($output instanceof \App\Application\Usecases\Queries\FindAssuntoByIdOutputDTO);

            if ($output->assunto === null) {
                $this->dispatch('show-error', message: 'Assunto não encontrado');
                return;
            }

            $this->editingId = (string) $output->assunto->codas();
            $this->description = $output->assunto->descricao()->value();
            $this->saving = false;
            $this->dispatch('modal:open', id: 'subjectModal');
        } catch (\Throwable $e) {
            $this->dispatch('show-error', message: $e->getMessage());
        }
    }

    public function save(): void
    {
        $this->saving = true;

        $this->validate([
            'description' => ['required', 'string', 'max:255'],
        ]);

        try {
            if ($this->editingId) {
                $command = app(UpdateAssuntoCommand::class);
                $input = new \App\Application\Usecases\Commands\UpdateAssuntoInputDTO(
                    codas: (int) $this->editingId,
                    descricao: $this->description
                );
                $command->execute($input);
                $this->dispatch('show-success', message: 'Assunto atualizado com sucesso');
            } else {
                $command = app(CreateAssuntoCommand::class);
                $input = new \App\Application\Usecases\Commands\CreateAssuntoInputDTO(
                    descricao: $this->description
                );
                $output = $command->execute($input);
                assert($output instanceof \App\Application\Usecases\Commands\CreateAssuntoOutputDTO);
                $this->dispatch('show-success', message: 'Assunto criado com sucesso');
            }

            $this->dispatch('modal:close', id: 'subjectModal');
            $this->reset(['editingId', 'description']);
        } catch (\DomainException $e) {
            $this->addError('description', $e->getMessage());
        } catch (\Throwable $e) {
            $this->dispatch('show-error', message: 'Erro ao salvar assunto: ' . $e->getMessage());
        } finally {
            $this->saving = false;
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
            $command = app(DeleteAssuntoCommand::class);
            $input = new \App\Application\Usecases\Commands\DeleteAssuntoInputDTO(codas: (int) $this->editingId);
            $command->execute($input);

            $this->dispatch('show-success', message: 'Assunto excluído com sucesso');
            $this->dispatch('modal:close', id: 'subjectDeleteModal');
            $this->editingId = null;
        } catch (\DomainException $e) {
            $this->dispatch('modal:close', id: 'subjectDeleteModal');
            $this->dispatch('show-error', message: $e->getMessage());
        } catch (\Throwable $e) {
            $this->dispatch('modal:close', id: 'subjectDeleteModal');
            $this->dispatch('show-error', message: 'Erro ao excluir assunto: ' . $e->getMessage());
        }
    }

    public function setSort(string $column): void
    {
        if ($this->sort === $column) {
            $this->dir = $this->dir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $column;
            $this->dir = 'asc';
        }
        $this->page = 1;
    }

    public function updatedSearch(): void
    {
        $this->page = 1;
    }

    public function goto(int $p): void
    {
        $this->page = max(1, $p);
    }

    public function getRowsProperty(): array
    {
        try {
            $query = app(ListAssuntosQuery::class);
            $input = new \App\Application\Usecases\Queries\ListAssuntosInputDTO(
                search: $this->search ?: null,
                sort: $this->sort,
                dir: $this->dir,
                page: $this->page,
                limit: $this->perPage
            );

            $output = $query->execute($input);
            assert($output instanceof \App\Application\Usecases\Queries\ListAssuntosOutputDTO);

            $items = array_map(function ($assunto) {
                return [
                    'id' => (string) $assunto->codas(),
                    'description' => $assunto->descricao()->value(),
                ];
            }, $output->assuntos());

            return [
                'items' => $items,
                'total' => $output->total,
                'pages' => $output->totalPages,
            ];
        } catch (\Throwable $e) {
            return [
                'items' => [],
                'total' => 0,
                'pages' => 0,
            ];
        }
    }

    public function render()
    {
        return view('livewire.subjects-page');
    }
}
