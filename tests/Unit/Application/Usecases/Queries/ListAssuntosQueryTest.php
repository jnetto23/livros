<?php

use App\Application\Usecases\Queries\ListAssuntosQuery;
use App\Application\Repository\AssuntoRepositoryInterface;
use App\Domain\Entity\Assunto;
use App\Domain\VOs\DescricaoAssunto;

beforeEach(function () {
    $this->repository = Mockery::mock(AssuntoRepositoryInterface::class);
    $this->query = new ListAssuntosQuery($this->repository);
});

test('lista assuntos sem filtros', function () {
    $assuntos = [
        Assunto::restore(1, DescricaoAssunto::create('Programação')),
        Assunto::restore(2, DescricaoAssunto::create('Arquitetura')),
    ];
    
    $this->repository
        ->shouldReceive('findAll')
        ->once()
        ->andReturn($assuntos);
    
    $input = new \App\Application\Usecases\Queries\ListAssuntosInputDTO();
    $output = $this->query->execute($input);
    
    expect($output)->toBeInstanceOf(\App\Application\Usecases\Queries\ListAssuntosOutputDTO::class);
    expect($output->assuntos())->toHaveCount(2);
    expect($output->total)->toBe(2);
});

test('filtra assuntos por busca', function () {
    $assuntos = [
        Assunto::restore(1, DescricaoAssunto::create('Programação')),
        Assunto::restore(2, DescricaoAssunto::create('Arquitetura')),
        Assunto::restore(3, DescricaoAssunto::create('Padrões')),
    ];
    
    $this->repository
        ->shouldReceive('findAll')
        ->once()
        ->andReturn($assuntos);
    
    $input = new \App\Application\Usecases\Queries\ListAssuntosInputDTO(
        search: 'Prog'
    );
    $output = $this->query->execute($input);
    
    expect($output->assuntos())->toHaveCount(1);
    expect($output->assuntos()[0]->descricao()->value())->toBe('Programação');
});

test('ordena assuntos por descrição ascendente', function () {
    $assuntos = [
        Assunto::restore(2, DescricaoAssunto::create('Arquitetura')),
        Assunto::restore(1, DescricaoAssunto::create('Programação')),
    ];
    
    $this->repository
        ->shouldReceive('findAll')
        ->once()
        ->andReturn($assuntos);
    
    $input = new \App\Application\Usecases\Queries\ListAssuntosInputDTO(
        sort: 'description',
        dir: 'asc'
    );
    $output = $this->query->execute($input);
    
    expect($output->assuntos()[0]->descricao()->value())->toBe('Arquitetura');
    expect($output->assuntos()[1]->descricao()->value())->toBe('Programação');
});

test('pagina resultados', function () {
    $assuntos = [];
    for ($i = 1; $i <= 15; $i++) {
        $assuntos[] = Assunto::restore($i, DescricaoAssunto::create("Assunto {$i}"));
    }
    
    $this->repository
        ->shouldReceive('findAll')
        ->once()
        ->andReturn($assuntos);
    
    $input = new \App\Application\Usecases\Queries\ListAssuntosInputDTO(
        page: 1,
        limit: 10
    );
    $output = $this->query->execute($input);
    
    expect($output->assuntos())->toHaveCount(10);
    expect($output->total)->toBe(15);
    expect($output->totalPages)->toBe(2);
});

