<?php

namespace App\Application\Usecases\Commands;

use App\Application\Usecases\UseCaseInterface;
use App\Domain\Entity\Autor;
use App\Application\Repository\AutorRepositoryInterface;
use App\Domain\VOs\NomeAutor;

final class CreateAutorCommand implements UseCaseInterface
{
    public function __construct(
        private readonly AutorRepositoryInterface $repository
    ) {
    }

    public function execute(object $input): object
    {
        assert($input instanceof CreateAutorInputDTO);

        $nome = NomeAutor::create($input->nome);

        // Regra: Não pode cadastrar autores com mesmo nome
        // Usa o valor normalizado do VO para verificar duplicidade
        $autorExistente = $this->repository->findByNome($nome->value());
        if ($autorExistente !== null) {
            throw new \DomainException("Já existe um autor com o nome '{$nome->value()}'.");
        }

        $autor = Autor::create($nome);

        $autorSalvo = $this->repository->save($autor);

        return new CreateAutorOutputDTO($autorSalvo->codau());
    }
}

final readonly class CreateAutorInputDTO
{
    public function __construct(
        public string $nome
    ) {
    }
}

final readonly class CreateAutorOutputDTO
{
    public function __construct(
        public int $codau
    ) {
    }
}
