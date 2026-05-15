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
    Schema::create('chamados', function (Blueprint $table) {
        $table->id();
        
        // Relacionamentos (Chaves Estrangeiras)
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Responsável
        $table->foreignId('setor_id')->constrained();
        $table->foreignId('patrimonio_id')->constrained();
        
        // Dados do Chamado
        $table->string('prioridade'); // baixa, media, alta, emergencia
        $table->string('imagem')->nullable();
        $table->text('observacao');
        $table->string('status')->default('aberto'); // aberto, em_andamento, concluido
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chamados');
    }
};
