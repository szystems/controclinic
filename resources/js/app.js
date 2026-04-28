import './bootstrap';

// Re-apply dark mode after Livewire SPA navigation
document.addEventListener('livewire:navigated', () => {
    const theme = localStorage.getItem('theme') || 'light';
    document.documentElement.classList.toggle('dark', theme === 'dark');
});
