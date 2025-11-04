<?php

namespace App\Application\Usecases\Commands;

use App\Application\Usecases\UseCaseInterface;
use App\Application\Repository\LivroRepositoryInterface;

final class DeleteLivroCommand implements UseCaseInterface
{
    public function __construct(
        private readonly LivroRepositoryInterface $repository
    ) {
    }

    public function execute(object $input): object
    {
        assert($input instanceof DeleteLivroInputDTO);

        $livro = $this->repository->findById($input->codl);

        if ($livro === null) {
            throw new \RuntimeException("Livro com ID {$input->codl} não encontrado.");
        }

        $this->repository->delete($input->codl);

        return new DeleteLivroOutputDTO();
    }
}

final readonly class DeleteLivroInputDTO
{
    public function __construct(
        public int $codl
    ) {
    }
}

final readonly class DeleteLivroOutputDTO
{
    public function __construct()
    {
    }
}
