import React, { memo, useEffect, useRef, useState } from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faEthernet, faHdd, faMemory, faMicrochip, faServer, faStar } from '@fortawesome/free-solid-svg-icons';
import { Link } from 'react-router-dom';
import { Server } from '@/api/server/getServer';
import getServerResourceUsage, { ServerPowerState, ServerStats } from '@/api/server/getServerResourceUsage';
import { bytesToString, ip, mbToBytes } from '@/lib/formatters';
import tw from 'twin.macro';
import GreyRowBox from '@/components/elements/GreyRowBox';
import Spinner from '@/components/elements/Spinner';
import styled from 'styled-components/macro';
import isEqual from 'react-fast-compare';
import RuntimeIcon from '@/components/dashboard/RuntimeIcon';

// Determines if the current value is in an alarm threshold so we can show it in red rather
// than the more faded default style.
const isAlarmState = (current: number, limit: number): boolean => limit > 0 && current / (limit * 1024 * 1024) >= 0.9;

const Icon = memo(
    styled(FontAwesomeIcon)<{ $alarm: boolean }>`
        ${(props) => (props.$alarm ? tw`text-red-400` : tw`text-neutral-500`)};
    `,
    isEqual
);

const IconDescription = styled.p<{ $alarm: boolean }>`
    ${tw`text-sm ml-2`};
    ${(props) => (props.$alarm ? tw`text-white` : tw`text-neutral-400`)};
`;

const StatusIndicatorBox = styled(GreyRowBox)<{ $status: ServerPowerState | undefined }>`
    ${tw`relative no-underline overflow-hidden`};
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.8rem;
    min-height: 14.5rem;
    padding: 1.35rem 1.5rem 1.1rem;
    background: var(--vh-surface, #14171b);
    border: 1px solid var(--vh-border, #262b31);
    border-radius: var(--vh-radius-card, 10px);
    box-shadow: var(--vh-shadow);

    &::before {
        content: '';
        position: absolute;
        inset: 0;
        pointer-events: none;
        opacity: 1;
        background-image: linear-gradient(
            90deg,
            transparent 0 74%,
            var(--vh-border, #262b31) 74% 74.3%,
            transparent 74.3%
        );
    }

    & > div {
        position: relative;
        z-index: 1;
    }

    & > div:nth-of-type(1) {
        padding-left: 0;
        min-height: 3.5rem;
    }

    & > div:nth-of-type(1) .icon {
        display: none;
    }

    & > div:nth-of-type(1) p:first-of-type {
        font-size: 1.2rem;
        font-weight: 700;
        letter-spacing: -0.02em;
    }

    & > div:nth-of-type(2) {
        display: block;
        margin-left: 0;
    }

    & > div:nth-of-type(2) > div {
        justify-content: flex-start;
    }

    & > div:nth-of-type(3) {
        display: flex !important;
        justify-content: flex-start;
        gap: 1rem;
        margin: 0;
        padding: 0.65rem 0 0;
        border-top: 1px solid var(--vh-border, #262b31);
    }

    & > div:nth-of-type(3) > div {
        margin-left: 0;
    }

    & .customer-manage-link {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 2.8rem;
        margin-top: 0.15rem;
        border: 1px solid var(--vh-border, #262b31);
        border-radius: var(--vh-radius-control, 6px);
        color: var(--text-secondary);
        background: var(--vh-elevated, #1b1f24);
        font-size: 0.9rem;
        font-weight: 600;
        transition: background 150ms ease, border-color 150ms ease;
    }

    &:hover .customer-manage-link {
        background: var(--vh-elevated, #1b1f24);
        border-color: var(--vh-border, #262b31);
        color: var(--vh-text, #e7e9ec);
    }

    & .status-bar {
        ${tw`w-1.5 absolute right-0 z-20 rounded-full m-1 transition-all duration-300`};
        top: 12px;
        right: 12px;
        width: 8px;
        height: 8px;
        margin: 0;
        opacity: 1;

        ${({ $status }) =>
            !$status || $status === 'offline'
                ? 'background: #5b6570;'
                : $status === 'running'
                ? 'background: #3ecf8e;'
                : 'background: #e8a33d;'};
    }

    & .status-bar {
        box-shadow: none !important;
    }
`;

const FavoriteButton = styled.button<{ $active: boolean }>`
    ${tw`absolute left-1 top-1 z-30 flex items-center justify-center w-7 h-7 rounded-full transition-colors duration-150`};
    ${(props) => (props.$active ? 'color: #e7e9ec;' : 'color: #8a93a0;')};

    &:hover {
        color: #e7e9ec;
        background: var(--color-surface-hover);
    }
`;

type Timer = ReturnType<typeof setInterval>;

interface Props {
    server: Server;
    className?: string;
    isFavorite?: boolean;
    onToggleFavorite?: (uuid: string) => void;
}

export default ({ server, className, isFavorite, onToggleFavorite }: Props) => {
    const interval = useRef<Timer>(null) as React.MutableRefObject<Timer>;
    const [isSuspended, setIsSuspended] = useState(server.status === 'suspended');
    const [stats, setStats] = useState<ServerStats | null>(null);

    const getStats = () =>
        getServerResourceUsage(server.uuid)
            .then((data) => setStats(data))
            .catch((error) => console.error(error));

    useEffect(() => {
        setIsSuspended(stats?.isSuspended || server.status === 'suspended');
    }, [stats?.isSuspended, server.status]);

    useEffect(() => {
        // Don't waste a HTTP request if there is nothing important to show to the user because
        // the server is suspended.
        if (isSuspended || server.isNodeUnderMaintenance) return;

        getStats().then(() => {
            interval.current = setInterval(() => getStats(), 30000);
        });

        return () => {
            interval.current && clearInterval(interval.current);
        };
    }, [isSuspended, server.isNodeUnderMaintenance]);

    const alarms = { cpu: false, memory: false, disk: false };
    if (stats) {
        alarms.cpu = server.limits.cpu === 0 ? false : stats.cpuUsagePercent >= server.limits.cpu * 0.9;
        alarms.memory = isAlarmState(stats.memoryUsageInBytes, server.limits.memory);
        alarms.disk = server.limits.disk === 0 ? false : isAlarmState(stats.diskUsageInBytes, server.limits.disk);
    }

    const diskLimit = server.limits.disk !== 0 ? bytesToString(mbToBytes(server.limits.disk)) : 'Unlimited';
    const memoryLimit = server.limits.memory !== 0 ? bytesToString(mbToBytes(server.limits.memory)) : 'Unlimited';
    const cpuLimit = server.limits.cpu !== 0 ? server.limits.cpu + ' %' : 'Unlimited';

    return (
        <StatusIndicatorBox as={Link} to={`/server/${server.id}`} className={className} $status={stats?.status}>
            {onToggleFavorite && (
                <FavoriteButton
                    type={'button'}
                    $active={!!isFavorite}
                    title={isFavorite ? 'Remove from favorites' : 'Add to favorites'}
                    aria-label={isFavorite ? 'Remove from favorites' : 'Add to favorites'}
                    onClick={(e: React.MouseEvent<HTMLButtonElement>) => {
                        e.preventDefault();
                        e.stopPropagation();
                        onToggleFavorite(server.uuid);
                    }}
                >
                    <FontAwesomeIcon icon={faStar} />
                </FavoriteButton>
            )}
            <div css={tw`flex items-center col-span-12 sm:col-span-5 lg:col-span-6 pl-6`}>
                <div className={'icon mr-4'}>
                    <FontAwesomeIcon icon={faServer} />
                </div>
                <div>
                    <div className={'flex items-center gap-2'}>
                        <RuntimeIcon image={server.dockerImage} />
                        <p css={tw`text-lg break-words`}>{server.name}</p>
                    </div>
                    {!!server.description && (
                        <p css={tw`text-sm text-neutral-300 break-words line-clamp-2`}>{server.description}</p>
                    )}
                </div>
            </div>
            <div css={tw`flex-1 ml-4 lg:block lg:col-span-2 hidden`}>
                <div css={tw`flex justify-center`}>
                    <FontAwesomeIcon icon={faEthernet} css={tw`text-neutral-500`} />
                    <p css={tw`text-sm text-neutral-400 ml-2`}>
                        {server.allocations
                            .filter((alloc) => alloc.isDefault)
                            .map((allocation) => (
                                <React.Fragment key={allocation.ip + allocation.port.toString()}>
                                    {allocation.alias || ip(allocation.ip)}:{allocation.port}
                                </React.Fragment>
                            ))}
                    </p>
                </div>
            </div>
            <div css={tw`hidden col-span-7 lg:col-span-4 sm:flex items-baseline justify-center`}>
                {!stats || isSuspended || server.isNodeUnderMaintenance ? (
                    isSuspended ? (
                        <div css={tw`flex-1 text-center`}>
                            <span css={tw`bg-red-500 rounded px-2 py-1 text-red-100 text-xs`}>
                                {server.status === 'suspended' ? 'Suspended' : 'Connection Error'}
                            </span>
                        </div>
                    ) : server.isNodeUnderMaintenance ? (
                        <div css={tw`flex-1 text-center`}>
                            <span css={tw`bg-yellow-500 rounded px-2 py-1 text-yellow-100 text-xs`}>
                                Under Maintenance
                            </span>
                        </div>
                    ) : server.isTransferring || server.status ? (
                        <div css={tw`flex-1 text-center`}>
                            <span css={tw`bg-neutral-500 rounded px-2 py-1 text-neutral-100 text-xs`}>
                                {server.isTransferring
                                    ? 'Transferring'
                                    : server.status === 'installing'
                                    ? 'Installing'
                                    : server.status === 'restoring_backup'
                                    ? 'Restoring Backup'
                                    : 'Unavailable'}
                            </span>
                        </div>
                    ) : (
                        <Spinner size={'small'} />
                    )
                ) : (
                    <React.Fragment>
                        <div css={tw`flex-1 ml-4 sm:block hidden`}>
                            <div css={tw`flex justify-center`}>
                                <Icon icon={faMicrochip} $alarm={alarms.cpu} />
                                <IconDescription $alarm={alarms.cpu}>
                                    {stats.cpuUsagePercent.toFixed(2)} %
                                </IconDescription>
                            </div>
                            <p css={tw`text-xs text-neutral-600 text-center mt-1`}>of {cpuLimit}</p>
                        </div>
                        <div css={tw`flex-1 ml-4 sm:block hidden`}>
                            <div css={tw`flex justify-center`}>
                                <Icon icon={faMemory} $alarm={alarms.memory} />
                                <IconDescription $alarm={alarms.memory}>
                                    {bytesToString(stats.memoryUsageInBytes)}
                                </IconDescription>
                            </div>
                            <p css={tw`text-xs text-neutral-600 text-center mt-1`}>of {memoryLimit}</p>
                        </div>
                        <div css={tw`flex-1 ml-4 sm:block hidden`}>
                            <div css={tw`flex justify-center`}>
                                <Icon icon={faHdd} $alarm={alarms.disk} />
                                <IconDescription $alarm={alarms.disk}>
                                    {bytesToString(stats.diskUsageInBytes)}
                                </IconDescription>
                            </div>
                            <p css={tw`text-xs text-neutral-600 text-center mt-1`}>of {diskLimit}</p>
                        </div>
                    </React.Fragment>
                )}
            </div>
            <div className={'status-bar'} />
            <span className='customer-manage-link'>Manage server</span>
        </StatusIndicatorBox>
    );
};
