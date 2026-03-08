import http from '@/api/http';

export interface Order {
    order_no: string;
    subject: string;
    product_name: string | null;
    amount: number;
    currency: string;
    status: string;
    payment_method: string | null;
    paid_at: string | null;
    created_at: string;
}

export interface OrdersResult {
    data: Order[];
    meta: {
        total: number;
        current_page: number;
        last_page: number;
    };
}

export const getOrders = (): Promise<OrdersResult> => {
    return new Promise((resolve, reject) => {
        http.get('/api/client/orders')
            .then(({ data }) => resolve(data))
            .catch(reject);
    });
};

export const cancelOrder = (orderNo: string): Promise<void> => {
    return new Promise((resolve, reject) => {
        http.delete(`/api/client/orders/${orderNo}`)
            .then(() => resolve())
            .catch(reject);
    });
};
