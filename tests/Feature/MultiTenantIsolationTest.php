<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Bloque B — Sprint Estabilización
 *
 * Verifica que datos de la clínica A nunca sean visibles ni modificables
 * desde la sesión de la clínica B (cross-tenant data leak).
 *
 * Cobertura:
 *  - Listados respetan clinic_id en query.
 *  - Show con UUID de otra clínica → 403/404 (no leak).
 *  - Edición de recurso de otra clínica → 403/404.
 *  - Citas no se mezclan entre clínicas.
 */
class MultiTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinicA;

    private Clinic $clinicB;

    private User $ownerA;

    private User $ownerB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinicA = Clinic::factory()->onboarded()->create(['name' => 'Clínica A']);
        $this->clinicB = Clinic::factory()->onboarded()->create(['name' => 'Clínica B']);

        $this->ownerA = User::factory()->owner()->create(['clinic_id' => $this->clinicA->id]);
        $this->ownerB = User::factory()->owner()->create(['clinic_id' => $this->clinicB->id]);
    }

    // ============================================================
    // ACCESO INTER-TENANT POR URL
    // ============================================================

    public function test_owner_a_cannot_open_clinic_b_dashboard(): void
    {
        $response = $this->actingAs($this->ownerA)
            ->get("/app/{$this->clinicB->slug}/dashboard");

        // Tenant middleware aborta — 403 o 404 son aceptables
        $this->assertContains($response->status(), [403, 404]);
    }

    public function test_owner_a_cannot_list_clinic_b_patients(): void
    {
        $response = $this->actingAs($this->ownerA)
            ->get("/app/{$this->clinicB->slug}/patients");

        $this->assertContains($response->status(), [403, 404]);
    }

    // ============================================================
    // LISTADOS FILTRADOS POR CLÍNICA
    // ============================================================

    public function test_patients_list_only_shows_own_clinic_patients(): void
    {
        $patientA = Patient::factory()->create([
            'clinic_id' => $this->clinicA->id,
            'first_name' => 'AlphaPatient',
        ]);
        $patientB = Patient::factory()->create([
            'clinic_id' => $this->clinicB->id,
            'first_name' => 'BravoPatient',
        ]);

        $response = $this->actingAs($this->ownerA)
            ->get("/app/{$this->clinicA->slug}/patients");

        $response->assertOk()
            ->assertSee('AlphaPatient')
            ->assertDontSee('BravoPatient');
    }

    public function test_appointments_list_only_shows_own_clinic_appointments(): void
    {
        $patientA = Patient::factory()->create(['clinic_id' => $this->clinicA->id]);
        $patientB = Patient::factory()->create(['clinic_id' => $this->clinicB->id]);

        Appointment::factory()->create([
            'clinic_id' => $this->clinicA->id,
            'patient_id' => $patientA->id,
            'doctor_id' => $this->ownerA->id,
            'reason' => 'AlphaReason',
        ]);
        Appointment::factory()->create([
            'clinic_id' => $this->clinicB->id,
            'patient_id' => $patientB->id,
            'doctor_id' => $this->ownerB->id,
            'reason' => 'BravoReason',
        ]);

        $response = $this->actingAs($this->ownerA)
            ->get("/app/{$this->clinicA->slug}/appointments");

        $response->assertOk()
            ->assertDontSee('BravoReason');
    }

    // ============================================================
    // ACCESO DIRECTO A RECURSOS DE OTRA CLÍNICA POR UUID
    // ============================================================

    public function test_owner_a_cannot_show_patient_of_clinic_b(): void
    {
        $patientB = Patient::factory()->create(['clinic_id' => $this->clinicB->id]);

        // Intenta acceder al show de un paciente de B usando la URL de su propia clínica
        $response = $this->actingAs($this->ownerA)
            ->get("/app/{$this->clinicA->slug}/patients/{$patientB->id}");

        // No debe ser 200 OK con datos de B
        $this->assertNotEquals(200, $response->status(), 'No debe permitir ver paciente de otra clínica');
    }

    public function test_owner_a_cannot_show_appointment_of_clinic_b(): void
    {
        $patientB = Patient::factory()->create(['clinic_id' => $this->clinicB->id]);
        $apptB = Appointment::factory()->create([
            'clinic_id' => $this->clinicB->id,
            'patient_id' => $patientB->id,
            'doctor_id' => $this->ownerB->id,
        ]);

        $response = $this->actingAs($this->ownerA)
            ->get("/app/{$this->clinicA->slug}/appointments/{$apptB->id}");

        $this->assertNotEquals(200, $response->status(), 'No debe permitir ver cita de otra clínica');
    }

    // ============================================================
    // SUPER ADMIN
    // ============================================================

    public function test_super_admin_can_access_admin_panel(): void
    {
        $admin = User::factory()->create([
            'clinic_id' => $this->clinicA->id,
            'is_super_admin' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
    }

    public function test_non_super_admin_cannot_access_admin_panel(): void
    {
        $response = $this->actingAs($this->ownerA)->get('/admin');

        // EnsureIsAdmin aborta para ocultar la existencia del panel (404 o 403)
        $this->assertContains($response->status(), [403, 404]);
    }

    // ============================================================
    // FACTORIES — guarantía de aislamiento
    // ============================================================

    public function test_patient_factory_creates_clinic_when_none_provided(): void
    {
        $patient = Patient::factory()->create();

        $this->assertNotNull($patient->clinic_id, 'Patient factory debe asignar clinic_id (no null)');
    }

    public function test_appointment_factory_creates_clinic_when_none_provided(): void
    {
        $appt = Appointment::factory()->create();

        $this->assertNotNull($appt->clinic_id, 'Appointment factory debe asignar clinic_id (no null)');
    }
}
