<?php

namespace App\Domain\Entity;

use App\Domain\VOs\DescricaoAssunto;

final class Assunto
{
    private ?int $codas = null;
    private DescricaoAssunto $descricao;

    private function __construct(DescricaoAssunto $descricao, ?int $codas = null)
    {
        $this->codas = $codas;
        $this->descricao = $descricao;
    }

    public static function create(DescricaoAssunto $descricao): self
    {
        return new self($descricao);
    }

    public static function restore(int $codas, DescricaoAssunto $descricao): self
    {
        return new self($descricao, $codas);
    }

    public function codas(): ?int
    {
        return $this->codas;
    }

    public function descricao(): DescricaoAssunto
    {
        return $this->descricao;
    }

    public function alterarDescricao(DescricaoAssunto $novaDescricao): void
    {
        $this->descricao = $novaDescricao;
    }

    public function equals(Assunto $other): bool
    {
        if ($this->codas !== null && $other->codas !== null) {
            return $this->codas === $other->codas;
        }

        return $this->descricao->equals($other->descricao);
    }
}

