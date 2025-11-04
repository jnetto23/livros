<?php

namespace App\Application\Usecases\Queries;

use App\Application\Usecases\UseCaseInterface;
use App\Domain\Entity\Autor;
use App\Application\Repository\AutorRepositoryInterface;

final class ListAutoresQuery implements UseCaseInterface
{
    private const DEFAULT_SORT = 'name';

    public function __construct(
        private readonly AutorRepositoryInterface $repository
    ) {
    }

    public function execute(object $input): object
    {
        assert($input instanceof ListAutoresInputDTO);

        $autores = $this->repository->findAll();

        // Filtro por busca
        if ($input->search !== null) {
            $search = mb_strtolower($input->search);
            $autores = array_filter($autores, function (Autor $autor) use ($search) {
                return str_contains(mb_strtolower($autor->nome()->value()), $search);
            });
        }

        // Ordenação
        $sort = $input->sort ?? self::DEFAULT_SORT;

        if ($sort === 'name') {
            usort($autores, function (Autor $a, Autor $b) use ($input) {
                $cmp = strnatcasecmp($a->nome()->value(), $b->nome()->value());
                return $input->dir === 'asc' ? $cmp : -$cmp;
            });
        }

        // Paginação
        $total = count($autores);
        $totalPages = (int) ceil($total / $input->limit);

        $offset = ($input->page - 1) * $input->limit;
        $autoresPaginados = array_slice(array_values($autores), $offset, $input->limit);

        return new ListAutoresOutputDTO($autoresPaginados, $total, $input->page, $input->limit, $totalPages);
    }
}

final class ListAutoresInputDTO extends ListInputDTO
{
}

final class ListAutoresOutputDTO extends ListOutputDTO
{
    /** @param array<Autor> $items */
    public function __construct(
        array $items,
        int $total,
        int $page,
        int $limit,
        int $totalPages
    ) {
        parent::__construct($items, $total, $page, $limit, $totalPages);
    }

    /** @return array<Autor> */
    public function autores(): array
    {
        return $this->items;
    }
}

