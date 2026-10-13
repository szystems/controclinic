<?php

namespace App\Support;

use App\Models\Clinic;
use Carbon\Carbon;
use Illuminate\Support\Facades\Lang;
use Spatie\Activitylog\Models\Activity;

class ProfileActivityPresenter
{
    /** @var list<string> */
    private const MONEY = [
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total',
        'paid_amount',
    ];

    /** @var list<string> */
    private const DATES = [
        'issued_at',
        'due_at',
        'appointment_date',
        'valid_until',
    ];

    public function __construct(private Clinic $clinic) {}

    /**
     * @param  iterable<int, Activity>  $activities
     * @return list<array{when: string, summary: string, changes: list<array{label: string, value?: string, from?: string, to?: string}>}>
     */
    public function present(iterable $activities): array
    {
        $rows = [];

        foreach ($activities as $activity) {
            $rows[] = [
                'when' => $this->clinic->formatDate($activity->created_at, true),
                'summary' => $this->summary($activity),
                'changes' => $this->changes($activity),
            ];
        }

        return $rows;
    }

    private function summary(Activity $activity): string
    {
        $description = (string) $activity->description;
        $event = Lang::has('audit_log.events.'.$description)
            ? __('audit_log.events.'.$description)
            : $description;

        $subject = $activity->subject_type ? class_basename($activity->subject_type) : null;
        $subjectLabel = $subject && Lang::has('audit_log.subjects.'.$subject)
            ? __('audit_log.subjects.'.$subject)
            : null;

        if ($subjectLabel) {
            return __('profile.activity_summary', [
                'event' => $event,
                'subject' => $subjectLabel,
            ]);
        }

        return $event;
    }

    /**
     * @return list<array{label: string, value?: string, from?: string, to?: string}>
     */
    private function changes(Activity $activity): array
    {
        $properties = $activity->properties?->toArray() ?? [];
        $attributes = is_array($properties['attributes'] ?? null) ? $properties['attributes'] : [];
        $old = is_array($properties['old'] ?? null) ? $properties['old'] : [];
        $rows = [];

        foreach ($attributes as $key => $value) {
            if (! is_string($key) || ! Lang::has('profile.fields.'.$key)) {
                continue;
            }

            $current = $this->displayValue($key, $value);
            if ($current === null) {
                continue;
            }

            $label = __('profile.fields.'.$key);

            if (array_key_exists($key, $old)) {
                $previous = $this->displayValue($key, $old[$key]);
                if ($previous !== null && $previous !== $current) {
                    $rows[] = ['label' => $label, 'from' => $previous, 'to' => $current];

                    continue;
                }
            }

            $rows[] = ['label' => $label, 'value' => $current];
        }

        return $rows;
    }

    private function displayValue(string $key, mixed $value): ?string
    {
        if ($value === null || $value === '' || is_array($value)) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? __('general.yes') : __('general.no');
        }

        $string = trim((string) $value);
        if ($string === '') {
            return null;
        }

        if (in_array($key, self::MONEY, true) && is_numeric($string)) {
            return number_format((float) $string, 2);
        }

        if (in_array($key, self::DATES, true) && preg_match('/^(\d{4}-\d{2}-\d{2})/', $string, $match)) {
            return Carbon::parse($match[1])->format($this->clinic->dateFormat());
        }

        if ($key === 'status') {
            return $this->translated(['invoices.status_', 'appointments.status_', 'prescriptions.status_'], $string);
        }

        if ($key === 'role') {
            return $this->translated(['staff.role_'], $string);
        }

        if ($key === 'is_active') {
            return in_array($string, ['1', 'true'], true) ? __('general.yes') : __('general.no');
        }

        return $string;
    }

    /**
     * @param  list<string>  $prefixes
     */
    private function translated(array $prefixes, string $value): string
    {
        foreach ($prefixes as $prefix) {
            if (Lang::has($prefix.$value)) {
                return __($prefix.$value);
            }
        }

        return $value;
    }
}
