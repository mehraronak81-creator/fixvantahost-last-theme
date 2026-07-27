import React, { useEffect, useRef } from 'react';
import { ServerContext } from '@/state/server';
import { SocketEvent } from '@/components/server/events';
import useWebsocketEvent from '@/plugins/useWebsocketEvent';
import { Line } from 'react-chartjs-2';
import { createChartFill, useChart, useChartTickLabel } from '@/components/server/console/chart';
import { bytesToString } from '@/lib/formatters';
import { CloudDownloadIcon, CloudUploadIcon } from '@heroicons/react/solid';
import ChartBlock from '@/components/server/console/ChartBlock';
import Tooltip from '@/components/elements/tooltip/Tooltip';

export default () => {
    const status = ServerContext.useStoreState((state) => state.status.value);
    const limits = ServerContext.useStoreState((state) => state.server.data!.limits);
    const previous = useRef<Record<'tx' | 'rx', number>>({ tx: -1, rx: -1 });

    const cpu = useChartTickLabel('CPU', limits.cpu, '%', 2);
    const memory = useChartTickLabel('Memory', limits.memory, 'MiB');
    const network = useChart('Network', {
        sets: 2,
        options: {
            scales: {
                y: {
                    ticks: {
                        callback(value) {
                            return bytesToString(typeof value === 'string' ? parseInt(value, 10) : value);
                        },
                    },
                },
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label(context) {
                            return context.dataset.label + ': ' + bytesToString(context.parsed.y);
                        },
                    },
                },
            },
        },
        callback(opts, index) {
            return {
                ...opts,
                label: !index ? 'Network In' : 'Network Out',
                borderColor: !index ? '#4f7cff' : '#8a93a0',
                backgroundColor: !index
                    ? createChartFill('rgba(79, 124, 255, 0.22)')
                    : createChartFill('rgba(138, 147, 160, 0.14)', 'rgba(138, 147, 160, 0)'),
            };
        },
    });

    useEffect(() => {
        if (status === 'offline') {
            cpu.clear();
            memory.clear();
            network.clear();
        }
    }, [status]);

    useWebsocketEvent(SocketEvent.STATS, (data: string) => {
        let values: any = {};
        try {
            values = JSON.parse(data);
        } catch (e) {
            return;
        }
        cpu.push(values.cpu_absolute);
        memory.push(Math.floor(values.memory_bytes / 1024 / 1024));
        network.push([
            previous.current.tx < 0 ? 0 : Math.max(0, values.network.tx_bytes - previous.current.tx),
            previous.current.rx < 0 ? 0 : Math.max(0, values.network.rx_bytes - previous.current.rx),
        ]);

        previous.current = { tx: values.network.tx_bytes, rx: values.network.rx_bytes };
    });

    return (
        <>
            <ChartBlock title={'CPU Load'}>
                <Line {...cpu.props} />
            </ChartBlock>
            <ChartBlock title={'Memory'}>
                <Line {...memory.props} />
            </ChartBlock>
            <ChartBlock
                title={'Network'}
                legend={
                    <>
                        <Tooltip arrow content={'Inbound'}>
                            <CloudDownloadIcon className={'mr-2 w-4 h-4'} style={{ color: '#4f7cff' }} />
                        </Tooltip>
                        <Tooltip arrow content={'Outbound'}>
                            <CloudUploadIcon className={'w-4 h-4'} style={{ color: '#8a93a0' }} />
                        </Tooltip>
                    </>
                }
            >
                <Line {...network.props} />
            </ChartBlock>
        </>
    );
};
