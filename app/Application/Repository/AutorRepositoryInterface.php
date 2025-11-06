<?php

namespace App\Application\Repository;

use App\Domain\Entity\Autor;
use App\Application\Usecases\Queries\ListAutoresInputDTO;

interface AutorRepositoryInterface
{
    public function save(Autor $autor): Autor;
    public function findById(int $codau): ?Autor;
    /**
     * @return array{data: array<Autor>, total: int}
     */
    public function findAll(ListAutoresInputDTO $input): array;
    public function delete(int $codau): void;
    public function findByNome(string $nome): ?Autor;
}

