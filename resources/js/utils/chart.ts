import { type ChartData, type ChartOptions } from 'chart.js';

export const defaultChartOptions: ChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
        intersect: false,
        mode: 'index',
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                callback: (value) => {
                    if (typeof value === 'number') {
                        return new Intl.NumberFormat('en-US', {
                            style: 'currency',
                            currency: 'USD',
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 0,
                        }).format(value);
                    }
                    return value;
                },
            },
        },
    },
    plugins: {
        legend: {
            position: 'top' as const,
        },
        tooltip: {
            callbacks: {
                label: (context) => {
                    let label = context.dataset.label || '';
                    if (label) {
                        label += ': ';
                    }
                    if (context.parsed.y !== null) {
                        label += new Intl.NumberFormat('en-US', {
                            style: 'currency',
                            currency: 'USD',
                        }).format(context.parsed.y);
                    }
                    return label;
                },
            },
        },
    },
};