<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('patrimonios', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique(); // O seu código identificador
            $table->decimal('valor', 10, 2)->nullable();
            $table->date('data_aquisicao')->nullable();
            $table->string('imagem')->nullable(); // Caminho da imagem
            $table->foreignId('setor_id')->nullable()->constrained('setors')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patrimonios');
    }
};
