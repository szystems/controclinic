<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class MailTestCommand extends Command
{
    protected $signature = 'mail:test {email? : Recipient address (defaults to MAIL_FROM_ADDRESS)}';

    protected $description = 'Send a test email to verify outbound mail (Resend/SMTP) configuration';

    public function handle(): int
    {
        $recipient = $this->argument('email') ?: config('mail.from.address');

        if (! $recipient || ! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $this->error('Provide a valid email address or set MAIL_FROM_ADDRESS.');

            return self::FAILURE;
        }

        $driver = config('mail.default');

        if ($driver === 'log') {
            $this->warn('Mail driver is "log" — no real email will be sent. Configure MAIL_HOST or RESEND in Coolify.');
        }

        $this->info("Sending test email via [{$driver}] to {$recipient}...");

        try {
            Mail::raw(
                __('mail.test_body', ['time' => now()->toDateTimeString(), 'app' => config('app.name')]),
                function ($message) use ($recipient) {
                    $message->to($recipient)
                        ->subject(__('mail.test_subject', ['app' => config('app.name')]));
                }
            );
        } catch (\Throwable $e) {
            $this->error('Failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Test email queued/sent successfully.');

        return self::SUCCESS;
    }
}
