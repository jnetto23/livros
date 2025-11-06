<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\{
    Url,
    Computed,
};

use App\Application\Usecases\Queries\{
    ListLivrosQuery, ListLivrosInputDTO,
    ListAutoresQuery, ListAutoresInputDTO,
    ListAssuntosQuery, ListAssuntosInputDTO,
    FindLivroByIdQuery, FindLivroByIdInputDTO
};
use App\Application\Usecases\Commands\{
    CreateLivroCommand, CreateLivroInputDTO,
    UpdateLivroCommand, UpdateLivroInputDTO,
    DeleteLivroCommand, DeleteLivroInputDTO
};
use App\Application\Repository\{
    AutorRepositoryInterface,
    AssuntoRepositoryInterface
};

final class BooksPage extends Component
{
    use WithPagination;

    /**
     * Nome do paginador usado pelo trait (mantém compat com previousPage/gotoPage/nextPage).
     * Se você tiver mais de uma tabela na mesma página, crie outros pageNames.
     */
    protected string $pageName = 'book_page';

    // Filtros/ordenação
    #[Url(history: true)] public string $search = '';
    #[Url(history: true)] public ?string $sort = 'titulo'; // nomes de coluna válidos no backend
    #[Url(history: true)] public string $dir = 'asc';

    // Tamanho da página na URL (opcional)
    #[Url(history: true)] public int $perPage = 10;

    // Edição
    public ?string $editingId = null;

    // Form
    public string $title = '';
    public string $publisher = '';
    public string $edition = '';
    public string $year = '';
    /** @var int[] */
    public array $autoresIds = [];
    /** @var int[] */
    public array $assuntosIds = [];
    public string $price = '';

    public bool $saving = false;

    // Dependências
    private ListLivrosQuery $listLivros;
    private ListAutoresQuery $listAutores;
    private ListAssuntosQuery $listAssuntos;
    private FindLivroByIdQuery $findLivro;
    private CreateLivroCommand $createLivro;
    private UpdateLivroCommand $updateLivro;
    private DeleteLivroCommand $deleteLivro;
    private AutorRepositoryInterface $autorRepo;
    private AssuntoRepositoryInterface $assuntoRepo;

    public function boot(
        ListLivrosQuery $listLivros,
        ListAutoresQuery $listAutores,
        ListAssuntosQuery $listAssuntos,
        FindLivroByIdQuery $findLivro,
        CreateLivroCommand $createLivro,
        UpdateLivroCommand $updateLivro,
        DeleteLivroCommand $deleteLivro,
        AutorRepositoryInterface $autorRepo,
        AssuntoRepositoryInterface $assuntoRepo,
    ): void {
        $this->listLivros   = $listLivros;
        $this->listAutores  = $listAutores;
        $this->listAssuntos = $listAssuntos;
        $this->findLivro    = $findLivro;
        $this->createLivro  = $createLivro;
        $this->updateLivro  = $updateLivro;
        $this->deleteLivro  = $deleteLivro;
        $this->autorRepo    = $autorRepo;
        $this->assuntoRepo  = $assuntoRepo;
    }

    /** ================= AÇÕES DE UI ================= */

    public function setSort(string $column): void
    {
        // Mapa do cabeçalho (UI) -> chaves aceitas pelo repositório
        $map = [
            'title'     => 'titulo',
            'publisher' => 'editora',
            'year'      => 'anopublicacao',
            'price'     => 'valor',
            'autores'   => 'autor',    // ordenação relacional (via withMin/orderByRaw no repo)
            'assunto'   => 'assunto',  // idem
        ];
        $col = $map[$column] ?? "";
        if ($col === '') {
            return;
        }

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
        $this->resetForm();
        $this->dispatch('modal:open', id: 'bookModal');
    }

    public function openEdit(string $id): void
    {
        try {
            $out = $this->findLivro->execute(new FindLivroByIdInputDTO((int) $id));
            if ($out->livro === null) {
                $this->dispatch('show-error', message: 'Livro não encontrado');
                return;
            }
            $l = $out->livro;

            $this->editingId  = (string) $l->codl();
            $this->title      = $l->titulo()->value();
            $this->publisher  = $l->editora()->value();
            $this->edition    = (string) $l->edicao()->value();
            $this->year       = $l->anoPublicacao()->value();
            $this->price      = $this->formatPrice((int) $l->valor()->value());
            $this->autoresIds = array_map('intval', $l->autoresIds());
            $this->assuntosIds= array_map('intval', $l->assuntosIds());

            $this->dispatch('modal:open', id: 'bookModal');
        } catch (\Throwable $e) {
            $this->dispatch('show-error', message: $e->getMessage());
        }
    }

    public function confirmDelete(string $id): void
    {
        $this->editingId = $id;
        $this->dispatch('modal:open', id: 'bookDeleteModal');
    }

    public function delete(): void
    {
        try {
            $this->deleteLivro->execute(new DeleteLivroInputDTO((int) $this->editingId));
            $this->dispatch('show-success', message: 'Livro excluído com sucesso');
        } catch (\Throwable $e) {
            $this->dispatch('show-error', message: 'Erro ao excluir livro: ' . $e->getMessage());
        } finally {
            $this->dispatch('modal:close', id: 'bookDeleteModal');
            $this->editingId = null;
        }
    }

    /** ================= SALVAR ================= */

    protected function rules(): array
    {
        return [
            'title'        => ['required', 'string', 'max:255'],
            'publisher'    => ['required', 'string', 'max:255'],
            'edition'      => ['required', 'integer', 'min:1'],
            'year'         => ['required', 'string', 'size:4'],
            'price'        => ['required', 'string'],
            'autoresIds'   => ['required', 'array', 'min:1'],
            'assuntosIds'  => ['required', 'array', 'min:1'],
            'autoresIds.*' => ['integer'],
            'assuntosIds.*'=> ['integer'],
        ];
    }

    protected function messages(): array
    {
        return [
            'autoresIds.required'  => 'Selecione pelo menos um autor.',
            'autoresIds.min'       => 'Selecione pelo menos um autor.',
            'assuntosIds.required' => 'Selecione pelo menos um assunto.',
            'assuntosIds.min'      => 'Selecione pelo menos um assunto.',
        ];
    }

    public function save(): void
    {
        $this->saving = true;
        $this->validate();

        try {
            $valorCentavos = $this->parsePriceToCents($this->price);
            if ($valorCentavos <= 0) {
                $this->addError('price', 'O valor do livro deve ser maior que zero.');
                return;
            }

            if ($this->editingId) {
                $this->updateLivro->execute(new UpdateLivroInputDTO(
                    codl: (int) $this->editingId,
                    titulo: $this->title,
                    editora: $this->publisher,
                    edicao: (int) $this->edition,
                    anoPublicacao: $this->year,
                    valor: $valorCentavos,
                    autoresIds: array_map('intval', $this->autoresIds),
                    assuntosIds: array_map('intval', $this->assuntosIds)
                ));
                $this->dispatch('show-success', message: 'Livro atualizado com sucesso');
            } else {
                $this->createLivro->execute(new CreateLivroInputDTO(
                    titulo: $this->title,
                    editora: $this->publisher,
                    edicao: (int) $this->edition,
                    anoPublicacao: $this->year,
                    valor: $valorCentavos,
                    autoresIds: array_map('intval', $this->autoresIds),
                    assuntosIds: array_map('intval', $this->assuntosIds)
                ));
                $this->dispatch('show-success', message: 'Livro criado com sucesso');
            }

            $this->dispatch('modal:close', id: 'bookModal');
            $this->resetForm();
            $this->resetPage($this->pageName);
        } catch (\DomainException $e) {
            $this->addError('title', $e->getMessage());
        } catch (\Throwable $e) {
            $this->dispatch('show-error', message: 'Erro ao salvar livro: ' . $e->getMessage());
        } finally {
            $this->saving = false;
        }
    }

    /** ================= COMPUTEDS ================= */

    #[Computed]
    public function rows(): array
    {
        // Fonte da verdade da página vem do trait
        $currentPage = $this->getPage($this->pageName);

        $out = $this->listLivros->execute(new ListLivrosInputDTO(
            search: $this->search ?: null,
            sort:   $this->sort ?: null,
            dir:    $this->dir,
            page:   $currentPage,
            limit:  $this->perPage
        ));

        // Evitar N+1 na montagem de nomes (ideal: repos com findMany)
        $uniqueAutorIds   = [];
        $uniqueAssuntoIds = [];
        foreach ($out->livros() as $l) {
            foreach ($l->autoresIds() as $id)  { $uniqueAutorIds[$id] = true; }
            foreach ($l->assuntosIds() as $id) { $uniqueAssuntoIds[$id] = true; }
        }
        $autoresMap  = $this->fetchAutoresMap(array_map('intval', array_keys($uniqueAutorIds)));
        $assuntosMap = $this->fetchAssuntosMap(array_map('intval', array_keys($uniqueAssuntoIds)));

        $items = array_map(function ($livro) use ($autoresMap, $assuntosMap) {
            $assuntos = array_values(array_intersect_key($assuntosMap, array_flip($livro->assuntosIds())));
            $autores  = array_values(array_intersect_key($autoresMap,  array_flip($livro->autoresIds())));

            return [
                'id'        => (string) $livro->codl(),
                'title'     => $livro->titulo()->value(),
                'publisher' => $livro->editora()->value(),
                'edition'   => (string) $livro->edicao()->value(),
                'year'      => $livro->anoPublicacao()->value(),
                'autores'   => count($autores)  ? implode(', ', $autores)   : 'Sem autor',
                'subject'   => count($assuntos) ? implode(', ', $assuntos)  : 'Sem assunto',
                'price'     => $this->formatPrice((int) $livro->valor()->value()),
            ];
        }, $out->livros());

        return [
            'items'       => $items,
            'total'       => $out->total,
            'pages'       => $out->totalPages,
            'currentPage' => $currentPage, // enviado para a view
        ];
    }

    #[Computed]
    public function autores(): array
    {
        $out = $this->listAutores->execute(new ListAutoresInputDTO(limit: 1000));
        return array_map(
            fn($a) => ['id' => $a->codau(), 'name' => $a->nome()->value()],
            $out->autores()
        );
    }

    #[Computed]
    public function assuntos(): array
    {
        $out = $this->listAssuntos->execute(new ListAssuntosInputDTO(limit: 1000));
        return array_map(
            fn($s) => ['id' => $s->codas(), 'description' => $s->descricao()->value()],
            $out->assuntos()
        );
    }

    /** ================= HELPERS ================= */

    private function fetchAutoresMap(array $ids): array
    {
        if (!$ids) return [];
        $map = [];
        foreach ($ids as $id) {
            $a = $this->autorRepo->findById($id);
            if ($a) $map[$id] = $a->nome()->value();
        }
        return $map;
    }

    private function fetchAssuntosMap(array $ids): array
    {
        if (!$ids) return [];
        $map = [];
        foreach ($ids as $id) {
            $s = $this->assuntoRepo->findById($id);
            if ($s) $map[$id] = $s->descricao()->value();
        }
        return $map;
    }

    private function parsePriceToCents(string $input): int
    {
        // aceita "R$ 1.234,56", "1234,56", "1234.56" etc.
        $clean = preg_replace('/[^\d,.\-]/', '', $input) ?? '0';
        if (str_contains($clean, ',') && !str_contains($clean, '.')) {
            $clean = str_replace(',', '.', $clean);
        } else {
            $clean = str_replace(',', '', $clean);
        }
        $float = (float) $clean;
        return (int) round($float * 100);
    }

    private function formatPrice(int $cents): string
    {
        return 'R$ ' . number_format($cents / 100, 2, ',', '.');
    }

    private function resetForm(): void
    {
        $this->title = $this->publisher = $this->edition = $this->year = $this->price = '';
        $this->autoresIds = $this->assuntosIds = [];
        $this->saving = false;
    }

    public function render()
    {
        return view('livewire.books-page', [
            'rows'     => $this->rows,     // computed
            'autores'  => $this->autores,  // computed
            'assuntos' => $this->assuntos, // computed
        ]);
    }
}
