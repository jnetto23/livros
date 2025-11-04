<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BibliotecaSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // -------- Helpers --------
            $cents = fn(string $brl) => (int) round(
                floatval(str_replace(',', '.', str_replace('.', '', $brl))) * 100
            );

            $insertAndReturnIds = function (string $table, string $pk, array $rows): array {
                $map = [];
                foreach ($rows as $row) {
                    $id = DB::table($table)->insertGetId($row, $pk);
                    $map[$row['descricao'] ?? $row['nome'] ?? $row['titulo']] = $id;
                }
                return $map;
            };

            // -------- Assuntos --------
            $assuntos = [
                ['descricao' => 'Programação'],
                ['descricao' => 'Arquitetura'],
                ['descricao' => 'Padrões'],
                ['descricao' => 'Algoritmos'],
                ['descricao' => 'Refatoração'],
                ['descricao' => 'DDD'],
                ['descricao' => 'Eng. Software'],
                ['descricao' => 'Banco de Dados'],
                ['descricao' => 'Java'],
                ['descricao' => 'JavaScript'],
                ['descricao' => 'DevOps'],
                ['descricao' => 'Testes'],
                ['descricao' => 'Fantasia'],
            ];
            $assuntoId = $insertAndReturnIds('assunto', 'codas', $assuntos);

            // -------- Autores --------
            $autores = [
                ['nome' => 'Robert C. Martin'],
                ['nome' => 'Andrew Hunt'],
                ['nome' => 'David Thomas'],
                ['nome' => 'Erich Gamma'],
                ['nome' => 'Richard Helm'],
                ['nome' => 'Ralph Johnson'],
                ['nome' => 'John Vlissides'],
                ['nome' => 'Thomas H. Cormen'],
                ['nome' => 'Charles E. Leiserson'],
                ['nome' => 'Ronald L. Rivest'],
                ['nome' => 'Clifford Stein'],
                ['nome' => 'Martin Fowler'],
                ['nome' => 'Eric Evans'],
                ['nome' => 'Michael Feathers'],
                ['nome' => 'Aditya Bhargava'],
                ['nome' => 'Loiane Groner'],
                ['nome' => 'Kent Beck'],
                ['nome' => 'J.K. Rowling'],
                ['nome' => 'J.R.R. Tolkien'],
                ['nome' => 'George R.R. Martin'],
            ];
            $autorId = $insertAndReturnIds('autor', 'codau', $autores);

            // -------- Livros --------
            // OBS: valor em BRL como string "69,90" -> convertido para centavos
            $livros = [
                // === 20 Programação (pt-BR) ===
                ['titulo'=>'Código Limpo', 'editora'=>'Alta Books', 'edicao'=>1, 'anopublicacao'=>'2009', 'valor'=>$cents('89,90')],
                ['titulo'=>'Arquitetura Limpa', 'editora'=>'Alta Books', 'edicao'=>1, 'anopublicacao'=>'2019', 'valor'=>$cents('69,90')],
                ['titulo'=>'O Programador Pragmático', 'editora'=>'Bookman', 'edicao'=>1, 'anopublicacao'=>'2010', 'valor'=>$cents('174,30')],
                ['titulo'=>'Padrões de Projetos (GoF)', 'editora'=>'Bookman', 'edicao'=>1, 'anopublicacao'=>'2000', 'valor'=>$cents('206,33')],
                ['titulo'=>'Algoritmos (Cormen)', 'editora'=>'GEN LTC', 'edicao'=>4, 'anopublicacao'=>'2024', 'valor'=>$cents('437,58')],
                ['titulo'=>'Refatoração (2ª ed.)', 'editora'=>'Novatec', 'edicao'=>2, 'anopublicacao'=>'2019', 'valor'=>$cents('199,90')],
                ['titulo'=>'Padrões de Arquitetura', 'editora'=>'Bookman', 'edicao'=>1, 'anopublicacao'=>'2006', 'valor'=>$cents('199,90')],
                ['titulo'=>'Domain-Driven Design', 'editora'=>'Alta Books', 'edicao'=>1, 'anopublicacao'=>'2016', 'valor'=>$cents('229,90')],
                ['titulo'=>'Código Legado', 'editora'=>'Bookman', 'edicao'=>1, 'anopublicacao'=>'2010', 'valor'=>$cents('189,90')],
                ['titulo'=>'Entendendo Algoritmos', 'editora'=>'Novatec', 'edicao'=>1, 'anopublicacao'=>'2017', 'valor'=>$cents('129,90')],
                ['titulo'=>'Algoritmos com JavaScript', 'editora'=>'Novatec', 'edicao'=>2, 'anopublicacao'=>'2019', 'valor'=>$cents('129,90')],
                ['titulo'=>'Test-Driven Development', 'editora'=>'Bookman', 'edicao'=>1, 'anopublicacao'=>'2003', 'valor'=>$cents('149,90')],
                ['titulo'=>'Padrões de Projeto em Java', 'editora'=>'Novatec', 'edicao'=>1, 'anopublicacao'=>'2014', 'valor'=>$cents('119,90')],
                ['titulo'=>'Clean Code JavaScript', 'editora'=>'Independente', 'edicao'=>1, 'anopublicacao'=>'2020', 'valor'=>$cents('99,90')],
                ['titulo'=>'Clean Coder', 'editora'=>'Alta Books', 'edicao'=>1, 'anopublicacao'=>'2011', 'valor'=>$cents('129,90')],
                ['titulo'=>'Clean Agile (pt-BR)', 'editora'=>'Alta Books', 'edicao'=>1, 'anopublicacao'=>'2020', 'valor'=>$cents('119,90')],
                ['titulo'=>'Design de Software', 'editora'=>'Alta Books', 'edicao'=>1, 'anopublicacao'=>'2021', 'valor'=>$cents('149,90')],
                ['titulo'=>'Você Não Sabe JS', 'editora'=>'Novatec', 'edicao'=>1, 'anopublicacao'=>'2017', 'valor'=>$cents('84,90')],
                ['titulo'=>'SQL Antipatterns (pt-BR)', 'editora'=>'Novatec', 'edicao'=>1, 'anopublicacao'=>'2013', 'valor'=>$cents('119,90')],
                ['titulo'=>'Banco de Dados', 'editora'=>'Pearson', 'edicao'=>1, 'anopublicacao'=>'2011', 'valor'=>$cents('199,90')],

                // === Harry Potter (Rocco) ===
                ['titulo'=>'Harry Potter e a Pedra Filosofal', 'editora'=>'Rocco', 'edicao'=>1, 'anopublicacao'=>'2000', 'valor'=>$cents('48,04')],
                ['titulo'=>'Harry Potter e a Câmara Secreta', 'editora'=>'Rocco', 'edicao'=>1, 'anopublicacao'=>'2000', 'valor'=>$cents('49,90')],
                ['titulo'=>'Harry Potter e o Prisioneiro de Azkaban', 'editora'=>'Rocco', 'edicao'=>1, 'anopublicacao'=>'2000', 'valor'=>$cents('59,90')],
                ['titulo'=>'Harry Potter e o Cálice de Fogo', 'editora'=>'Rocco', 'edicao'=>1, 'anopublicacao'=>'2001', 'valor'=>$cents('69,90')],
                ['titulo'=>'Harry Potter e a Ordem da Fênix', 'editora'=>'Rocco', 'edicao'=>1, 'anopublicacao'=>'2003', 'valor'=>$cents('79,90')],
                ['titulo'=>'Harry Potter e o Enigma do Príncipe', 'editora'=>'Rocco', 'edicao'=>1, 'anopublicacao'=>'2005', 'valor'=>$cents('79,90')],
                ['titulo'=>'Harry Potter e as Relíquias da Morte', 'editora'=>'Rocco', 'edicao'=>1, 'anopublicacao'=>'2007', 'valor'=>$cents('89,90')],

                // === O Senhor dos Anéis (HarperCollins Brasil) ===
                ['titulo'=>'O Senhor dos Anéis: A Sociedade', 'editora'=>'HarperCollins Brasil', 'edicao'=>1, 'anopublicacao'=>'2019', 'valor'=>$cents('49,90')],
                ['titulo'=>'O Senhor dos Anéis: As Duas Torres', 'editora'=>'HarperCollins Brasil', 'edicao'=>1, 'anopublicacao'=>'2019', 'valor'=>$cents('49,90')],
                ['titulo'=>'O Senhor dos Anéis: O Retorno', 'editora'=>'HarperCollins Brasil', 'edicao'=>1, 'anopublicacao'=>'2019', 'valor'=>$cents('49,90')],

                // === As Crônicas de Gelo e Fogo (Suma/LeYa) ===
                ['titulo'=>'A Guerra dos Tronos (Livro 1)', 'editora'=>'Suma', 'edicao'=>1, 'anopublicacao'=>'2019', 'valor'=>$cents('74,90')],
                ['titulo'=>'A Fúria dos Reis (Livro 2)', 'editora'=>'Suma', 'edicao'=>1, 'anopublicacao'=>'2019', 'valor'=>$cents('84,90')],
                ['titulo'=>'A Tormenta de Espadas (Livro 3)', 'editora'=>'Suma', 'edicao'=>1, 'anopublicacao'=>'2019', 'valor'=>$cents('89,90')],
                ['titulo'=>'O Festim dos Corvos (Livro 4)', 'editora'=>'Suma', 'edicao'=>1, 'anopublicacao'=>'2019', 'valor'=>$cents('89,90')],
                ['titulo'=>'A Dança dos Dragões (Livro 5)', 'editora'=>'Suma', 'edicao'=>1, 'anopublicacao'=>'2019', 'valor'=>$cents('99,90')],
            ];

            $livroId = [];
            foreach ($livros as $livro) {
                $id = DB::table('livro')->insertGetId($livro, 'codl');
                $livroId[$livro['titulo']] = $id;
            }

            // -------- Pivot: livro_autor --------
            $la = function (string $titulo, array $autores) use ($livroId, $autorId) {
                foreach ($autores as $nome) {
                    if (!isset($autorId[$nome])) {
                        continue; // Ignora autores que não existem
                    }
                    DB::table('livro_autor')->insert([
                        'livro_codl' => $livroId[$titulo],
                        'autor_codau' => $autorId[$nome],
                    ]);
                }
            };

            // Programação
            $la('Código Limpo', ['Robert C. Martin']);
            $la('Arquitetura Limpa', ['Robert C. Martin']);
            $la('O Programador Pragmático', ['Andrew Hunt', 'David Thomas']);
            $la('Padrões de Projetos (GoF)', ['Erich Gamma','Richard Helm','Ralph Johnson','John Vlissides']);
            $la('Algoritmos (Cormen)', ['Thomas H. Cormen','Charles E. Leiserson','Ronald L. Rivest','Clifford Stein']);
            $la('Refatoração (2ª ed.)', ['Martin Fowler']);
            $la('Padrões de Arquitetura', ['Martin Fowler']);
            $la('Domain-Driven Design', ['Eric Evans']);
            $la('Código Legado', ['Michael Feathers']);
            $la('Entendendo Algoritmos', ['Aditya Bhargava']);
            $la('Algoritmos com JavaScript', ['Loiane Groner']);
            $la('Test-Driven Development', ['Kent Beck']);
            $la('Clean Coder', ['Robert C. Martin']);
            $la('Clean Agile (pt-BR)', ['Robert C. Martin']);
            $la('Design de Software', ['Robert C. Martin']);
            // Nota: Livros sem autor específico (Padrões de Projeto em Java, Clean Code JavaScript, etc) não terão autores vinculados

            // Fantasia
            foreach ([
                'Harry Potter e a Pedra Filosofal',
                'Harry Potter e a Câmara Secreta',
                'Harry Potter e o Prisioneiro de Azkaban',
                'Harry Potter e o Cálice de Fogo',
                'Harry Potter e a Ordem da Fênix',
                'Harry Potter e o Enigma do Príncipe',
                'Harry Potter e as Relíquias da Morte',
            ] as $hp) {
                $la($hp, ['J.K. Rowling']);
            }

            foreach ([
                'O Senhor dos Anéis: A Sociedade',
                'O Senhor dos Anéis: As Duas Torres',
                'O Senhor dos Anéis: O Retorno',
            ] as $lotr) {
                $la($lotr, ['J.R.R. Tolkien']);
            }

            foreach ([
                'A Guerra dos Tronos (Livro 1)',
                'A Fúria dos Reis (Livro 2)',
                'A Tormenta de Espadas (Livro 3)',
                'O Festim dos Corvos (Livro 4)',
                'A Dança dos Dragões (Livro 5)',
            ] as $got) {
                $la($got, ['George R.R. Martin']);
            }

            // -------- Pivot: livro_assunto --------
            $linkAssuntos = function (string $titulo, array $assuntosNome) use ($livroId, $assuntoId) {
                foreach ($assuntosNome as $a) {
                    if (!isset($assuntoId[$a])) {
                        continue; // Ignora assuntos que não existem
                    }
                    DB::table('livro_assunto')->insert([
                        'livro_codl' => $livroId[$titulo],
                        'assunto_codas' => $assuntoId[$a],
                    ]);
                }
            };

            // Programação
            $linkAssuntos('Código Limpo', ['Programação','Eng. Software','Refatoração']);
            $linkAssuntos('Arquitetura Limpa', ['Arquitetura','Eng. Software']);
            $linkAssuntos('O Programador Pragmático', ['Programação','Eng. Software']);
            $linkAssuntos('Padrões de Projetos (GoF)', ['Padrões','Eng. Software']);
            $linkAssuntos('Algoritmos (Cormen)', ['Algoritmos']);
            $linkAssuntos('Refatoração (2ª ed.)', ['Refatoração']);
            $linkAssuntos('Padrões de Arquitetura', ['Arquitetura','Eng. Software']);
            $linkAssuntos('Domain-Driven Design', ['DDD','Arquitetura']);
            $linkAssuntos('Código Legado', ['Programação','Refatoração']);
            $linkAssuntos('Entendendo Algoritmos', ['Algoritmos']);
            $linkAssuntos('Algoritmos com JavaScript', ['Algoritmos','JavaScript']);
            $linkAssuntos('Test-Driven Development', ['Testes','Programação']);
            $linkAssuntos('Padrões de Projeto em Java', ['Padrões','Java']);
            $linkAssuntos('Clean Code JavaScript', ['Programação','JavaScript']);
            $linkAssuntos('Clean Coder', ['Programação','Eng. Software']);
            $linkAssuntos('Clean Agile (pt-BR)', ['Eng. Software']);
            $linkAssuntos('Design de Software', ['Eng. Software','Arquitetura']);
            $linkAssuntos('Você Não Sabe JS', ['JavaScript','Programação']);
            $linkAssuntos('SQL Antipatterns (pt-BR)', ['Banco de Dados','Padrões']);
            $linkAssuntos('Banco de Dados', ['Banco de Dados']);

            // Fantasia
            foreach ([
                'Harry Potter e a Pedra Filosofal',
                'Harry Potter e a Câmara Secreta',
                'Harry Potter e o Prisioneiro de Azkaban',
                'Harry Potter e o Cálice de Fogo',
                'Harry Potter e a Ordem da Fênix',
                'Harry Potter e o Enigma do Príncipe',
                'Harry Potter e as Relíquias da Morte',
                'O Senhor dos Anéis: A Sociedade',
                'O Senhor dos Anéis: As Duas Torres',
                'O Senhor dos Anéis: O Retorno',
                'A Guerra dos Tronos (Livro 1)',
                'A Fúria dos Reis (Livro 2)',
                'A Tormenta de Espadas (Livro 3)',
                'O Festim dos Corvos (Livro 4)',
                'A Dança dos Dragões (Livro 5)',
            ] as $fantasia) {
                $linkAssuntos($fantasia, ['Fantasia']);
            }
        });
    }
}

