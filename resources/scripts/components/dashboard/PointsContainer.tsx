import React from 'react';
import useSWR from 'swr';
import tw from 'twin.macro';
import PageContentBlock from '@/components/elements/PageContentBlock';
import Spinner from '@/components/elements/Spinner';
import ContentBox from '@/components/elements/ContentBox';
import getPoints, { PointsData } from '@/api/getPoints';
import getServers from '@/api/getServers';
import useFlash from '@/plugins/useFlash';
import { PaginatedResult } from '@/api/http';
import { Server } from '@/api/server/getServer';

const typeLabel: Record<string, string> = {
    earn: '购买获得',
    spend: '消费',
    refund: '退款',
    admin_adjust: '管理员调整',
    redeem_code: '兑换码',
};

export default () => {
    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const uuid = useStoreState((state) => state.user.data!.uuid);

    const { data, error } = useSWR<PointsData>('/api/client/points', () => getPoints());
    const { data: serversData } = useSWR<PaginatedResult<Server>>(
        ['/api/client/servers', false, 1],
        () => getServers({ page: 1 })
    );

    React.useEffect(() => {
        if (error) clearAndAddHttpError({ key: 'points', error });
        if (!error) clearFlashes('points');
    }, [error]);

    const serversWithCost = serversData?.items.filter((s) => s.pointsPerDay) ?? [];
    const totalDailyCost = serversWithCost.reduce((sum, s) => sum + (s.pointsPerDay ?? 0), 0);

    return (
        <PageContentBlock title={'我的积分'} showFlashKey={'points'}>
            {!data ? (
                <Spinner centered size={'large'} />
            ) : (
                <div css={tw`mt-4 space-y-6`}>
                    {/* Balance + daily cost card */}
                    <div css={tw`grid grid-cols-1 sm:grid-cols-2 gap-4`}>
                        <div css={tw`bg-neutral-700 rounded-lg p-6 shadow-lg flex items-center justify-between`}>
                            <div>
                                <p css={tw`text-neutral-400 text-sm`}>当前积分余额</p>
                                <p css={tw`text-5xl font-bold text-cyan-400 mt-1`}>{data.balance.toLocaleString()}</p>
                            </div>
                            <div css={tw`text-6xl opacity-10`}>⭐</div>
                        </div>
                        <div css={tw`bg-neutral-700 rounded-lg p-6 shadow-lg flex items-center justify-between`}>
                            <div>
                                <p css={tw`text-neutral-400 text-sm`}>每日扣除（所有服务器）</p>
                                <p css={tw`text-5xl font-bold text-yellow-400 mt-1`}>{totalDailyCost.toLocaleString()}</p>
                                {data.balance > 0 && totalDailyCost > 0 && (
                                    <p css={tw`text-neutral-500 text-xs mt-2`}>
                                        约可维持 {Math.floor(data.balance / totalDailyCost)} 天
                                        {serversData && serversData.pagination.total > serversData.pagination.perPage && (
                                            <span css={tw`text-neutral-600`}> (仅计算当前页)</span>
                                        )}
                                    </p>
                                )}
                            </div>
                            <div css={tw`text-6xl opacity-10`}>🕐</div>
                        </div>
                    </div>

                    {/* Per-server cost breakdown */}
                    {serversWithCost.length > 0 && (
                        <ContentBox title={'服务器积分消耗明细'}>
                            <div css={tw`divide-y divide-neutral-600`}>
                                {serversWithCost.map((server) => (
                                    <div key={server.uuid} css={tw`flex items-center justify-between py-3`}>
                                        <div>
                                            <p css={tw`text-neutral-200 text-sm`}>{server.name}</p>
                                            <p css={tw`text-neutral-500 text-xs mt-0.5`}>
                                                CPU {server.limits.cpu}% / 内存 {server.limits.memory}MB / 磁盘 {server.limits.disk}MB
                                            </p>
                                        </div>
                                        <span css={tw`text-yellow-400 font-bold text-sm`}>
                                            {server.pointsPerDay} 积分/天
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </ContentBox>
                    )}

                    {/* Transaction history */}
                    <ContentBox title={'积分明细'}>
                        {data.transactions.length === 0 ? (
                            <p css={tw`text-center text-sm text-neutral-400 py-4`}>暂无积分记录。</p>
                        ) : (
                            <div css={tw`divide-y divide-neutral-600`}>
                                {data.transactions.map((t) => (
                                    <div key={t.id} css={tw`flex items-center justify-between py-3`}>
                                        <div>
                                            <p css={tw`text-neutral-200 text-sm`}>
                                                {typeLabel[t.type] || t.type}
                                            </p>
                                            {t.description && (
                                                <p css={tw`text-neutral-500 text-xs mt-0.5`}>{t.description}</p>
                                            )}
                                            <p css={tw`text-neutral-600 text-xs mt-0.5`}>
                                                {new Date(t.created_at).toLocaleString('zh-CN')}
                                            </p>
                                        </div>
                                        <span
                                            css={[
                                                tw`font-bold text-lg`,
                                                t.amount >= 0 ? tw`text-green-400` : tw`text-red-400`,
                                            ]}
                                        >
                                            {t.amount >= 0 ? '+' : ''}
                                            {t.amount}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        )}
                    </ContentBox>
                </div>
            )}
        </PageContentBlock>
    );
};
