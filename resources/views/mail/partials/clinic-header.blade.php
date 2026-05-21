@php
    /** @var \App\Models\Clinic $clinic */
    $clinicBranding  = $clinic->branding ?? [];
    $clinicLogoPath  = $clinicBranding['logo'] ?? null;
    $clinicLogoUrl   = $clinicLogoPath
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($clinicLogoPath)
        : null;
    $primaryColor    = $clinicBranding['primary_color'] ?? '#4f46e5';

    // Initial letter fallback
    $initials = mb_strtoupper(mb_substr($clinic->name, 0, 2));
    $bgColor = '#f8f8fc';
@endphp
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td style="padding: 0;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td style="background-color: {{ $bgColor }}; border-top: 3px solid {{ $primaryColor }}; border-bottom: 1px solid #e4e4e7; padding: 14px 24px; text-align: center;">
            @if ($clinicLogoUrl)
                <img src="{{ $clinicLogoUrl }}"
                     alt="{{ $clinic->name }}"
                     style="display:block;margin:0 auto 8px auto;max-height:56px;width:auto;border-radius:4px;">
            @else
                {{-- Fallback: iniciales en círculo con color primario de la clínica --}}
                <span style="display:inline-block;width:48px;height:48px;border-radius:50%;background-color:{{ $primaryColor }};color:#ffffff;font-size:18px;font-weight:700;line-height:48px;text-align:center;margin-bottom:8px;font-family:-apple-system,sans-serif;">{{ $initials }}</span>
            @endif
            <span style="display:block;font-size:15px;font-weight:700;color:#18181b;line-height:1.3;">
                {{ $clinic->name }}
            </span>
            @if ($clinic->address || $clinic->phone || $clinic->email)
                <span style="display:block;font-size:12px;color:#71717a;margin-top:3px;line-height:1.5;">
                    @if ($clinic->address){{ $clinic->address }}@endif
                    @if ($clinic->address && ($clinic->phone || $clinic->email)) &nbsp;·&nbsp; @endif
                    @if ($clinic->phone){{ $clinic->phone }}@endif
                    @if ($clinic->phone && $clinic->email) &nbsp;·&nbsp; @endif
                    @if ($clinic->email){{ $clinic->email }}@endif
                </span>
            @endif
        </td>
    </tr>
    </table>
</td>
</tr>
</table>
