<?php

namespace App\Application\Usecases\Queries;

use App\Application\Usecases\UseCaseInterface;
use App\Domain\Entity\Livro;
use App\Application\Repository\LivroRepositoryInterface;

final class ListLivrosQuery implements UseCaseInterface
{
    public function __construct(
        private readonly LivroRepositoryInterface $repository
    ) {
    }

    public function execute(object $input): object
    {
        assert($input instanceof ListLivrosInputDTO);

        $result = $this->repository->findAll($input);

        $total = (int)($result['total'] ?? 0);
        $limit = $input->limit;
        $page  = $input->page;
        $totalPages = $total === 0 ? 0 : (int)ceil($total / $limit);

        return new ListLivrosOutputDTO(
            $result['data'] ?? [],
            $total,
            $page,
            $limit,
            $totalPages
        );
    }
}

final class ListLivrosInputDTO extends ListInputDTO
{
    protected function getDefaultSort(): string
    {
        return 'titulo';
    }
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
