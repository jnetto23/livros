<?php

namespace App\Application\Usecases\Queries;

use App\Application\Usecases\UseCaseInterface;
use App\Domain\Entity\Autor;
use App\Application\Repository\AutorRepositoryInterface;

final class FindAutorByIdQuery implements UseCaseInterface
{
    public function __construct(
        private readonly AutorRepositoryInterface $repository
    ) {
    }

    public function execute(object $input): object
    {
        assert($input instanceof FindAutorByIdInputDTO);

        $autor = $this->repository->findById($input->codau);

        if ($autor === null) {
            return new FindAutorByIdOutputDTO(null);
        }

        return new FindAutorByIdOutputDTO($autor);
    }
}

final readonly class FindAutorByIdInputDTO
{
    public function __construct(
        public int $codau
    ) {
    }
}

final readonly class FindAutorByIdOutputDTO
{
    public function __construct(
        public ?Autor $autor
    ) {
    }
}
