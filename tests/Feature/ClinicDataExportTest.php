<?php

namespace Tests\Feature;

use App\Livewire\App\Settings\Index as SettingsIndex;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use App\Support\Csv;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;
use ZipArchive;

class ClinicDataExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function makeClinicWithOwner(): array
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $owner = User::factory()->create([
            'clinic_id' => $clinic->id,
        ]);
        $owner->assignRole('owner');
        $clinic->update(['owner_id' => $owner->id]);
        $clinic->refresh();

        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        return [$clinic, $owner];
    }

    /** @test */
    #[Test]
    public function owner_can_export_clinic_data_as_zip(): void
    {
        [$clinic, $owner] = $this->makeClinicWithOwner();

        // Create a patient so the CSV has at least one row
        Patient::factory()->create(['clinic_id' => $clinic->id]);

        $response = Livewire::actingAs($owner)
            ->test(SettingsIndex::class, ['clinic' => $clinic])
            ->call('exportData');

        // Livewire returns a StreamedResponse — check it dispatched without error
        $response->assertHasNoErrors();
    }

    /** @test */
    #[Test]
    public function non_owner_cannot_export_clinic_data(): void
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $doctor = User::factory()->create(['clinic_id' => $clinic->id]);
        $doctor->assignRole('doctor');

        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        Livewire::actingAs($doctor)
            ->test(SettingsIndex::class, ['clinic' => $clinic])
            ->call('exportData')
            ->assertForbidden();
    }

    /** @test */
    #[Test]
    public function export_includes_birth_date_and_neutralizes_formulas(): void
    {
        $this->assertSame("'=1", Csv::safe('=1'));
        $this->assertSame("'+1", Csv::safe('+1'));
        $this->assertSame("'-1", Csv::safe('-1'));
        $this->assertSame("'@1", Csv::safe('@1'));
        $this->assertSame("'\t1", Csv::safe("\t1"));
        $this->assertSame("'\r1", Csv::safe("\r1"));
        $this->assertSame('Ana', Csv::safe('Ana'));
        $this->assertSame(12, Csv::safe(12));

        [$clinic, $owner] = $this->makeClinicWithOwner();

        Patient::factory()->create([
            'clinic_id' => $clinic->id,
            'first_name' => '=cmd',
            'last_name' => 'Lopez',
            'birth_date' => '1990-05-15',
        ]);

        $component = Livewire::actingAs($owner)
            ->test(SettingsIndex::class, ['clinic' => $clinic])
            ->call('exportData');

        $download = data_get($component->effects, 'download');
        $this->assertNotNull($download);

        $path = tempnam(sys_get_temp_dir(), 'cc-export');
        file_put_contents($path, base64_decode($download['content']));

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path));
        $patients = $zip->getFromName('pacientes.csv');
        $staff = $zip->getFromName('staff.csv');
        $zip->close();
        @unlink($path);

        $this->assertIsString($patients);
        $this->assertStringContainsString('15/05/1990', $patients);
        $this->assertStringContainsString("'=cmd", $patients);
        $this->assertStringContainsString(__('patients.birth_date'), $patients);
        $this->assertStringContainsString('owner', $staff);
    }
}
