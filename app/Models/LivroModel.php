<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LivroModel extends Model
{
    protected $table = 'livro';
    protected $primaryKey = 'codl';
    public $timestamps = false;

    protected $fillable = [
        'titulo',
        'editora',
        'edicao',
        'anopublicacao',
        'valor',
    ];

    protected $casts = [
        'codl' => 'integer',
        'edicao' => 'integer',
        'valor' => 'integer',
    ];

    public function autores(): BelongsToMany
    {
        return $this->belongsToMany(
            AutorModel::class,
            'livro_autor',
            'livro_codl',
            'autor_codau'
        );
    }

    public function assuntos(): BelongsToMany
    {
        return $this->belongsToMany(
            AssuntoModel::class,
            'livro_assunto',
            'livro_codl',
            'assunto_codas'
        );
    }
}

