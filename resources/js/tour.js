import { driver } from 'driver.js';
import 'driver.js/dist/driver.css';

/**
 * ControClinic Interactive Tour — F.4
 * Builds role-aware steps and launches Driver.js.
 *
 * Steps that target elements which might not exist on the current page
 * are filtered out automatically (element check before launch).
 */

/**
 * All possible tour steps keyed by step ID.
 * Translations are passed in from Blade via window.TOUR_LABELS.
 */
function buildSteps(labels) {
    return {
        dashboard: {
            element: '[data-tour="nav-dashboard"]',
            popover: {
                title: labels.dashboard_title,
                description: labels.dashboard_body,
                side: 'bottom',
                align: 'start',
            },
        },
        patients: {
            element: '[data-tour="nav-patients"]',
            popover: {
                title: labels.patients_nav_title,
                description: labels.patients_nav_body,
                side: 'bottom',
                align: 'start',
            },
        },
        appointments: {
            element: '[data-tour="nav-appointments"]',
            popover: {
                title: labels.appointments_nav_title,
                description: labels.appointments_nav_body,
                side: 'bottom',
                align: 'start',
            },
        },
        calendar: {
            element: '[data-tour="nav-calendar"]',
            popover: {
                title: labels.calendar_nav_title,
                description: labels.calendar_nav_body,
                side: 'bottom',
                align: 'start',
            },
        },
        records: {
            element: '[data-tour="nav-records"]',
            popover: {
                title: labels.records_nav_title,
                description: labels.records_nav_body,
                side: 'bottom',
                align: 'start',
            },
        },
        invoices: {
            element: '[data-tour="nav-invoices"]',
            popover: {
                title: labels.invoices_nav_title,
                description: labels.invoices_nav_body,
                side: 'bottom',
                align: 'start',
            },
        },
        reports: {
            element: '[data-tour="nav-reports"]',
            popover: {
                title: labels.reports_nav_title,
                description: labels.reports_nav_body,
                side: 'bottom',
                align: 'start',
            },
        },
        settings: {
            element: '[data-tour="nav-settings"]',
            popover: {
                title: labels.settings_nav_title,
                description: labels.settings_nav_body,
                side: 'bottom',
                align: 'start',
            },
        },
    };
}

/** Step IDs per role. Unknown roles get the minimal set. */
const ROLE_STEPS = {
    owner:        ['dashboard', 'patients', 'appointments', 'calendar', 'records', 'invoices', 'reports', 'settings'],
    doctor:       ['dashboard', 'patients', 'appointments', 'calendar', 'records', 'invoices', 'reports', 'settings'],
    assistant:    ['dashboard', 'patients', 'appointments', 'calendar', 'records'],
    secretary:    ['dashboard', 'patients', 'appointments', 'calendar'],
    receptionist: ['dashboard', 'appointments', 'calendar', 'patients'],
    admin:        ['dashboard', 'patients', 'appointments', 'calendar', 'records', 'invoices', 'reports', 'settings'],
};

/**
 * Launch the tour for the given role.
 * @param {string} role  — user's primary role name
 * @param {object} labels — translated strings from window.TOUR_LABELS
 * @param {object} callbacks — { onComplete, onSkip }
 */
export function launchTour(role, labels, callbacks = {}) {
    const allSteps = buildSteps(labels);
    const stepIds  = ROLE_STEPS[role] ?? ROLE_STEPS.receptionist;

    // Only include steps whose target element is actually in the DOM
    const steps = stepIds
        .map(id => allSteps[id])
        .filter(step => step && document.querySelector(step.element));

    if (steps.length === 0) return;

    // Track whether the user reached the last step
    let finishedAllSteps = false;

    const driverObj = driver({
        showProgress:         true,
        animate:              true,
        overlayOpacity:       0.35,
        stagePadding:         6,
        allowClose:           true,
        disableActiveInteraction: false,
        nextBtnText:          labels.next,
        prevBtnText:          labels.prev,
        doneBtnText:          labels.done,

        popoverClass:        'cc-tour-popover',

        steps,

        onDestroyStarted: () => {
            // getActiveIndex() is 0-based; compare against last step index
            const idx = driverObj.getActiveIndex?.() ?? -1;
            finishedAllSteps = idx === steps.length - 1;
            driverObj.destroy();
            if (finishedAllSteps) {
                callbacks.onComplete?.();
            } else {
                callbacks.onSkip?.();
            }
        },
    });

    driverObj.drive();
}

/** localStorage key scoped per user to survive full page reloads */
function lsKey() {
    return `cc_tour_dismissed_${window.TOUR_CONFIG?.userId ?? 'anon'}`;
}

/**
 * Persiste que el tour fue completado/saltado:
 *  1. Inmediato en memoria (evita re-trigger en SPA navigation)
 *  2. Inmediato en localStorage (evita re-trigger tras recarga)
 *  3. Asíncrono en DB vía $wire (persiste entre dispositivos/navegadores)
 */
function onTourDone(isDone) {
    if (window.TOUR_CONFIG) window.TOUR_CONFIG.autoStart = false;
    try { localStorage.setItem(lsKey(), '1'); } catch (_) {}
    if (isDone) {
        window.__tourLauncher?.complete();
    } else {
        window.__tourLauncher?.skip();
    }
}

/**
 * Auto-start hook: called on DOMContentLoaded and after Livewire navigation.
 */
function maybeAutoStart() {
    const cfg = window.TOUR_CONFIG;
    if (!cfg?.autoStart) return;

    // Check localStorage fallback — catches reloads before DB response arrives
    try { if (localStorage.getItem(lsKey())) return; } catch (_) {}

    // Avoid double-start if driver is already active
    if (document.querySelector('.driver-overlay')) return;

    launchTour(cfg.role, window.TOUR_LABELS ?? {}, {
        onComplete: () => onTourDone(true),
        onSkip:     () => onTourDone(false),
    });
}

document.addEventListener('DOMContentLoaded', maybeAutoStart);
document.addEventListener('livewire:navigated', maybeAutoStart);

// Manual re-start: "Repetir tour" button dispatches 'replay-tour' browser event
document.addEventListener('replay-tour', handleReplay);

function handleReplay() {
    const cfg = window.TOUR_CONFIG;
    if (!cfg) return;

    // Clear both guards so the tour can start again
    if (window.TOUR_CONFIG) window.TOUR_CONFIG.autoStart = true;
    try { localStorage.removeItem(lsKey()); } catch (_) {}

    // Save cleared state to DB
    window.__tourLauncher?.replay();

    launchTour(cfg.role, window.TOUR_LABELS ?? {}, {
        onComplete: () => onTourDone(true),
        onSkip:     () => onTourDone(false),
    });
}
