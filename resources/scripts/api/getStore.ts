import http from '@/api/http';

export interface Product {
    id: number;
    name: string;
    description: string | null;
    type: string;
    value: number;
    price: number;
    currency: string;
}

export interface StoreData {
    products: Product[];
}

export default (): Promise<StoreData> => {
    return new Promise((resolve, reject) => {
        http.get('/api/client/store')
            .then(({ data }) => resolve(data))
            .catch(reject);
    });
};
