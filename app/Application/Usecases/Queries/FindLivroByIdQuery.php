<?php

namespace App\Application\Usecases\Queries;

use App\Application\Usecases\UseCaseInterface;
use App\Domain\Entity\Livro;
use App\Application\Repository\LivroRepositoryInterface;

final class FindLivroByIdQuery implements UseCaseInterface
{
    public function __construct(
        private readonly LivroRepositoryInterface $repository
    ) {
    }

    public function execute(object $input): object
    {
        assert($input instanceof FindLivroByIdInputDTO);

        $livro = $this->repository->findById($input->codl);

        if ($livro === null) {
            return new FindLivroByIdOutputDTO(null);
        }

        return new FindLivroByIdOutputDTO($livro);
    }
}

final readonly class FindLivroByIdInputDTO
{
    public function __construct(
        public int $codl
    ) {
    }
}

final readonly class FindLivroByIdOutputDTO
{
    public function __construct(
        public ?Livro $livro
    ) {
    }
}
