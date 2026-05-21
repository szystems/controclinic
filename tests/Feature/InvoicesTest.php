<?php

namespace Tests\Feature;

use App\Livewire\App\Invoices\Create as InvoicesCreate;
use App\Livewire\App\Invoices\Index as InvoicesIndex;
use App\Livewire\App\Invoices\Show as InvoicesShow;
use App\Models\Clinic;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InvoicesTest extends TestCase
{
    use RefreshDatabase;

    private function createClinicWithOwner(): array
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $owner = User::factory()->owner()->create(['clinic_id' => $clinic->id]);

        // Enable billing in settings
        $settings = $clinic->settings ?? [];
        $settings['billing_enabled'] = true;
        $settings['invoice_prefix'] = 'TEST-';
        $settings['next_invoice_number'] = 1;
        $clinic->update(['settings' => $settings]);

        return [$clinic, $owner];
    }

    private function createDoctor(Clinic $clinic): User
    {
        return User::factory()->create([
            'clinic_id' => $clinic->id,
        ])->assignRole('doctor');
    }

    private function createPatient(Clinic $clinic): Patient
    {
        return Patient::factory()->create(['clinic_id' => $clinic->id]);
    }

    private function createInvoice(Clinic $clinic, Patient $patient, array $overrides = []): Invoice
    {
        $invoice = Invoice::create(array_merge([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'invoice_number' => 'TEST-000001',
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
            'unit_price' => 100.00,
            'discount_amount' => 0,
            'tax_rate' => 0,
            'total' => 100.00,
        ]);

        return $invoice;
    }

    // ==================== INDEX ====================

    public function test_index_requires_permission(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();

        // Recepcionista sin permiso invoices.view debe obtener 403
        $receptionist = User::factory()->create(['clinic_id' => $clinic->id]);
        $receptionist->syncRoles(['receptionist']);

        Livewire::actingAs($receptionist)
            ->test(InvoicesIndex::class, ['clinic' => $clinic])
            ->assertStatus(403);
    }

    public function test_index_renders_for_owner(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();
        $patient = $this->createPatient($clinic);
        $this->createInvoice($clinic, $patient);

        Livewire::actingAs($owner)
            ->test(InvoicesIndex::class, ['clinic' => $clinic])
            ->assertStatus(200);
    }

    public function test_index_filters_by_status(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();
        $patient = $this->createPatient($clinic);

        $this->createInvoice($clinic, $patient, ['invoice_number' => 'TEST-000001', 'status' => Invoice::STATUS_PAID, 'paid_amount' => 100]);
        $this->createInvoice($clinic, $patient, ['invoice_number' => 'TEST-000002', 'status' => Invoice::STATUS_PENDING]);

        Livewire::actingAs($owner)
            ->test(InvoicesIndex::class, ['clinic' => $clinic])
            ->set('status', Invoice::STATUS_PAID)
            ->assertSee('TEST-000001')
            ->assertDontSee('TEST-000002');
    }

    public function test_index_does_not_show_other_clinic_invoices(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();
        [$clinic2, $owner2] = $this->createClinicWithOwner();
        $patient2 = $this->createPatient($clinic2);
        $this->createInvoice($clinic2, $patient2, ['invoice_number' => 'ALIEN-999']);

        Livewire::actingAs($owner)
            ->test(InvoicesIndex::class, ['clinic' => $clinic])
            ->assertDontSee('ALIEN-999');
    }

    // ==================== CREATE ====================

    public function test_create_generates_invoice_and_items(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();
        $patient = $this->createPatient($clinic);

        Livewire::actingAs($owner)
            ->test(InvoicesCreate::class, ['clinic' => $clinic])
            ->call('selectPatient', $patient->id, $patient->full_name)
            ->set('issued_at', now()->toDateString())
            ->set('items.0.type', InvoiceItem::TYPE_CONSULTATION)
            ->set('items.0.description', 'Consulta general')
            ->set('items.0.quantity', 1)
            ->set('items.0.unit_price', 75)
            ->set('items.0.tax_rate', 0)
            ->call('save');

        $this->assertDatabaseHas('invoices', [
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
        ]);
        $this->assertDatabaseHas('invoice_items', ['description' => 'Consulta general']);
    }

    public function test_create_calculates_totals_correctly(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();
        $patient = $this->createPatient($clinic);

        Livewire::actingAs($owner)
            ->test(InvoicesCreate::class, ['clinic' => $clinic])
            ->call('selectPatient', $patient->id, $patient->full_name)
            ->set('issued_at', now()->toDateString())
            ->set('items.0.description', 'Consulta')
            ->set('items.0.quantity', 2)
            ->set('items.0.unit_price', 50)
            ->set('items.0.discount_amount', 10)
            ->set('items.0.tax_rate', 10)
            ->call('save');

        // Net = (2 * 50) - 10 = 90, tax = 9, total = 99
        $invoice = Invoice::where('clinic_id', $clinic->id)->first();
        $this->assertNotNull($invoice);
        $this->assertEquals(99.00, (float) $invoice->total);
    }

    public function test_create_validates_required_fields(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();

        // patient_id está vacío — issued_at tiene default de mount()
        Livewire::actingAs($owner)
            ->test(InvoicesCreate::class, ['clinic' => $clinic])
            ->call('save')
            ->assertHasErrors(['patient_id']);
    }

    public function test_add_and_remove_item_lines(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();

        Livewire::actingAs($owner)
            ->test(InvoicesCreate::class, ['clinic' => $clinic])
            ->assertCount('items', 1)
            ->call('addItem')
            ->assertCount('items', 2)
            ->call('removeItem', 1)
            ->assertCount('items', 1);
    }

    // ==================== SHOW / PAYMENTS ====================

    public function test_show_renders_invoice_details(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();
        $patient = $this->createPatient($clinic);
        $invoice = $this->createInvoice($clinic, $patient);

        Livewire::actingAs($owner)
            ->test(InvoicesShow::class, ['clinic' => $clinic, 'invoice' => $invoice])
            ->assertSee($invoice->invoice_number)
            ->assertStatus(200);
    }

    public function test_show_aborts_for_other_clinic_invoice(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();
        [$clinic2, $owner2] = $this->createClinicWithOwner();
        $patient2 = $this->createPatient($clinic2);
        $invoice2 = $this->createInvoice($clinic2, $patient2, ['invoice_number' => 'ALIEN-001']);

        Livewire::actingAs($owner)
            ->test(InvoicesShow::class, ['clinic' => $clinic, 'invoice' => $invoice2])
            ->assertStatus(404);
    }

    public function test_record_payment_updates_paid_amount_and_status(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();
        $patient = $this->createPatient($clinic);
        $invoice = $this->createInvoice($clinic, $patient); // total = 100

        Livewire::actingAs($owner)
            ->test(InvoicesShow::class, ['clinic' => $clinic, 'invoice' => $invoice])
            ->call('openPaymentModal')
            ->set('pay_amount', 60)
            ->set('pay_method', 'cash')
            ->set('pay_date', now()->toDateString())
            ->call('recordPayment');

        $invoice->refresh();
        $this->assertEquals(60.00, (float) $invoice->paid_amount);
        $this->assertEquals(Invoice::STATUS_PARTIAL, $invoice->status);
    }

    public function test_full_payment_marks_invoice_as_paid(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();
        $patient = $this->createPatient($clinic);
        $invoice = $this->createInvoice($clinic, $patient); // total = 100

        Livewire::actingAs($owner)
            ->test(InvoicesShow::class, ['clinic' => $clinic, 'invoice' => $invoice])
            ->call('openPaymentModal')
            ->set('pay_amount', 100)
            ->set('pay_method', 'card')
            ->set('pay_date', now()->toDateString())
            ->call('recordPayment');

        $invoice->refresh();
        $this->assertEquals(Invoice::STATUS_PAID, $invoice->status);
    }

    public function test_cancel_invoice(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();
        $patient = $this->createPatient($clinic);
        $invoice = $this->createInvoice($clinic, $patient);

        Livewire::actingAs($owner)
            ->test(InvoicesShow::class, ['clinic' => $clinic, 'invoice' => $invoice])
            ->call('cancel');

        $invoice->refresh();
        $this->assertEquals(Invoice::STATUS_CANCELLED, $invoice->status);
    }

    public function test_invoices_index_blocked_when_billing_disabled(): void
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $owner = User::factory()->owner()->create(['clinic_id' => $clinic->id]);

        // billing_enabled = false (default)
        $settings = $clinic->settings ?? [];
        $settings['billing_enabled'] = false;
        $clinic->update(['settings' => $settings]);

        Livewire::actingAs($owner)
            ->test(InvoicesIndex::class, ['clinic' => $clinic])
            ->assertForbidden();
    }

    public function test_invoices_index_accessible_when_billing_enabled(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner(); // billing_enabled = true

        Livewire::actingAs($owner)
            ->test(InvoicesIndex::class, ['clinic' => $clinic])
            ->assertOk();
    }

    // ==================== InvoiceService ====================

    public function test_invoice_service_generates_sequential_numbers(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();

        $service = app(InvoiceService::class);
        $n1 = $service->nextInvoiceNumber($clinic);
        $n2 = $service->nextInvoiceNumber($clinic);

        $this->assertEquals('TEST-000001', $n1);
        $this->assertEquals('TEST-000002', $n2);
    }

    public function test_invoice_service_recalculate_updates_totals(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();
        $patient = $this->createPatient($clinic);
        $invoice = $this->createInvoice($clinic, $patient);

        // Forzar un total incorrecto
        $invoice->update(['total' => 0]);

        app(InvoiceService::class)->recalculate($invoice);
        $invoice->refresh();

        $this->assertEquals(100.00, (float) $invoice->total);
    }
}
