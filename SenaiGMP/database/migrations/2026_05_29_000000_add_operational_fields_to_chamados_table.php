<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chamados', function (Blueprint $table) {
            $table->date('prazo')->nullable()->after('status');
            $table->timestamp('iniciado_em')->nullable()->after('prazo');
            $table->timestamp('concluido_em')->nullable()->after('iniciado_em');

            $table->index(['status', 'prioridade']);
            $table->index('tipo');
            $table->index('prazo');
        });
    }

    public function down(): void
    {
        Schema::table('chamados', function (Blueprint $table) {
            $table->dropIndex(['status', 'prioridade']);
            $table->dropIndex(['tipo']);
            $table->dropIndex(['prazo']);

            $table->dropColumn([
                'prazo',
                'iniciado_em',
                'concluido_em',
            ]);
        });
    }
};
