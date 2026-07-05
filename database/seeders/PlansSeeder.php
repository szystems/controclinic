<?php

namespace Database\Seeders;

use App\Models\Clinic;
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
                'max_appointments_per_month' => 5,
                'max_doctors' => 1,
                'max_staff' => 1,
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
                'features' => ['custom_portal', 'booking', '2fa', 'compliance'],
                'highlight_features' => null,
                'monthly_price' => 19.00,
                'yearly_price' => 190.00,
                'paddle_monthly_price_id' => config('cashier.prices.solo.monthly'),
                'paddle_yearly_price_id' => config('cashier.prices.solo.yearly'),
                'paddle_product_id' => 'pro_01kmh5g4stmy7p3awpkdstmg63',
                'cta_text' => null,
                'cta_url' => null,
                'trial_days' => 0,
                'sort_order' => 1,
                'is_active' => true,
                'is_popular' => false,
                'is_free' => false,
                'is_enterprise' => false,
                'is_private' => false,
                'requires_code' => false,
            ],

            // ── Práctica: 3 doctores + 4 asistentes — $49/mes · $490/año ─────
            [
                'name' => 'Práctica',
                'slug' => 'practica',
                'description' => 'Para clínicas con varios doctores',
                'max_patients' => null,
                'max_appointments_per_month' => null,
                'max_doctors' => 3,
                'max_staff' => 4,
                'max_storage_bytes' => null,
                'features' => ['multi_doctor_portal', 'booking_advanced', 'audit_logs', '2fa', 'compliance'],
                'highlight_features' => null,
                'monthly_price' => 49.00,
                'yearly_price' => 490.00,
                'paddle_monthly_price_id' => config('cashier.prices.practica.monthly'),
                'paddle_yearly_price_id' => config('cashier.prices.practica.yearly'),
                'paddle_product_id' => null,
                'cta_text' => null,
                'cta_url' => null,
                'trial_days' => 0,
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
                'features' => ['multi_doctor_portal', 'booking_advanced', 'audit_logs', '2fa', 'compliance'],
                'highlight_features' => null,
                'monthly_price' => 99.00,
                'yearly_price' => 990.00,
                'paddle_monthly_price_id' => config('cashier.prices.clinica.monthly'),
                'paddle_yearly_price_id' => config('cashier.prices.clinica.yearly'),
                'paddle_product_id' => null,
                'cta_text' => null,
                'cta_url' => null,
                'trial_days' => 0,
                'sort_order' => 3,
                'is_active' => true,
                'is_popular' => false,
                'is_free' => false,
                'is_enterprise' => false,
                'is_private' => false,
                'requires_code' => false,
            ],

            // ── Enterprise: contactar (inactivo hasta flujo a medida) ─────────
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
                'cta_url' => null,
                'trial_days' => 0,
                'sort_order' => 4,
                'is_active' => false,
                'is_popular' => false,
                'is_free' => false,
                'is_enterprise' => true,
                'is_private' => true,
                'requires_code' => false,
            ],

            // ── Friendly: plan privado con código (descuento estudiantes/amigos) ─
            [
                'name' => 'Friendly',
                'slug' => 'friendly',
                'description' => 'Plan Solo con descuento (código requerido)',
                'max_patients' => null,
                'max_appointments_per_month' => null,
                'max_doctors' => 1,
                'max_staff' => 1,
                'max_storage_bytes' => null,
                'features' => ['custom_portal', 'booking', '2fa', 'compliance'],
                'highlight_features' => null,
                'monthly_price' => 9.00,
                'yearly_price' => 90.00,
                'paddle_monthly_price_id' => config('cashier.prices.friendly.monthly'),
                'paddle_yearly_price_id' => config('cashier.prices.friendly.yearly'),
                'paddle_product_id' => null,
                'cta_text' => null,
                'cta_url' => null,
                'trial_days' => 0,
                'sort_order' => 5,
                'is_active' => true,
                'is_popular' => false,
                'is_free' => false,
                'is_enterprise' => false,
                'is_private' => true,
                'requires_code' => true,
                'access_code' => env('PLAN_ACCESS_CODE_FRIENDLY', env('PLAN_ACCESS_CODE_SOLO_ESTUDIANTE', 'CC-ESTUDIANTE')),
            ],
        ];

        foreach ($plans as $planData) {
            Plan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData,
            );
        }

        $this->purgeLegacyPlans();

        $this->command->info('✅ Planes creados/actualizados exitosamente');
    }

    private function purgeLegacyPlans(): void
    {
        $friendly = Plan::where('slug', 'friendly')->first();
        $soloEstudiante = Plan::where('slug', 'solo-estudiante')->first();

        if ($friendly && $soloEstudiante) {
            Clinic::query()
                ->where('plan_id', $soloEstudiante->id)
                ->update([
                    'plan_id' => $friendly->id,
                    'plan_type' => 'friendly',
                ]);

            Clinic::query()
                ->where('plan_type', 'solo-estudiante')
                ->where(function ($query) use ($soloEstudiante) {
                    $query->whereNull('plan_id')
                        ->orWhere('plan_id', $soloEstudiante->id);
                })
                ->update([
                    'plan_id' => $friendly->id,
                    'plan_type' => 'friendly',
                ]);

            Clinic::query()->each(function (Clinic $clinic) {
                $slugs = $clinic->unlockedPlanSlugs();
                if (! in_array('solo-estudiante', $slugs, true)) {
                    return;
                }

                $settings = $clinic->settings ?? [];
                $updated = array_values(array_unique(array_map(
                    fn (string $slug) => $slug === 'solo-estudiante' ? 'friendly' : $slug,
                    $slugs
                )));
                $settings['billing']['unlocked_plan_slugs'] = $updated;
                $clinic->update(['settings' => $settings]);
            });
        }

        foreach (['group', 'solo-estudiante'] as $legacySlug) {
            $plan = Plan::where('slug', $legacySlug)->first();
            if ($plan && $plan->canBeDeleted()) {
                $plan->delete();
                $this->command?->info("🗑️  Plan legacy eliminado: {$legacySlug}");
            }
        }
    }
}
