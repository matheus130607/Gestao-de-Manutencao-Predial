<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chamado extends Model
{
    protected $fillable = [
        'user_id', 'setor_id', 'patrimonio_id', 
        'prioridade', 'imagem', 'observacao', 'status'
    ];

    public function responsavel(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function setor(): BelongsTo {
        return $this->belongsTo(Setor::class);
    }

    public function patrimonio(): BelongsTo {
        return $this->belongsTo(Patrimonio::class);
    }
}