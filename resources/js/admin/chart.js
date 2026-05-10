import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

export async function initRegistrationsChart() {
    const canvas = document.getElementById('registrationsChart');
    if (!canvas) return;

    try {
        const response = await fetch('/admin/stats/registrations');
        const data = await response.json();

        new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: window.__['admin.chart_registrations'] || 'Registrations',
                        data: data.values,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59,130,246,0.1)',
                        tension: 0.3,
                        fill: true,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        labels: {
                            color: getComputedStyle(document.body).getPropertyValue(
                                '--admin-text-secondary'
                            ),
                        },
                    },
                },
                scales: {
                    y: {
                        ticks: {
                            color: getComputedStyle(document.body).getPropertyValue(
                                '--admin-text-secondary'
                            ),
                        },
                        grid: {
                            color: getComputedStyle(document.body).getPropertyValue(
                                '--admin-border'
                            ),
                        },
                    },
                    x: {
                        ticks: {
                            color: getComputedStyle(document.body).getPropertyValue(
                                '--admin-text-secondary'
                            ),
                        },
                        grid: {
                            color: getComputedStyle(document.body).getPropertyValue(
                                '--admin-border'
                            ),
                        },
                    },
                },
            },
        });
    } catch (e) {
        console.error('Chart init error:', e);
    }
}
