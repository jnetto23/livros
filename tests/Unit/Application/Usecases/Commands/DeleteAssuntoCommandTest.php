<?php

use App\Application\Usecases\Commands\DeleteAssuntoCommand;
use App\Application\Repository\AssuntoRepositoryInterface;
use App\Application\Repository\LivroRepositoryInterface;
use App\Domain\Entity\Assunto;
use App\Domain\VOs\DescricaoAssunto;
use DomainException;
use RuntimeException;

beforeEach(function () {
    $this->assuntoRepository = Mockery::mock(AssuntoRepositoryInterface::class);
    $this->livroRepository = Mockery::mock(LivroRepositoryInterface::class);
    $this->command = new DeleteAssuntoCommand($this->assuntoRepository, $this->livroRepository);
});

test('exclui assunto com sucesso quando não está vinculado', function () {
    $descricao = DescricaoAssunto::create('Programação');
    $assunto = Assunto::restore(1, $descricao);
    
    $this->assuntoRepository
        ->shouldReceive('findById')
        ->with(1)
        ->once()
        ->andReturn($assunto);
    
    $this->livroRepository
        ->shouldReceive('existsByAssuntoId')
        ->with(1)
        ->once()
        ->andReturn(false);
    
    $this->assuntoRepository
        ->shouldReceive('delete')
        ->with(1)
        ->once();
    
    $input = new \App\Application\Usecases\Commands\DeleteAssuntoInputDTO(1);
    $output = $this->command->execute($input);
    
    expect($output)->toBeInstanceOf(\App\Application\Usecases\Commands\DeleteAssuntoOutputDTO::class);
});

test('lança exceção quando assunto não existe', function () {
    $this->assuntoRepository
        ->shouldReceive('findById')
        ->with(999)
        ->once()
        ->andReturn(null);
    
    $input = new \App\Application\Usecases\Commands\DeleteAssuntoInputDTO(999);
    
    expect(fn() => $this->command->execute($input))
        ->toThrow(RuntimeException::class, 'Assunto com ID 999 não encontrado.');
});

test('lança exceção quando assunto está vinculado a livros', function () {
    $descricao = DescricaoAssunto::create('Programação');
    $assunto = Assunto::restore(1, $descricao);
    
    $this->assuntoRepository
        ->shouldReceive('findById')
        ->with(1)
        ->once()
        ->andReturn($assunto);
    
    $this->livroRepository
        ->shouldReceive('existsByAssuntoId')
        ->with(1)
        ->once()
        ->andReturn(true);
    
    $input = new \App\Application\Usecases\Commands\DeleteAssuntoInputDTO(1);
    
    expect(fn() => $this->command->execute($input))
        ->toThrow(DomainException::class, "Não é possível excluir o assunto 'Programação' pois está vinculado a um ou mais livros.");
});

