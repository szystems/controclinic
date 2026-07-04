<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MailTestCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_mail_test_command_sends_email(): void
    {
        Mail::fake();

        $this->artisan('mail:test', ['email' => 'test@example.com'])
            ->assertSuccessful();

        Mail::assertSentCount(1);
    }

    public function test_mail_test_command_fails_without_valid_email(): void
    {
        config(['mail.from.address' => '']);

        $this->artisan('mail:test')
            ->assertFailed();
    }
}
