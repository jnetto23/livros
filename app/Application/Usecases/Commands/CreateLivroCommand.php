<?php

namespace App\Application\Usecases\Commands;

use App\Application\Usecases\UseCaseInterface;
use App\Domain\Entity\Livro;
use App\Application\Repository\LivroRepositoryInterface;
use App\Domain\VOs\AnoPublicacao;
use App\Domain\VOs\NomeEditora;
use App\Domain\VOs\NumeroEdicao;
use App\Domain\VOs\TituloLivro;
use App\Domain\VOs\ValorLivro;

final class CreateLivroCommand implements UseCaseInterface
{
    public function __construct(
        private readonly LivroRepositoryInterface $repository
    ) {
    }

    public function execute(object $input): object
    {
        assert($input instanceof CreateLivroInputDTO);

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
        $titulo = TituloLivro::create($input->titulo);
        $editora = NomeEditora::create($input->editora);
        $edicao = NumeroEdicao::create($input->edicao);
        $anoPublicacao = AnoPublicacao::create($input->anoPublicacao);
        $valor = ValorLivro::create($input->valor);

        $livro = Livro::create($titulo, $editora, $edicao, $anoPublicacao, $valor);
        $livro->definirAutores($autoresIdsUnicos);
        $livro->definirAssuntos($assuntosIdsUnicos);

        $livroSalvo = $this->repository->save($livro);

        return new CreateLivroOutputDTO($livroSalvo->codl());
    }
}

final readonly class CreateLivroInputDTO
{
    /** @param array<int> $autoresIds */
    /** @param array<int> $assuntosIds */
    public function __construct(
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

final readonly class CreateLivroOutputDTO
{
    public function __construct(
        public int $codl
    ) {
    }
}
