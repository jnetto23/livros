<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssuntoModel extends Model
{
    protected $table = 'assunto';
    protected $primaryKey = 'codas';
    public $timestamps = false;

    protected $fillable = [
        'descricao',
    ];

    protected $casts = [
        'codas' => 'integer',
    ];
}

