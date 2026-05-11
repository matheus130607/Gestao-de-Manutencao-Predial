<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    // Adicione esta linha para liberar o cadastro
    protected $fillable = ['nome', 'email', 'cnpj', 'local'];
}