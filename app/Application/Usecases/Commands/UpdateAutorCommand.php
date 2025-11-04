<?php

namespace App\Application\Usecases\Commands;

use App\Application\Usecases\UseCaseInterface;
use App\Application\Repository\AutorRepositoryInterface;
use App\Domain\VOs\NomeAutor;

final class UpdateAutorCommand implements UseCaseInterface
{
    public function __construct(
        private readonly AutorRepositoryInterface $repository
    ) {
    }

    public function execute(object $input): object
    {
        assert($input instanceof UpdateAutorInputDTO);

        $autor = $this->repository->findById($input->codau);

        if ($autor === null) {
            throw new \RuntimeException("Autor com ID {$input->codau} não encontrado.");
        }

        $novoNome = NomeAutor::create($input->nome);

        // Regra: Não pode atualizar para um nome que já existe (exceto o próprio registro)
        // Usa o valor normalizado do VO para verificar duplicidade
        $autorExistente = $this->repository->findByNome($novoNome->value());
        if ($autorExistente !== null && $autorExistente->codau() !== $input->codau) {
            throw new \DomainException("Já existe um autor com o nome '{$novoNome->value()}'.");
        }
        $autor->alterarNome($novoNome);

        $this->repository->save($autor);

        return new UpdateAutorOutputDTO();
    }
}

final readonly class UpdateAutorInputDTO
{
    public function __construct(
        public int $codau,
        public string $nome
    ) {
    }
}

final readonly class UpdateAutorOutputDTO
{
    public function __construct()
    {
    }
}
