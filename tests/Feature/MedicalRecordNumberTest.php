<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicalRecordNumberTest extends TestCase
{
    use RefreshDatabase;

    public function test_next_number_skips_soft_deleted_patients(): void
    {
        $clinic = Clinic::factory()->onboarded()->create(['slug' => 'clinica-szarata']);

        $first = Patient::factory()->create(['clinic_id' => $clinic->id, 'medical_record_number' => null]);
        $first->update(['medical_record_number' => $first->generateMedicalRecordNumber()]);

        $second = Patient::factory()->create(['clinic_id' => $clinic->id, 'medical_record_number' => null]);
        $second->update(['medical_record_number' => $second->generateMedicalRecordNumber()]);
        $second->delete();

        $third = Patient::factory()->create(['clinic_id' => $clinic->id, 'medical_record_number' => null]);
        $thirdNumber = $third->generateMedicalRecordNumber();
        $third->update(['medical_record_number' => $thirdNumber]);

        $this->assertNotSame($first->fresh()->medical_record_number, $thirdNumber);
        $this->assertNotSame($second->medical_record_number, $thirdNumber);
        $this->assertSame(0, Patient::where('clinic_id', $clinic->id)->whereNull('medical_record_number')->count());
    }
}
