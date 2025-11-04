<?php

use App\Domain\VOs\DescricaoAssunto;
use InvalidArgumentException;

test('cria descrição de assunto válida', function () {
    $descricao = DescricaoAssunto::create('Programação');

    expect($descricao->value())->toBe('Programação');
});

test('normaliza espaços duplos para espaço simples', function () {
    $descricao = DescricaoAssunto::create('Programação  Web');

    expect($descricao->value())->toBe('Programação Web');
});

test('remove espaços no início e fim', function () {
    $descricao = DescricaoAssunto::create('  Programação  ');

    expect($descricao->value())->toBe('Programação');
});

test('lança exceção para descrição vazia', function () {
    expect(fn() => DescricaoAssunto::create(''))
        ->toThrow(InvalidArgumentException::class, 'A descrição do assunto não pode ser vazia.');
});

test('lança exceção para descrição apenas com espaços', function () {
    expect(fn() => DescricaoAssunto::create('   '))
        ->toThrow(InvalidArgumentException::class, 'A descrição do assunto não pode ser vazia.');
});

test('lança exceção para descrição maior que 20 caracteres', function () {
    $textoLongo = str_repeat('A', 21);

    expect(fn() => DescricaoAssunto::create($textoLongo))
        ->toThrow(InvalidArgumentException::class, 'A descrição do assunto não pode ter mais de 20 caracteres.');
});

test('aceita descrição com exatamente 20 caracteres', function () {
    $texto = str_repeat('A', 20);
    $descricao = DescricaoAssunto::create($texto);

    expect($descricao->value())->toBe($texto);
});

test('equals retorna true para descrições iguais', function () {
    $desc1 = DescricaoAssunto::create('Programação');
    $desc2 = DescricaoAssunto::create('Programação');

    expect($desc1->equals($desc2))->toBeTrue();
});

test('equals retorna false para descrições diferentes', function () {
    $desc1 = DescricaoAssunto::create('Programação');
    $desc2 = DescricaoAssunto::create('Arquitetura');

    expect($desc1->equals($desc2))->toBeFalse();
});

test('__toString retorna o valor', function () {
    $descricao = DescricaoAssunto::create('Programação');

    expect((string) $descricao)->toBe('Programação');
});

