<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Cria a versão JSON
        DB::statement('
            CREATE VIEW autores_livros_json AS
            SELECT
              a.nome AS autor_nome,
              JSON_ARRAYAGG(
                JSON_OBJECT(
                  "codl", l.codl,
                  "titulo", l.titulo,
                  "editora", l.editora,
                  "edicao", l.edicao,
                  "anoPublicacao", l.anopublicacao,
                  "valor", l.valor,
                  "assuntos", COALESCE(
                    (
                      SELECT JSON_ARRAYAGG(s.descricao)
                      FROM livro_assunto la
                      JOIN assunto s ON s.codas = la.assunto_codas
                      WHERE la.livro_codl = l.codl
                    ),
                    JSON_ARRAY()
                  )
                )
              ) AS livros
            FROM autor a
            JOIN livro_autor la2 ON la2.autor_codau = a.codau
            JOIN livro l ON l.codl = la2.livro_codl
            GROUP BY a.nome;
        ');
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS autores_livros_json');
    }
};
