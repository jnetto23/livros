<?php

use App\Application\Usecases\Commands\CreateLivroCommand;
use App\Application\Repository\LivroRepositoryInterface;
use App\Domain\Entity\Livro;
use App\Domain\VOs\AnoPublicacao;
use App\Domain\VOs\NomeEditora;
use App\Domain\VOs\NumeroEdicao;
use App\Domain\VOs\TituloLivro;
use App\Domain\VOs\ValorLivro;
use DomainException;

beforeEach(function () {
    $this->repository = Mockery::mock(LivroRepositoryInterface::class);
    $this->command = new CreateLivroCommand($this->repository);
});

test('cria livro com sucesso', function () {
    $titulo = TituloLivro::create('Código Limpo');
    $editora = NomeEditora::create('Alta Books');
    $edicao = NumeroEdicao::create(1);
    $ano = AnoPublicacao::create('2009');
    $valor = ValorLivro::create(8990);
    
    $livro = Livro::create($titulo, $editora, $edicao, $ano, $valor);
    $livro->definirAutores([1, 2]);
    $livro->definirAssuntos([1]);
    
    $livroSalvo = Livro::restore(1, $titulo, $editora, $edicao, $ano, $valor);
    $livroSalvo->definirAutores([1, 2]);
    $livroSalvo->definirAssuntos([1]);
    
    $this->repository
        ->shouldReceive('save')
        ->once()
        ->andReturn($livroSalvo);
    
    $input = new \App\Application\Usecases\Commands\CreateLivroInputDTO(
        titulo: 'Código Limpo',
        editora: 'Alta Books',
        edicao: 1,
        anoPublicacao: '2009',
        valor: 8990,
        autoresIds: [1, 2],
        assuntosIds: [1]
    );
    
    $output = $this->command->execute($input);
    
    expect($output)->toBeInstanceOf(\App\Application\Usecases\Commands\CreateLivroOutputDTO::class);
    expect($output->codl)->toBe(1);
});

test('lança exceção quando livro tem autores repetidos', function () {
    $input = new \App\Application\Usecases\Commands\CreateLivroInputDTO(
        titulo: 'Código Limpo',
        editora: 'Alta Books',
        edicao: 1,
        anoPublicacao: '2009',
        valor: 8990,
        autoresIds: [1, 1, 2],
        assuntosIds: [1]
    );
    
    expect(fn() => $this->command->execute($input))
        ->toThrow(DomainException::class, 'Um livro não pode ter autores repetidos.');
});

test('lança exceção quando livro tem assuntos repetidos', function () {
    $input = new \App\Application\Usecases\Commands\CreateLivroInputDTO(
        titulo: 'Código Limpo',
        editora: 'Alta Books',
        edicao: 1,
        anoPublicacao: '2009',
        valor: 8990,
        autoresIds: [1, 2],
        assuntosIds: [1, 1]
    );
    
    expect(fn() => $this->command->execute($input))
        ->toThrow(DomainException::class, 'Um livro não pode ter assuntos repetidos.');
});

