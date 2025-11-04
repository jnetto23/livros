<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutorModel extends Model
{
    protected $table = 'autor';
    protected $primaryKey = 'codau';
    public $timestamps = false;

    protected $fillable = [
        'nome',
    ];

    protected $casts = [
        'codau' => 'integer',
    ];
}

