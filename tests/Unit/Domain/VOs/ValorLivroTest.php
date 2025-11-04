<?php

use App\Domain\VOs\ValorLivro;
use InvalidArgumentException;

test('cria valor de livro válido', function () {
    $valor = ValorLivro::create(8990); // R$ 89,90 em centavos
    
    expect($valor->value())->toBe(8990);
});

test('aceita valor zero', function () {
    $valor = ValorLivro::create(0);
    
    expect($valor->value())->toBe(0);
});

test('lança exceção para valor negativo', function () {
    expect(fn() => ValorLivro::create(-100))
        ->toThrow(InvalidArgumentException::class, 'O valor do livro não pode ser negativo.');
});

test('equals retorna true para valores iguais', function () {
    $val1 = ValorLivro::create(8990);
    $val2 = ValorLivro::create(8990);
    
    expect($val1->equals($val2))->toBeTrue();
});

test('__toString retorna string do valor', function () {
    $valor = ValorLivro::create(8990);
    
    expect((string) $valor)->toBe('8990');
});

