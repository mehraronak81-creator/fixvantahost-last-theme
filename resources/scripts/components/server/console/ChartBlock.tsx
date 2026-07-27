import React from 'react';
import classNames from 'classnames';
import styles from '@/components/server/console/style.module.css';

interface ChartBlockProps {
    title: string;
    legend?: React.ReactNode;
    children: React.ReactNode;
}

interface ChartChildProps {
    data?: {
        datasets?: Array<{ data?: Array<number | null> }>;
    };
}

export default ({ title, legend, children }: ChartBlockProps) => {
    const chartData = React.isValidElement(children) ? (children.props as ChartChildProps).data : undefined;
    const isEmpty =
        !!chartData?.datasets?.length &&
        chartData.datasets.every((dataset) => dataset.data?.every((value) => value === 0 || value === null));

    return (
        <section className={classNames(styles.chart_container, 'group')} aria-label={title + ' live chart'}>
            <header className={'flex items-center justify-between px-4 pt-3'}>
                <div>
                    <p className={styles.chart_eyebrow}>Live telemetry</p>
                    <h3 className={'font-header font-semibold'} style={{ color: 'var(--text-main)' }}>
                        {title}
                    </h3>
                </div>
                {legend && <p className={'text-sm flex items-center'}>{legend}</p>}
            </header>
            <div className={styles.chart_plot}>
                {children}
                {isEmpty && <span className={styles.chart_empty}>No telemetry yet</span>}
            </div>
        </section>
    );
};
