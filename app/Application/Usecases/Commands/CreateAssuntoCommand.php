<?php

namespace App\Application\Usecases\Commands;

use App\Application\Usecases\UseCaseInterface;
use App\Domain\Entity\Assunto;
use App\Application\Repository\AssuntoRepositoryInterface;
use App\Domain\VOs\DescricaoAssunto;

final class CreateAssuntoCommand implements UseCaseInterface
{
    public function __construct(
        private readonly AssuntoRepositoryInterface $repository
    ) {
    }

    public function execute(object $input): object
    {
        assert($input instanceof CreateAssuntoInputDTO);

        $descricao = DescricaoAssunto::create($input->descricao);

        // Regra: Não pode cadastrar assuntos iguais (mesmo texto)
        // Usa o valor normalizado do VO para verificar duplicidade
        $assuntoExistente = $this->repository->findByDescricao($descricao->value());
        if ($assuntoExistente !== null) {
            throw new \DomainException("Já existe um assunto com a descrição '{$descricao->value()}'.");
        }

        $assunto = Assunto::create($descricao);

        $assuntoSalvo = $this->repository->save($assunto);

        return new CreateAssuntoOutputDTO($assuntoSalvo->codas());
    }
}

final readonly class CreateAssuntoInputDTO
{
    public function __construct(
        public string $descricao
    ) {
    }
}

final readonly class CreateAssuntoOutputDTO
{
    public function __construct(
        public int $codas
    ) {
    }
}
