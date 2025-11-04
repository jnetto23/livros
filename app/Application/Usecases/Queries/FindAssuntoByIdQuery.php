<?php

namespace App\Application\Usecases\Queries;

use App\Application\Usecases\UseCaseInterface;
use App\Domain\Entity\Assunto;
use App\Application\Repository\AssuntoRepositoryInterface;

final class FindAssuntoByIdQuery implements UseCaseInterface
{
    public function __construct(
        private readonly AssuntoRepositoryInterface $repository
    ) {
    }

    public function execute(object $input): object
    {
        assert($input instanceof FindAssuntoByIdInputDTO);

        $assunto = $this->repository->findById($input->codas);

        if ($assunto === null) {
            return new FindAssuntoByIdOutputDTO(null);
        }

        return new FindAssuntoByIdOutputDTO($assunto);
    }
}

final readonly class FindAssuntoByIdInputDTO
{
    public function __construct(
        public int $codas
    ) {
    }
}

final readonly class FindAssuntoByIdOutputDTO
{
    public function __construct(
        public ?Assunto $assunto
    ) {
    }
}
