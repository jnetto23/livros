<?php

namespace App\Application\Repository;

use App\Domain\Entity\Autor;

interface AutorRepositoryInterface
{
    public function save(Autor $autor): Autor;
    public function findById(int $codau): ?Autor;
    /** @return array<Autor> */
    public function findAll(): array;
    public function delete(int $codau): void;
    public function findByNome(string $nome): ?Autor;
}

