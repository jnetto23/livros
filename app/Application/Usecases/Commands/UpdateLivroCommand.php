<?php

namespace App\Application\Usecases\Commands;

use App\Application\Usecases\UseCaseInterface;
use App\Application\Repository\LivroRepositoryInterface;
use App\Domain\VOs\AnoPublicacao;
use App\Domain\VOs\NomeEditora;
use App\Domain\VOs\NumeroEdicao;
use App\Domain\VOs\TituloLivro;
use App\Domain\VOs\ValorLivro;

final class UpdateLivroCommand implements UseCaseInterface
{
    public function __construct(
        private readonly LivroRepositoryInterface $repository
    ) {
    }

    public function execute(object $input): object
    {
        assert($input instanceof UpdateLivroInputDTO);

        $livro = $this->repository->findById($input->codl);

        if ($livro === null) {
            throw new \RuntimeException("Livro com ID {$input->codl} não encontrado.");
        }

        // Regra: Livro não pode ter autores ou assuntos repetidos
        $autoresIdsUnicos = array_unique($input->autoresIds);
        $assuntosIdsUnicos = array_unique($input->assuntosIds);

        if (count($input->autoresIds) !== count($autoresIdsUnicos)) {
            throw new \DomainException('Um livro não pode ter autores repetidos.');
        }

        if (count($input->assuntosIds) !== count($assuntosIdsUnicos)) {
            throw new \DomainException('Um livro não pode ter assuntos repetidos.');
        }

        // Normalização de espaços é feita automaticamente nos VOs
        $livro->alterarTitulo(TituloLivro::create($input->titulo));
        $livro->alterarEditora(NomeEditora::create($input->editora));
        $livro->alterarEdicao(NumeroEdicao::create($input->edicao));
        $livro->alterarAnoPublicacao(AnoPublicacao::create($input->anoPublicacao));
        $livro->alterarValor(ValorLivro::create($input->valor));
        $livro->definirAutores($autoresIdsUnicos);
        $livro->definirAssuntos($assuntosIdsUnicos);

        $this->repository->save($livro);

        return new UpdateLivroOutputDTO();
    }
}

final readonly class UpdateLivroInputDTO
{
    /** @param array<int> $autoresIds */
    /** @param array<int> $assuntosIds */
    public function __construct(
        public int $codl,
        public string $titulo,
        public string $editora,
        public int $edicao,
        public string $anoPublicacao,
        public int $valor,
        public array $autoresIds = [],
        public array $assuntosIds = []
    ) {
    }
}

final readonly class UpdateLivroOutputDTO
{
    public function __construct()
    {
    }
}
