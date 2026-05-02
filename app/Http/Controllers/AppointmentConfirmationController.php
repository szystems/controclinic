<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentConfirmationController extends Controller
{
    public function confirm(string $token)
    {
        $appointment = Appointment::where('confirmation_token', $token)->first();

        if (! $appointment) {
            return view('appointment.invalid-token');
        }

        if ($appointment->status === Appointment::STATUS_CANCELLED) {
            return view('appointment.already-cancelled', compact('appointment'));
        }

        if ($appointment->status !== Appointment::STATUS_CONFIRMED) {
            $appointment->update([
                'status' => Appointment::STATUS_CONFIRMED,
                'confirmed_via' => 'link',
            ]);
        }

        return view('appointment.confirmed', compact('appointment'));
    }

    public function cancel(Request $request, string $token)
    {
        $appointment = Appointment::where('confirmation_token', $token)->first();

        if (! $appointment) {
            return view('appointment.invalid-token');
        }

        if ($appointment->status === Appointment::STATUS_CANCELLED) {
            return view('appointment.already-cancelled', compact('appointment'));
        }

        if (in_array($appointment->status, [Appointment::STATUS_SCHEDULED, Appointment::STATUS_CONFIRMED])) {
            $appointment->update([
                'status' => Appointment::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'cancellation_reason' => __('appointments_mail.cancel_via_link'),
            ]);
        }

        return view('appointment.cancelled', compact('appointment'));
    }
}
