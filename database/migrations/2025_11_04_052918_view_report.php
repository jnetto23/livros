<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('
            CREATE VIEW autores_livros AS
            SELECT
                autor.nome AS autor_nome,
                GROUP_CONCAT(
                CONCAT(
					livro.codl, \',\',
					livro.titulo, \',\',
					livro.editora, \',\',
					livro.edicao, \',\',
					livro.anopublicacao, \',\',
					livro.valor, \',\',
					COALESCE((
                        SELECT GROUP_CONCAT(assunto.descricao SEPARATOR \';\')
                        FROM livro_assunto
                        JOIN assunto ON livro_assunto.assunto_codas = assunto.codas
                        WHERE livro_assunto.livro_codl = livro.codl
                    ), \'Sem assunto\')
				)
                SEPARATOR \' | \'
                ) as livros
            FROM
                autor
            JOIN
                livro_autor ON autor.codau = livro_autor.autor_codau
            JOIN
                livro ON livro_autor.livro_codl = livro.codl
            GROUP BY
                autor.nome;
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS autores_livros');
    }
};
