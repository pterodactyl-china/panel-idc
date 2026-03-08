import http from '@/api/http';

export interface TicketSummary {
    id: number;
    title: string;
    status: string;
    priority: string;
    replies_count: number;
    last_reply_at: string | null;
    created_at: string;
}

export interface TicketReply {
    id: number;
    content: string;
    is_staff: boolean;
    user_name: string;
    created_at: string;
}

export interface TicketDetail extends TicketSummary {
    content: string;
    replies: TicketReply[];
}

export interface TicketsResult {
    data: TicketSummary[];
    meta: {
        total: number;
        current_page: number;
        last_page: number;
    };
}

export const getTickets = (): Promise<TicketsResult> => {
    return new Promise((resolve, reject) => {
        http.get('/api/client/tickets')
            .then(({ data }) => resolve(data))
            .catch(reject);
    });
};

export const getTicket = (id: number): Promise<TicketDetail> => {
    return new Promise((resolve, reject) => {
        http.get(`/api/client/tickets/${id}`)
            .then(({ data }) => resolve(data.ticket))
            .catch(reject);
    });
};

export const createTicket = (title: string, content: string, priority?: string): Promise<TicketSummary> => {
    return new Promise((resolve, reject) => {
        http.post('/api/client/tickets', { title, content, priority: priority ?? 'normal' })
            .then(({ data }) => resolve(data.ticket))
            .catch(reject);
    });
};

export const replyTicket = (id: number, content: string): Promise<void> => {
    return new Promise((resolve, reject) => {
        http.post(`/api/client/tickets/${id}/reply`, { content })
            .then(() => resolve())
            .catch(reject);
    });
};

export const closeTicket = (id: number): Promise<void> => {
    return new Promise((resolve, reject) => {
        http.post(`/api/client/tickets/${id}/close`, {})
            .then(() => resolve())
            .catch(reject);
    });
};
