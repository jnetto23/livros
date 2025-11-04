<?php

use App\Application\Usecases\Commands\CreateAutorCommand;
use App\Application\Repository\AutorRepositoryInterface;
use App\Domain\Entity\Autor;
use App\Domain\VOs\NomeAutor;
use DomainException;

beforeEach(function () {
    $this->repository = Mockery::mock(AutorRepositoryInterface::class);
    $this->command = new CreateAutorCommand($this->repository);
});

test('cria autor com sucesso', function () {
    $nome = NomeAutor::create('Robert C. Martin');
    $autor = Autor::create($nome);
    $autorSalvo = Autor::restore(1, $nome);
    
    $this->repository
        ->shouldReceive('findByNome')
        ->with('Robert C. Martin')
        ->once()
        ->andReturn(null);
    
    $this->repository
        ->shouldReceive('save')
        ->once()
        ->andReturn($autorSalvo);
    
    $input = new \App\Application\Usecases\Commands\CreateAutorInputDTO('Robert C. Martin');
    $output = $this->command->execute($input);
    
    expect($output)->toBeInstanceOf(\App\Application\Usecases\Commands\CreateAutorOutputDTO::class);
    expect($output->codau)->toBe(1);
});

test('lança exceção quando autor já existe', function () {
    $nome = NomeAutor::create('Robert C. Martin');
    $autorExistente = Autor::restore(1, $nome);
    
    $this->repository
        ->shouldReceive('findByNome')
        ->with('Robert C. Martin')
        ->once()
        ->andReturn($autorExistente);
    
    $input = new \App\Application\Usecases\Commands\CreateAutorInputDTO('Robert C. Martin');
    
    expect(fn() => $this->command->execute($input))
        ->toThrow(DomainException::class, "Já existe um autor com o nome 'Robert C. Martin'.");
});

