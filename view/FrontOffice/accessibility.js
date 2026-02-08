// accessibility.js - Shared Accessibility Logic

document.addEventListener('DOMContentLoaded', function () {
    // High Contrast Toggle
    const contrastToggle = document.getElementById('contrast-toggle');
    if (contrastToggle) {
        // Load preference
        if (localStorage.getItem('highContrast') === 'true') {
            document.body.classList.add('high-contrast');
            contrastToggle.setAttribute('aria-pressed', 'true');
        }

        contrastToggle.addEventListener('click', function () {
            document.body.classList.toggle('high-contrast');
            const isPressed = this.getAttribute('aria-pressed') === 'true';
            this.setAttribute('aria-pressed', !isPressed);

            // Save preference
            localStorage.setItem('highContrast', !isPressed);
        });
    }
});

// Live Notifications for Screen Readers
function announceNotification(message) {
    const announcement = document.createElement('div');
    announcement.setAttribute('role', 'status');
    announcement.setAttribute('aria-live', 'polite');
    announcement.className = 'sr-only';
    announcement.textContent = message;
    document.body.appendChild(announcement);
    // Remove after reading is likely done (3-5 seconds)
    setTimeout(() => announcement.remove(), 5000);
}

// Example usage: announceNotification('Bienvenue');
