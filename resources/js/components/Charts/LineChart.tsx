import React, { useEffect, useRef } from 'react';
import { Chart, ChartData, ChartOptions } from 'chart.js/auto';

interface Props {
    data: {
        date: string;
        value: number;
    }[];
    options?: ChartOptions;
}

export function LineChart({ data, options }: Props) {
    const canvasRef = useRef<HTMLCanvasElement>(null);
    const chartRef = useRef<Chart | null>(null);

    useEffect(() => {
        if (canvasRef.current) {
            // Destroy previous chart if it exists
            if (chartRef.current) {
                chartRef.current.destroy();
            }

            // Create new chart
            const ctx = canvasRef.current.getContext('2d');
            if (ctx) {
                const chartData: ChartData = {
                    labels: data.map(item => item.date),
                    datasets: [
                        {
                            label: 'Performance',
                            data: data.map(item => item.value),
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true
                        }
                    ]
                };

                const chartOptions: ChartOptions = {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    ...options
                };

                chartRef.current = new Chart(ctx, {
                    type: 'line',
                    data: chartData,
                    options: chartOptions
                });
            }
        }

        // Cleanup function
        return () => {
            if (chartRef.current) {
                chartRef.current.destroy();
            }
        };
    }, [data, options]);

    return <canvas ref={canvasRef} />;
} 