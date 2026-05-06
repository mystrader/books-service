<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Autor extends Model
{
    protected $table = 'autores';

    protected $primaryKey = 'cod_au';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = ['nome'];

    protected $hidden = ['pivot'];

    public function livros(): BelongsToMany
    {
        return $this->belongsToMany(
            Livro::class,
            'livro_autor',
            'autor_cod_au',
            'livro_codl',
            'cod_au',
            'codl'
        );
    }
}
