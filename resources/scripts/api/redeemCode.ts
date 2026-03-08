import http from '@/api/http';

export interface RedeemResult {
    success: boolean;
    type: string;
    value: number;
    message: string;
}

export default (code: string): Promise<RedeemResult> => {
    return new Promise((resolve, reject) => {
        http.post('/api/client/redeem', { code })
            .then(({ data }) => resolve(data))
            .catch(reject);
    });
};
