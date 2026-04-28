<?php

namespace Database\Seeders;

use App\Models\Estoque;
use App\Models\Produto;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Usuários ──────────────────────────────────────────────────────
        User::create([
            'name'                => 'Administrador Raízes',
            'email'               => 'admin@raizes.com',
            'password'            => Hash::make('password'),
            'profile'             => 'ADMIN',
            'consentimentos_lgpd' => true,
        ]);

        User::create([
            'name'                => 'Gerente Unidade 1',
            'email'               => 'gerente@raizes.com',
            'password'            => Hash::make('password'),
            'profile'             => 'GERENTE',
            'consentimentos_lgpd' => true,
        ]);

        User::create([
            'name'                => 'Cozinha Unidade 1',
            'email'               => 'cozinha@raizes.com',
            'password'            => Hash::make('password'),
            'profile'             => 'COZINHA',
            'consentimentos_lgpd' => true,
        ]);

        User::create([
            'name'                => 'Maria Santos',
            'email'               => 'cliente@raizes.com',
            'password'            => Hash::make('password'),
            'profile'             => 'CLIENTE',
            'consentimentos_lgpd' => true,
        ]);

        // ── Unidades ──────────────────────────────────────────────────────
        $unidade1 = Unidade::create([
            'name'                    => 'Raízes do Nordeste - Recife Centro',
            'cidade'                  => 'Recife',
            'estado'                  => 'PE',
            'endereco'                => 'Rua das Flores, 120, Boa Vista',
            'ativa'                   => true,
            'possui_cozinha_completa' => true,
            'horario_abertura'        => '06:00:00',
            'horario_fechamento'      => '22:00:00',
        ]);

        $unidade2 = Unidade::create([
            'name'                    => 'Raízes do Nordeste - Fortaleza Aldeota',
            'cidade'                  => 'Fortaleza',
            'estado'                  => 'CE',
            'endereco'                => 'Av. Santos Dumont, 500',
            'ativa'                   => true,
            'possui_cozinha_completa' => false,
            'horario_abertura'        => '07:00:00',
            'horario_fechamento'      => '21:00:00',
        ]);

        // ── Produtos ──────────────────────────────────────────────────────
        $produtos = collect([
            [
                'name'      => 'Tapioca Nordestina Tradicional',
                'descricao' => 'Tapioca com queijo coalho e manteiga de garrafa',
                'preco'     => 12.90,
                'categoria' => 'TAPIOCAS',
            ],
            [
                'name'      => 'Cuscuz de Milho Recheado',
                'descricao' => 'Cuscuz com frango desfiado e legumes',
                'preco'     => 18.50,
                'categoria' => 'CUSCUZ',
            ],
            [
                'name'      => 'Bolo de Macaxeira',
                'descricao' => 'Bolo úmido de macaxeira com coco ralado',
                'preco'     => 9.90,
                'categoria' => 'BOLOS',
            ],
            [
                'name'      => 'Café Nordestino Coado',
                'descricao' => 'Café passado na hora com rapadura',
                'preco'     => 5.00,
                'categoria' => 'BEBIDAS',
            ],
            [
                'name'      => 'Suco de Cajá',
                'descricao' => 'Suco natural de cajá gelado 300ml',
                'preco'     => 8.00,
                'categoria' => 'BEBIDAS',
            ],
            [
                'name'      => 'Combo Café da Manhã Nordestino',
                'descricao' => 'Cuscuz + tapioca + café + suco',
                'preco'     => 32.90,
                'categoria' => 'COMBOS',
            ],
        ])->map(fn($p) => Produto::create(array_merge($p, ['disponivel' => true])));

        // ── Estoque inicial por unidade ───────────────────────────────────
        foreach ($produtos as $produto) {
            Estoque::create([
                'unidade_id' => $unidade1->id,
                'produto_id' => $produto->id,
                'quantidade' => 50,
            ]);

            Estoque::create([
                'unidade_id' => $unidade2->id,
                'produto_id' => $produto->id,
                'quantidade' => 30,
            ]);
        }

        // Produto com estoque zerado na unidade 2 para testar erro 409
        Estoque::where('unidade_id', $unidade2->id)
            ->where('produto_id', $produtos[2]->id) // Bolo de Macaxeira
            ->update(['quantidade' => 0]);

        // ── Resumo no terminal ────────────────────────────────────────────
        $this->command->info('✅ Seed concluído!');
        $this->command->table(
            ['Perfil', 'E-mail', 'Senha'],
            [
                ['ADMIN',   'admin@raizes.com',   'password'],
                ['GERENTE', 'gerente@raizes.com', 'password'],
                ['COZINHA', 'cozinha@raizes.com', 'password'],
                ['CLIENTE', 'cliente@raizes.com', 'password'],
            ]
        );
    }
}
