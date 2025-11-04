<?php

namespace App\Domain\Entity;

use App\Domain\VOs\AnoPublicacao;
use App\Domain\VOs\NomeEditora;
use App\Domain\VOs\NumeroEdicao;
use App\Domain\VOs\TituloLivro;
use App\Domain\VOs\ValorLivro;

final class Livro
{
    private ?int $codl = null;
    private TituloLivro $titulo;
    private NomeEditora $editora;
    private NumeroEdicao $edicao;
    private AnoPublicacao $anoPublicacao;
    private ValorLivro $valor;

    /** @var array<int> */
    private array $autoresIds = [];

    /** @var array<int> */
    private array $assuntosIds = [];

    private function __construct(
        TituloLivro $titulo,
        NomeEditora $editora,
        NumeroEdicao $edicao,
        AnoPublicacao $anoPublicacao,
        ValorLivro $valor,
        ?int $codl = null
    ) {
        $this->codl = $codl;
        $this->titulo = $titulo;
        $this->editora = $editora;
        $this->edicao = $edicao;
        $this->anoPublicacao = $anoPublicacao;
        $this->valor = $valor;
    }

    public static function create(
        TituloLivro $titulo,
        NomeEditora $editora,
        NumeroEdicao $edicao,
        AnoPublicacao $anoPublicacao,
        ValorLivro $valor
    ): self {
        return new self($titulo, $editora, $edicao, $anoPublicacao, $valor);
    }

    public static function restore(
        int $codl,
        TituloLivro $titulo,
        NomeEditora $editora,
        NumeroEdicao $edicao,
        AnoPublicacao $anoPublicacao,
        ValorLivro $valor
    ): self {
        return new self($titulo, $editora, $edicao, $anoPublicacao, $valor, $codl);
    }

    public function codl(): ?int
    {
        return $this->codl;
    }

    public function titulo(): TituloLivro
    {
        return $this->titulo;
    }

    public function editora(): NomeEditora
    {
        return $this->editora;
    }

    public function edicao(): NumeroEdicao
    {
        return $this->edicao;
    }

    public function anoPublicacao(): AnoPublicacao
    {
        return $this->anoPublicacao;
    }

    public function valor(): ValorLivro
    {
        return $this->valor;
    }

    /** @return array<int> */
    public function autoresIds(): array
    {
        return $this->autoresIds;
    }

    /** @return array<int> */
    public function assuntosIds(): array
    {
        return $this->assuntosIds;
    }

    public function alterarTitulo(TituloLivro $novoTitulo): void
    {
        $this->titulo = $novoTitulo;
    }

    public function alterarEditora(NomeEditora $novaEditora): void
    {
        $this->editora = $novaEditora;
    }

    public function alterarEdicao(NumeroEdicao $novaEdicao): void
    {
        $this->edicao = $novaEdicao;
    }

    public function alterarAnoPublicacao(AnoPublicacao $novoAno): void
    {
        $this->anoPublicacao = $novoAno;
    }

    public function alterarValor(ValorLivro $novoValor): void
    {
        $this->valor = $novoValor;
    }

    /** @param array<int> $autoresIds */
    public function definirAutores(array $autoresIds): void
    {
        $this->autoresIds = $autoresIds;
    }

    /** @param array<int> $assuntosIds */
    public function definirAssuntos(array $assuntosIds): void
    {
        $this->assuntosIds = $assuntosIds;
    }

    public function adicionarAutor(int $autorId): void
    {
        if (!in_array($autorId, $this->autoresIds, true)) {
            $this->autoresIds[] = $autorId;
        }
    }

    public function removerAutor(int $autorId): void
    {
        $this->autoresIds = array_values(array_filter(
            $this->autoresIds,
            fn(int $id) => $id !== $autorId
        ));
    }

    public function adicionarAssunto(int $assuntoId): void
    {
        if (!in_array($assuntoId, $this->assuntosIds, true)) {
            $this->assuntosIds[] = $assuntoId;
        }
    }

    public function removerAssunto(int $assuntoId): void
    {
        $this->assuntosIds = array_values(array_filter(
            $this->assuntosIds,
            fn(int $id) => $id !== $assuntoId
        ));
    }

    public function equals(Livro $other): bool
    {
        if ($this->codl !== null && $other->codl !== null) {
            return $this->codl === $other->codl;
        }

        return $this->titulo->equals($other->titulo)
            && $this->editora->equals($other->editora)
            && $this->edicao->equals($other->edicao)
            && $this->anoPublicacao->equals($other->anoPublicacao);
    }
}

