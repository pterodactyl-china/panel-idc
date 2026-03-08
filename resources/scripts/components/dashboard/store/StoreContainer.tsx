import React, { useState } from 'react';
import useSWR from 'swr';
import tw from 'twin.macro';
import PageContentBlock from '@/components/elements/PageContentBlock';
import Spinner from '@/components/elements/Spinner';
import getStore, { Product, StoreData } from '@/api/getStore';
import { createOrder, PaymentMethod } from '@/api/createOrder';
import useFlash from '@/plugins/useFlash';
import ContentBox from '@/components/elements/ContentBox';
import CheckoutModal from '@/components/dashboard/store/CheckoutModal';

export default () => {
    const { clearFlashes, clearAndAddHttpError, addFlash } = useFlash();
    const [selectedProduct, setSelectedProduct] = useState<Product | null>(null);
    const [isCheckoutOpen, setIsCheckoutOpen] = useState(false);

    const { data, error } = useSWR<StoreData>('/api/client/store', () => getStore());

    React.useEffect(() => {
        if (error) clearAndAddHttpError({ key: 'store', error });
        if (!error) clearFlashes('store');
    }, [error]);

    const handleBuy = (product: Product) => {
        setSelectedProduct(product);
        setIsCheckoutOpen(true);
    };

    const handleCheckout = async (paymentMethod: PaymentMethod) => {
        if (!selectedProduct) return;
        clearFlashes('store');
        try {
            const order = await createOrder(selectedProduct.id, paymentMethod);
            setIsCheckoutOpen(false);
            addFlash({
                type: 'success',
                key: 'store',
                title: '订单已创建',
                message: `订单号：${order.order_no}，请完成支付。`,
            });
            return order;
        } catch (e) {
            clearAndAddHttpError({ key: 'store', error: e as any });
        }
    };

    const currencyLabel = (currency: string) => (currency === 'CNY' ? '¥' : currency);

    const typeLabel = (type: string, value: number) => {
        switch (type) {
            case 'points':
                return `${value} 积分`;
            case 'server_days':
                return `延期 ${value} 天`;
            case 'server':
                return '服务器套餐';
            default:
                return String(value);
        }
    };

    return (
        <PageContentBlock title={'商店'} showFlashKey={'store'}>
            <CheckoutModal
                product={selectedProduct}
                isOpen={isCheckoutOpen}
                enabledMethods={data?.payment_methods ?? []}
                onClose={() => setIsCheckoutOpen(false)}
                onCheckout={handleCheckout}
            />
            {!data ? (
                <Spinner centered size={'large'} />
            ) : data.products.length === 0 ? (
                <p css={tw`text-center text-sm text-neutral-400 mt-8`}>暂无在售商品。</p>
            ) : (
                <div css={tw`grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-4`}>
                    {data.products.map((product) => (
                        <ContentBox key={product.id} css={tw`flex flex-col`}>
                            <div css={tw`flex-1`}>
                                <div css={tw`flex items-center justify-between mb-2`}>
                                    <h3 css={tw`text-neutral-100 text-lg font-semibold`}>{product.name}</h3>
                                    <span css={tw`text-xs px-2 py-0.5 rounded-full bg-neutral-600 text-neutral-300`}>
                                        {product.type === 'points'     ? '积分'
                                            : product.type === 'server_days' ? '延期'
                                            : product.type === 'server'      ? '服务器'
                                            : '其他'}
                                    </span>
                                </div>
                                {product.description && (
                                    <p css={tw`text-neutral-400 text-sm mb-3`}>{product.description}</p>
                                )}
                                <p css={tw`text-neutral-300 text-sm`}>
                                    内容：<span css={tw`text-white`}>{typeLabel(product.type, product.value)}</span>
                                </p>

                                {/* Server package resource details */}
                                {product.type === 'server' && product.server_config && (
                                    <div css={tw`mt-3 space-y-1`}>
                                        {product.server_config.location && (
                                            <p css={tw`text-neutral-400 text-xs`}>
                                                📍 {product.server_config.location.short} — {product.server_config.location.long}
                                            </p>
                                        )}
                                        {product.server_config.node && (
                                            <p css={tw`text-neutral-400 text-xs`}>
                                                🖥 节点：{product.server_config.node.name}
                                            </p>
                                        )}
                                        <div css={tw`flex flex-wrap gap-2 mt-2`}>
                                            {product.server_config.cpu != null && (
                                                <span css={tw`bg-neutral-600 text-neutral-200 text-xs px-2 py-0.5 rounded`}>
                                                    CPU {product.server_config.cpu}%
                                                </span>
                                            )}
                                            {product.server_config.memory != null && (
                                                <span css={tw`bg-neutral-600 text-neutral-200 text-xs px-2 py-0.5 rounded`}>
                                                    内存 {(product.server_config.memory / 1024).toFixed(1)}GB
                                                </span>
                                            )}
                                            {product.server_config.disk != null && (
                                                <span css={tw`bg-neutral-600 text-neutral-200 text-xs px-2 py-0.5 rounded`}>
                                                    磁盘 {(product.server_config.disk / 1024).toFixed(1)}GB
                                                </span>
                                            )}
                                            {product.server_config.databases != null && product.server_config.databases > 0 && (
                                                <span css={tw`bg-neutral-600 text-neutral-200 text-xs px-2 py-0.5 rounded`}>
                                                    DB×{product.server_config.databases}
                                                </span>
                                            )}
                                            {product.server_config.backups != null && product.server_config.backups > 0 && (
                                                <span css={tw`bg-neutral-600 text-neutral-200 text-xs px-2 py-0.5 rounded`}>
                                                    备份×{product.server_config.backups}
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                )}
                            </div>
                            <div css={tw`mt-4 flex items-center justify-between`}>
                                <span css={tw`text-2xl font-bold text-cyan-400`}>
                                    {currencyLabel(product.currency)}
                                    {product.price.toFixed(2)}
                                </span>
                                <button
                                    css={[
                                        tw`text-white text-sm px-4 py-2 rounded transition-colors duration-150`,
                                        data.payment_methods.length === 0
                                            ? tw`bg-neutral-600 cursor-not-allowed opacity-50`
                                            : tw`bg-cyan-500 hover:bg-cyan-600`,
                                    ]}
                                    onClick={() => handleBuy(product)}
                                    disabled={data.payment_methods.length === 0}
                                    title={data.payment_methods.length === 0 ? '暂无可用支付方式' : undefined}
                                >
                                    立即购买
                                </button>
                            </div>
                        </ContentBox>
                    ))}
                </div>
            )}
            {data && data.payment_methods.length === 0 && (
                <p css={tw`text-center text-xs text-neutral-500 mt-4`}>⚠ 管理员尚未开启任何支付方式，暂时无法购买。</p>
            )}
        </PageContentBlock>
    );
};
