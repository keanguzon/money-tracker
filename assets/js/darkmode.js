/**
 * Dark Mode Toggle
 * BukoJuice Application
 */

class DarkMode {
    constructor() {
        this.darkModeKey = 'bukojuice_darkmode';
        this.init();
    }

    init() {
        // Check for saved preference or system preference
        const savedMode = localStorage.getItem(this.darkModeKey);
        
        if (savedMode !== null) {
            this.setMode(savedMode === 'true');
        } else {
            // Check system preference
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            this.setMode(prefersDark);
        }

        // Listen for system preference changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (localStorage.getItem(this.darkModeKey) === null) {
                this.setMode(e.matches);
            }
        });

        // Initialize toggle buttons
        this.initToggles();
    }

    setMode(isDark) {
        if (isDark) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        this.updateIcons(isDark);
    }

    toggle() {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem(this.darkModeKey, isDark);
        this.updateIcons(isDark);
        
        // Update user preference in database if logged in
        this.savePreference(isDark);
        
        return isDark;
    }

    updateIcons(isDark) {
        const sunIcons = document.querySelectorAll('.theme-icon-sun');
        const moonIcons = document.querySelectorAll('.theme-icon-moon');

        sunIcons.forEach(icon => {
            icon.style.display = isDark ? 'none' : 'block';
        });

        moonIcons.forEach(icon => {
            icon.style.display = isDark ? 'block' : 'none';
        });
    }

    initToggles() {
        const toggleButtons = document.querySelectorAll('[data-theme-toggle]');
        
        toggleButtons.forEach(button => {
            button.addEventListener('click', () => {
                this.toggle();
            });
        });
    }

    async savePreference(isDark) {
        try {
            const response = await fetch('/money-tracker/api/update_preference.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ dark_mode: isDark ? 1 : 0 })
            });
        } catch (error) {
            // Silently fail - preference still saved locally
        }
    }

    isDarkMode() {
        return document.documentElement.classList.contains('dark');
    }
}

// Initialize dark mode
const darkMode = new DarkMode();
