<?php

namespace App\Domain\Entity;

use App\Domain\VOs\NomeAutor;

final class Autor
{
    private ?int $codau = null;
    private NomeAutor $nome;

    private function __construct(NomeAutor $nome, ?int $codau = null)
    {
        $this->codau = $codau;
        $this->nome = $nome;
    }

    public static function create(NomeAutor $nome): self
    {
        return new self($nome);
    }

    public static function restore(int $codau, NomeAutor $nome): self
    {
        return new self($nome, $codau);
    }

    public function codau(): ?int
    {
        return $this->codau;
    }

    public function nome(): NomeAutor
    {
        return $this->nome;
    }

    public function alterarNome(NomeAutor $novoNome): void
    {
        $this->nome = $novoNome;
    }

    public function equals(Autor $other): bool
    {
        if ($this->codau !== null && $other->codau !== null) {
            return $this->codau === $other->codau;
        }

        return $this->nome->equals($other->nome);
    }
}

