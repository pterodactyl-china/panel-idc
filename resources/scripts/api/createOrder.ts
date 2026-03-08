import http from '@/api/http';

export interface OrderPaymentInfo {
    type: string;
    instructions: string;
    qr_placeholder: string;
}

export interface OrderResponse {
    order_no: string;
    amount: number;
    currency: string;
    payment_method: string;
    payment_info: OrderPaymentInfo;
}

export interface OrderStatus {
    order_no: string;
    status: string;
    amount: number;
    paid_at: string | null;
    payment_info?: OrderPaymentInfo;
}

export type PaymentMethod = 'alipay' | 'alipay_face' | 'wechat';

export const createOrder = (productId: number, paymentMethod: PaymentMethod): Promise<OrderResponse> => {
    return new Promise((resolve, reject) => {
        http.post('/api/client/store/order', { product_id: productId, payment_method: paymentMethod })
            .then(({ data }) => resolve(data))
            .catch(reject);
    });
};

export const queryOrder = (orderNo: string): Promise<OrderStatus> => {
    return new Promise((resolve, reject) => {
        http.get(`/api/client/store/order/${orderNo}`)
            .then(({ data }) => resolve(data))
            .catch(reject);
    });
};
