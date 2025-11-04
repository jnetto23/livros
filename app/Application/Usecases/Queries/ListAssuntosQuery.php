<?php

namespace App\Application\Usecases\Queries;

use App\Application\Usecases\UseCaseInterface;
use App\Domain\Entity\Assunto;
use App\Application\Repository\AssuntoRepositoryInterface;

final class ListAssuntosQuery implements UseCaseInterface
{
    private const DEFAULT_SORT = 'description';

    public function __construct(
        private readonly AssuntoRepositoryInterface $repository
    ) {
    }

    public function execute(object $input): object
    {
        assert($input instanceof ListAssuntosInputDTO);

        $assuntos = $this->repository->findAll();

        // Filtro por busca
        if ($input->search !== null) {
            $search = mb_strtolower($input->search);
            $assuntos = array_filter($assuntos, function (Assunto $assunto) use ($search) {
                return str_contains(mb_strtolower($assunto->descricao()->value()), $search);
            });
        }

        // Ordenação
        $sort = $input->sort ?? self::DEFAULT_SORT;

        if ($sort === 'description') {
            usort($assuntos, function (Assunto $a, Assunto $b) use ($input) {
                $cmp = strnatcasecmp($a->descricao()->value(), $b->descricao()->value());
                return $input->dir === 'asc' ? $cmp : -$cmp;
            });
        }

        // Paginação
        $total = count($assuntos);
        $totalPages = (int) ceil($total / $input->limit);

        $offset = ($input->page - 1) * $input->limit;
        $assuntosPaginados = array_slice(array_values($assuntos), $offset, $input->limit);

        return new ListAssuntosOutputDTO($assuntosPaginados, $total, $input->page, $input->limit, $totalPages);
    }
}

final class ListAssuntosInputDTO extends ListInputDTO
{
}

final class ListAssuntosOutputDTO extends ListOutputDTO
{
    /** @param array<Assunto> $items */
    public function __construct(
        array $items,
        int $total,
        int $page,
        int $limit,
        int $totalPages
    ) {
        parent::__construct($items, $total, $page, $limit, $totalPages);
    }

    /** @return array<Assunto> */
    public function assuntos(): array
    {
        return $this->items;
    }
}

