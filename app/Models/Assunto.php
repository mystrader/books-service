<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Assunto extends Model
{
    protected $table = 'assuntos';

    protected $primaryKey = 'cod_as';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = ['descricao'];

    protected $hidden = ['pivot'];

    public function livros(): BelongsToMany
    {
        return $this->belongsToMany(
            Livro::class,
            'livro_assunto',
            'assunto_cod_as',
            'livro_codl',
            'cod_as',
            'codl'
        );
    }
}
