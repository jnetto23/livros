<?php

namespace App\Application\Repository;

use App\Domain\Entity\Assunto;
use App\Application\Usecases\Queries\ListAssuntosInputDTO;

interface AssuntoRepositoryInterface
{
    public function save(Assunto $assunto): Assunto;

    public function findById(int $codas): ?Assunto;

    /**
     * @return array{data: array<Assunto>, total: int}
     */
    public function findAll(ListAssuntosInputDTO $filters): array;

    public function delete(int $codas): void;

    public function findByDescricao(string $descricao): ?Assunto;
}

