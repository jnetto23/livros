<?php

namespace App\Livewire;

use App\Application\Usecases\Commands\CreateAutorCommand;
use App\Application\Usecases\Commands\DeleteAutorCommand;
use App\Application\Usecases\Commands\UpdateAutorCommand;
use App\Application\Usecases\Queries\FindAutorByIdQuery;
use App\Application\Usecases\Queries\ListAutoresQuery;
use Livewire\Component;

class AuthorsPage extends Component
{
    public string $search = '';
    public string $sort = 'name';
    public string $dir = 'asc';

    public int $page = 1;
    public int $perPage = 10;

    public ?string $editingId = null;
    public string $name = '';
    public bool $saving = false;

    public function mount(): void
    {
        // Carrega dados iniciais
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
            $query = app(FindAutorByIdQuery::class);
            $input = new \App\Application\Usecases\Queries\FindAutorByIdInputDTO(codau: (int) $id);
            $output = $query->execute($input);
            assert($output instanceof \App\Application\Usecases\Queries\FindAutorByIdOutputDTO);

            if ($output->autor === null) {
                $this->dispatch('show-error', message: 'Autor não encontrado');
                return;
            }

            $this->editingId = (string) $output->autor->codau();
            $this->name = $output->autor->nome()->value();
            $this->saving = false;
            $this->dispatch('modal:open', id: 'authorModal');
        } catch (\Throwable $e) {
            $this->dispatch('show-error', message: $e->getMessage());
        }
    }

    public function save(): void
    {
        $this->saving = true;

        $this->validate(['name' => ['required', 'string', 'max:255']]);

        try {
            if ($this->editingId) {
                $command = app(UpdateAutorCommand::class);
                $input = new \App\Application\Usecases\Commands\UpdateAutorInputDTO(
                    codau: (int) $this->editingId,
                    nome: $this->name
                );
                $command->execute($input);
                $this->dispatch('show-success', message: 'Autor atualizado com sucesso');
            } else {
                $command = app(CreateAutorCommand::class);
                $input = new \App\Application\Usecases\Commands\CreateAutorInputDTO(
                    nome: $this->name
                );
                $output = $command->execute($input);
                assert($output instanceof \App\Application\Usecases\Commands\CreateAutorOutputDTO);
                $this->dispatch('show-success', message: 'Autor criado com sucesso');
            }

            $this->dispatch('modal:close', id: 'authorModal');
            $this->reset(['editingId', 'name']);
        } catch (\DomainException $e) {
            $this->addError('name', $e->getMessage());
        } catch (\Throwable $e) {
            $this->dispatch('show-error', message: 'Erro ao salvar autor: ' . $e->getMessage());
        } finally {
            $this->saving = false;
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
            $command = app(DeleteAutorCommand::class);
            $input = new \App\Application\Usecases\Commands\DeleteAutorInputDTO(codau: (int) $this->editingId);
            $command->execute($input);

            $this->dispatch('show-success', message: 'Autor excluído com sucesso');
            $this->dispatch('modal:close', id: 'authorDeleteModal');
            $this->editingId = null;
        } catch (\DomainException $e) {
            $this->dispatch('modal:close', id: 'authorDeleteModal');
            $this->dispatch('show-error', message: $e->getMessage());
        } catch (\Throwable $e) {
            $this->dispatch('modal:close', id: 'authorDeleteModal');
            $this->dispatch('show-error', message: 'Erro ao excluir autor: ' . $e->getMessage());
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
            $query = app(ListAutoresQuery::class);
            $input = new \App\Application\Usecases\Queries\ListAutoresInputDTO(
                search: $this->search ?: null,
                sort: $this->sort,
                dir: $this->dir,
                page: $this->page,
                limit: $this->perPage
            );

            $output = $query->execute($input);
            assert($output instanceof \App\Application\Usecases\Queries\ListAutoresOutputDTO);

            $items = array_map(function ($autor) {
                return [
                    'id' => (string) $autor->codau(),
                    'name' => $autor->nome()->value(),
                ];
            }, $output->autores());

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
        return view('livewire.authors-page');
    }
}
