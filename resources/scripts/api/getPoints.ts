import http from '@/api/http';

export interface PointTransaction {
    id: number;
    amount: number;
    type: string;
    description: string | null;
    created_at: string;
}

export interface PointsData {
    balance: number;
    transactions: PointTransaction[];
}

export default (): Promise<PointsData> => {
    return new Promise((resolve, reject) => {
        http.get('/api/client/points')
            .then(({ data }) => resolve(data))
            .catch(reject);
    });
};
