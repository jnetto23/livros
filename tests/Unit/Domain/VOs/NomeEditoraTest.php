<?php

use App\Domain\VOs\NomeEditora;
use InvalidArgumentException;

test('cria nome de editora válido', function () {
    $editora = NomeEditora::create('Alta Books');
    
    expect($editora->value())->toBe('Alta Books');
});

test('normaliza espaços duplos para espaço simples', function () {
    $editora = NomeEditora::create('Alta  Books  Editora');
    
    expect($editora->value())->toBe('Alta Books Editora');
});

test('remove espaços no início e fim', function () {
    $editora = NomeEditora::create('  Alta Books  ');
    
    expect($editora->value())->toBe('Alta Books');
});

test('lança exceção para nome vazio', function () {
    expect(fn() => NomeEditora::create(''))
        ->toThrow(InvalidArgumentException::class, 'O nome da editora não pode ser vazio.');
});

test('lança exceção para nome maior que 40 caracteres', function () {
    $textoLongo = str_repeat('A', 41);
    
    expect(fn() => NomeEditora::create($textoLongo))
        ->toThrow(InvalidArgumentException::class, 'O nome da editora não pode ter mais de 40 caracteres.');
});

