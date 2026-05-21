{{--
    Tour Launcher — F.4
    Injects TOUR_CONFIG + TOUR_LABELS into the page so tour.js can pick them up.
    The component is rendered once per page (included in layouts/app.blade.php).
--}}
<div
    x-init="
        // Expose $wire methods to JS so tour.js can call them directly
        // without relying on browser-event → Alpine → Livewire chain.
        window.__tourLauncher = {
            complete: () => $wire.completeTour(),
            skip:     () => $wire.skipTour(),
            replay:   () => $wire.replayTour(),
        };
    "
>
    @php
        $locale = app()->getLocale();
    @endphp

    {{-- Inject tour configuration & labels once --}}
    <script>
        window.TOUR_CONFIG  = {
            autoStart: @js($autoStart),
            role:      @js($role),
            userId:    @js(auth()->id()),
        };
        window.TOUR_LABELS  = {
            next:  @js(__('tour.next')),
            prev:  @js(__('tour.prev')),
            done:  @js(__('tour.done')),
            skip:  @js(__('tour.skip')),

            dashboard_title:         @js(__('tour.dashboard_title')),
            dashboard_body:          @js(__('tour.dashboard_body')),

            patients_nav_title:      @js(__('tour.patients_nav_title')),
            patients_nav_body:       @js(__('tour.patients_nav_body')),

            appointments_nav_title:  @js(__('tour.appointments_nav_title')),
            appointments_nav_body:   @js(__('tour.appointments_nav_body')),

            calendar_nav_title:      @js(__('tour.calendar_nav_title')),
            calendar_nav_body:       @js(__('tour.calendar_nav_body')),

            records_nav_title:       @js(__('tour.records_nav_title')),
            records_nav_body:        @js(__('tour.records_nav_body')),

            invoices_nav_title:      @js(__('tour.invoices_nav_title')),
            invoices_nav_body:       @js(__('tour.invoices_nav_body')),

            reports_nav_title:       @js(__('tour.reports_nav_title')),
            reports_nav_body:        @js(__('tour.reports_nav_body')),

            settings_nav_title:      @js(__('tour.settings_nav_title')),
            settings_nav_body:       @js(__('tour.settings_nav_body')),
        };
    </script>
</div>
