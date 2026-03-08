import React, { useEffect, useMemo, useState } from 'react';
import {
    faCalendarAlt,
    faCoins,
    faClock,
    faCloudDownloadAlt,
    faCloudUploadAlt,
    faHdd,
    faMemory,
    faMicrochip,
    faWifi,
} from '@fortawesome/free-solid-svg-icons';
import { bytesToString, ip, mbToBytes } from '@/lib/formatters';
import { ServerContext } from '@/state/server';
import { SocketEvent, SocketRequest } from '@/components/server/events';
import UptimeDuration from '@/components/server/UptimeDuration';
import StatBlock from '@/components/server/console/StatBlock';
import useWebsocketEvent from '@/plugins/useWebsocketEvent';
import classNames from 'classnames';
import { ServerStatus } from '@/state/server';

const statusMap: Record<NonNullable<ServerStatus>, string> = {
    offline: '离线',
    starting: '启动中',
    stopping: '停止中',
    running: '运行中',
};

type Stats = Record<'memory' | 'cpu' | 'disk' | 'uptime' | 'rx' | 'tx', number>;

/** Compute display info for an expiry timestamp. */
const useExpiryInfo = (expiresAt: string | null) =>
    useMemo(() => {
        if (!expiresAt) return null;
        const now = Date.now();
        const expiry = new Date(expiresAt).getTime();
        const diff = expiry - now;
        const isExpired = diff <= 0;
        const isExpiringSoon = !isExpired && diff < 7 * 24 * 60 * 60 * 1000;
        const d = new Date(expiresAt);
        const label = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
        return { label, isExpired, isExpiringSoon };
    }, [expiresAt]);

const getBackgroundColor = (value: number, max: number | null): string | undefined => {
    const delta = !max ? 0 : value / max;

    if (delta > 0.8) {
        if (delta > 0.9) {
            return 'bg-red-500';
        }
        return 'bg-yellow-500';
    }

    return undefined;
};

const Limit = ({ limit, children }: { limit: string | null; children: React.ReactNode }) => (
    <>
        {children}
        <span className={'ml-1 text-gray-300 text-[70%] select-none'}>/ {limit || <>&infin;</>}</span>
    </>
);

const ServerDetailsBlock = ({ className }: { className?: string }) => {
    const [stats, setStats] = useState<Stats>({ memory: 0, cpu: 0, disk: 0, uptime: 0, tx: 0, rx: 0 });

    const status = ServerContext.useStoreState((state) => state.status.value);
    const connected = ServerContext.useStoreState((state) => state.socket.connected);
    const instance = ServerContext.useStoreState((state) => state.socket.instance);
    const limits = ServerContext.useStoreState((state) => state.server.data!.limits);
    const expiresAt = ServerContext.useStoreState((state) => state.server.data!.expiresAt);
    const pointsPerDay = ServerContext.useStoreState((state) => state.server.data!.pointsPerDay);

    const expiryInfo = useExpiryInfo(expiresAt);

    const textLimits = useMemo(
        () => ({
            cpu: limits?.cpu ? `${limits.cpu}%` : null,
            memory: limits?.memory ? bytesToString(mbToBytes(limits.memory)) : null,
            disk: limits?.disk ? bytesToString(mbToBytes(limits.disk)) : null,
        }),
        [limits]
    );

    const allocation = ServerContext.useStoreState((state) => {
        const match = state.server.data!.allocations.find((allocation) => allocation.isDefault);

        return !match ? 'n/a' : `${match.alias || ip(match.ip)}:${match.port}`;
    });

    useEffect(() => {
        if (!connected || !instance) {
            return;
        }

        instance.send(SocketRequest.SEND_STATS);
    }, [instance, connected]);

    useWebsocketEvent(SocketEvent.STATS, (data) => {
        let stats: any = {};
        try {
            stats = JSON.parse(data);
        } catch (e) {
            return;
        }

        setStats({
            memory: stats.memory_bytes,
            cpu: stats.cpu_absolute,
            disk: stats.disk_bytes,
            tx: stats.network.tx_bytes,
            rx: stats.network.rx_bytes,
            uptime: stats.uptime || 0,
        });
    });

    return (
        <div className={classNames('grid grid-cols-6 gap-2 md:gap-4', className)}>
            <StatBlock icon={faWifi} title={'地址'} copyOnClick={allocation}>
                {allocation}
            </StatBlock>
            <StatBlock
                icon={faClock}
                title={'正常运行时间'}
                color={getBackgroundColor(status === 'running' ? 0 : status !== 'offline' ? 9 : 10, 10)}
            >
                {status === null ? (
                    '离线'
                ) : stats.uptime > 0 ? (
                    <UptimeDuration uptime={stats.uptime / 1000} />
                ) : (
                    statusMap[status] ?? '未知状态'
                )}
            </StatBlock>
            <StatBlock icon={faMicrochip} title={'CPU'} color={getBackgroundColor(stats.cpu, limits.cpu)}>
                {status === 'offline' ? (
                    <span className={'text-gray-400'}>离线</span>
                ) : (
                    <Limit limit={textLimits.cpu}>{stats.cpu.toFixed(2)}%</Limit>
                )}
            </StatBlock>
            <StatBlock
                icon={faMemory}
                title={'内存'}
                color={getBackgroundColor(stats.memory / 1024, limits.memory * 1024)}
            >
                {status === 'offline' ? (
                    <span className={'text-gray-400'}>离线</span>
                ) : (
                    <Limit limit={textLimits.memory}>{bytesToString(stats.memory)}</Limit>
                )}
            </StatBlock>
            <StatBlock
                icon={faHdd}
                title={'存储空间'}
                color={getBackgroundColor(stats.disk / 1024, limits.disk * 1024)}
            >
                <Limit limit={textLimits.disk}>{bytesToString(stats.disk)}</Limit>
            </StatBlock>
            <StatBlock icon={faCloudDownloadAlt} title={'网络(入站)'}>
                {status === 'offline' ? <span className={'text-gray-400'}>离线</span> : bytesToString(stats.rx)}
            </StatBlock>
            <StatBlock icon={faCloudUploadAlt} title={'网络(出站)'}>
                {status === 'offline' ? <span className={'text-gray-400'}>离线</span> : bytesToString(stats.tx)}
            </StatBlock>
            {expiryInfo && (
                <StatBlock
                    icon={faCalendarAlt}
                    title={'到期时间'}
                    color={
                        expiryInfo.isExpired
                            ? 'bg-red-500'
                            : expiryInfo.isExpiringSoon
                            ? 'bg-yellow-500'
                            : undefined
                    }
                >
                    <span
                        className={
                            expiryInfo.isExpired
                                ? 'text-red-400'
                                : expiryInfo.isExpiringSoon
                                ? 'text-yellow-400'
                                : ''
                        }
                    >
                        {expiryInfo.isExpired && <span className={'mr-1'}>已到期</span>}
                        {expiryInfo.label}
                    </span>
                </StatBlock>
            )}
            {pointsPerDay !== null && pointsPerDay !== undefined && (
                <StatBlock icon={faCoins} title={'每日积分'}>
                    {pointsPerDay} <span className={'text-gray-400 text-xs'}>积分/天</span>
                </StatBlock>
            )}
        </div>
    );
};

export default ServerDetailsBlock;
