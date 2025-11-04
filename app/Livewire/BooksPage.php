<?php

namespace App\Livewire;

use App\Application\Usecases\Commands\CreateLivroCommand;
use App\Application\Usecases\Commands\DeleteLivroCommand;
use App\Application\Usecases\Commands\UpdateLivroCommand;
use App\Application\Usecases\Queries\FindLivroByIdQuery;
use App\Application\Usecases\Queries\ListLivrosQuery;
use App\Application\Usecases\Queries\ListAutoresQuery;
use App\Application\Usecases\Queries\ListAssuntosQuery;
use App\Application\Repository\AssuntoRepositoryInterface;
use Livewire\Component;

class BooksPage extends Component
{
    public string $search = '';
    public ?string $sort = 'title';
    public string $dir = 'asc';

    public int $page = 1;
    public int $perPage = 10;

    public ?string $editingId = null;

    public string $title = '';
    public string $publisher = '';
    public string $edition = '';
    public string $year = '';
    public array $autoresIds = [];
    public array $assuntosIds = [];
    public string $price = '';
    public bool $saving = false;

    public function mount(): void
    {
        // Carrega dados iniciais
    }

    public function openCreate(): void
    {
        $this->editingId = null;
        $this->resetForm();
        $this->autoresIds = [];
        $this->assuntosIds = [];
        $this->saving = false;
        $this->dispatch('modal:open', id: 'bookModal');
    }

    public function openEdit(string $id): void
    {
        try {
            $query = app(FindLivroByIdQuery::class);
            $input = new \App\Application\Usecases\Queries\FindLivroByIdInputDTO(codl: (int) $id);
            $output = $query->execute($input);
            assert($output instanceof \App\Application\Usecases\Queries\FindLivroByIdOutputDTO);

            if ($output->livro === null) {
                $this->dispatch('show-error', message: 'Livro não encontrado');
                return;
            }

            $livro = $output->livro;
            $this->editingId = (string) $livro->codl();
            $this->title = $livro->titulo()->value();
            $this->publisher = $livro->editora()->value();
            $this->edition = (string) $livro->edicao()->value();
            $this->year = $livro->anoPublicacao()->value();
            $this->price = 'R$ ' . number_format($livro->valor()->value() / 100, 2, ',', '.');
            $this->autoresIds = $livro->autoresIds();
            $this->assuntosIds = $livro->assuntosIds();
            $this->saving = false;
            $this->dispatch('modal:open', id: 'bookModal');
        } catch (\Throwable $e) {
            $this->dispatch('show-error', message: $e->getMessage());
        }
    }

    public function save(): void
    {
        $this->saving = true;

        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'publisher' => ['required', 'string', 'max:255'],
            'edition' => ['required', 'integer', 'min:1'],
            'year' => ['required', 'string', 'size:4'],
            'price' => ['required', 'string'],
            'autoresIds' => ['required', 'array', 'min:1'],
            'assuntosIds' => ['required', 'array', 'min:1'],
        ], [
            'autoresIds.required' => 'Selecione pelo menos um autor.',
            'autoresIds.min' => 'Selecione pelo menos um autor.',
            'assuntosIds.required' => 'Selecione pelo menos um assunto.',
            'assuntosIds.min' => 'Selecione pelo menos um assunto.',
        ]);

        try {
            // Converte preço de "R$ 24,99" para centavos (2499)
            $priceClean = preg_replace('/[^0-9,]/', '', $this->price);
            $priceClean = str_replace(',', '.', $priceClean);
            $valorFloat = (float) $priceClean;

            // Valida se o valor é válido e maior que zero
            if ($valorFloat <= 0) {
                $this->addError('price', 'O valor do livro deve ser maior que zero.');
                $this->saving = false;
                return;
            }

            $valorCentavos = (int) round($valorFloat * 100);

            // Valida se após conversão ainda é maior que zero (evita valores muito pequenos que viram 0)
            if ($valorCentavos <= 0) {
                $this->addError('price', 'O valor do livro deve ser maior que zero.');
                $this->saving = false;
                return;
            }

            if ($this->editingId) {
                $command = app(UpdateLivroCommand::class);
                $input = new \App\Application\Usecases\Commands\UpdateLivroInputDTO(
                    codl: (int) $this->editingId,
                    titulo: $this->title,
                    editora: $this->publisher,
                    edicao: (int) $this->edition,
                    anoPublicacao: $this->year,
                    valor: $valorCentavos,
                    autoresIds: array_map('intval', $this->autoresIds),
                    assuntosIds: array_map('intval', $this->assuntosIds)
                );
                $command->execute($input);
                $this->dispatch('show-success', message: 'Livro atualizado com sucesso');
            } else {
                $command = app(CreateLivroCommand::class);
                $input = new \App\Application\Usecases\Commands\CreateLivroInputDTO(
                    titulo: $this->title,
                    editora: $this->publisher,
                    edicao: (int) $this->edition,
                    anoPublicacao: $this->year,
                    valor: $valorCentavos,
                    autoresIds: array_map('intval', $this->autoresIds),
                    assuntosIds: array_map('intval', $this->assuntosIds)
                );
                $output = $command->execute($input);
                assert($output instanceof \App\Application\Usecases\Commands\CreateLivroOutputDTO);
                $this->dispatch('show-success', message: 'Livro criado com sucesso');
            }

            $this->dispatch('modal:close', id: 'bookModal');
            $this->resetForm();
        } catch (\DomainException $e) {
            $this->addError('title', $e->getMessage());
            $this->saving = false;
        } catch (\Throwable $e) {
            $this->dispatch('show-error', message: 'Erro ao salvar livro: ' . $e->getMessage());
            $this->saving = false;
        }
        // Garante que saving seja false mesmo se houver algum erro não tratado
        $this->saving = false;
    }

    public function confirmDelete(string $id): void
    {
        $this->editingId = $id;
        $this->dispatch('modal:open', id: 'bookDeleteModal');
    }

    public function delete(): void
    {
        try {
            $command = app(DeleteLivroCommand::class);
            $input = new \App\Application\Usecases\Commands\DeleteLivroInputDTO(codl: (int) $this->editingId);
            $command->execute($input);

            $this->dispatch('show-success', message: 'Livro excluído com sucesso');
            $this->dispatch('modal:close', id: 'bookDeleteModal');
            $this->editingId = null;
        } catch (\DomainException $e) {
            $this->dispatch('modal:close', id: 'bookDeleteModal');
            $this->dispatch('show-error', message: $e->getMessage());
        } catch (\Throwable $e) {
            $this->dispatch('modal:close', id: 'bookDeleteModal');
            $this->dispatch('show-error', message: 'Erro ao excluir livro: ' . $e->getMessage());
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
            $query = app(ListLivrosQuery::class);
            $input = new \App\Application\Usecases\Queries\ListLivrosInputDTO(
                search: $this->search ?: null,
                sort: $this->sort === 'title' ? 'title' : ($this->sort === 'publisher' ? 'publisher' : ($this->sort === 'edition' ? 'edition' : ($this->sort === 'year' ? 'year' : ($this->sort === 'price' ? 'valor' : 'title')))),
                dir: $this->dir,
                page: $this->page,
                limit: $this->perPage
            );

            $output = $query->execute($input);
            assert($output instanceof \App\Application\Usecases\Queries\ListLivrosOutputDTO);

            // Busca todos os assuntos para criar um mapa de IDs -> descrições
            $assuntoRepository = app(AssuntoRepositoryInterface::class);
            $assuntosMap = [];
            foreach ($output->livros() as $livro) {
                foreach ($livro->assuntosIds() as $assuntoId) {
                    if (!isset($assuntosMap[$assuntoId])) {
                        $assunto = $assuntoRepository->findById($assuntoId);
                        if ($assunto !== null) {
                            $assuntosMap[$assuntoId] = $assunto->descricao()->value();
                        }
                    }
                }
            }

            // Busca todos os autores para criar um mapa de IDs -> nomes
            $autorRepository = app(\App\Application\Repository\AutorRepositoryInterface::class);
            $autoresMap = [];
            foreach ($output->livros() as $livro) {
                foreach ($livro->autoresIds() as $autorId) {
                    if (!isset($autoresMap[$autorId])) {
                        $autor = $autorRepository->findById($autorId);
                        if ($autor !== null) {
                            $autoresMap[$autorId] = $autor->nome()->value();
                        }
                    }
                }
            }

            $items = array_map(function ($livro) use ($assuntosMap, $autoresMap) {
                // Busca os assuntos do livro e formata como texto
                $assuntosIds = $livro->assuntosIds();
                $assuntosDescricoes = [];
                foreach ($assuntosIds as $assuntoId) {
                    if (isset($assuntosMap[$assuntoId])) {
                        $assuntosDescricoes[] = $assuntosMap[$assuntoId];
                    }
                }
                $assuntoTexto = count($assuntosDescricoes) > 0
                    ? implode(', ', $assuntosDescricoes)
                    : 'Sem assunto';

                // Busca os autores do livro e formata como texto
                $autoresIds = $livro->autoresIds();
                $autoresNomes = [];
                foreach ($autoresIds as $autorId) {
                    if (isset($autoresMap[$autorId])) {
                        $autoresNomes[] = $autoresMap[$autorId];
                    }
                }
                $autoresTexto = count($autoresNomes) > 0
                    ? implode(', ', $autoresNomes)
                    : 'Sem autor';

                return [
                    'id' => (string) $livro->codl(),
                    'title' => $livro->titulo()->value(),
                    'publisher' => $livro->editora()->value(),
                    'edition' => (string) $livro->edicao()->value(),
                    'year' => $livro->anoPublicacao()->value(),
                    'autores' => $autoresTexto,
                    'subject' => $assuntoTexto,
                    'price' => 'R$ ' . number_format($livro->valor()->value() / 100, 2, ',', '.'),
                ];
            }, $output->livros());

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

    public function getAutoresProperty(): array
    {
        try {
            $query = app(ListAutoresQuery::class);
            $input = new \App\Application\Usecases\Queries\ListAutoresInputDTO(
                limit: 1000 // Busca todos para os selects
            );
            $output = $query->execute($input);
            assert($output instanceof \App\Application\Usecases\Queries\ListAutoresOutputDTO);

            return array_map(function ($autor) {
                return [
                    'id' => $autor->codau(),
                    'name' => $autor->nome()->value(),
                ];
            }, $output->autores());
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getAssuntosProperty(): array
    {
        try {
            $query = app(ListAssuntosQuery::class);
            $input = new \App\Application\Usecases\Queries\ListAssuntosInputDTO(
                limit: 1000 // Busca todos para os selects
            );
            $output = $query->execute($input);
            assert($output instanceof \App\Application\Usecases\Queries\ListAssuntosOutputDTO);

            return array_map(function ($assunto) {
                return [
                    'id' => $assunto->codas(),
                    'description' => $assunto->descricao()->value(),
                ];
            }, $output->assuntos());
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function resetForm(): void
    {
        $this->title = '';
        $this->publisher = '';
        $this->edition = '';
        $this->year = '';
        $this->price = '';
        $this->autoresIds = [];
        $this->assuntosIds = [];
    }

    public function render() { return view('livewire.books-page'); }
}
