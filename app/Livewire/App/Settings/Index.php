<?php

namespace App\Livewire\App\Settings;

use App\Models\Clinic;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class Index extends Component
{
    use WithFileUploads;

    public Clinic $clinic;

    public string $activeTab = 'general';

    // General Info
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $address = '';

    public string $city = '';

    public string $country = '';

    public ?string $website = '';

    public ?string $description = '';

    // Localization
    public string $locale = 'es';

    public string $timezone = 'America/Guatemala';

    public string $currency = 'USD';

    public string $date_format = 'd/m/Y';

    public string $time_format = '24h';

    public string $phone_country_code = '502';

    // Appointments
    public int $appointment_duration = 30;

    public int $appointment_buffer = 5;

    public array $working_days = [1, 2, 3, 4, 5];

    public string $working_hours_start = '08:00';

    public string $working_hours_end = '18:00';

    public bool $allow_online_booking = true;

    public bool $require_booking_confirmation = true;

    public int $min_booking_notice = 2;

    public int $max_booking_advance = 30;

    public int $cancellation_notice = 24;

    // Notifications
    public bool $send_reminders = true;

    public int $reminder_hours_before = 24;

    public bool $send_confirmations = true;

    // Billing
    public bool $billing_enabled = false;

    public ?string $tax_id = '';

    public ?string $legal_name = '';

    public ?string $billing_address = '';

    // Branding
    public $logo;

    public ?string $currentLogo = null;

    public string $primary_color = '#4f46e5';

    public string $secondary_color = '#10b981';

    protected function rules(): array
    {
        return [
            // General
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:2'],
            'website' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],

            // Localization
            'locale' => ['required', 'in:es,en'],
            'timezone' => ['required', 'string'],
            'currency' => ['required', 'string', 'size:3'],
            'date_format' => ['required', 'string'],
            'time_format' => ['required', 'in:12h,24h'],
            'phone_country_code' => ['nullable', 'string', 'max:5', 'regex:/^[0-9]{1,5}$/'],

            // Appointments
            'appointment_duration' => ['required', 'integer', 'min:5', 'max:180'],
            'appointment_buffer' => ['required', 'integer', 'min:0', 'max:60'],
            'working_days' => ['required', 'array', 'min:1'],
            'working_days.*' => ['integer', 'min:0', 'max:6'],
            'working_hours_start' => ['required', 'date_format:H:i'],
            'working_hours_end' => ['required', 'date_format:H:i', 'after:working_hours_start'],
            'allow_online_booking' => ['boolean'],
            'require_booking_confirmation' => ['boolean'],
            'min_booking_notice' => ['required', 'integer', 'min:0', 'max:168'],
            'max_booking_advance' => ['required', 'integer', 'min:1', 'max:365'],
            'cancellation_notice' => ['required', 'integer', 'min:0', 'max:168'],

            // Notifications
            'send_reminders' => ['boolean'],
            'reminder_hours_before' => ['required', 'integer', 'min:1', 'max:168'],
            'send_confirmations' => ['boolean'],

            // Billing
            'billing_enabled' => ['boolean'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'billing_address' => ['nullable', 'string', 'max:500'],

            // Branding
            'logo' => ['nullable', 'image', 'max:2048'],
            'primary_color' => ['required', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'secondary_color' => ['required', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'],
        ];
    }

    public function mount(Clinic $clinic): void
    {
        $this->clinic = $clinic;
        $this->loadClinicData();
    }

    protected function loadClinicData(): void
    {
        // General
        $this->name = $this->clinic->name;
        $this->email = $this->clinic->email;
        $this->phone = $this->clinic->phone ?? '';
        $this->address = $this->clinic->address ?? '';
        $this->city = $this->clinic->city ?? '';
        $this->country = $this->clinic->country ?? 'GT';

        $settings = $this->clinic->settings ?? [];
        $branding = $this->clinic->branding ?? [];

        $this->website = $settings['website'] ?? '';
        $this->description = $settings['description'] ?? '';

        // Localization
        $this->locale = $this->clinic->locale ?? 'es';
        $this->timezone = $this->clinic->timezone ?? 'America/Guatemala';
        $this->currency = $this->clinic->currency ?? 'USD';
        $this->date_format = $settings['date_format'] ?? 'd/m/Y';
        $this->time_format = $settings['time_format'] ?? '24h';
        $this->phone_country_code = $settings['phone_country_code'] ?? '502';

        // Appointments
        $this->appointment_duration = $settings['appointment_duration'] ?? 30;
        $this->appointment_buffer = $settings['appointment_buffer'] ?? 5;
        $this->working_days = $settings['working_days'] ?? [1, 2, 3, 4, 5];
        $this->working_hours_start = $settings['working_hours_start'] ?? '08:00';
        $this->working_hours_end = $settings['working_hours_end'] ?? '18:00';
        $this->allow_online_booking = $settings['allow_online_booking'] ?? true;
        $this->require_booking_confirmation = $settings['require_booking_confirmation'] ?? true;
        $this->min_booking_notice = $settings['min_booking_notice'] ?? 2;
        $this->max_booking_advance = $settings['max_booking_advance'] ?? 30;
        $this->cancellation_notice = $settings['cancellation_notice'] ?? 24;

        // Notifications
        $this->send_reminders = $settings['send_reminders'] ?? true;
        $this->reminder_hours_before = $settings['reminder_hours_before'] ?? 24;
        $this->send_confirmations = $settings['send_confirmations'] ?? true;

        // Billing
        $this->billing_enabled = (bool) ($settings['billing_enabled'] ?? false);
        $this->tax_id = $settings['tax_id'] ?? '';
        $this->legal_name = $settings['legal_name'] ?? '';
        $this->billing_address = $settings['billing_address'] ?? '';

        // Branding
        $this->currentLogo = $branding['logo'] ?? null;
        $this->primary_color = $branding['primary_color'] ?? '#4f46e5';
        $this->secondary_color = $branding['secondary_color'] ?? '#10b981';
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function saveGeneral(): void
    {
        $this->validate([
            'name' => $this->rules()['name'],
            'email' => $this->rules()['email'],
            'phone' => $this->rules()['phone'],
            'address' => $this->rules()['address'],
            'city' => $this->rules()['city'],
            'country' => $this->rules()['country'],
            'website' => $this->rules()['website'],
            'description' => $this->rules()['description'],
        ]);

        $this->clinic->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
            'address' => $this->address ?: null,
            'city' => $this->city ?: null,
            'country' => $this->country ?: null,
        ]);

        $this->updateSettings([
            'website' => $this->website ?: null,
            'description' => $this->description ?: null,
        ]);

        session()->flash('success', __('settings.general_saved'));
    }

    public function saveLocalization(): void
    {
        $this->validate([
            'locale' => $this->rules()['locale'],
            'timezone' => $this->rules()['timezone'],
            'currency' => $this->rules()['currency'],
            'date_format' => $this->rules()['date_format'],
            'time_format' => $this->rules()['time_format'],
            'phone_country_code' => $this->rules()['phone_country_code'],
        ]);

        $this->clinic->update([
            'locale' => $this->locale,
            'timezone' => $this->timezone,
            'currency' => $this->currency,
        ]);

        $this->updateSettings([
            'date_format' => $this->date_format,
            'time_format' => $this->time_format,
            'phone_country_code' => $this->phone_country_code ?: '502',
        ]);

        session()->flash('success', __('settings.localization_saved'));
    }

    public function saveAppointments(): void
    {
        $this->validate([
            'appointment_duration' => $this->rules()['appointment_duration'],
            'appointment_buffer' => $this->rules()['appointment_buffer'],
            'working_days' => $this->rules()['working_days'],
            'working_hours_start' => $this->rules()['working_hours_start'],
            'working_hours_end' => $this->rules()['working_hours_end'],
            'allow_online_booking' => $this->rules()['allow_online_booking'],
            'require_booking_confirmation' => $this->rules()['require_booking_confirmation'],
            'min_booking_notice' => $this->rules()['min_booking_notice'],
            'max_booking_advance' => $this->rules()['max_booking_advance'],
            'cancellation_notice' => $this->rules()['cancellation_notice'],
        ]);

        $this->updateSettings([
            'appointment_duration' => $this->appointment_duration,
            'appointment_buffer' => $this->appointment_buffer,
            'working_days' => $this->working_days,
            'working_hours_start' => $this->working_hours_start,
            'working_hours_end' => $this->working_hours_end,
            'allow_online_booking' => $this->allow_online_booking,
            'require_booking_confirmation' => $this->require_booking_confirmation,
            'min_booking_notice' => $this->min_booking_notice,
            'max_booking_advance' => $this->max_booking_advance,
            'cancellation_notice' => $this->cancellation_notice,
        ]);

        session()->flash('success', __('settings.appointments_saved'));
    }

    public function saveNotifications(): void
    {
        $this->validate([
            'send_reminders' => $this->rules()['send_reminders'],
            'reminder_hours_before' => $this->rules()['reminder_hours_before'],
            'send_confirmations' => $this->rules()['send_confirmations'],
        ]);

        $this->updateSettings([
            'send_reminders' => $this->send_reminders,
            'reminder_hours_before' => $this->reminder_hours_before,
            'send_confirmations' => $this->send_confirmations,
        ]);

        session()->flash('success', __('settings.notifications_saved'));
    }

    public function saveBilling(): void
    {
        $this->validate([
            'billing_enabled' => $this->rules()['billing_enabled'],
            'tax_id' => $this->rules()['tax_id'],
            'legal_name' => $this->rules()['legal_name'],
            'billing_address' => $this->rules()['billing_address'],
        ]);

        $this->updateSettings([
            'billing_enabled' => $this->billing_enabled,
            'tax_id' => $this->tax_id ?: null,
            'legal_name' => $this->legal_name ?: null,
            'billing_address' => $this->billing_address ?: null,
        ]);

        session()->flash('success', __('settings.billing_saved'));
    }

    public function saveBranding(): void
    {
        $this->validate([
            'logo' => $this->rules()['logo'],
            'primary_color' => $this->rules()['primary_color'],
            'secondary_color' => $this->rules()['secondary_color'],
        ]);

        $branding = $this->clinic->branding ?? [];

        // Handle logo upload
        if ($this->logo) {
            // Delete old logo if exists
            if ($this->currentLogo && Storage::disk('public')->exists($this->currentLogo)) {
                Storage::disk('public')->delete($this->currentLogo);
            }

            $path = $this->logo->store("clinics/{$this->clinic->id}/branding", 'public');
            $branding['logo'] = $path;
            $this->currentLogo = $path;
            $this->logo = null;
        }

        $branding['primary_color'] = $this->primary_color;
        $branding['secondary_color'] = $this->secondary_color;

        $this->clinic->update(['branding' => $branding]);

        session()->flash('success', __('settings.branding_saved'));
    }

    public function removeLogo(): void
    {
        if ($this->currentLogo && Storage::disk('public')->exists($this->currentLogo)) {
            Storage::disk('public')->delete($this->currentLogo);
        }

        $branding = $this->clinic->branding ?? [];
        unset($branding['logo']);

        $this->clinic->update(['branding' => $branding]);
        $this->currentLogo = null;

        session()->flash('success', __('settings.logo_removed'));
    }

    protected function updateSettings(array $newSettings): void
    {
        $settings = $this->clinic->settings ?? [];
        $settings = array_merge($settings, $newSettings);
        $this->clinic->update(['settings' => $settings]);
    }

    public function exportData(): StreamedResponse
    {
        abort_if(auth()->id() !== $this->clinic->owner_id, 403);

        $clinic = $this->clinic;
        $filename = 'datos-clinica-'.$clinic->slug.'-'.now()->format('Y-m-d').'.zip';
        $tmpPath = storage_path('app/tmp/'.$filename);

        if (! is_dir(storage_path('app/tmp'))) {
            mkdir(storage_path('app/tmp'), 0755, true);
        }

        $zip = new ZipArchive;
        $zip->open($tmpPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // --- Pacientes ---
        $patientsRows = [['ID', 'Nombre', 'Apellido', 'Correo', 'Teléfono', 'Fecha de nacimiento', 'Registrado']];
        $clinic->patients()->withTrashed()->get()
            ->each(function ($p) use (&$patientsRows) {
                $patientsRows[] = [
                    $p->id,
                    $p->first_name,
                    $p->last_name,
                    $p->email ?? '',
                    $p->phone ?? '',
                    $p->date_of_birth?->format('d/m/Y') ?? '',
                    $p->created_at->format('d/m/Y H:i'),
                ];
            });
        $zip->addFromString('pacientes.csv', $this->arrayToCsv($patientsRows));

        // --- Citas ---
        $apptRows = [['ID', 'Paciente', 'Doctor', 'Fecha', 'Hora inicio', 'Hora fin', 'Estado', 'Tipo', 'Notas']];
        $clinic->appointments()->withTrashed()->with(['patient', 'doctor'])->get()
            ->each(function ($a) use (&$apptRows) {
                $apptRows[] = [
                    $a->id,
                    $a->patient?->full_name ?? '',
                    $a->doctor?->name ?? '',
                    $a->appointment_date?->format('d/m/Y') ?? '',
                    $a->start_time ?? '',
                    $a->end_time ?? '',
                    $a->status,
                    $a->type ?? '',
                    $a->notes ?? '',
                ];
            });
        $zip->addFromString('citas.csv', $this->arrayToCsv($apptRows));

        // --- Historiales médicos ---
        $recordRows = [['ID', 'Paciente', 'Doctor', 'Tipo', 'Estado', 'Fecha', 'Confidencial']];
        $clinic->medicalRecords()->withTrashed()->with(['patient', 'doctor'])->get()
            ->each(function ($r) use (&$recordRows) {
                $recordRows[] = [
                    $r->id,
                    $r->patient?->full_name ?? '',
                    $r->doctor?->name ?? '',
                    $r->record_type,
                    $r->status,
                    $r->created_at->format('d/m/Y H:i'),
                    $r->is_confidential ? 'Sí' : 'No',
                ];
            });
        $zip->addFromString('historiales.csv', $this->arrayToCsv($recordRows));

        // --- Staff ---
        $staffRows = [['ID', 'Nombre', 'Correo', 'Rol', 'Estado', 'Registrado']];
        $clinic->users()->withTrashed()->get()
            ->each(function ($u) use (&$staffRows) {
                $staffRows[] = [
                    $u->id,
                    $u->name,
                    $u->email,
                    $u->getRoleNames()->first() ?? '',
                    $u->trashed() ? 'eliminado' : 'activo',
                    $u->created_at->format('d/m/Y H:i'),
                ];
            });
        $zip->addFromString('staff.csv', $this->arrayToCsv($staffRows));

        // --- README ---
        $readme = "ControClinic — Exportación de datos\n";
        $readme .= "Clínica: {$clinic->name}\n";
        $readme .= 'Generado: '.now()->format('d/m/Y H:i')." UTC\n\n";
        $readme .= "Archivos incluidos:\n";
        $readme .= "- pacientes.csv\n- citas.csv\n- historiales.csv\n- staff.csv\n";
        $zip->addFromString('README.txt', $readme);

        $zip->close();

        $content = file_get_contents($tmpPath);
        @unlink($tmpPath);

        activity()->causedBy(auth()->user())
            ->performedOn($clinic)
            ->withProperties(['files' => ['pacientes.csv', 'citas.csv', 'historiales.csv', 'staff.csv']])
            ->log('data_exported');

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, $filename, ['Content-Type' => 'application/zip']);
    }

    private function arrayToCsv(array $rows): string
    {
        $output = "\xEF\xBB\xBF"; // UTF-8 BOM for Excel
        $f = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($f, $row);
        }
        rewind($f);
        $output .= stream_get_contents($f);
        fclose($f);

        return $output;
    }

    public function render()
    {
        return view('livewire.app.settings.index')
            ->layout('layouts.app');
    }
}
