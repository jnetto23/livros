<?php

namespace App\Application\Usecases\Queries;

use App\Application\Usecases\UseCaseInterface;
use App\Domain\Entity\Assunto;
use App\Application\Repository\AssuntoRepositoryInterface;

final class ListAssuntosQuery implements UseCaseInterface
{
    public function __construct(
        private readonly AssuntoRepositoryInterface $repository
    ) {
    }

    public function execute(object $input): object
    {
        assert($input instanceof ListAssuntosInputDTO);

        $result = $this->repository->findAll($input);

        $total = (int)($result['total'] ?? 0);
        $limit = $input->limit;
        $page  = $input->page;
        $totalPages = $total === 0 ? 0 : (int)ceil($total / $limit);

        return new ListAssuntosOutputDTO(
            $result['data'] ?? [],
            $total,
            $page,
            $limit,
            $totalPages
        );
    }
}

final class ListAssuntosInputDTO extends ListInputDTO
{
    protected function getDefaultSort(): string
    {
        return 'descricao';
    }
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

