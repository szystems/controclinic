<?php

namespace Tests\Feature;

use App\Livewire\Public\Booking;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PublicBookingTest extends TestCase
{
    use RefreshDatabase;

    private function makeClinicWithDoctor(array $clinicState = []): array
    {
        $clinic = Clinic::factory()->onboarded()->create(array_merge([
            'public_portal_enabled' => true,
            'settings' => array_merge(Clinic::getDefaultSettings(), [
                'allow_online_booking' => true,
                'require_booking_confirmation' => true,
                'min_booking_notice' => 0,
                'max_booking_advance' => 30,
                'working_days' => [0, 1, 2, 3, 4, 5, 6],
                'working_hours_start' => '08:00',
                'working_hours_end' => '18:00',
                'appointment_duration' => 30,
            ]),
        ], $clinicState));

        $doctor = User::factory()->create([
            'clinic_id' => $clinic->id,
            'role' => User::ROLE_DOCTOR,
            'is_active' => true,
        ]);

        return [$clinic, $doctor];
    }

    public function test_public_booking_page_loads_when_portal_enabled(): void
    {
        [$clinic] = $this->makeClinicWithDoctor();

        $this->get('/c/'.$clinic->slug)->assertOk()->assertSee($clinic->name);
    }

    public function test_legacy_public_route_works(): void
    {
        [$clinic] = $this->makeClinicWithDoctor();

        $this->get('/public/'.$clinic->slug)->assertOk();
    }

    public function test_resolves_clinic_by_public_portal_slug(): void
    {
        [$clinic] = $this->makeClinicWithDoctor(['public_portal_slug' => 'my-custom-portal']);

        $this->get('/c/my-custom-portal')->assertOk()->assertSee($clinic->name);
    }

    public function test_returns_404_when_portal_disabled(): void
    {
        [$clinic] = $this->makeClinicWithDoctor(['public_portal_enabled' => false]);

        $this->get('/c/'.$clinic->slug)->assertNotFound();
    }

    public function test_shows_disabled_message_when_online_booking_off(): void
    {
        [$clinic] = $this->makeClinicWithDoctor([
            'settings' => array_merge(Clinic::getDefaultSettings(), [
                'allow_online_booking' => false,
            ]),
        ]);

        $this->get('/c/'.$clinic->slug)->assertOk()->assertSee(__('booking.booking_disabled'));
    }

    public function test_lists_active_doctors(): void
    {
        [$clinic, $doctor] = $this->makeClinicWithDoctor();
        // Inactive doctor should not appear
        User::factory()->create([
            'clinic_id' => $clinic->id,
            'role' => User::ROLE_DOCTOR,
            'is_active' => false,
            'name' => 'Inactive Doctor X',
        ]);

        Livewire::test(Booking::class, ['clinic' => $clinic])
            ->assertSee($doctor->name)
            ->assertDontSee('Inactive Doctor X');
    }

    public function test_select_doctor_advances_to_step_2(): void
    {
        [$clinic, $doctor] = $this->makeClinicWithDoctor();

        Livewire::test(Booking::class, ['clinic' => $clinic])
            ->call('selectDoctor', $doctor->id)
            ->assertSet('doctor_id', $doctor->id)
            ->assertSet('step', 2);
    }

    public function test_available_slots_excludes_existing_appointments(): void
    {
        [$clinic, $doctor] = $this->makeClinicWithDoctor();
        $tomorrow = now()->addDay()->toDateString();

        // Create existing appointment at 10:00
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        Appointment::create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'created_by' => $doctor->id,
            'appointment_type' => 'scheduled',
            'appointment_date' => $tomorrow,
            'start_time' => '10:00',
            'end_time' => '10:30',
            'duration_minutes' => 30,
            'status' => Appointment::STATUS_CONFIRMED,
        ]);

        $component = Livewire::test(Booking::class, ['clinic' => $clinic])
            ->call('selectDoctor', $doctor->id)
            ->set('selectedDate', $tomorrow);

        $slots = $component->get('availableSlots');
        $this->assertNotContains('10:00', $slots);
        $this->assertContains('10:30', $slots);
    }

    public function test_submit_creates_patient_and_appointment(): void
    {
        [$clinic, $doctor] = $this->makeClinicWithDoctor();
        $tomorrow = now()->addDay()->toDateString();

        Livewire::test(Booking::class, ['clinic' => $clinic])
            ->call('selectDoctor', $doctor->id)
            ->set('selectedDate', $tomorrow)
            ->call('selectSlot', '09:00')
            ->call('nextStep')
            ->set('first_name', 'Ana')
            ->set('last_name', 'Pérez')
            ->set('phone', '+5215555555555')
            ->set('email', 'ana@example.com')
            ->set('reason', 'Consulta general')
            ->set('accept_terms', true)
            ->call('submitBooking')
            ->assertSet('step', 4)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('patients', [
            'clinic_id' => $clinic->id,
            'first_name' => 'Ana',
            'email' => 'ana@example.com',
        ]);
        $this->assertDatabaseHas('appointments', [
            'clinic_id' => $clinic->id,
            'doctor_id' => $doctor->id,
            'start_time' => '09:00',
            'status' => Appointment::STATUS_SCHEDULED,
        ]);
    }

    public function test_submit_auto_confirms_when_no_confirmation_required(): void
    {
        [$clinic, $doctor] = $this->makeClinicWithDoctor([
            'settings' => array_merge(Clinic::getDefaultSettings(), [
                'allow_online_booking' => true,
                'require_booking_confirmation' => false,
                'min_booking_notice' => 0,
                'max_booking_advance' => 30,
                'working_days' => [0, 1, 2, 3, 4, 5, 6],
                'working_hours_start' => '08:00',
                'working_hours_end' => '18:00',
                'appointment_duration' => 30,
            ]),
        ]);

        $tomorrow = now()->addDay()->toDateString();

        Livewire::test(Booking::class, ['clinic' => $clinic])
            ->call('selectDoctor', $doctor->id)
            ->set('selectedDate', $tomorrow)
            ->call('selectSlot', '11:00')
            ->call('nextStep')
            ->set('first_name', 'Bob')
            ->set('last_name', 'Smith')
            ->set('phone', '+15555555555')
            ->set('accept_terms', true)
            ->call('submitBooking')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('appointments', [
            'clinic_id' => $clinic->id,
            'status' => Appointment::STATUS_CONFIRMED,
        ]);
    }

    public function test_submit_reuses_existing_patient_by_email(): void
    {
        [$clinic, $doctor] = $this->makeClinicWithDoctor();
        $existing = Patient::factory()->create([
            'clinic_id' => $clinic->id,
            'email' => 'returning@example.com',
        ]);

        $tomorrow = now()->addDay()->toDateString();

        Livewire::test(Booking::class, ['clinic' => $clinic])
            ->call('selectDoctor', $doctor->id)
            ->set('selectedDate', $tomorrow)
            ->call('selectSlot', '09:00')
            ->call('nextStep')
            ->set('first_name', 'Returning')
            ->set('last_name', 'User')
            ->set('phone', '+5215999999999')
            ->set('email', 'returning@example.com')
            ->set('accept_terms', true)
            ->call('submitBooking')
            ->assertHasNoErrors();

        $this->assertEquals(1, Patient::where('clinic_id', $clinic->id)->count());
        $this->assertDatabaseHas('appointments', [
            'patient_id' => $existing->id,
        ]);
    }

    public function test_submit_validates_required_fields(): void
    {
        [$clinic, $doctor] = $this->makeClinicWithDoctor();
        $tomorrow = now()->addDay()->toDateString();

        Livewire::test(Booking::class, ['clinic' => $clinic])
            ->call('selectDoctor', $doctor->id)
            ->set('selectedDate', $tomorrow)
            ->call('selectSlot', '09:00')
            ->call('nextStep')
            ->call('submitBooking')
            ->assertHasErrors(['first_name', 'last_name', 'phone', 'accept_terms']);
    }

    public function test_honeypot_silently_blocks_submission(): void
    {
        [$clinic, $doctor] = $this->makeClinicWithDoctor();
        $tomorrow = now()->addDay()->toDateString();

        Livewire::test(Booking::class, ['clinic' => $clinic])
            ->call('selectDoctor', $doctor->id)
            ->set('selectedDate', $tomorrow)
            ->call('selectSlot', '09:00')
            ->call('nextStep')
            ->set('first_name', 'Bot')
            ->set('last_name', 'Spam')
            ->set('phone', '+15555555555')
            ->set('accept_terms', true)
            ->set('website', 'http://spammer.example')
            ->call('submitBooking');

        $this->assertEquals(0, Appointment::count());
    }

    public function test_cannot_book_when_clinic_at_appointment_limit(): void
    {
        // Free plan default limit (constants) = 5 appointments/month
        [$clinic, $doctor] = $this->makeClinicWithDoctor(['plan_type' => 'free']);

        // Fill the monthly quota (free = 5)
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        for ($i = 0; $i < 5; $i++) {
            Appointment::create([
                'clinic_id' => $clinic->id,
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'created_by' => $doctor->id,
                'appointment_type' => 'scheduled',
                'appointment_date' => now()->toDateString(),
                'start_time' => sprintf('%02d:00', 8 + $i),
                'end_time' => sprintf('%02d:30', 8 + $i),
                'duration_minutes' => 30,
                'status' => 'confirmed',
            ]);
        }

        $tomorrow = now()->addDay()->toDateString();

        Livewire::test(Booking::class, ['clinic' => $clinic])
            ->call('selectDoctor', $doctor->id)
            ->set('selectedDate', $tomorrow)
            ->call('selectSlot', '09:00')
            ->call('nextStep')
            ->set('first_name', 'Late')
            ->set('last_name', 'Booker')
            ->set('phone', '+15555555111')
            ->set('accept_terms', true)
            ->call('submitBooking')
            ->assertHasErrors(['submit']);
    }

    // ─── Landing pública enriquecida (F.3) ───────────────────────────────────

    public function test_public_page_shows_description_when_set(): void
    {
        [$clinic] = $this->makeClinicWithDoctor([
            'public_description' => 'Clínica especializada en medicina interna.',
        ]);

        $this->get('/c/'.$clinic->slug)
            ->assertOk()
            ->assertSee('Clínica especializada en medicina interna.', false);
    }

    public function test_public_page_does_not_show_about_section_when_no_description(): void
    {
        [$clinic] = $this->makeClinicWithDoctor(['public_description' => null]);

        $this->get('/c/'.$clinic->slug)
            ->assertOk()
            ->assertDontSee(__('booking.about_us'), false);
    }

    public function test_public_page_shows_services_when_set(): void
    {
        [$clinic] = $this->makeClinicWithDoctor([
            'public_services' => [
                ['title' => 'Cardiología', 'description' => 'Atención del corazón', 'icon' => ''],
            ],
        ]);

        $this->get('/c/'.$clinic->slug)
            ->assertOk()
            ->assertSee('Cardiología', false);
    }

    public function test_public_page_does_not_show_services_section_when_empty(): void
    {
        [$clinic] = $this->makeClinicWithDoctor(['public_services' => []]);

        $this->get('/c/'.$clinic->slug)
            ->assertOk()
            ->assertDontSee(__('booking.our_services'), false);
    }

    public function test_public_page_shows_doctor_team_when_enabled(): void
    {
        [$clinic, $doctor] = $this->makeClinicWithDoctor([
            'public_show_doctors' => true,
        ]);

        $this->get('/c/'.$clinic->slug)
            ->assertOk()
            ->assertSee(__('booking.our_team'), false);
    }

    public function test_public_page_hides_doctor_team_when_disabled(): void
    {
        [$clinic] = $this->makeClinicWithDoctor(['public_show_doctors' => false]);

        $this->get('/c/'.$clinic->slug)
            ->assertOk()
            ->assertDontSee(__('booking.our_team'), false);
    }

    public function test_public_page_uses_seo_title_in_meta(): void
    {
        [$clinic] = $this->makeClinicWithDoctor([
            'public_seo_title' => 'Clínica Norte | Reserva tu cita',
        ]);

        $this->get('/c/'.$clinic->slug)
            ->assertOk()
            ->assertSee('Clínica Norte | Reserva tu cita', false);
    }

    public function test_public_page_uses_seo_description_in_meta(): void
    {
        [$clinic] = $this->makeClinicWithDoctor([
            'public_seo_description' => 'Atención médica de calidad en el norte.',
        ]);

        $response = $this->get('/c/'.$clinic->slug)->assertOk();

        $this->assertStringContainsString(
            'Atención médica de calidad en el norte.',
            $response->getContent()
        );
    }

    public function test_public_page_shows_cover_image_when_set(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $path = 'clinics/test/public/cover.jpg';
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, 'fake');

        [$clinic] = $this->makeClinicWithDoctor(['public_cover_image_url' => $path]);

        $this->get('/c/'.$clinic->slug)
            ->assertOk()
            ->assertSee($path, false);
    }

    public function test_public_page_shows_book_appointment_cta_when_booking_enabled(): void
    {
        [$clinic] = $this->makeClinicWithDoctor();

        $this->get('/c/'.$clinic->slug)
            ->assertOk()
            ->assertSee(__('booking.book_appointment'), false);
    }
}
