/**
 * Charts Configuration
 * BukoJuice Application
 * Using Chart.js
 */

class Charts {
    constructor() {
        this.charts = {};
        this.colors = {
            primary: '#10b981',
            secondary: '#34d399',
            success: '#10b981',
            danger: '#ef4444',
            warning: '#f59e0b',
            info: '#3b82f6'
        };
    }

    // Get CSS variable value
    getCSSVar(name) {
        return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    }

    // Common chart options
    getCommonOptions() {
        const textColor = this.getCSSVar('--text-secondary') || '#64748b';
        const gridColor = this.getCSSVar('--border-color') || '#e2e8f0';

        return {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: this.getCSSVar('--bg-secondary') || '#ffffff',
                    titleColor: this.getCSSVar('--text-primary') || '#0f172a',
                    bodyColor: this.getCSSVar('--text-secondary') || '#64748b',
                    borderColor: gridColor,
                    borderWidth: 1,
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: true,
                    boxPadding: 4,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += '₱' + context.parsed.y.toLocaleString();
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: textColor,
                        font: {
                            size: 12,
                            family: 'Inter'
                        }
                    }
                },
                y: {
                    grid: {
                        color: gridColor,
                        drawBorder: false
                    },
                    ticks: {
                        color: textColor,
                        font: {
                            size: 12,
                            family: 'Inter'
                        },
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            }
        };
    }

    // Create income vs expense chart
    createIncomeExpenseChart(canvasId, data) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;

        // Destroy existing chart
        if (this.charts[canvasId]) {
            this.charts[canvasId].destroy();
        }

        const gradient1 = ctx.getContext('2d').createLinearGradient(0, 0, 0, 300);
        gradient1.addColorStop(0, 'rgba(16, 185, 129, 0.3)');
        gradient1.addColorStop(1, 'rgba(16, 185, 129, 0)');

        const gradient2 = ctx.getContext('2d').createLinearGradient(0, 0, 0, 300);
        gradient2.addColorStop(0, 'rgba(239, 68, 68, 0.3)');
        gradient2.addColorStop(1, 'rgba(239, 68, 68, 0)');

        this.charts[canvasId] = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Income',
                        data: data.income,
                        borderColor: this.colors.success,
                        backgroundColor: gradient1,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: this.colors.success,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Expenses',
                        data: data.expenses,
                        borderColor: this.colors.danger,
                        backgroundColor: gradient2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: this.colors.danger,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                ...this.getCommonOptions(),
                plugins: {
                    ...this.getCommonOptions().plugins,
                    legend: {
                        display: true,
                        position: 'top',
                        align: 'end',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 20,
                            font: {
                                size: 12,
                                family: 'Inter'
                            },
                            color: this.getCSSVar('--text-secondary') || '#64748b'
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });

        return this.charts[canvasId];
    }

    // Create expense breakdown chart (Doughnut)
    createExpenseBreakdownChart(canvasId, data) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;

        // Destroy existing chart
        if (this.charts[canvasId]) {
            this.charts[canvasId].destroy();
        }

        this.charts[canvasId] = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.values,
                    backgroundColor: data.colors || [
                        '#ef4444',
                        '#f97316',
                        '#eab308',
                        '#14b8a6',
                        '#a855f7',
                        '#ec4899',
                        '#3b82f6',
                        '#64748b'
                    ],
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: this.getCSSVar('--bg-secondary') || '#ffffff',
                        titleColor: this.getCSSVar('--text-primary') || '#0f172a',
                        bodyColor: this.getCSSVar('--text-secondary') || '#64748b',
                        borderColor: this.getCSSVar('--border-color') || '#e2e8f0',
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.raw / total) * 100).toFixed(1);
                                return `${context.label}: ₱${context.raw.toLocaleString()} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        return this.charts[canvasId];
    }

    // Create bar chart
    createBarChart(canvasId, data) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;

        // Destroy existing chart
        if (this.charts[canvasId]) {
            this.charts[canvasId].destroy();
        }

        this.charts[canvasId] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: data.label || 'Amount',
                    data: data.values,
                    backgroundColor: data.colors || this.colors.primary,
                    borderRadius: 8,
                    maxBarThickness: 40
                }]
            },
            options: {
                ...this.getCommonOptions(),
                plugins: {
                    ...this.getCommonOptions().plugins
                }
            }
        });

        return this.charts[canvasId];
    }

    // Update chart theme on dark mode toggle
    updateTheme() {
        Object.values(this.charts).forEach(chart => {
            if (chart) {
                chart.options = this.getCommonOptions();
                chart.update();
            }
        });
    }

    // Destroy chart
    destroy(canvasId) {
        if (this.charts[canvasId]) {
            this.charts[canvasId].destroy();
            delete this.charts[canvasId];
        }
    }

    // Destroy all charts
    destroyAll() {
        Object.keys(this.charts).forEach(id => this.destroy(id));
    }
}

// Initialize charts
window.MoneyCharts = new Charts();

// Update charts on theme change
document.addEventListener('DOMContentLoaded', () => {
    const themeToggles = document.querySelectorAll('[data-theme-toggle]');
    themeToggles.forEach(toggle => {
        toggle.addEventListener('click', () => {
            setTimeout(() => {
                window.MoneyCharts.updateTheme();
            }, 100);
        });
    });
});
