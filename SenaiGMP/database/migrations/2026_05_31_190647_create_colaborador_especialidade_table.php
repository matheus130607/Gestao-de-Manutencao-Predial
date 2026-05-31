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
        Schema::create('colaborador_especialidade', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('especialidade');
            $table->timestamps();
            $table->unique(['user_id', 'especialidade']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('especialidades');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('colaborador_especialidade');

        Schema::table('users', function (Blueprint $table) {
            $table->json('especialidades')->nullable()->after('foto_perfil');
        });
    }
};
