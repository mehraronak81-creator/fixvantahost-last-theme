import {
    CategoryScale,
    Chart as ChartJS,
    ChartData,
    ChartDataset,
    ChartOptions,
    Filler,
    LinearScale,
    LineElement,
    PointElement,
    ScriptableContext,
    Tooltip,
} from 'chart.js';
import { DeepPartial } from 'ts-essentials';
import { useState } from 'react';
import { deepmerge, deepmergeCustom } from 'deepmerge-ts';

ChartJS.register(CategoryScale, LineElement, PointElement, Filler, LinearScale, Tooltip);

const prefersReducedMotion =
    typeof window !== 'undefined' && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const sampleTime = () =>
    new Intl.DateTimeFormat([], { hour: '2-digit', minute: '2-digit', second: '2-digit' }).format(new Date());

const options: ChartOptions<'line'> = {
    responsive: true,
    animation: prefersReducedMotion ? false : { duration: 160, easing: 'linear' },
    interaction: { intersect: false, mode: 'index' },
    plugins: {
        legend: { display: false },
        title: { display: false },
        tooltip: {
            enabled: true,
            backgroundColor: '#1b1f24',
            borderColor: '#262b31',
            borderWidth: 1,
            titleColor: '#e7e9ec',
            bodyColor: '#8a93a0',
            padding: 10,
            displayColors: true,
            callbacks: {
                title: (items) => items[0]?.label || 'Live sample',
            },
        },
    },
    layout: { padding: { left: 2, right: 8, bottom: 4 } },
    scales: {
        x: {
            type: 'category',
            grid: { display: false, drawBorder: false },
            ticks: { display: false },
        },
        y: {
            min: 0,
            type: 'linear',
            grid: {
                display: true,
                color: 'rgba(138, 147, 160, 0.16)',
                drawBorder: false,
            },
            ticks: {
                display: true,
                count: 3,
                color: '#8a93a0',
                font: {
                    family: 'ui-monospace, SFMono-Regular, Menlo, Consolas, monospace',
                    size: 10,
                    weight: '400',
                },
            },
        },
    },
    elements: {
        point: { radius: 0, hitRadius: 14, hoverRadius: 3 },
        line: { tension: 0.38, borderWidth: 2, cubicInterpolationMode: 'monotone' },
    },
};

function createChartFill(top: string, bottom = 'rgba(79, 124, 255, 0)') {
    return (context: ScriptableContext<'line'>): string | CanvasGradient => {
        const chart = context.chart;
        const area = chart.chartArea;
        if (!area) return top;
        const gradient = chart.ctx.createLinearGradient(0, area.top, 0, area.bottom);
        gradient.addColorStop(0, top);
        gradient.addColorStop(1, bottom);
        return gradient;
    };
}

function getOptions(opts?: DeepPartial<ChartOptions<'line'>> | undefined): ChartOptions<'line'> {
    return deepmerge(options, opts || {});
}

type ChartDatasetCallback = (value: ChartDataset<'line'>, index: number) => ChartDataset<'line'>;

function getEmptyData(label: string, sets = 1, callback?: ChartDatasetCallback | undefined): ChartData<'line'> {
    const next = callback || ((value) => value);

    return {
        labels: Array(20).fill(''),
        datasets: Array(sets)
            .fill(0)
            .map((_, index) =>
                next(
                    {
                        fill: 'origin',
                        label,
                        data: Array(20).fill(0),
                        borderColor: '#4f7cff',
                        backgroundColor: createChartFill('rgba(79, 124, 255, 0.22)'),
                    },
                    index
                )
            ),
    };
}

const merge = deepmergeCustom({ mergeArrays: false });

interface UseChartOptions {
    sets: number;
    options?: DeepPartial<ChartOptions<'line'>> | number | undefined;
    callback?: ChartDatasetCallback | undefined;
}

function useChart(label: string, opts?: UseChartOptions) {
    const chartOptions = getOptions(
        typeof opts?.options === 'number' ? { scales: { y: { min: 0, suggestedMax: opts.options } } } : opts?.options
    );
    const [data, setData] = useState(getEmptyData(label, opts?.sets || 1, opts?.callback));

    const push = (items: number | null | (number | null)[]) =>
        setData((state) =>
            merge(state, {
                labels: (state.labels || []).slice(1).concat(sampleTime()),
                datasets: (Array.isArray(items) ? items : [items]).map((item, index) => ({
                    ...state.datasets[index],
                    data: state.datasets[index].data
                        .slice(1)
                        .concat(typeof item === 'number' ? Number(item.toFixed(2)) : item),
                })),
            })
        );

    const clear = () =>
        setData((state) =>
            merge(state, {
                labels: Array(20).fill(''),
                datasets: state.datasets.map((value) => ({
                    ...value,
                    data: Array(20).fill(0),
                })),
            })
        );

    return { props: { data, options: chartOptions }, push, clear };
}

function useChartTickLabel(label: string, max: number, tickLabel: string, roundTo?: number) {
    return useChart(label + ' (' + tickLabel + ')', {
        sets: 1,
        options: {
            scales: {
                y: {
                    suggestedMax: max,
                    ticks: {
                        callback(value) {
                            return (roundTo ? Number(value).toFixed(roundTo) : value) + tickLabel;
                        },
                    },
                },
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label(context) {
                            const value = roundTo ? Number(context.parsed.y).toFixed(roundTo) : context.parsed.y;
                            return label + ': ' + value + tickLabel;
                        },
                    },
                },
            },
        },
    });
}

export { useChart, useChartTickLabel, getOptions, getEmptyData, createChartFill };
