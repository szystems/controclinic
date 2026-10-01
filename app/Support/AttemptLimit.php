<?php

namespace App\Support;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AttemptLimit
{
    /**
     * @param  list<array{key: string, max: int, decay: int}>  $limits
     */
    public static function allow(array $limits): ?string
    {
        foreach ($limits as $limit) {
            if (! RateLimiter::tooManyAttempts($limit['key'], $limit['max'])) {
                continue;
            }

            $seconds = RateLimiter::availableIn($limit['key']);

            return __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => (int) ceil($seconds / 60),
            ]);
        }

        foreach ($limits as $limit) {
            RateLimiter::hit($limit['key'], $limit['decay']);
        }

        return null;
    }

    /**
     * @param  list<array{key: string, max: int, decay: int}>  $limits
     */
    public static function hit(array $limits, string $field): void
    {
        $message = self::allow($limits);

        if ($message === null) {
            return;
        }

        throw ValidationException::withMessages([
            $field => $message,
        ]);
    }

    public static function appointmentReminder(string $appointmentId): ?string
    {
        return self::allow([[
            'key' => 'appointment-reminder:'.$appointmentId,
            'max' => 1,
            'decay' => 600,
        ]]);
    }
}
