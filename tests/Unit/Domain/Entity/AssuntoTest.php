<?php

use App\Domain\Entity\Assunto;
use App\Domain\VOs\DescricaoAssunto;

test('cria assunto sem ID', function () {
    $descricao = DescricaoAssunto::create('Programação');
    $assunto = Assunto::create($descricao);
    
    expect($assunto->codas())->toBeNull();
    expect($assunto->descricao()->value())->toBe('Programação');
});

test('restaura assunto com ID', function () {
    $descricao = DescricaoAssunto::create('Programação');
    $assunto = Assunto::restore(1, $descricao);
    
    expect($assunto->codas())->toBe(1);
    expect($assunto->descricao()->value())->toBe('Programação');
});

test('altera descrição do assunto', function () {
    $desc1 = DescricaoAssunto::create('Programação');
    $assunto = Assunto::create($desc1);
    
    $desc2 = DescricaoAssunto::create('Arquitetura');
    $assunto->alterarDescricao($desc2);
    
    expect($assunto->descricao()->value())->toBe('Arquitetura');
});

test('equals retorna true para assuntos com mesmo ID', function () {
    $desc = DescricaoAssunto::create('Programação');
    $assunto1 = Assunto::restore(1, $desc);
    $assunto2 = Assunto::restore(1, $desc);
    
    expect($assunto1->equals($assunto2))->toBeTrue();
});

test('equals retorna true para assuntos com mesma descrição quando sem ID', function () {
    $desc = DescricaoAssunto::create('Programação');
    $assunto1 = Assunto::create($desc);
    $assunto2 = Assunto::create($desc);
    
    expect($assunto1->equals($assunto2))->toBeTrue();
});

test('equals retorna false para assuntos diferentes', function () {
    $desc1 = DescricaoAssunto::create('Programação');
    $desc2 = DescricaoAssunto::create('Arquitetura');
    $assunto1 = Assunto::create($desc1);
    $assunto2 = Assunto::create($desc2);
    
    expect($assunto1->equals($assunto2))->toBeFalse();
});

