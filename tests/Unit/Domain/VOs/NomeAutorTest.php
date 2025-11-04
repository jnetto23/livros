<?php

use App\Domain\VOs\NomeAutor;
use InvalidArgumentException;

test('cria nome de autor válido', function () {
    $nome = NomeAutor::create('Robert C. Martin');
    
    expect($nome->value())->toBe('Robert C. Martin');
});

test('normaliza espaços duplos para espaço simples', function () {
    $nome = NomeAutor::create('Robert  C.   Martin');
    
    expect($nome->value())->toBe('Robert C. Martin');
});

test('remove espaços no início e fim', function () {
    $nome = NomeAutor::create('  Robert C. Martin  ');
    
    expect($nome->value())->toBe('Robert C. Martin');
});

test('lança exceção para nome vazio', function () {
    expect(fn() => NomeAutor::create(''))
        ->toThrow(InvalidArgumentException::class, 'O nome do autor não pode ser vazio.');
});

test('lança exceção para nome apenas com espaços', function () {
    expect(fn() => NomeAutor::create('   '))
        ->toThrow(InvalidArgumentException::class, 'O nome do autor não pode ser vazio.');
});

test('lança exceção para nome maior que 40 caracteres', function () {
    $textoLongo = str_repeat('A', 41);
    
    expect(fn() => NomeAutor::create($textoLongo))
        ->toThrow(InvalidArgumentException::class, 'O nome do autor não pode ter mais de 40 caracteres.');
});

test('aceita nome com exatamente 40 caracteres', function () {
    $texto = str_repeat('A', 40);
    $nome = NomeAutor::create($texto);
    
    expect($nome->value())->toBe($texto);
});

test('equals retorna true para nomes iguais', function () {
    $nome1 = NomeAutor::create('Robert C. Martin');
    $nome2 = NomeAutor::create('Robert C. Martin');
    
    expect($nome1->equals($nome2))->toBeTrue();
});

test('equals retorna false para nomes diferentes', function () {
    $nome1 = NomeAutor::create('Robert C. Martin');
    $nome2 = NomeAutor::create('Martin Fowler');
    
    expect($nome1->equals($nome2))->toBeFalse();
});

