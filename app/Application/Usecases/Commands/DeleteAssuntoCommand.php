<?php

namespace App\Application\Usecases\Commands;

use App\Application\Usecases\UseCaseInterface;
use App\Application\Repository\AssuntoRepositoryInterface;
use App\Application\Repository\LivroRepositoryInterface;

final class DeleteAssuntoCommand implements UseCaseInterface
{
    public function __construct(
        private readonly AssuntoRepositoryInterface $repository,
        private readonly LivroRepositoryInterface $livroRepository
    ) {
    }

    public function execute(object $input): object
    {
        assert($input instanceof DeleteAssuntoInputDTO);

        $assunto = $this->repository->findById($input->codas);

        if ($assunto === null) {
            throw new \RuntimeException("Assunto com ID {$input->codas} não encontrado.");
        }

        // Regra: Não pode excluir assunto vinculado a um ou mais livros
        if ($this->livroRepository->existsByAssuntoId($input->codas)) {
            throw new \DomainException("Não é possível excluir o assunto '{$assunto->descricao()->value()}' pois está vinculado a um ou mais livros.");
        }

        $this->repository->delete($input->codas);

        return new DeleteAssuntoOutputDTO();
    }
}

final readonly class DeleteAssuntoInputDTO
{
    public function __construct(
        public int $codas
    ) {
    }
}

final readonly class DeleteAssuntoOutputDTO
{
    public function __construct()
    {
    }
}
