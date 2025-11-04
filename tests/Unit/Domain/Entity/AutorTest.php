<?php

use App\Domain\Entity\Autor;
use App\Domain\VOs\NomeAutor;

test('cria autor sem ID', function () {
    $nome = NomeAutor::create('Robert C. Martin');
    $autor = Autor::create($nome);
    
    expect($autor->codau())->toBeNull();
    expect($autor->nome()->value())->toBe('Robert C. Martin');
});

test('restaura autor com ID', function () {
    $nome = NomeAutor::create('Robert C. Martin');
    $autor = Autor::restore(1, $nome);
    
    expect($autor->codau())->toBe(1);
    expect($autor->nome()->value())->toBe('Robert C. Martin');
});

test('altera nome do autor', function () {
    $nome1 = NomeAutor::create('Robert C. Martin');
    $autor = Autor::create($nome1);
    
    $nome2 = NomeAutor::create('Martin Fowler');
    $autor->alterarNome($nome2);
    
    expect($autor->nome()->value())->toBe('Martin Fowler');
});

test('equals retorna true para autores com mesmo ID', function () {
    $nome = NomeAutor::create('Robert C. Martin');
    $autor1 = Autor::restore(1, $nome);
    $autor2 = Autor::restore(1, $nome);
    
    expect($autor1->equals($autor2))->toBeTrue();
});

test('equals retorna true para autores com mesmo nome quando sem ID', function () {
    $nome = NomeAutor::create('Robert C. Martin');
    $autor1 = Autor::create($nome);
    $autor2 = Autor::create($nome);
    
    expect($autor1->equals($autor2))->toBeTrue();
});

