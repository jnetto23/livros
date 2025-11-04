<?php

namespace App\Application\Usecases\Commands;

use App\Application\Usecases\UseCaseInterface;
use App\Application\Repository\AssuntoRepositoryInterface;
use App\Domain\VOs\DescricaoAssunto;

final class UpdateAssuntoCommand implements UseCaseInterface
{
    public function __construct(
        private readonly AssuntoRepositoryInterface $repository
    ) {
    }

    public function execute(object $input): object
    {
        assert($input instanceof UpdateAssuntoInputDTO);

        $assunto = $this->repository->findById($input->codas);

        if ($assunto === null) {
            throw new \RuntimeException("Assunto com ID {$input->codas} não encontrado.");
        }

        $novaDescricao = DescricaoAssunto::create($input->descricao);

        // Regra: Não pode atualizar para uma descrição que já existe (exceto o próprio registro)
        // Usa o valor normalizado do VO para verificar duplicidade
        $assuntoExistente = $this->repository->findByDescricao($novaDescricao->value());
        if ($assuntoExistente !== null && $assuntoExistente->codas() !== $input->codas) {
            throw new \DomainException("Já existe um assunto com a descrição '{$novaDescricao->value()}'.");
        }
        $assunto->alterarDescricao($novaDescricao);

        $this->repository->save($assunto);

        return new UpdateAssuntoOutputDTO();
    }
}

final readonly class UpdateAssuntoInputDTO
{
    public function __construct(
        public int $codas,
        public string $descricao
    ) {
    }
}

final readonly class UpdateAssuntoOutputDTO
{
    public function __construct()
    {
    }
}
