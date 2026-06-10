<?php

namespace Tests\Feature;

use App\Filament\Resources\ColaboradorResource;
use App\Filament\Resources\ResponsavelResource;
use App\Models\Chamado;
use App\Models\Patrimonio;
use App\Models\Setor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class ChamadoAccessAndStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_chamado_visibility_follows_user_roles(): void
    {
        $setorA = Setor::create(['nome' => 'TI', 'andar' => '1', 'bloco' => 'A']);
        $setorB = Setor::create(['nome' => 'RH', 'andar' => '2', 'bloco' => 'B']);

        $admin = $this->user('admin');
        $responsavelA = $this->user('responsavel', ['setor_id' => $setorA->id]);
        $responsavelA2 = $this->user('responsavel', ['setor_id' => $setorA->id]);
        $responsavelB = $this->user('responsavel', ['setor_id' => $setorB->id]);
        $colaborador = $this->user('colaborador');

        $patrimonioA = Patrimonio::create(['codigo' => 'A-1', 'setor_id' => $setorA->id]);
        $patrimonioB = Patrimonio::create(['codigo' => 'B-1', 'setor_id' => $setorB->id]);

        $chamadoA = $this->chamado([
            'user_id' => $responsavelA->id,
            'colaborador_id' => $colaborador->id,
            'setor_id' => $setorA->id,
            'patrimonio_id' => $patrimonioA->id,
        ]);

        $chamadoB = $this->chamado([
            'user_id' => $responsavelB->id,
            'setor_id' => $setorB->id,
            'patrimonio_id' => $patrimonioB->id,
        ]);

        $chamadoA2 = $this->chamado([
            'user_id' => $responsavelA2->id,
            'setor_id' => $setorA->id,
            'patrimonio_id' => $patrimonioA->id,
        ]);

        $this->assertEqualsCanonicalizing(
            [$chamadoA->id, $chamadoA2->id, $chamadoB->id],
            Chamado::query()->visibleTo($admin)->pluck('id')->all()
        );

        $this->assertEqualsCanonicalizing(
            [$chamadoA->id, $chamadoA2->id],
            Chamado::query()->visibleTo($responsavelA)->pluck('id')->all()
        );

        $this->assertEqualsCanonicalizing(
            [$chamadoA->id, $chamadoA2->id, $chamadoB->id],
            Chamado::query()->visibleTo($colaborador)->pluck('id')->all()
        );

        $this->assertSame(
            [$patrimonioA->id],
            Patrimonio::query()->visibleTo($responsavelA)->pluck('id')->all()
        );

        $this->assertSame(
            [$setorA->id],
            Setor::query()->visibleTo($responsavelA)->pluck('id')->all()
        );

        $setoresVisiveisParaColaborador = Setor::query()->visibleTo($colaborador)->pluck('id')->all();

        $this->assertContains($setorA->id, $setoresVisiveisParaColaborador);
        $this->assertContains($setorB->id, $setoresVisiveisParaColaborador);
    }

    public function test_start_and_finish_flow_records_operational_dates_and_users(): void
    {
        $colaborador = $this->user('colaborador');
        $chamado = $this->chamado(['colaborador_id' => $colaborador->id]);

        $this->assertTrue($colaborador->can('iniciar', $chamado));

        $chamado->iniciar($colaborador);
        $chamado->refresh();

        $this->assertSame(Chamado::STATUS_EM_ANDAMENTO, $chamado->status);
        $this->assertNotNull($chamado->iniciado_em);
        $this->assertSame($colaborador->id, $chamado->iniciado_por_id);
        $this->assertTrue($colaborador->can('concluir', $chamado));

        $chamado->concluir($colaborador);
        $chamado->refresh();

        $this->assertSame(Chamado::STATUS_CONCLUIDO, $chamado->status);
        $this->assertNotNull($chamado->concluido_em);
        $this->assertSame($colaborador->id, $chamado->concluido_por_id);
    }

    public function test_colaborador_can_take_available_chamado_for_execution(): void
    {
        $colaborador = $this->user('colaborador');
        $outroColaborador = $this->user('colaborador');
        $chamado = $this->chamado(['colaborador_id' => null]);

        $this->assertTrue($colaborador->can('iniciar', $chamado));

        $chamado->iniciar($colaborador);
        $chamado->refresh();

        $this->assertSame($colaborador->id, $chamado->colaborador_id);
        $this->assertTrue($colaborador->can('concluir', $chamado));
        $this->assertFalse($outroColaborador->can('concluir', $chamado));
    }

    public function test_colaborador_resource_limits_common_user_to_own_record(): void
    {
        $admin = $this->user('admin');
        $colaborador = $this->user('colaborador');
        $outroColaborador = $this->user('colaborador');

        $this->actingAs($colaborador);

        $this->assertTrue(ColaboradorResource::canViewAny());
        $this->assertTrue(ColaboradorResource::canView($colaborador));
        $this->assertFalse(ColaboradorResource::canView($outroColaborador));
        $this->assertFalse(ColaboradorResource::canCreate());
        $this->assertFalse(ColaboradorResource::canEdit($colaborador));
        $this->assertSame(
            [$colaborador->id],
            ColaboradorResource::getEloquentQuery()->pluck('id')->all()
        );

        $this->actingAs($admin);

        $this->assertEqualsCanonicalizing(
            [$colaborador->id, $outroColaborador->id],
            ColaboradorResource::getEloquentQuery()->pluck('id')->all()
        );
    }

    public function test_responsavel_resource_limits_common_user_to_own_record(): void
    {
        $admin = $this->user('admin');
        $responsavel = $this->user('responsavel');
        $outroResponsavel = $this->user('responsavel');

        $this->actingAs($responsavel);

        $this->assertTrue(ResponsavelResource::canViewAny());
        $this->assertTrue(ResponsavelResource::canView($responsavel));
        $this->assertFalse(ResponsavelResource::canView($outroResponsavel));
        $this->assertFalse(ResponsavelResource::canCreate());
        $this->assertFalse(ResponsavelResource::canEdit($responsavel));
        $this->assertSame(
            [$responsavel->id],
            ResponsavelResource::getEloquentQuery()->pluck('id')->all()
        );

        $this->actingAs($admin);

        $this->assertEqualsCanonicalizing(
            [$responsavel->id, $outroResponsavel->id],
            ResponsavelResource::getEloquentQuery()->pluck('id')->all()
        );
    }

    public function test_chamado_cannot_be_finished_before_it_is_started(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->chamado()->concluir($this->user('colaborador'));
    }

    public function test_cancelled_chamado_cannot_be_started_or_finished(): void
    {
        $actor = $this->user('admin');
        $chamado = $this->chamado();

        $chamado->cancelar($actor);
        $chamado->refresh();

        $this->assertSame(Chamado::STATUS_CANCELADO, $chamado->status);
        $this->assertFalse($chamado->podeIniciar());
        $this->assertFalse($chamado->podeConcluir());

        $this->expectException(InvalidArgumentException::class);
        $chamado->iniciar($actor);
    }

    public function test_public_storage_paths_only_return_existing_files(): void
    {
        Storage::fake('public');

        $path = 'perfil-usuarios/avatar-'.uniqid().'.jpg';
        $user = $this->user('responsavel', ['foto_perfil' => $path]);
        $this->assertNull($user->publicStoragePath($user->foto_perfil));

        Storage::disk('public')->put($path, 'image');
        $this->assertSame($path, $user->publicStoragePath($user->foto_perfil));
    }

    private function user(string $cargo, array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'cargo' => $cargo,
            'ativo' => true,
        ], $attributes));
    }

    private function chamado(array $attributes = []): Chamado
    {
        $setor = Setor::create([
            'nome' => 'Manutencao',
            'andar' => '1',
            'bloco' => 'A',
        ]);

        $responsavel = $this->user('responsavel', ['setor_id' => $setor->id]);

        $patrimonio = Patrimonio::create([
            'codigo' => 'PAT-'.fake()->unique()->numberBetween(1000, 9999),
            'setor_id' => $setor->id,
        ]);

        return Chamado::create(array_merge([
            'user_id' => $responsavel->id,
            'setor_id' => $setor->id,
            'patrimonio_id' => $patrimonio->id,
            'prioridade' => Chamado::PRIORIDADE_MEDIA,
            'tipo' => 'eletrica',
            'observacao' => 'Lampada queimada',
            'status' => Chamado::STATUS_ABERTO,
        ], $attributes));
    }
}
