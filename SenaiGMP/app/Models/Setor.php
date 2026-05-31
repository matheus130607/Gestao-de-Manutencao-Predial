<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// DÍVIDA: tabela 'setors' deveria ser 'setores' — corrigir com nova migration em versão futura
class Setor extends Model
{
        protected $fillable = [
            'nome',
            'andar',
            'bloco',
        ];
}
