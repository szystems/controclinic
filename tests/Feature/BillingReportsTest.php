<?php

namespace Tests\Feature;

use App\Livewire\App\Reports\Index;
use App\Models\Clinic;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BillingReportsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function makeClinicWithBillingAndOwner(): array
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $owner = User::factory()->create(['clinic_id' => $clinic->id]);
        $owner->assignRole('owner');

        $settings = $clinic->settings ?? [];
        $settings['billing_enabled'] = true;
        $settings['invoice_prefix'] = 'BR-';
        $settings['next_invoice_number'] = 1;
        $clinic->update(['settings' => $settings]);

        return [$clinic, $owner];
    }

    private function makeInvoice(Clinic $clinic, array $overrides = []): Invoice
    {
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);

        $invoice = Invoice::create(array_merge([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'invoice_number' => 'BR-'.uniqid(),
            'issued_at' => now()->toDateString(),
            'currency' => 'USD',
            'status' => Invoice::STATUS_PENDING,
            'subtotal' => 100.00,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => 100.00,
            'paid_amount' => 0,
        ], $overrides));

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'order' => 1,
            'type' => InvoiceItem::TYPE_CONSULTATION,
            'description' => 'Consulta',
            'quantity' => 1,
            'unit_price' => $invoice->total,
            'discount_amount' => 0,
            'tax_rate' => 0,
            'total' => $invoice->total,
        ]);

        return $invoice;
    }

    private function makePayment(Invoice $invoice, User $recorder, array $overrides = []): InvoicePayment
    {
        return InvoicePayment::create(array_merge([
            'invoice_id' => $invoice->id,
            'recorded_by' => $recorder->id,
            'amount' => 100.00,
            'currency' => 'USD',
            'method' => 'cash',
            'paid_at' => now(),
        ], $overrides));
    }

    // ==================== VISIBILITY ====================

    public function test_billing_section_visible_when_billing_enabled(): void
    {
        [$clinic, $owner] = $this->makeClinicWithBillingAndOwner();

        $component = Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic]);

        $component->assertViewHas('billingEnabled', true);
    }

    public function test_billing_section_hidden_when_billing_disabled(): void
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $owner = User::factory()->create(['clinic_id' => $clinic->id]);
        $owner->assignRole('owner');

        $component = Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic]);

        $component->assertViewHas('billingEnabled', false);
        $component->assertViewHas('totalInvoiced', null);
        $component->assertViewHas('totalCollected', null);
    }

    // ==================== TOTALS ====================

    public function test_total_invoiced_sums_non_draft_non_cancelled_invoices(): void
    {
        [$clinic, $owner] = $this->makeClinicWithBillingAndOwner();

        $this->makeInvoice($clinic, ['total' => 200, 'subtotal' => 200, 'status' => Invoice::STATUS_PENDING]);
        $this->makeInvoice($clinic, ['total' => 100, 'subtotal' => 100, 'status' => Invoice::STATUS_PAID]);
        // Draft and cancelled should be excluded
        $this->makeInvoice($clinic, ['total' => 50, 'subtotal' => 50, 'status' => Invoice::STATUS_DRAFT]);
        $this->makeInvoice($clinic, ['total' => 50, 'subtotal' => 50, 'status' => Invoice::STATUS_CANCELLED]);

        $component = Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic]);

        $component->assertViewHas('totalInvoiced', 300.00);
    }

    public function test_total_collected_sums_payments_in_period(): void
    {
        [$clinic, $owner] = $this->makeClinicWithBillingAndOwner();
        $invoice = $this->makeInvoice($clinic, ['status' => Invoice::STATUS_PAID, 'paid_amount' => 150]);
        $this->makePayment($invoice, $owner, ['amount' => 150]);

        $component = Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic]);

        $component->assertViewHas('totalCollected', 150.00);
    }

    public function test_pending_revenue_sums_unpaid_balances(): void
    {
        [$clinic, $owner] = $this->makeClinicWithBillingAndOwner();
        // Partial invoice: total 100, paid 40 → pending 60
        $this->makeInvoice($clinic, [
            'total' => 100,
            'subtotal' => 100,
            'paid_amount' => 40,
            'status' => Invoice::STATUS_PARTIAL,
        ]);
        // Pending invoice: total 80, paid 0 → pending 80
        $this->makeInvoice($clinic, [
            'total' => 80,
            'subtotal' => 80,
            'paid_amount' => 0,
            'status' => Invoice::STATUS_PENDING,
        ]);

        $component = Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic]);

        $component->assertViewHas('pendingRevenue', 140.00);
    }

    // ==================== TENANT ISOLATION ====================

    public function test_billing_totals_are_tenant_scoped(): void
    {
        [$clinic, $owner] = $this->makeClinicWithBillingAndOwner();
        $this->makeInvoice($clinic, ['total' => 200, 'subtotal' => 200]);

        // Another clinic
        $otherClinic = Clinic::factory()->onboarded()->create();
        $settings = $otherClinic->settings ?? [];
        $settings['billing_enabled'] = true;
        $otherClinic->update(['settings' => $settings]);
        $this->makeInvoice($otherClinic, ['total' => 9999, 'subtotal' => 9999]);

        $component = Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic]);

        $component->assertViewHas('totalInvoiced', 200.00);
    }

    // ==================== REVENUE BY DOCTOR ====================

    public function test_revenue_by_doctor_groups_by_assigned_doctor(): void
    {
        [$clinic, $owner] = $this->makeClinicWithBillingAndOwner();
        $doctor = User::factory()->create(['clinic_id' => $clinic->id]);
        $doctor->assignRole('doctor');
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);

        Invoice::create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'invoice_number' => 'DR-001',
            'issued_at' => now()->toDateString(),
            'currency' => 'USD',
            'status' => Invoice::STATUS_PAID,
            'subtotal' => 150.00,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => 150.00,
            'paid_amount' => 150.00,
        ]);

        $component = Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic]);

        $component->assertViewHas('revenueByDoctor', function ($rows) {
            return is_array($rows) && count($rows) === 1 && $rows[0]['invoiced'] == 150.00;
        });
    }

    // ==================== REVENUE BY PAYMENT METHOD ====================

    public function test_revenue_by_payment_method_groups_correctly(): void
    {
        [$clinic, $owner] = $this->makeClinicWithBillingAndOwner();
        $invoice = $this->makeInvoice($clinic, ['status' => Invoice::STATUS_PAID, 'paid_amount' => 200]);
        $this->makePayment($invoice, $owner, ['amount' => 120, 'method' => 'cash']);
        $this->makePayment($invoice, $owner, ['amount' => 80,  'method' => 'card']);

        $component = Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic]);

        $component->assertViewHas('revenueByPaymentMethod', function ($rows) {
            $rows = collect($rows);
            $cash = $rows->firstWhere('method', 'cash');
            $card = $rows->firstWhere('method', 'card');

            return $cash && $cash['amount'] == 120.00
                && $card && $card['amount'] == 80.00;
        });
    }
}
