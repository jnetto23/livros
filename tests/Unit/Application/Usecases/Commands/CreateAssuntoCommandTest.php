<?php

use App\Application\Usecases\Commands\CreateAssuntoCommand;
use App\Application\Repository\AssuntoRepositoryInterface;
use App\Domain\Entity\Assunto;
use App\Domain\VOs\DescricaoAssunto;
use DomainException;

beforeEach(function () {
    $this->repository = Mockery::mock(AssuntoRepositoryInterface::class);
    $this->command = new CreateAssuntoCommand($this->repository);
});

test('cria assunto com sucesso', function () {
    $descricao = DescricaoAssunto::create('Programação');
    $assunto = Assunto::create($descricao);
    $assuntoSalvo = Assunto::restore(1, $descricao);

    $this->repository
        ->shouldReceive('findByDescricao')
        ->with('Programação')
        ->once()
        ->andReturn(null);

    $this->repository
        ->shouldReceive('save')
        ->once()
        ->andReturn($assuntoSalvo);

    $input = new \App\Application\Usecases\Commands\CreateAssuntoInputDTO('Programação');
    $output = $this->command->execute($input);

    expect($output)->toBeInstanceOf(\App\Application\Usecases\Commands\CreateAssuntoOutputDTO::class);
    expect($output->codas)->toBe(1);
});

test('lança exceção quando assunto já existe', function () {
    $descricao = DescricaoAssunto::create('Programação');
    $assuntoExistente = Assunto::restore(1, $descricao);

    $this->repository
        ->shouldReceive('findByDescricao')
        ->with('Programação')
        ->once()
        ->andReturn($assuntoExistente);

    $input = new \App\Application\Usecases\Commands\CreateAssuntoInputDTO('Programação');

    expect(fn() => $this->command->execute($input))
        ->toThrow(DomainException::class, "Já existe um assunto com a descrição 'Programação'.");
});

test('normaliza espaços antes de verificar duplicidade', function () {
    $descricaoNormalizada = DescricaoAssunto::create('Programação Web');
    $assuntoSalvo = Assunto::restore(1, $descricaoNormalizada);

    // Verifica duplicidade com valor normalizado (sem espaços duplos)
    $this->repository
        ->shouldReceive('findByDescricao')
        ->with('Programação Web')
        ->once()
        ->andReturn(null);

    $this->repository
        ->shouldReceive('save')
        ->once()
        ->andReturn($assuntoSalvo);

    // Input com espaços duplos será normalizado para "Programação Web"
    $input = new \App\Application\Usecases\Commands\CreateAssuntoInputDTO('Programação  Web');
    $output = $this->command->execute($input);

    expect($output->codas)->toBe(1);
});

