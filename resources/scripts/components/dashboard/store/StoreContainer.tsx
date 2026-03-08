import React, { useState } from 'react';
import useSWR from 'swr';
import tw from 'twin.macro';
import PageContentBlock from '@/components/elements/PageContentBlock';
import Spinner from '@/components/elements/Spinner';
import getStore, { Product } from '@/api/getStore';
import { createOrder, PaymentMethod } from '@/api/createOrder';
import useFlash from '@/plugins/useFlash';
import ContentBox from '@/components/elements/ContentBox';
import CheckoutModal from '@/components/dashboard/store/CheckoutModal';

export default () => {
    const { clearFlashes, clearAndAddHttpError, addFlash } = useFlash();
    const [selectedProduct, setSelectedProduct] = useState<Product | null>(null);
    const [isCheckoutOpen, setIsCheckoutOpen] = useState(false);

    const { data, error } = useSWR<{ products: Product[] }>('/api/client/store', () => getStore());

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
                return `${value} 天服务器时长`;
            default:
                return String(value);
        }
    };

    return (
        <PageContentBlock title={'商店'} showFlashKey={'store'}>
            <CheckoutModal
                product={selectedProduct}
                isOpen={isCheckoutOpen}
                onClose={() => setIsCheckoutOpen(false)}
                onCheckout={handleCheckout}
            />
            {!data ? (
                <Spinner centered size={'large'} />
            ) : data.products.length === 0 ? (
                <p css={tw`text-center text-sm text-neutral-400 mt-8`}>暂无商品。</p>
            ) : (
                <div css={tw`grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-4`}>
                    {data.products.map((product) => (
                        <ContentBox key={product.id} css={tw`flex flex-col`}>
                            <div css={tw`flex-1`}>
                                <h3 css={tw`text-neutral-100 text-lg font-semibold mb-2`}>{product.name}</h3>
                                {product.description && (
                                    <p css={tw`text-neutral-400 text-sm mb-3`}>{product.description}</p>
                                )}
                                <p css={tw`text-neutral-300 text-sm`}>
                                    内容：<span css={tw`text-white`}>{typeLabel(product.type, product.value)}</span>
                                </p>
                            </div>
                            <div css={tw`mt-4 flex items-center justify-between`}>
                                <span css={tw`text-2xl font-bold text-cyan-400`}>
                                    {currencyLabel(product.currency)}
                                    {product.price.toFixed(2)}
                                </span>
                                <button
                                    css={tw`bg-cyan-500 hover:bg-cyan-600 text-white text-sm px-4 py-2 rounded transition-colors duration-150`}
                                    onClick={() => handleBuy(product)}
                                >
                                    立即购买
                                </button>
                            </div>
                        </ContentBox>
                    ))}
                </div>
            )}
        </PageContentBlock>
    );
};
