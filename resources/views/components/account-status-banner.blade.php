@php
    $clinic = $clinic ?? (app()->bound('current_clinic') ? app('current_clinic') : null);
    if (! $clinic) return;
    $level = $clinic->accessLevel();
    if ($level === \App\Models\Clinic::ACCESS_FULL) return;

    $isReadOnly = $level === \App\Models\Clinic::ACCESS_READ_ONLY;
    $isBillingOnly = $level === \App\Models\Clinic::ACCESS_BILLING_ONLY;
@endphp

<div class="bg-amber-50 border-b border-amber-200 dark:bg-amber-900/30 dark:border-amber-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.05 19h13.9a2 2 0 001.74-3l-6.95-12a2 2 0 00-3.48 0l-6.95 12a2 2 0 001.74 3z"/>
                </svg>
                <div class="text-sm text-amber-800 dark:text-amber-200">
                    @if ($isBillingOnly)
                        <strong>{{ __('billing.banner_billing_only_title') }}</strong>
                        <span>{{ __('billing.banner_billing_only_body') }}</span>
                    @elseif ($isReadOnly)
                        <strong>{{ __('billing.banner_read_only_title') }}</strong>
                        @if ($clinic->status === 'trial' && $clinic->trial_ends_at?->isPast())
                            <span>{{ __('billing.banner_trial_expired_body', ['date' => $clinic->trial_ends_at->isoFormat('LL')]) }}</span>
                        @else
                            <span>{{ __('billing.banner_subscription_expired_body') }}</span>
                        @endif
                    @endif
                </div>
            </div>
            <a href="{{ route('app.billing.index', $clinic->slug) }}"
               class="inline-flex items-center justify-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors flex-shrink-0">
                {{ __('billing.banner_view_plans') }}
            </a>
        </div>
    </div>
</div>
