<?php

use App\Application\Usecases\Commands\UpdateAssuntoCommand;
use App\Application\Repository\AssuntoRepositoryInterface;
use App\Domain\Entity\Assunto;
use App\Domain\VOs\DescricaoAssunto;
use DomainException;
use RuntimeException;

beforeEach(function () {
    $this->repository = Mockery::mock(AssuntoRepositoryInterface::class);
    $this->command = new UpdateAssuntoCommand($this->repository);
});

test('atualiza assunto com sucesso', function () {
    $descricaoAntiga = DescricaoAssunto::create('Programação');
    $assunto = Assunto::restore(1, $descricaoAntiga);
    
    $this->repository
        ->shouldReceive('findById')
        ->with(1)
        ->once()
        ->andReturn($assunto);
    
    $this->repository
        ->shouldReceive('findByDescricao')
        ->with('Arquitetura')
        ->once()
        ->andReturn(null);
    
    $this->repository
        ->shouldReceive('save')
        ->once()
        ->andReturn($assunto);
    
    $input = new \App\Application\Usecases\Commands\UpdateAssuntoInputDTO(
        codas: 1,
        descricao: 'Arquitetura'
    );
    
    $output = $this->command->execute($input);
    
    expect($output)->toBeInstanceOf(\App\Application\Usecases\Commands\UpdateAssuntoOutputDTO::class);
});

test('lança exceção quando assunto não existe', function () {
    $this->repository
        ->shouldReceive('findById')
        ->with(999)
        ->once()
        ->andReturn(null);
    
    $input = new \App\Application\Usecases\Commands\UpdateAssuntoInputDTO(
        codas: 999,
        descricao: 'Arquitetura'
    );
    
    expect(fn() => $this->command->execute($input))
        ->toThrow(RuntimeException::class, 'Assunto com ID 999 não encontrado.');
});

test('lança exceção quando nova descrição já existe em outro assunto', function () {
    $descricaoAntiga = DescricaoAssunto::create('Programação');
    $assunto = Assunto::restore(1, $descricaoAntiga);
    
    $descricaoNova = DescricaoAssunto::create('Arquitetura');
    $assuntoExistente = Assunto::restore(2, $descricaoNova);
    
    $this->repository
        ->shouldReceive('findById')
        ->with(1)
        ->once()
        ->andReturn($assunto);
    
    $this->repository
        ->shouldReceive('findByDescricao')
        ->with('Arquitetura')
        ->once()
        ->andReturn($assuntoExistente);
    
    $input = new \App\Application\Usecases\Commands\UpdateAssuntoInputDTO(
        codas: 1,
        descricao: 'Arquitetura'
    );
    
    expect(fn() => $this->command->execute($input))
        ->toThrow(DomainException::class, "Já existe um assunto com a descrição 'Arquitetura'.");
});

test('permite atualizar para mesma descrição do próprio registro', function () {
    $descricao = DescricaoAssunto::create('Programação');
    $assunto = Assunto::restore(1, $descricao);
    
    $this->repository
        ->shouldReceive('findById')
        ->with(1)
        ->once()
        ->andReturn($assunto);
    
    $this->repository
        ->shouldReceive('findByDescricao')
        ->with('Programação')
        ->once()
        ->andReturn($assunto); // Retorna o próprio registro
    
    $this->repository
        ->shouldReceive('save')
        ->once()
        ->andReturn($assunto);
    
    $input = new \App\Application\Usecases\Commands\UpdateAssuntoInputDTO(
        codas: 1,
        descricao: 'Programação'
    );
    
    $output = $this->command->execute($input);
    
    expect($output)->toBeInstanceOf(\App\Application\Usecases\Commands\UpdateAssuntoOutputDTO::class);
});

