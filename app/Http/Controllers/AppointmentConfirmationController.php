<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\View\View;

class AppointmentConfirmationController extends Controller
{
    public function confirm(string $token): View
    {
        $appointment = $this->findByToken($token);

        if (! $appointment) {
            return view('appointment.invalid-token');
        }

        if ($appointment->status === Appointment::STATUS_CANCELLED) {
            return view('appointment.already-cancelled', compact('appointment'));
        }

        if ($appointment->status === Appointment::STATUS_CONFIRMED) {
            return view('appointment.confirmed', compact('appointment'));
        }

        if (! $this->canConfirmFromLink($appointment)) {
            return $this->unavailable($appointment, $this->confirmRefusalMessage($appointment));
        }

        return view('appointment.confirm', compact('appointment'));
    }

    public function confirmStore(string $token): View
    {
        $appointment = $this->findByToken($token);

        if (! $appointment) {
            return view('appointment.invalid-token');
        }

        if ($appointment->status === Appointment::STATUS_CANCELLED) {
            return view('appointment.already-cancelled', compact('appointment'));
        }

        if ($appointment->status === Appointment::STATUS_CONFIRMED) {
            return view('appointment.confirmed', compact('appointment'));
        }

        if (! $this->canConfirmFromLink($appointment)) {
            return $this->unavailable($appointment, $this->confirmRefusalMessage($appointment));
        }

        $appointment->update([
            'status' => Appointment::STATUS_CONFIRMED,
            'confirmed_via' => 'link',
        ]);

        return view('appointment.confirmed', compact('appointment'));
    }

    public function cancel(string $token): View
    {
        $appointment = $this->findByToken($token);

        if (! $appointment) {
            return view('appointment.invalid-token');
        }

        if ($appointment->status === Appointment::STATUS_CANCELLED) {
            return view('appointment.already-cancelled', compact('appointment'));
        }

        if (! $this->canCancelFromLink($appointment)) {
            return $this->unavailable($appointment, $this->cancelRefusalMessage($appointment));
        }

        return view('appointment.cancel', compact('appointment'));
    }

    public function cancelStore(string $token): View
    {
        $appointment = $this->findByToken($token);

        if (! $appointment) {
            return view('appointment.invalid-token');
        }

        if ($appointment->status === Appointment::STATUS_CANCELLED) {
            return view('appointment.already-cancelled', compact('appointment'));
        }

        if (! $this->canCancelFromLink($appointment)) {
            return $this->unavailable($appointment, $this->cancelRefusalMessage($appointment));
        }

        $appointment->update([
            'status' => Appointment::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => __('appointments_mail.cancel_via_link'),
        ]);

        return view('appointment.cancelled', compact('appointment'));
    }

    private function findByToken(string $token): ?Appointment
    {
        return Appointment::where('confirmation_token', $token)->first();
    }

    private function canConfirmFromLink(Appointment $appointment): bool
    {
        return $appointment->status === Appointment::STATUS_SCHEDULED
            && $this->isFuture($appointment);
    }

    private function canCancelFromLink(Appointment $appointment): bool
    {
        return in_array($appointment->status, [
            Appointment::STATUS_SCHEDULED,
            Appointment::STATUS_CONFIRMED,
        ], true)
            && $this->isFuture($appointment)
            && ! $this->blockedByCancellationNotice($appointment);
    }

    private function isFuture(Appointment $appointment): bool
    {
        $startsAt = $this->startsAt($appointment);

        if (! $startsAt) {
            return false;
        }

        $now = $appointment->clinic?->localNow() ?? now();

        return $startsAt->gt($now);
    }

    private function blockedByCancellationNotice(Appointment $appointment): bool
    {
        $hours = $this->cancellationNoticeHours($appointment);
        $startsAt = $this->startsAt($appointment);

        if ($hours === null || ! $startsAt) {
            return false;
        }

        $now = $appointment->clinic?->localNow() ?? now();

        return $startsAt->lte($now->copy()->addHours($hours));
    }

    private function cancellationNoticeHours(Appointment $appointment): ?int
    {
        $notice = data_get($appointment->clinic?->settings, 'cancellation_notice');

        if ($notice === null || $notice === '') {
            return null;
        }

        return max(0, (int) $notice);
    }

    private function startsAt(Appointment $appointment): ?Carbon
    {
        if (! $appointment->appointment_date || ! $appointment->start_time) {
            return null;
        }

        $timezone = $appointment->clinic?->timezone ?: config('app.timezone');
        $time = $appointment->start_time instanceof Carbon
            ? $appointment->start_time->format('H:i:s')
            : Carbon::parse($appointment->start_time)->format('H:i:s');

        return Carbon::parse(
            $appointment->appointment_date->format('Y-m-d').' '.$time,
            $timezone,
        );
    }

    private function confirmRefusalMessage(Appointment $appointment): string
    {
        if ($appointment->status === Appointment::STATUS_SCHEDULED && ! $this->isFuture($appointment)) {
            return __('appointments.link_past_message');
        }

        return __('appointments.link_unavailable_message');
    }

    private function cancelRefusalMessage(Appointment $appointment): string
    {
        if (! in_array($appointment->status, [
            Appointment::STATUS_SCHEDULED,
            Appointment::STATUS_CONFIRMED,
        ], true)) {
            return __('appointments.link_unavailable_message');
        }

        if (! $this->isFuture($appointment)) {
            return __('appointments.link_past_message');
        }

        $hours = $this->cancellationNoticeHours($appointment);

        if ($hours !== null) {
            return __('appointments.link_notice_message', ['hours' => $hours]);
        }

        return __('appointments.link_unavailable_message');
    }

    private function unavailable(Appointment $appointment, string $message): View
    {
        return view('appointment.unavailable', compact('appointment', 'message'));
    }
}
