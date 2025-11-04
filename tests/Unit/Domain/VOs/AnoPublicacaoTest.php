<?php

use App\Domain\VOs\AnoPublicacao;
use InvalidArgumentException;

test('cria ano de publicação válido', function () {
    $ano = AnoPublicacao::create('2024');
    
    expect($ano->value())->toBe('2024');
    expect($ano->toInt())->toBe(2024);
});

test('aceita ano como inteiro', function () {
    $ano = AnoPublicacao::create(2024);
    
    expect($ano->value())->toBe('2024');
    expect($ano->toInt())->toBe(2024);
});

test('lança exceção para ano vazio', function () {
    expect(fn() => AnoPublicacao::create(''))
        ->toThrow(InvalidArgumentException::class, 'O ano de publicação não pode ser vazio.');
});

test('lança exceção para ano com menos de 4 dígitos', function () {
    expect(fn() => AnoPublicacao::create('202'))
        ->toThrow(InvalidArgumentException::class, 'O ano de publicação deve ter exatamente 4 caracteres.');
});

test('lança exceção para ano com mais de 4 dígitos', function () {
    expect(fn() => AnoPublicacao::create('20245'))
        ->toThrow(InvalidArgumentException::class, 'O ano de publicação deve ter exatamente 4 caracteres.');
});

test('lança exceção para ano com letras', function () {
    expect(fn() => AnoPublicacao::create('202a'))
        ->toThrow(InvalidArgumentException::class, 'O ano de publicação deve conter apenas dígitos.');
});

test('lança exceção para ano menor que 1455', function () {
    expect(fn() => AnoPublicacao::create('1454'))
        ->toThrow(InvalidArgumentException::class);
});

test('lança exceção para ano maior que o ano atual', function () {
    $anoFuturo = (int) date('Y') + 1;
    
    expect(fn() => AnoPublicacao::create((string) $anoFuturo))
        ->toThrow(InvalidArgumentException::class);
});

test('aceita ano mínimo válido', function () {
    $ano = AnoPublicacao::create('1455');
    
    expect($ano->value())->toBe('1455');
});

test('aceita ano máximo válido (ano atual)', function () {
    $anoAtual = date('Y');
    $ano = AnoPublicacao::create($anoAtual);
    
    expect($ano->value())->toBe($anoAtual);
});

test('equals retorna true para anos iguais', function () {
    $ano1 = AnoPublicacao::create('2024');
    $ano2 = AnoPublicacao::create('2024');
    
    expect($ano1->equals($ano2))->toBeTrue();
});

