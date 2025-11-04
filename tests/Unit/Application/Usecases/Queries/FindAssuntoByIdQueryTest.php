<?php

use App\Application\Usecases\Queries\FindAssuntoByIdQuery;
use App\Application\Repository\AssuntoRepositoryInterface;
use App\Domain\Entity\Assunto;
use App\Domain\VOs\DescricaoAssunto;

beforeEach(function () {
    $this->repository = Mockery::mock(AssuntoRepositoryInterface::class);
    $this->query = new FindAssuntoByIdQuery($this->repository);
});

test('encontra assunto por ID', function () {
    $descricao = DescricaoAssunto::create('Programação');
    $assunto = Assunto::restore(1, $descricao);
    
    $this->repository
        ->shouldReceive('findById')
        ->with(1)
        ->once()
        ->andReturn($assunto);
    
    $input = new \App\Application\Usecases\Queries\FindAssuntoByIdInputDTO(1);
    $output = $this->query->execute($input);
    
    expect($output)->toBeInstanceOf(\App\Application\Usecases\Queries\FindAssuntoByIdOutputDTO::class);
    expect($output->assunto)->not->toBeNull();
    expect($output->assunto->codas())->toBe(1);
});

test('retorna null quando assunto não existe', function () {
    $this->repository
        ->shouldReceive('findById')
        ->with(999)
        ->once()
        ->andReturn(null);
    
    $input = new \App\Application\Usecases\Queries\FindAssuntoByIdInputDTO(999);
    $output = $this->query->execute($input);
    
    expect($output)->toBeInstanceOf(\App\Application\Usecases\Queries\FindAssuntoByIdOutputDTO::class);
    expect($output->assunto)->toBeNull();
});

