<?php

use App\Domain\Entity\Livro;
use App\Domain\VOs\AnoPublicacao;
use App\Domain\VOs\NomeEditora;
use App\Domain\VOs\NumeroEdicao;
use App\Domain\VOs\TituloLivro;
use App\Domain\VOs\ValorLivro;

beforeEach(function () {
    $this->titulo = TituloLivro::create('Código Limpo');
    $this->editora = NomeEditora::create('Alta Books');
    $this->edicao = NumeroEdicao::create(1);
    $this->ano = AnoPublicacao::create('2009');
    $this->valor = ValorLivro::create(8990);
});

test('cria livro sem ID', function () {
    $livro = Livro::create(
        $this->titulo,
        $this->editora,
        $this->edicao,
        $this->ano,
        $this->valor
    );
    
    expect($livro->codl())->toBeNull();
    expect($livro->titulo()->value())->toBe('Código Limpo');
    expect($livro->autoresIds())->toBe([]);
    expect($livro->assuntosIds())->toBe([]);
});

test('restaura livro com ID', function () {
    $livro = Livro::restore(
        1,
        $this->titulo,
        $this->editora,
        $this->edicao,
        $this->ano,
        $this->valor
    );
    
    expect($livro->codl())->toBe(1);
    expect($livro->titulo()->value())->toBe('Código Limpo');
});

test('altera título do livro', function () {
    $livro = Livro::create($this->titulo, $this->editora, $this->edicao, $this->ano, $this->valor);
    $novoTitulo = TituloLivro::create('Arquitetura Limpa');
    
    $livro->alterarTitulo($novoTitulo);
    
    expect($livro->titulo()->value())->toBe('Arquitetura Limpa');
});

test('definir autores', function () {
    $livro = Livro::create($this->titulo, $this->editora, $this->edicao, $this->ano, $this->valor);
    
    $livro->definirAutores([1, 2, 3]);
    
    expect($livro->autoresIds())->toBe([1, 2, 3]);
});

test('definir assuntos', function () {
    $livro = Livro::create($this->titulo, $this->editora, $this->edicao, $this->ano, $this->valor);
    
    $livro->definirAssuntos([1, 2]);
    
    expect($livro->assuntosIds())->toBe([1, 2]);
});

test('adicionar autor', function () {
    $livro = Livro::create($this->titulo, $this->editora, $this->edicao, $this->ano, $this->valor);
    
    $livro->adicionarAutor(1);
    $livro->adicionarAutor(2);
    
    expect($livro->autoresIds())->toContain(1, 2);
});

test('não adiciona autor duplicado', function () {
    $livro = Livro::create($this->titulo, $this->editora, $this->edicao, $this->ano, $this->valor);
    
    $livro->adicionarAutor(1);
    $livro->adicionarAutor(1);
    
    expect($livro->autoresIds())->toBe([1]);
});

test('remover autor', function () {
    $livro = Livro::create($this->titulo, $this->editora, $this->edicao, $this->ano, $this->valor);
    $livro->definirAutores([1, 2, 3]);
    
    $livro->removerAutor(2);
    
    expect($livro->autoresIds())->toBe([1, 3]);
});

test('adicionar assunto', function () {
    $livro = Livro::create($this->titulo, $this->editora, $this->edicao, $this->ano, $this->valor);
    
    $livro->adicionarAssunto(1);
    $livro->adicionarAssunto(2);
    
    expect($livro->assuntosIds())->toContain(1, 2);
});

test('remover assunto', function () {
    $livro = Livro::create($this->titulo, $this->editora, $this->edicao, $this->ano, $this->valor);
    $livro->definirAssuntos([1, 2, 3]);
    
    $livro->removerAssunto(2);
    
    expect($livro->assuntosIds())->toBe([1, 3]);
});

test('equals retorna true para livros com mesmo ID', function () {
    $livro1 = Livro::restore(1, $this->titulo, $this->editora, $this->edicao, $this->ano, $this->valor);
    $livro2 = Livro::restore(1, $this->titulo, $this->editora, $this->edicao, $this->ano, $this->valor);
    
    expect($livro1->equals($livro2))->toBeTrue();
});

