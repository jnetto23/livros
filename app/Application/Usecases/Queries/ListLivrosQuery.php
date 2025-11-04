<?php

namespace App\Application\Usecases\Queries;

use App\Application\Usecases\UseCaseInterface;
use App\Domain\Entity\Livro;
use App\Application\Repository\LivroRepositoryInterface;

final class ListLivrosQuery implements UseCaseInterface
{
    private const DEFAULT_SORT = 'title';

    public function __construct(
        private readonly LivroRepositoryInterface $repository
    ) {
    }

    public function execute(object $input): object
    {
        assert($input instanceof ListLivrosInputDTO);

        $livros = $this->repository->findAll();

        // Filtro por busca
        if ($input->search !== null) {
            $search = mb_strtolower($input->search);
            $livros = array_filter($livros, function (Livro $livro) use ($search) {
                return str_contains(mb_strtolower($livro->titulo()->value()), $search)
                    || str_contains(mb_strtolower($livro->editora()->value()), $search)
                    || str_contains(mb_strtolower($livro->anoPublicacao()->value()), $search);
            });
        }

        // Ordenação
        $sort = $input->sort ?? self::DEFAULT_SORT;

        usort($livros, function (Livro $a, Livro $b) use ($sort, $input) {
            $valueA = match($sort) {
                'title' => $a->titulo()->value(),
                'publisher' => $a->editora()->value(),
                'edition' => (string) $a->edicao()->value(),
                'year' => $a->anoPublicacao()->value(),
                'valor' => (string) $a->valor()->value(),
                default => $a->titulo()->value(),
            };
            $valueB = match($sort) {
                'title' => $b->titulo()->value(),
                'publisher' => $b->editora()->value(),
                'edition' => (string) $b->edicao()->value(),
                'year' => $b->anoPublicacao()->value(),
                'valor' => (string) $b->valor()->value(),
                default => $b->titulo()->value(),
            };

            $cmp = strnatcasecmp($valueA, $valueB);
            return $input->dir === 'asc' ? $cmp : -$cmp;
        });

        // Paginação
        $total = count($livros);
        $totalPages = (int) ceil($total / $input->limit);

        $offset = ($input->page - 1) * $input->limit;
        $livrosPaginados = array_slice(array_values($livros), $offset, $input->limit);

        return new ListLivrosOutputDTO($livrosPaginados, $total, $input->page, $input->limit, $totalPages);
    }
}

final class ListLivrosInputDTO extends ListInputDTO
{
}

final class ListLivrosOutputDTO extends ListOutputDTO
{
    /** @param array<Livro> $items */
    public function __construct(
        array $items,
        int $total,
        int $page,
        int $limit,
        int $totalPages
    ) {
        parent::__construct($items, $total, $page, $limit, $totalPages);
    }

    /** @return array<Livro> */
    public function livros(): array
    {
        return $this->items;
    }
}
