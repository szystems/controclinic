<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            // ── Free (cortesía) ──────────────────────────────────────────────
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Para empezar a probar ControClinic',
                'max_patients' => 25,
                'max_appointments_per_month' => null, // Ilimitado en dev; fijar a 5 en producción
                'max_doctors' => 1,
                'max_staff' => 0,
                'max_storage_bytes' => 524288000, // 500 MB
                'features' => ['basic_forms', 'basic_portal'],
                'highlight_features' => null,
                'monthly_price' => 0,
                'yearly_price' => 0,
                'cta_text' => null,
                'cta_url' => null,
                'trial_days' => 0,
                'sort_order' => 0,
                'is_active' => true,
                'is_popular' => false,
                'is_free' => true,
                'is_enterprise' => false,
                'is_private' => false,
                'requires_code' => false,
            ],

            // ── Solo: 1 doctor + 1 asistente — $19/mes · $190/año ────────────
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
                'highlight_features' => null,
                'monthly_price' => 19.00,
                'yearly_price' => 190.00,
                'paddle_monthly_price_id' => config('cashier.prices.solo.monthly'),
                'paddle_yearly_price_id' => config('cashier.prices.solo.yearly'),
                'paddle_product_id' => 'pro_01kmh5g4stmy7p3awpkdstmg63',
                'cta_text' => null,
                'cta_url' => null,
                'trial_days' => 14,
                'sort_order' => 1,
                'is_active' => true,
                'is_popular' => false,
                'is_free' => false,
                'is_enterprise' => false,
                'is_private' => false,
                'requires_code' => false,
            ],

            // ── Práctica: 3 doctores + 4 asistentes — $49/mes · $490/año ─────
            // Plan POPULAR (más vendido)
            [
                'name' => 'Práctica',
                'slug' => 'practica',
                'description' => 'Para clínicas con varios doctores',
                'max_patients' => null,
                'max_appointments_per_month' => null,
                'max_doctors' => 3,
                'max_staff' => 4,
                'max_storage_bytes' => null,
                'features' => ['ai', 'ai_collaborative', 'mobile_advanced', '2fa', 'compliance', 'audit_logs', 'multi_doctor_portal', 'booking_advanced'],
                'highlight_features' => null,
                'monthly_price' => 49.00,
                'yearly_price' => 490.00,
                'paddle_monthly_price_id' => config('cashier.prices.practica.monthly'),
                'paddle_yearly_price_id' => config('cashier.prices.practica.yearly'),
                'paddle_product_id' => null, // pendiente de crear en Paddle sandbox
                'cta_text' => null,
                'cta_url' => null,
                'trial_days' => 14,
                'sort_order' => 2,
                'is_active' => true,
                'is_popular' => true,
                'is_free' => false,
                'is_enterprise' => false,
                'is_private' => false,
                'requires_code' => false,
            ],

            // ── Clínica: 8 doctores + 10 asistentes — $99/mes · $990/año ─────
            [
                'name' => 'Clínica',
                'slug' => 'clinica',
                'description' => 'Para clínicas medianas y consultorios consolidados',
                'max_patients' => null,
                'max_appointments_per_month' => null,
                'max_doctors' => 8,
                'max_staff' => 10,
                'max_storage_bytes' => null,
                'features' => ['ai', 'ai_collaborative', 'mobile_advanced', '2fa', 'compliance', 'audit_logs', 'multi_doctor_portal', 'booking_advanced', 'priority_support'],
                'highlight_features' => null,
                'monthly_price' => 99.00,
                'yearly_price' => 990.00,
                'paddle_monthly_price_id' => config('cashier.prices.clinica.monthly'),
                'paddle_yearly_price_id' => config('cashier.prices.clinica.yearly'),
                'paddle_product_id' => null,
                'cta_text' => null,
                'cta_url' => null,
                'trial_days' => 14,
                'sort_order' => 3,
                'is_active' => true,
                'is_popular' => false,
                'is_free' => false,
                'is_enterprise' => false,
                'is_private' => false,
                'requires_code' => false,
            ],

            // ── Enterprise: contactar ────────────────────────────────────────
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
                'highlight_features' => null,
                'monthly_price' => null,
                'yearly_price' => null,
                'cta_text' => 'Contactar comercial',
                'cta_url' => null, // resuelto en runtime → route('contact', ['subject' => 'enterprise'])
                'trial_days' => 30,
                'sort_order' => 4,
                'is_active' => true,
                'is_popular' => false,
                'is_free' => false,
                'is_enterprise' => true,
                'is_private' => false,
                'requires_code' => false,
            ],

            // ── Group (legacy) ───────────────────────────────────────────────
            // Plan antiguo "Group" $79. Se mantiene en BD por si alguna `clinics.plan_id`
            // apunta a él, pero queda `is_private=true` para no aparecer en /pricing
            // y `is_active=false` para excluirlo de filtros públicos.
            [
                'name' => 'Group (legacy)',
                'slug' => 'group',
                'description' => 'Plan legacy — reemplazado por Práctica/Clínica',
                'max_patients' => null,
                'max_appointments_per_month' => null,
                'max_doctors' => 5,
                'max_staff' => 3,
                'max_storage_bytes' => null,
                'features' => ['ai', 'ai_collaborative', 'mobile_advanced', '2fa', 'compliance', 'audit_logs', 'multi_doctor_portal', 'booking_advanced'],
                'highlight_features' => null,
                'monthly_price' => 79.00,
                'yearly_price' => 756.00,
                'paddle_monthly_price_id' => config('cashier.prices.group.monthly'),
                'paddle_yearly_price_id' => config('cashier.prices.group.yearly'),
                'paddle_product_id' => 'pro_01kmh5gnnwg92kg0fsrv49d9fg',
                'cta_text' => null,
                'cta_url' => null,
                'trial_days' => 14,
                'sort_order' => 99,
                'is_active' => false,
                'is_popular' => false,
                'is_free' => false,
                'is_enterprise' => false,
                'is_private' => true,
                'requires_code' => false,
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
