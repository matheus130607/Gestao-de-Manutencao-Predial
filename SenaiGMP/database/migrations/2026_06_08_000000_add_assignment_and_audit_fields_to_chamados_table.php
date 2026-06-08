<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chamados', function (Blueprint $table): void {
            if (! Schema::hasColumn('chamados', 'colaborador_id')) {
                $table->foreignId('colaborador_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('chamados', 'iniciado_por_id')) {
                $table->foreignId('iniciado_por_id')
                    ->nullable()
                    ->after('iniciado_em')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('chamados', 'concluido_por_id')) {
                $table->foreignId('concluido_por_id')
                    ->nullable()
                    ->after('concluido_em')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('chamados', 'cancelado_em')) {
                $table->timestamp('cancelado_em')->nullable()->after('concluido_por_id');
            }

            if (! Schema::hasColumn('chamados', 'cancelado_por_id')) {
                $table->foreignId('cancelado_por_id')
                    ->nullable()
                    ->after('cancelado_em')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });

        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'foto_perfil')) {
                $table->string('foto_perfil')->nullable()->after('ativo');
            }
        });

        if (Schema::hasColumn('users', 'fot_perfil') && Schema::hasColumn('users', 'foto_perfil')) {
            DB::table('users')
                ->whereNull('foto_perfil')
                ->whereNotNull('fot_perfil')
                ->update(['foto_perfil' => DB::raw('fot_perfil')]);
        }
    }

    public function down(): void
    {
        Schema::table('chamados', function (Blueprint $table): void {
            foreach ([
                'cancelado_por_id',
                'concluido_por_id',
                'iniciado_por_id',
                'colaborador_id',
            ] as $column) {
                if (Schema::hasColumn('chamados', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }

            if (Schema::hasColumn('chamados', 'cancelado_em')) {
                $table->dropColumn('cancelado_em');
            }
        });
    }
};
