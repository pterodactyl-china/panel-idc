import http from '@/api/http';

export interface ServerConfig {
    location: { id: number; short: string; long: string } | null;
    node: { id: number; name: string } | null;
    cpu: number | null;
    memory: number | null;
    disk: number | null;
    databases: number | null;
    backups: number | null;
    allocations: number | null;
}

export interface Product {
    id: number;
    name: string;
    description: string | null;
    type: string;
    value: number;
    price: number;
    currency: string;
    server_config?: ServerConfig;
}

export interface StoreData {
    products: Product[];
    payment_methods: string[];
}

export default (): Promise<StoreData> => {
    return new Promise((resolve, reject) => {
        http.get('/api/client/store')
            .then(({ data }) => resolve(data))
            .catch(reject);
    });
};
