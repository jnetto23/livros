<?php

namespace App\Application\Repository;

use App\Domain\Entity\Assunto;

interface AssuntoRepositoryInterface
{
    public function save(Assunto $assunto): Assunto;
    public function findById(int $codas): ?Assunto;
    /** @return array<Assunto> */
    public function findAll(): array;
    public function delete(int $codas): void;
    public function findByDescricao(string $descricao): ?Assunto;
}

