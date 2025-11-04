<?php

use App\Domain\VOs\TituloLivro;
use InvalidArgumentException;

test('cria título de livro válido', function () {
    $titulo = TituloLivro::create('Código Limpo');
    
    expect($titulo->value())->toBe('Código Limpo');
});

test('normaliza espaços duplos para espaço simples', function () {
    $titulo = TituloLivro::create('Código  Limpo  e   Elegante');
    
    expect($titulo->value())->toBe('Código Limpo e Elegante');
});

test('remove espaços no início e fim', function () {
    $titulo = TituloLivro::create('  Código Limpo  ');
    
    expect($titulo->value())->toBe('Código Limpo');
});

test('lança exceção para título vazio', function () {
    expect(fn() => TituloLivro::create(''))
        ->toThrow(InvalidArgumentException::class, 'O título do livro não pode ser vazio.');
});

test('lança exceção para título maior que 40 caracteres', function () {
    $textoLongo = str_repeat('A', 41);
    
    expect(fn() => TituloLivro::create($textoLongo))
        ->toThrow(InvalidArgumentException::class, 'O título do livro não pode ter mais de 40 caracteres.');
});

test('aceita título com exatamente 40 caracteres', function () {
    $texto = str_repeat('A', 40);
    $titulo = TituloLivro::create($texto);
    
    expect($titulo->value())->toBe($texto);
});

