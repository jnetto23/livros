<?php

namespace App\Application\Repository;

use App\Domain\Entity\Livro;

interface LivroRepositoryInterface
{
    public function save(Livro $livro): Livro;
    public function findById(int $codl): ?Livro;
    /** @return array<Livro> */
    public function findAll(): array;
    public function delete(int $codl): void;
    public function existsByAssuntoId(int $codas): bool;
    public function existsByAutorId(int $codau): bool;
}

