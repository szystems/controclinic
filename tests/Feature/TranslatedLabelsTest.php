<?php

namespace Tests\Feature;

use App\Models\MedicalRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslatedLabelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_and_role_labels_use_translation_keys(): void
    {
        app()->setLocale('en');

        $record = new MedicalRecord([
            'record_type' => MedicalRecord::TYPE_CONSULTATION,
            'status' => MedicalRecord::STATUS_DRAFT,
            'vital_signs' => ['temperature' => '37'],
        ]);

        $this->assertSame('Consultation', $record->type_label);
        $this->assertSame('Draft', $record->status_label);
        $this->assertSame('Temperature', $record->getFormattedVitalSigns()['temperature']['label']);

        $user = new User(['role' => User::ROLE_OWNER, 'name' => 'Ada']);
        $this->assertSame('Owner', $user->role_label);

        app()->setLocale('es');
        $this->assertSame('Mejorar plan', __('general.upgrade'));
        $this->assertSame('General', __('settings.tab_general'));
    }
}
