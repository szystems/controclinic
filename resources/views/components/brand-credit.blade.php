@props([
    'app' => null,
    'linkClass' => 'underline hover:opacity-80 transition-opacity',
    'variant' => 'product',
])

<span {{ $attributes }}>
    @if ($variant === 'developed')
        {{ __('public.developed_by') }}
    @else
        {{ $app ?? app_setting('branding.app_name', config('app.name', 'ControClinic')) }} · {{ __('public.product_of') }}
    @endif
    <a href="https://szystems.com" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 align-middle">
        <img src="{{ asset('images/szystems-mark.png') }}" alt="" width="16" height="16" class="h-4 w-4 shrink-0" style="height:16px;width:16px;vertical-align:middle;border:0;">
        <span class="{{ $linkClass }}">Szystems</span>
    </a>
    @if ($variant !== 'developed')
        · Victoria, BC
    @endif
</span>
