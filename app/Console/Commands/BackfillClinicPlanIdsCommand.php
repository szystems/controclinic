<?php

namespace App\Console\Commands;

use App\Models\Clinic;
use App\Models\Plan;
use Illuminate\Console\Command;

class BackfillClinicPlanIdsCommand extends Command
{
    protected $signature = 'clinics:backfill-plan-ids {--dry-run : Show changes without saving}';

    protected $description = 'Link clinics missing plan_id to the Plan row matching their plan_type slug';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $updated = 0;

        $clinics = Clinic::query()->whereNull('plan_id')->whereNotNull('plan_type')->get();

        foreach ($clinics as $clinic) {
            $plan = Plan::findBySlug($clinic->plan_type);

            if (! $plan) {
                $this->warn("No plan found for clinic {$clinic->slug} (plan_type={$clinic->plan_type})");

                continue;
            }

            $this->line("{$clinic->slug} → plan {$plan->slug} (#{$plan->id})");

            if (! $dryRun) {
                $clinic->update(['plan_id' => $plan->id]);
            }

            $updated++;
        }

        $this->info($dryRun
            ? "Would update {$updated} clinic(s)."
            : "Updated {$updated} clinic(s).");

        return self::SUCCESS;
    }
}
