<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ColaboradorEspecialidade extends Model
{
    protected $table = 'colaborador_especialidade';

    protected $fillable = ['user_id', 'especialidade'];
}
