<?php

namespace App\Application\Usecases\Queries;

use App\Application\Usecases\UseCaseInterface;
use App\Domain\Entity\Autor;
use App\Application\Repository\AutorRepositoryInterface;

final class ListAutoresQuery implements UseCaseInterface
{

    public function __construct(
        private readonly AutorRepositoryInterface $repository
    ) {
    }

    public function execute(object $input): object
    {
        assert($input instanceof ListAutoresInputDTO);

        $result = $this->repository->findAll($input);

        $total = (int)($result['total'] ?? 0);
        $limit = $input->limit;
        $page  = $input->page;
        $totalPages = $total === 0 ? 0 : (int)ceil($total / $limit);

        return new ListAutoresOutputDTO(
            $result['data'] ?? [],
            $total,
            $page,
            $limit,
            $totalPages
        );
    }
}

final class ListAutoresInputDTO extends ListInputDTO
{
    protected function getDefaultSort(): string
    {
        return 'nome';
    }
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

