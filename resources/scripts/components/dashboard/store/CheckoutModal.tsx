import React, { useState } from 'react';
import tw from 'twin.macro';
import Modal from '@/components/elements/Modal';
import { Product } from '@/api/getStore';
import { OrderResponse, PaymentMethod } from '@/api/createOrder';

const ALL_PAYMENT_METHODS: { key: PaymentMethod; label: string; description: string }[] = [
    {
        key: 'alipay',
        label: '支付宝（在线）',
        description: '使用支付宝 APP 扫码支付',
    },
    {
        key: 'alipay_face',
        label: '支付宝（面对面）',
        description: '出示付款码或扫描对方收款码',
    },
    {
        key: 'wechat',
        label: '微信支付',
        description: '使用微信扫描支付二维码',
    },
];

interface Props {
    product: Product | null;
    isOpen: boolean;
    enabledMethods: string[];
    onClose: () => void;
    onCheckout: (paymentMethod: PaymentMethod) => Promise<OrderResponse | undefined>;
}

export default ({ product, isOpen, enabledMethods, onClose, onCheckout }: Props) => {
    const available = ALL_PAYMENT_METHODS.filter((m) => enabledMethods.includes(m.key));
    const [selectedMethod, setSelectedMethod] = useState<PaymentMethod>(
        available.length > 0 ? available[0].key : 'alipay'
    );
    const [loading, setLoading] = useState(false);
    const [orderResult, setOrderResult] = useState<OrderResponse | null>(null);

    const handleSubmit = async () => {
        setLoading(true);
        try {
            const result = await onCheckout(selectedMethod);
            if (result) {
                setOrderResult(result);
            }
        } catch {
            // Error is handled by the parent (StoreContainer) via flash messages.
        } finally {
            setLoading(false);
        }
    };

    const handleClose = () => {
        setOrderResult(null);
        onClose();
    };

    if (!product) return null;

    return (
        <Modal visible={isOpen} onDismissed={handleClose} showSpinnerOverlay={loading} dismissable>
            <div css={tw`p-4`}>
                {orderResult ? (
                    <div>
                        <h2 css={tw`text-xl font-bold text-white mb-4`}>订单已创建</h2>
                        <div css={tw`bg-neutral-800 rounded p-4 mb-4`}>
                            <p css={tw`text-neutral-300 text-sm mb-2`}>
                                订单号：<span css={tw`text-white font-mono`}>{orderResult.order_no}</span>
                            </p>
                            <p css={tw`text-neutral-300 text-sm mb-4`}>
                                金额：
                                <span css={tw`text-cyan-400 font-bold text-lg`}>
                                    ¥{orderResult.amount.toFixed(2)}
                                </span>
                            </p>
                            {orderResult.payment_info.instructions && (
                                <p css={tw`text-neutral-400 text-sm mb-2`}>{orderResult.payment_info.instructions}</p>
                            )}
                            {orderResult.payment_info.qr_placeholder && (
                                <div css={tw`bg-white p-4 rounded inline-block mt-2`}>
                                    {orderResult.payment_info.qr_placeholder.startsWith('data:image') ? (
                                        <img
                                            src={orderResult.payment_info.qr_placeholder}
                                            alt={'收款码'}
                                            css={tw`max-w-xs`}
                                        />
                                    ) : (
                                        <>
                                            <p css={tw`text-gray-500 text-xs text-center`}>（支付二维码将在此显示）</p>
                                            <p css={tw`text-gray-400 text-xs text-center mt-1 break-all`}>
                                                {orderResult.payment_info.qr_placeholder}
                                            </p>
                                        </>
                                    )}
                                </div>
                            )}
                        </div>
                        <button
                            css={tw`w-full bg-neutral-600 hover:bg-neutral-500 text-white py-2 rounded transition-colors duration-150`}
                            onClick={handleClose}
                        >
                            关闭
                        </button>
                    </div>
                ) : (
                    <div>
                        <h2 css={tw`text-xl font-bold text-white mb-1`}>购买确认</h2>
                        <p css={tw`text-neutral-400 text-sm mb-4`}>
                            {product.name} — ¥{product.price.toFixed(2)}
                        </p>

                        {available.length === 0 ? (
                            <div css={tw`bg-red-900 bg-opacity-40 border border-red-700 rounded p-4 mb-4`}>
                                <p css={tw`text-red-400 text-sm`}>当前暂无可用的支付方式，请联系管理员开启支付方式。</p>
                            </div>
                        ) : (
                            <>
                                <p css={tw`text-neutral-300 text-sm mb-3`}>请选择支付方式：</p>
                                <div css={tw`space-y-2 mb-6`}>
                                    {available.map((m) => (
                                        <label
                                            key={m.key}
                                            css={[
                                                tw`flex items-start p-3 rounded border cursor-pointer transition-colors duration-150`,
                                                selectedMethod === m.key
                                                    ? tw`border-cyan-500 bg-cyan-900 bg-opacity-30`
                                                    : tw`border-neutral-600 hover:border-neutral-400`,
                                            ]}
                                        >
                                            <input
                                                type={'radio'}
                                                name={'payment_method'}
                                                value={m.key}
                                                checked={selectedMethod === m.key}
                                                onChange={() => setSelectedMethod(m.key)}
                                                css={tw`mt-1 mr-3`}
                                            />
                                            <div>
                                                <p css={tw`text-white text-sm font-medium`}>{m.label}</p>
                                                <p css={tw`text-neutral-400 text-xs mt-0.5`}>{m.description}</p>
                                            </div>
                                        </label>
                                    ))}
                                </div>
                            </>
                        )}

                        <div css={tw`flex gap-3`}>
                            <button
                                css={tw`flex-1 bg-neutral-600 hover:bg-neutral-500 text-white py-2 rounded transition-colors duration-150`}
                                onClick={handleClose}
                            >
                                取消
                            </button>
                            {available.length > 0 && (
                                <button
                                    css={tw`flex-1 bg-cyan-500 hover:bg-cyan-600 text-white py-2 rounded transition-colors duration-150`}
                                    onClick={handleSubmit}
                                    disabled={loading}
                                >
                                    确认支付
                                </button>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </Modal>
    );
};
