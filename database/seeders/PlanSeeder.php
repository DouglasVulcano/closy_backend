<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'price' => 49.90,
                'stripe_price_id' => 'price_starter_monthly', // Substitua pelo ID real do Stripe
                'role' => 'STARTER',
                'active' => true,
                'description' => 'Plano ideal para começar com funcionalidades básicas',
                'features' => json_encode([
                    'Até 500 leads por mês',
                    'Suporte por email',
                ]),
                'trial_days' => 7,
                'monthly_leads_limit' => 500
            ],
            [
                'name' => 'Pro',
                'price' => 99.90,
                'stripe_price_id' => 'price_pro_monthly', // Substitua pelo ID real do Stripe
                'role' => 'PRO',
                'active' => true,
                'description' => 'Plano completo para profissionais e empresas',
                'features' => json_encode([
                    'Até 2.000 leads por mês',
                    'Suporte prioritário'
                ]),
                'trial_days' => 7,
                'monthly_leads_limit' => 2000
            ]
        ];

        foreach ($plans as $planData) {
            Plan::updateOrCreate(
                ['name' => $planData['name']],
                $planData
            );
        }
    }
}
