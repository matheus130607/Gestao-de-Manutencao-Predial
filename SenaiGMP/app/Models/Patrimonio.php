<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Patrimonio extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'valor',
        'data_aquisicao',
        'imagem',
        'setor_id',
    ];

    // Criar a relação: O patrimônio pertence a um Setor
    public function setor(): BelongsTo
    {
        return $this->belongsTo(Setor::class);
    }
}
