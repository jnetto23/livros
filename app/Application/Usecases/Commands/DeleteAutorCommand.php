<?php

namespace App\Application\Usecases\Commands;

use App\Application\Usecases\UseCaseInterface;
use App\Application\Repository\AutorRepositoryInterface;
use App\Application\Repository\LivroRepositoryInterface;

final class DeleteAutorCommand implements UseCaseInterface
{
    public function __construct(
        private readonly AutorRepositoryInterface $repository,
        private readonly LivroRepositoryInterface $livroRepository
    ) {
    }

    public function execute(object $input): object
    {
        assert($input instanceof DeleteAutorInputDTO);

        $autor = $this->repository->findById($input->codau);

        if ($autor === null) {
            throw new \RuntimeException("Autor com ID {$input->codau} não encontrado.");
        }

        // Regra: Não pode excluir autor vinculado a um ou mais livros
        if ($this->livroRepository->existsByAutorId($input->codau)) {
            throw new \DomainException("Não é possível excluir o autor '{$autor->nome()->value()}' pois está vinculado a um ou mais livros.");
        }

        $this->repository->delete($input->codau);

        return new DeleteAutorOutputDTO();
    }
}

final readonly class DeleteAutorInputDTO
{
    public function __construct(
        public int $codau
    ) {
    }
}

final readonly class DeleteAutorOutputDTO
{
    public function __construct()
    {
    }
}
