import './bootstrap';

import { Calendar } from 'fullcalendar';
import esLocale from '@fullcalendar/core/locales/es';
import enLocale from '@fullcalendar/core/locales/en-gb';

// Expose for Alpine x-init usage in Blade views
window.FullCalendar = { Calendar, locales: { es: esLocale, en: enLocale } };

// Chart.js — exposed globally for reports page
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);
window.Chart = Chart;

// Re-apply dark mode after Livewire SPA navigation
document.addEventListener('livewire:navigated', () => {
    const theme = localStorage.getItem('theme') || 'light';
    document.documentElement.classList.toggle('dark', theme === 'dark');
});

// Interactive onboarding tour (Driver.js)
import './tour';
