/**
 * Main JavaScript
 * BukoJuice Application
 */

// Utility Functions
const Utils = {
    // Format currency
    formatCurrency(amount, currency = 'PHP') {
        const symbols = {
            'PHP': '₱',
            'USD': '$',
            'EUR': '€',
            'GBP': '£',
            'JPY': '¥'
        };
        const symbol = symbols[currency] || currency;
        return symbol + parseFloat(amount).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    },

    // Format date
    formatDate(dateString, format = 'short') {
        const date = new Date(dateString);
        const options = {
            short: { month: 'short', day: 'numeric', year: 'numeric' },
            long: { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' },
            relative: null
        };

        if (format === 'relative') {
            return this.getRelativeTime(date);
        }

        return date.toLocaleDateString('en-US', options[format] || options.short);
    },

    // Get relative time
    getRelativeTime(date) {
        const now = new Date();
        const diff = now - date;
        const seconds = Math.floor(diff / 1000);
        const minutes = Math.floor(seconds / 60);
        const hours = Math.floor(minutes / 60);
        const days = Math.floor(hours / 24);

        if (days > 7) {
            return this.formatDate(date, 'short');
        } else if (days > 0) {
            return days === 1 ? 'Yesterday' : `${days} days ago`;
        } else if (hours > 0) {
            return `${hours}h ago`;
        } else if (minutes > 0) {
            return `${minutes}m ago`;
        } else {
            return 'Just now';
        }
    },

    // Show toast notification
    showToast(message, type = 'info', duration = 3000) {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <span class="toast-icon">${this.getToastIcon(type)}</span>
                <span class="toast-message">${message}</span>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        `;

        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        container.appendChild(toast);
        
        // Animate in
        setTimeout(() => toast.classList.add('show'), 10);

        // Auto remove
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    },

    getToastIcon(type) {
        const icons = {
            success: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>',
            error: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
            warning: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
            info: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>'
        };
        return icons[type] || icons.info;
    },

    // Confirm dialog
    confirm(message, onConfirm, onCancel) {
        const overlay = document.createElement('div');
        overlay.className = 'confirm-overlay';
        overlay.innerHTML = `
            <div class="confirm-dialog">
                <div class="confirm-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                </div>
                <p class="confirm-message">${message}</p>
                <div class="confirm-buttons">
                    <button class="btn btn-secondary confirm-cancel">Cancel</button>
                    <button class="btn btn-danger confirm-yes">Delete</button>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);
        setTimeout(() => overlay.classList.add('active'), 10);

        const cancelBtn = overlay.querySelector('.confirm-cancel');
        const yesBtn = overlay.querySelector('.confirm-yes');

        const close = () => {
            overlay.classList.remove('active');
            setTimeout(() => overlay.remove(), 300);
        };

        cancelBtn.addEventListener('click', () => {
            close();
            if (onCancel) onCancel();
        });

        yesBtn.addEventListener('click', () => {
            close();
            if (onConfirm) onConfirm();
        });

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                close();
                if (onCancel) onCancel();
            }
        });
    },

    // Debounce function
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    // Animate counter
    animateCounter(element, start, end, duration = 1000) {
        const range = end - start;
        const startTime = performance.now();

        const updateCounter = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Easing function
            const easeOutQuart = 1 - Math.pow(1 - progress, 4);
            const current = start + (range * easeOutQuart);
            
            element.textContent = this.formatCurrency(current);
            
            if (progress < 1) {
                requestAnimationFrame(updateCounter);
            }
        };

        requestAnimationFrame(updateCounter);
    }
};

// Mobile Sidebar
class Sidebar {
    constructor() {
        this.sidebar = document.querySelector('.sidebar');
        this.overlay = document.querySelector('.sidebar-overlay');
        this.menuToggle = document.querySelector('.menu-toggle');
        
        if (this.menuToggle) {
            this.init();
        }
    }

    init() {
        this.menuToggle.addEventListener('click', () => this.toggle());
        
        if (this.overlay) {
            this.overlay.addEventListener('click', () => this.close());
        }

        // Close on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen()) {
                this.close();
            }
        });
    }

    toggle() {
        this.sidebar.classList.toggle('open');
        this.overlay?.classList.toggle('show');
        document.body.classList.toggle('sidebar-open');
    }

    open() {
        this.sidebar.classList.add('open');
        this.overlay?.classList.add('show');
        document.body.classList.add('sidebar-open');
    }

    close() {
        this.sidebar.classList.remove('open');
        this.overlay?.classList.remove('show');
        document.body.classList.remove('sidebar-open');
    }

    isOpen() {
        return this.sidebar.classList.contains('open');
    }
}

// Modal Handler
class Modal {
    constructor(modalId) {
        this.modal = document.getElementById(modalId);
        this.init();
    }

    init() {
        if (!this.modal) return;

        // Close button
        const closeBtn = this.modal.querySelector('.modal-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.close());
        }

        // Click outside to close
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.close();
            }
        });

        // Escape key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen()) {
                this.close();
            }
        });
    }

    open() {
        this.modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    close() {
        this.modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    isOpen() {
        return this.modal.classList.contains('active');
    }
}

// Form Validation
class FormValidator {
    constructor(form) {
        this.form = form;
        this.errors = {};
    }

    validate(rules) {
        this.errors = {};
        
        for (const [field, fieldRules] of Object.entries(rules)) {
            const input = this.form.querySelector(`[name="${field}"]`);
            if (!input) continue;

            const value = input.value.trim();

            for (const rule of fieldRules) {
                const error = this.validateRule(value, rule, field);
                if (error) {
                    this.errors[field] = error;
                    break;
                }
            }
        }

        this.showErrors();
        return Object.keys(this.errors).length === 0;
    }

    validateRule(value, rule, field) {
        switch (rule.type) {
            case 'required':
                if (!value) return rule.message || `${field} is required`;
                break;
            case 'email':
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (value && !emailRegex.test(value)) {
                    return rule.message || 'Invalid email address';
                }
                break;
            case 'min':
                if (value && value.length < rule.value) {
                    return rule.message || `Minimum ${rule.value} characters required`;
                }
                break;
            case 'max':
                if (value && value.length > rule.value) {
                    return rule.message || `Maximum ${rule.value} characters allowed`;
                }
                break;
            case 'match':
                const matchInput = this.form.querySelector(`[name="${rule.field}"]`);
                if (matchInput && value !== matchInput.value) {
                    return rule.message || 'Fields do not match';
                }
                break;
            case 'number':
                if (value && isNaN(value)) {
                    return rule.message || 'Must be a number';
                }
                break;
        }
        return null;
    }

    showErrors() {
        // Clear previous errors
        this.form.querySelectorAll('.form-error').forEach(el => el.remove());
        this.form.querySelectorAll('.form-input').forEach(el => el.classList.remove('error'));

        // Show new errors
        for (const [field, message] of Object.entries(this.errors)) {
            const input = this.form.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('error');
                const errorEl = document.createElement('span');
                errorEl.className = 'form-error';
                errorEl.textContent = message;
                input.parentNode.appendChild(errorEl);
            }
        }
    }
}

// API Handler
class API {
    constructor(baseUrl = '/money-tracker/api') {
        this.baseUrl = baseUrl;
    }

    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}${endpoint}`;
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
            },
        };

        const response = await fetch(url, { ...defaultOptions, ...options });
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'An error occurred');
        }

        return data;
    }

    async get(endpoint) {
        return this.request(endpoint, { method: 'GET' });
    }

    async post(endpoint, body) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(body),
        });
    }

    async put(endpoint, body) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(body),
        });
    }

    async delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' });
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    // Initialize sidebar
    new Sidebar();

    // Add toast container styles
    const toastStyles = document.createElement('style');
    toastStyles.textContent = `
        .toast-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .toast {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.25rem;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            box-shadow: var(--shadow-lg);
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.3s ease;
            min-width: 300px;
            max-width: 450px;
        }
        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }
        .toast-content {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .toast-success .toast-icon { color: var(--success); }
        .toast-error .toast-icon { color: var(--danger); }
        .toast-warning .toast-icon { color: var(--warning); }
        .toast-info .toast-icon { color: var(--info); }
        .toast-message {
            font-size: 0.875rem;
            color: var(--text-primary);
        }
        .toast-close {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 0.25rem;
            display: flex;
        }
        .toast-close:hover { color: var(--text-primary); }
        
        .confirm-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
        }
        .confirm-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        .confirm-dialog {
            background: var(--bg-secondary);
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
            max-width: 400px;
            width: 90%;
        }
        .confirm-icon {
            width: 60px;
            height: 60px;
            background: rgba(245, 158, 11, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: var(--warning);
        }
        .confirm-message {
            font-size: 1rem;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
        }
        .confirm-buttons {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
        }
        
        .form-input.error {
            border-color: var(--danger) !important;
        }
        .form-error {
            display: block;
            font-size: 0.75rem;
            color: var(--danger);
            margin-top: 0.25rem;
        }
    `;
    document.head.appendChild(toastStyles);
});

// Export for use in other files
window.Utils = Utils;
window.Modal = Modal;
window.FormValidator = FormValidator;
window.API = new API();

// Modal Functions (Global)
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        setTimeout(() => modal.classList.add('show'), 10);
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }, 300);
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        closeModal(e.target.id);
    }
});

// Close modal with ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal.show').forEach(modal => {
            closeModal(modal.id);
        });
    }
});
