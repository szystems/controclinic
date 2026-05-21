<?php

namespace App\Livewire\Public;

use App\Jobs\SendAppointmentNotification;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\DoctorUnavailability;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Booking extends Component
{
    public Clinic $clinic;

    // Wizard state
    public int $step = 1;

    public int $maxStep = 3;

    // Step 1: doctor
    public ?string $doctor_id = null;

    // Step 2: date / time
    public ?string $selectedDate = null;

    public ?string $selectedTime = null;

    // Step 3: patient details
    public string $first_name = '';

    public string $last_name = '';

    public string $phone = '';

    public string $email = '';

    public string $reason = '';

    public bool $accept_terms = false;

    // Honeypot field — bots fill it, humans don't
    public string $website = '';

    // Result
    public ?string $appointmentId = null;

    public ?string $appointmentReference = null;

    public bool $portalDisabled = false;

    public function mount(Clinic $clinic): void
    {
        // Resolved by route model binding via slug or public_portal_slug
        if (! $clinic->public_portal_enabled) {
            abort(404);
        }

        $this->clinic = $clinic;

        // ADR-008: portal público sólo activo cuando la cuenta está full.
        // En read-only o billing-only, mostramos un mensaje en lugar del wizard.
        if (! $clinic->canWrite()) {
            $this->portalDisabled = true;
        }

        // Set locale from clinic if visitor has no session locale
        if (! session()->has('locale') && $clinic->locale) {
            app()->setLocale($clinic->locale);
        }
    }

    // ==================== COMPUTED ====================

    public function getDoctorsProperty()
    {
        return $this->clinic->doctors()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getOnlineBookingEnabledProperty(): bool
    {
        $settings = $this->clinic->settings ?? [];

        return ($settings['allow_online_booking'] ?? true) === true;
    }

    public function getRequiresConfirmationProperty(): bool
    {
        $settings = $this->clinic->settings ?? [];

        return ($settings['require_booking_confirmation'] ?? true) === true;
    }

    public function getWorkingHoursProperty(): array
    {
        $settings = $this->clinic->settings ?? [];

        return [
            'days' => $settings['working_days'] ?? [1, 2, 3, 4, 5],
            'start' => $settings['working_hours_start'] ?? '08:00',
            'end' => $settings['working_hours_end'] ?? '18:00',
            'duration' => (int) ($settings['appointment_duration'] ?? 30),
            'min_notice_hours' => (int) ($settings['min_booking_notice'] ?? 2),
            'max_advance_days' => (int) ($settings['max_booking_advance'] ?? 30),
        ];
    }

    public function getAvailableSlotsProperty(): array
    {
        if (! $this->doctor_id || ! $this->selectedDate) {
            return [];
        }

        try {
            $date = Carbon::parse($this->selectedDate);
        } catch (\Throwable $e) {
            return [];
        }

        $hours = $this->workingHours;
        $now = now($this->clinic->timezone ?? config('app.timezone'));

        // Day-of-week check
        if (! in_array($date->dayOfWeek, $hours['days'], true)) {
            return [];
        }

        // Min notice / max advance
        $minDate = $now->copy()->addHours($hours['min_notice_hours']);
        $maxDate = $now->copy()->addDays($hours['max_advance_days'])->endOfDay();

        if ($date->endOfDay()->lt($minDate->copy()->startOfDay()) || $date->startOfDay()->gt($maxDate)) {
            return [];
        }

        // Generate raw slots
        $start = Carbon::parse($date->toDateString().' '.$hours['start']);
        $end = Carbon::parse($date->toDateString().' '.$hours['end']);
        $duration = $hours['duration'];

        $slots = [];
        $cursor = $start->copy();
        while ($cursor->copy()->addMinutes($duration)->lte($end)) {
            $slots[] = $cursor->format('H:i');
            $cursor->addMinutes($duration);
        }

        // Drop slots earlier than min notice
        $minTime = $minDate;
        $slots = array_values(array_filter($slots, function ($time) use ($date, $minTime) {
            return Carbon::parse($date->toDateString().' '.$time)->gte($minTime);
        }));

        if (empty($slots)) {
            return [];
        }

        // Subtract slots conflicting with existing appointments
        $existing = Appointment::query()
            ->where('clinic_id', $this->clinic->id)
            ->where('doctor_id', $this->doctor_id)
            ->whereDate('appointment_date', $date->toDateString())
            ->whereNotIn('status', [
                Appointment::STATUS_CANCELLED,
                Appointment::STATUS_NO_SHOW,
            ])
            ->get(['start_time', 'end_time']);

        $available = [];
        foreach ($slots as $time) {
            $slotStart = Carbon::parse($date->toDateString().' '.$time);
            $slotEnd = $slotStart->copy()->addMinutes($duration);

            $conflict = $existing->contains(function ($appt) use ($slotStart, $slotEnd, $date) {
                $apptStart = Carbon::parse($date->toDateString().' '.$appt->start_time->format('H:i'));
                $apptEnd = $appt->end_time
                    ? Carbon::parse($date->toDateString().' '.$appt->end_time->format('H:i'))
                    : $apptStart->copy()->addMinutes(30);

                return $slotStart->lt($apptEnd) && $slotEnd->gt($apptStart);
            });

            if (! $conflict) {
                $available[] = $time;
            }
        }

        // Subtract slots blocked by doctor unavailabilities
        $unavailabilities = DoctorUnavailability::query()
            ->where('clinic_id', $this->clinic->id)
            ->where('doctor_id', $this->doctor_id)
            ->forDate($date->toDateString())
            ->get();

        if ($unavailabilities->isNotEmpty()) {
            $available = array_values(array_filter($available, function (string $time) use ($unavailabilities, $date, $duration) {
                $slotStart = Carbon::parse($date->toDateString().' '.$time);
                $slotEnd = $slotStart->copy()->addMinutes($duration);
                foreach ($unavailabilities as $block) {
                    if ($block->blocksSlot($date->toDateString(), $time, $slotEnd->format('H:i'))) {
                        return false;
                    }
                }

                return true;
            }));
        }

        return $available;
    }

    public function getMinBookableDateProperty(): string
    {
        return now($this->clinic->timezone ?? config('app.timezone'))->toDateString();
    }

    public function getMaxBookableDateProperty(): string
    {
        return now($this->clinic->timezone ?? config('app.timezone'))
            ->addDays($this->workingHours['max_advance_days'])
            ->toDateString();
    }

    // ==================== ACTIONS ====================

    public function selectDoctor(string $id): void
    {
        $valid = $this->doctors->pluck('id')->map(fn ($i) => (string) $i)->contains($id);
        if (! $valid) {
            return;
        }
        $this->doctor_id = $id;
        $this->selectedDate = null;
        $this->selectedTime = null;
        $this->step = 2;
    }

    public function selectSlot(string $time): void
    {
        if (! in_array($time, $this->availableSlots, true)) {
            return;
        }
        $this->selectedTime = $time;
    }

    public function goToStep(int $step): void
    {
        if ($step < 1 || $step > $this->maxStep) {
            return;
        }
        // Forward navigation requires fulfilled prior steps
        if ($step >= 2 && ! $this->doctor_id) {
            return;
        }
        if ($step >= 3 && (! $this->selectedDate || ! $this->selectedTime)) {
            return;
        }
        $this->step = $step;
    }

    public function nextStep(): void
    {
        if ($this->step === 1 && ! $this->doctor_id) {
            $this->addError('doctor_id', __('booking.validation.doctor_required'));

            return;
        }
        if ($this->step === 2) {
            if (! $this->selectedDate) {
                $this->addError('selectedDate', __('booking.validation.date_required'));

                return;
            }
            if (! $this->selectedTime) {
                $this->addError('selectedTime', __('booking.validation.time_required'));

                return;
            }
        }
        if ($this->step < $this->maxStep) {
            $this->step++;
        }
    }

    public function previousStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function submitBooking(): void
    {
        // Honeypot
        if (! empty($this->website)) {
            return;
        }

        // ADR-008: si la cuenta no está en estado 'full', no se aceptan reservas
        if (! $this->clinic->canWrite()) {
            abort(403);
        }

        // Rate limit per IP — 5 attempts per minute
        $key = 'public-booking:'.($this->clinic->id).':'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('submit', __('booking.error_too_many_requests'));

            return;
        }
        RateLimiter::hit($key, 60);

        if (! $this->onlineBookingEnabled) {
            abort(403);
        }

        $this->validate([
            'doctor_id' => ['required', 'exists:users,id'],
            'selectedDate' => ['required', 'date'],
            'selectedTime' => ['required', 'string'],
            'first_name' => ['required', 'string', 'min:2', 'max:80'],
            'last_name' => ['required', 'string', 'min:2', 'max:80'],
            'phone' => ['required', 'string', 'min:6', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'reason' => ['nullable', 'string', 'max:500'],
            'accept_terms' => ['accepted'],
        ], [
            'first_name.required' => __('booking.validation.first_name_required'),
            'last_name.required' => __('booking.validation.last_name_required'),
            'phone.required' => __('booking.validation.phone_required'),
            'email.email' => __('booking.validation.email_invalid'),
            'doctor_id.required' => __('booking.validation.doctor_required'),
            'selectedDate.required' => __('booking.validation.date_required'),
            'selectedTime.required' => __('booking.validation.time_required'),
            'accept_terms.accepted' => __('booking.validation.terms_required'),
        ]);

        // Verify doctor belongs to clinic
        $doctor = User::where('id', $this->doctor_id)
            ->where('clinic_id', $this->clinic->id)
            ->where('is_active', true)
            ->first();
        if (! $doctor) {
            $this->addError('doctor_id', __('booking.validation.doctor_required'));

            return;
        }

        // Capacity checks
        if (! $this->clinic->canAddAppointmentThisMonth()) {
            $this->addError('submit', __('booking.error_clinic_full'));

            return;
        }

        // Slot still available?
        if (! in_array($this->selectedTime, $this->availableSlots, true)) {
            $this->addError('submit', __('booking.error_slot_taken'));
            $this->selectedTime = null;
            $this->step = 2;

            return;
        }

        try {
            $appointment = DB::transaction(function () use ($doctor) {
                // Find or create patient by email or phone within this clinic
                $patient = null;
                if ($this->email) {
                    $patient = Patient::where('clinic_id', $this->clinic->id)
                        ->where('email', $this->email)
                        ->first();
                }
                if (! $patient) {
                    $patient = Patient::where('clinic_id', $this->clinic->id)
                        ->where('phone', $this->phone)
                        ->first();
                }
                if (! $patient) {
                    if (! $this->clinic->canAddPatient()) {
                        throw ValidationException::withMessages([
                            'submit' => __('booking.error_clinic_full'),
                        ]);
                    }
                    $patient = new Patient([
                        'clinic_id' => $this->clinic->id,
                        'primary_doctor_id' => $doctor->id,
                        'first_name' => $this->first_name,
                        'last_name' => $this->last_name,
                        'phone' => $this->phone,
                        'email' => $this->email ?: null,
                        'country' => $this->clinic->country,
                        'is_active' => true,
                    ]);
                    $patient->medical_record_number = $patient->generateMedicalRecordNumber();
                    $patient->save();
                }

                $duration = $this->workingHours['duration'];
                $endTime = Carbon::parse($this->selectedTime)->addMinutes($duration)->format('H:i:s');

                $status = $this->requiresConfirmation
                    ? Appointment::STATUS_SCHEDULED
                    : Appointment::STATUS_CONFIRMED;

                return Appointment::create([
                    'clinic_id' => $this->clinic->id,
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'created_by' => null,
                    'appointment_type' => Appointment::TYPE_SCHEDULED,
                    'appointment_date' => $this->selectedDate,
                    'start_time' => $this->selectedTime,
                    'end_time' => $endTime,
                    'duration_minutes' => $duration,
                    'status' => $status,
                    'created_via' => 'public',
                    'reason' => $this->reason ?: null,
                    'notes' => '[Public booking] '.($this->email ?: $this->phone),
                ]);
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->addError('submit', __('booking.error_generic'));

            return;
        }

        $this->appointmentId = $appointment->id;
        $this->appointmentReference = strtoupper(substr($appointment->id, 0, 8));

        // Dispatch email notifications (queued)
        SendAppointmentNotification::dispatch(
            $appointment->id,
            SendAppointmentNotification::TYPE_BOOKED,
        );

        $this->step = 4; // confirmation screen
    }

    public function startOver(): void
    {
        $this->reset([
            'step', 'doctor_id', 'selectedDate', 'selectedTime',
            'first_name', 'last_name', 'phone', 'email', 'reason',
            'accept_terms', 'appointmentId', 'appointmentReference', 'website',
        ]);
        $this->step = 1;
        $this->resetErrorBag();
    }

    public function render()
    {
        $clinic = $this->clinic;

        return view('livewire.public.booking')
            ->layout('components.layouts.public-clinic', [
                'clinic' => $clinic,
                'title' => $clinic->public_seo_title
                    ?: __('booking.page_title', ['clinic' => $clinic->name]),
                'description' => $clinic->public_seo_description
                    ?: ($clinic->public_description
                        ? mb_substr(strip_tags($clinic->public_description), 0, 155)
                        : __('booking.page_title', ['clinic' => $clinic->name])),
            ]);
    }
}
