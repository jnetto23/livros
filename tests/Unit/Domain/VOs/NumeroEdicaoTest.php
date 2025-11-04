<?php

use App\Domain\VOs\NumeroEdicao;
use InvalidArgumentException;

test('cria número de edição válido', function () {
    $edicao = NumeroEdicao::create(1);
    
    expect($edicao->value())->toBe(1);
});

test('lança exceção para edição menor que 1', function () {
    expect(fn() => NumeroEdicao::create(0))
        ->toThrow(InvalidArgumentException::class, 'O número da edição deve ser maior que zero.');
});

test('lança exceção para edição negativa', function () {
    expect(fn() => NumeroEdicao::create(-1))
        ->toThrow(InvalidArgumentException::class, 'O número da edição deve ser maior que zero.');
});

test('aceita edição igual a 1', function () {
    $edicao = NumeroEdicao::create(1);
    
    expect($edicao->value())->toBe(1);
});

test('aceita edição maior que 1', function () {
    $edicao = NumeroEdicao::create(5);
    
    expect($edicao->value())->toBe(5);
});

test('equals retorna true para edições iguais', function () {
    $ed1 = NumeroEdicao::create(1);
    $ed2 = NumeroEdicao::create(1);
    
    expect($ed1->equals($ed2))->toBeTrue();
});

test('__toString retorna string do valor', function () {
    $edicao = NumeroEdicao::create(3);
    
    expect((string) $edicao)->toBe('3');
});

