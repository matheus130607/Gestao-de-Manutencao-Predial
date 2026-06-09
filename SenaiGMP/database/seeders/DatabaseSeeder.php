<?php

namespace Database\Seeders;

use App\Models\Chamado;
use App\Models\ColaboradorEspecialidade;
use App\Models\Empresa;
use App\Models\Patrimonio;
use App\Models\Setor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Empresas
            $empresa1 = Empresa::firstOrCreate(
                ['cnpj' => '11.222.333/0001-44'],
                ['nome' => 'TechBuild Ltda', 'email' => 'contato@techbuild.local', 'telefone' => '(11) 91111-2222']
            );

            $empresa2 = Empresa::firstOrCreate(
                ['cnpj' => '55.666.777/0001-88'],
                ['nome' => 'Infraestrutura Geral S/A', 'email' => 'contato@infrageral.local', 'telefone' => '(11) 93333-4444']
            );

            // 2. Setores
            $setor_ti = Setor::firstOrCreate(
                ['nome' => 'TI'],
                ['andar' => '1º Andar', 'bloco' => 'Bloco A']
            );

            $setor_rh = Setor::firstOrCreate(
                ['nome' => 'RH'],
                ['andar' => '2º Andar', 'bloco' => 'Bloco B']
            );

            $setor_manutencao = Setor::firstOrCreate(
                ['nome' => 'Manutenção'],
                ['andar' => 'Térreo', 'bloco' => 'Bloco C']
            );

            // 3. Admin
            $admin = User::firstOrCreate(
                ['email' => 'admin@gmp.local'],
                [
                    'name'       => 'Administrador',
                    'password'   => Hash::make('password'),
                    'cargo'      => 'admin',
                    'ativo'      => true,
                    'empresa_id' => $empresa1->id,
                    'cpf'        => '000.000.000-01',
                    'nif'        => 'ADM001',
                    'telefone'   => '(11) 90000-0001',
                ]
            );

            // 4. Responsável vinculado ao setor TI
            $responsavel = User::firstOrCreate(
                ['email' => 'responsavel@gmp.local'],
                [
                    'name'       => 'Responsável TI',
                    'password'   => Hash::make('password'),
                    'cargo'      => 'responsavel',
                    'ativo'      => true,
                    'empresa_id' => $empresa1->id,
                    'setor_id'   => $setor_ti->id,
                    'cpf'        => '000.000.000-02',
                    'nif'        => 'RESP001',
                    'telefone'   => '(11) 90000-0002',
                ]
            );

            // 5. Colaborador com especialidades
            $colaborador = User::firstOrCreate(
                ['email' => 'colaborador@gmp.local'],
                [
                    'name'       => 'Colaborador Geral',
                    'password'   => Hash::make('password'),
                    'cargo'      => 'colaborador',
                    'ativo'      => true,
                    'empresa_id' => $empresa2->id,
                    'cpf'        => '000.000.000-03',
                    'nif'        => 'COLAB001',
                    'telefone'   => '(11) 90000-0003',
                ]
            );

            foreach (['eletrica', 'hidraulica'] as $especialidade) {
                ColaboradorEspecialidade::firstOrCreate([
                    'user_id'      => $colaborador->id,
                    'especialidade' => $especialidade,
                ]);
            }

            // 6. Patrimônios (um por setor)
            $pat_ti = Patrimonio::firstOrCreate(
                ['codigo' => 'PAT-001'],
                ['nome' => 'Servidor Principal', 'setor_id' => $setor_ti->id, 'valor' => 15000.00, 'data_aquisicao' => '2024-01-15']
            );

            $pat_rh = Patrimonio::firstOrCreate(
                ['codigo' => 'PAT-002'],
                ['nome' => 'Ar Condicionado RH', 'setor_id' => $setor_rh->id, 'valor' => 3500.00, 'data_aquisicao' => '2024-03-10']
            );

            $pat_man = Patrimonio::firstOrCreate(
                ['codigo' => 'PAT-003'],
                ['nome' => 'Compressor Industrial', 'setor_id' => $setor_manutencao->id, 'valor' => 8000.00, 'data_aquisicao' => '2024-06-01']
            );

            // 7. Chamados em status diferentes
            if (! Chamado::where('observacao', 'like', '[SEED]%')->exists()) {
                // Aberto
                Chamado::create([
                    'user_id'       => $responsavel->id,
                    'colaborador_id' => $colaborador->id,
                    'setor_id'      => $setor_ti->id,
                    'patrimonio_id' => $pat_ti->id,
                    'tipo'          => 'eletrica',
                    'prioridade'    => Chamado::PRIORIDADE_ALTA,
                    'status'        => Chamado::STATUS_ABERTO,
                    'observacao'    => '[SEED] Falha na alimentação elétrica do servidor.',
                ]);

                // Em andamento
                $chamado_andamento = new Chamado([
                    'user_id'       => $responsavel->id,
                    'colaborador_id' => $colaborador->id,
                    'setor_id'      => $setor_rh->id,
                    'patrimonio_id' => $pat_rh->id,
                    'tipo'          => 'ar_condicionado',
                    'prioridade'    => Chamado::PRIORIDADE_MEDIA,
                    'observacao'    => '[SEED] Ar condicionado sem refrigeração.',
                ]);
                $chamado_andamento->save();
                $chamado_andamento->iniciar($admin);

                // Concluído
                $chamado_concluido = new Chamado([
                    'user_id'       => $responsavel->id,
                    'colaborador_id' => $colaborador->id,
                    'setor_id'      => $setor_manutencao->id,
                    'patrimonio_id' => $pat_man->id,
                    'tipo'          => 'hidraulica',
                    'prioridade'    => Chamado::PRIORIDADE_BAIXA,
                    'observacao'    => '[SEED] Vazamento hidráulico no compressor.',
                ]);
                $chamado_concluido->save();
                $chamado_concluido->iniciar($admin);
                $chamado_concluido->concluir($admin);
            }
        });
    }
}
