<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Para empezar a probar ControClinic',
                'max_patients' => 25,
                'max_appointments_per_month' => 5,
                'max_doctors' => 1,
                'max_staff' => 0,
                'max_storage_bytes' => 524288000,
                'features' => ['basic_forms', 'basic_portal'],
                'monthly_price' => 0,
                'yearly_price' => 0,
                'trial_days' => 0,
                'sort_order' => 0,
                'is_active' => true,
                'is_free' => true,
            ],
            [
                'name' => 'Solo',
                'slug' => 'solo',
                'description' => 'Para médicos independientes',
                'max_patients' => null,
                'max_appointments_per_month' => null,
                'max_doctors' => 1,
                'max_staff' => 1,
                'max_storage_bytes' => null,
                'features' => ['ai', 'mobile_basic', '2fa', 'compliance', 'custom_portal', 'booking'],
                'monthly_price' => 29.00,
                'yearly_price' => 276.00,
                'paddle_monthly_price_id' => config('cashier.prices.solo.monthly'),
                'paddle_yearly_price_id' => config('cashier.prices.solo.yearly'),
                'paddle_product_id' => 'pro_01kmh5g4stmy7p3awpkdstmg63',
                'trial_days' => 14,
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Group',
                'slug' => 'group',
                'description' => 'Para clínicas con múltiples doctores',
                'max_patients' => null,
                'max_appointments_per_month' => null,
                'max_doctors' => 5,
                'max_staff' => 3,
                'max_storage_bytes' => null,
                'features' => ['ai', 'ai_collaborative', 'mobile_advanced', '2fa', 'compliance', 'audit_logs', 'multi_doctor_portal', 'booking_advanced'],
                'monthly_price' => 79.00,
                'yearly_price' => 756.00,
                'paddle_monthly_price_id' => config('cashier.prices.group.monthly'),
                'paddle_yearly_price_id' => config('cashier.prices.group.yearly'),
                'paddle_product_id' => 'pro_01kmh5gnnwg92kg0fsrv49d9fg',
                'trial_days' => 14,
                'sort_order' => 2,
                'is_active' => true,
                'is_popular' => true,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Para hospitales y redes de clínicas',
                'max_patients' => null,
                'max_appointments_per_month' => null,
                'max_doctors' => null,
                'max_staff' => null,
                'max_storage_bytes' => null,
                'features' => ['ai', 'ai_custom', 'mobile_enterprise', '2fa', 'compliance', 'audit_logs', 'api', 'white_label', 'bi', 'custom_domain'],
                'monthly_price' => null,
                'yearly_price' => null,
                'trial_days' => 30,
                'sort_order' => 3,
                'is_active' => true,
                'is_enterprise' => true,
            ],
        ];

        foreach ($plans as $planData) {
            Plan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData,
            );
        }

        $this->command->info('✅ Planes creados/actualizados exitosamente');
    }
}
