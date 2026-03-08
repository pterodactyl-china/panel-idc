import React, { useState } from 'react';
import useSWR from 'swr';
import tw from 'twin.macro';
import PageContentBlock from '@/components/elements/PageContentBlock';
import Spinner from '@/components/elements/Spinner';
import ContentBox from '@/components/elements/ContentBox';
import { getOrders, cancelOrder, Order, OrdersResult } from '@/api/getOrders';
import useFlash from '@/plugins/useFlash';

const statusLabel: Record<string, { text: string; color: string }> = {
    pending:   { text: '待支付', color: '#f39c12' },
    paid:      { text: '已支付', color: '#27ae60' },
    cancelled: { text: '已取消', color: '#7f8c8d' },
    refunded:  { text: '已退款', color: '#2980b9' },
};

const methodLabel: Record<string, string> = {
    alipay:      '支付宝',
    alipay_face: '支付宝面对面',
    wechat:      '微信支付',
};

export default () => {
    const { clearFlashes, clearAndAddHttpError, addFlash } = useFlash();
    const [cancelling, setCancelling] = useState<string | null>(null);

    const { data, error, mutate } = useSWR<OrdersResult>('/api/client/orders', () => getOrders());

    React.useEffect(() => {
        if (error) clearAndAddHttpError({ key: 'orders', error });
        if (!error) clearFlashes('orders');
    }, [error]);

    const handleCancel = async (orderNo: string) => {
        if (!confirm('确定取消此订单？')) return;
        setCancelling(orderNo);
        try {
            await cancelOrder(orderNo);
            addFlash({ type: 'success', key: 'orders', title: '订单已取消', message: `订单 ${orderNo} 已成功取消。` });
            mutate();
        } catch (e) {
            clearAndAddHttpError({ key: 'orders', error: e as any });
        } finally {
            setCancelling(null);
        }
    };

    return (
        <PageContentBlock title={'我的订单'} showFlashKey={'orders'}>
            {!data ? (
                <Spinner centered size={'large'} />
            ) : data.data.length === 0 ? (
                <p css={tw`text-center text-sm text-neutral-400 mt-8`}>暂无订单记录。</p>
            ) : (
                <ContentBox css={tw`mt-4`}>
                    <div css={tw`divide-y divide-neutral-600`}>
                        {data.data.map((order: Order) => {
                            const st = statusLabel[order.status] ?? { text: order.status, color: '#888' };
                            return (
                                <div key={order.order_no} css={tw`py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2`}>
                                    <div css={tw`flex-1`}>
                                        <div css={tw`flex items-center gap-2`}>
                                            <span css={tw`font-mono text-xs text-neutral-400`}>{order.order_no}</span>
                                            <span
                                                css={tw`text-xs px-2 py-0.5 rounded-full font-medium`}
                                                style={{ color: st.color, border: `1px solid ${st.color}` }}
                                            >
                                                {st.text}
                                            </span>
                                        </div>
                                        <p css={tw`text-neutral-200 text-sm mt-1`}>{order.product_name ?? order.subject}</p>
                                        <p css={tw`text-neutral-500 text-xs mt-0.5`}>
                                            {order.payment_method ? (methodLabel[order.payment_method] ?? order.payment_method) : '—'}
                                            &nbsp;·&nbsp;
                                            {new Date(order.created_at).toLocaleDateString('zh-CN')}
                                            {order.paid_at && (
                                                <span css={tw`text-green-500`}>&nbsp;· 支付于 {new Date(order.paid_at).toLocaleDateString('zh-CN')}</span>
                                            )}
                                        </p>
                                    </div>
                                    <div css={tw`flex items-center gap-3`}>
                                        <span css={tw`text-xl font-bold text-cyan-400`}>
                                            {order.currency === 'CNY' ? '¥' : order.currency}
                                            {order.amount.toFixed(2)}
                                        </span>
                                        {order.status === 'pending' && (
                                            <button
                                                css={tw`bg-red-700 hover:bg-red-600 text-white text-xs px-3 py-1 rounded transition-colors duration-150 disabled:opacity-50`}
                                                onClick={() => handleCancel(order.order_no)}
                                                disabled={cancelling === order.order_no}
                                            >
                                                {cancelling === order.order_no ? '取消中…' : '取消订单'}
                                            </button>
                                        )}
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </ContentBox>
            )}
        </PageContentBlock>
    );
};
