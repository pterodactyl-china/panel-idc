import React, { useState } from 'react';
import useSWR from 'swr';
import tw from 'twin.macro';
import { useHistory } from 'react-router-dom';
import PageContentBlock from '@/components/elements/PageContentBlock';
import Spinner from '@/components/elements/Spinner';
import ContentBox from '@/components/elements/ContentBox';
import { getTickets, createTicket, TicketSummary, TicketsResult } from '@/api/tickets';
import useFlash from '@/plugins/useFlash';

const statusColor: Record<string, string> = {
    open:        '#3498db',
    in_progress: '#f39c12',
    closed:      '#7f8c8d',
};

const statusLabel: Record<string, string> = {
    open:        '待处理',
    in_progress: '处理中',
    closed:      '已关闭',
};

const priorityColor: Record<string, string> = {
    low:    '#7f8c8d',
    normal: '#3498db',
    high:   '#e67e22',
    urgent: '#e74c3c',
};

const priorityLabel: Record<string, string> = {
    low: '低', normal: '普通', high: '高', urgent: '紧急',
};

export default () => {
    const history = useHistory();
    const { clearFlashes, clearAndAddHttpError, addFlash } = useFlash();

    const [showForm, setShowForm] = useState(false);
    const [title, setTitle] = useState('');
    const [content, setContent] = useState('');
    const [priority, setPriority] = useState('normal');
    const [submitting, setSubmitting] = useState(false);

    const { data, error, mutate } = useSWR<TicketsResult>('/api/client/tickets', () => getTickets());

    React.useEffect(() => {
        if (error) clearAndAddHttpError({ key: 'tickets', error });
        if (!error) clearFlashes('tickets');
    }, [error]);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!title.trim() || !content.trim()) return;

        setSubmitting(true);
        try {
            const ticket = await createTicket(title.trim(), content.trim(), priority);
            addFlash({ type: 'success', key: 'tickets', title: '工单已提交', message: `工单 #${ticket.id} 已成功创建。` });
            setTitle('');
            setContent('');
            setShowForm(false);
            mutate();
        } catch (e) {
            clearAndAddHttpError({ key: 'tickets', error: e as any });
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <PageContentBlock title={'我的工单'} showFlashKey={'tickets'}>
            <div css={tw`mt-4 space-y-4`}>
                {/* Create ticket button */}
                {!showForm && (
                    <button
                        css={tw`bg-cyan-500 hover:bg-cyan-600 text-white text-sm px-4 py-2 rounded transition-colors duration-150`}
                        onClick={() => setShowForm(true)}
                    >
                        + 新建工单
                    </button>
                )}

                {/* Create ticket form */}
                {showForm && (
                    <ContentBox title={'提交新工单'}>
                        <form onSubmit={handleSubmit} css={tw`space-y-4`}>
                            <div>
                                <label css={tw`block text-neutral-300 text-sm mb-1`}>标题</label>
                                <input
                                    type={'text'}
                                    value={title}
                                    onChange={(e) => setTitle(e.target.value)}
                                    css={tw`w-full bg-neutral-800 border border-neutral-600 text-white rounded px-3 py-2 text-sm focus:outline-none focus:border-cyan-500`}
                                    placeholder={'简要描述您的问题'}
                                    required
                                />
                            </div>
                            <div>
                                <label css={tw`block text-neutral-300 text-sm mb-1`}>内容</label>
                                <textarea
                                    value={content}
                                    onChange={(e) => setContent(e.target.value)}
                                    css={tw`w-full bg-neutral-800 border border-neutral-600 text-white rounded px-3 py-2 text-sm focus:outline-none focus:border-cyan-500`}
                                    rows={6}
                                    placeholder={'详细描述您遇到的问题…'}
                                    required
                                />
                            </div>
                            <div>
                                <label css={tw`block text-neutral-300 text-sm mb-1`}>优先级</label>
                                <select
                                    value={priority}
                                    onChange={(e) => setPriority(e.target.value)}
                                    css={tw`bg-neutral-800 border border-neutral-600 text-white rounded px-3 py-2 text-sm focus:outline-none focus:border-cyan-500`}
                                >
                                    <option value={'low'}>低</option>
                                    <option value={'normal'}>普通</option>
                                    <option value={'high'}>高</option>
                                    <option value={'urgent'}>紧急</option>
                                </select>
                            </div>
                            <div css={tw`flex gap-3`}>
                                <button
                                    type={'button'}
                                    css={tw`bg-neutral-600 hover:bg-neutral-500 text-white text-sm px-4 py-2 rounded transition-colors duration-150`}
                                    onClick={() => setShowForm(false)}
                                >
                                    取消
                                </button>
                                <button
                                    type={'submit'}
                                    css={tw`bg-cyan-500 hover:bg-cyan-600 text-white text-sm px-4 py-2 rounded transition-colors duration-150 disabled:opacity-50`}
                                    disabled={submitting}
                                >
                                    {submitting ? '提交中…' : '提交工单'}
                                </button>
                            </div>
                        </form>
                    </ContentBox>
                )}

                {/* Tickets list */}
                {!data ? (
                    <Spinner centered size={'large'} />
                ) : data.data.length === 0 ? (
                    <p css={tw`text-center text-sm text-neutral-400 mt-4`}>暂无工单。点击上方按钮创建您的第一个工单。</p>
                ) : (
                    <ContentBox>
                        <div css={tw`divide-y divide-neutral-600`}>
                            {data.data.map((ticket: TicketSummary) => (
                                <div
                                    key={ticket.id}
                                    css={tw`py-4 flex items-center justify-between cursor-pointer hover:bg-neutral-600 hover:bg-opacity-30 px-2 rounded transition-colors duration-150`}
                                    onClick={() => history.push(`/tickets/${ticket.id}`)}
                                >
                                    <div css={tw`flex-1`}>
                                        <div css={tw`flex items-center gap-2 flex-wrap`}>
                                            <span css={tw`text-neutral-400 text-xs font-mono`}>#{ticket.id}</span>
                                            <span
                                                css={tw`text-xs px-2 py-0.5 rounded-full`}
                                                style={{ color: statusColor[ticket.status], border: `1px solid ${statusColor[ticket.status]}` }}
                                            >
                                                {statusLabel[ticket.status] ?? ticket.status}
                                            </span>
                                            <span
                                                css={tw`text-xs px-2 py-0.5 rounded-full`}
                                                style={{ color: priorityColor[ticket.priority], border: `1px solid ${priorityColor[ticket.priority]}` }}
                                            >
                                                {priorityLabel[ticket.priority] ?? ticket.priority}
                                            </span>
                                        </div>
                                        <p css={tw`text-neutral-200 text-sm mt-1`}>{ticket.title}</p>
                                        <p css={tw`text-neutral-500 text-xs mt-0.5`}>
                                            {ticket.replies_count} 条回复 · {new Date(ticket.created_at).toLocaleDateString('zh-CN')}
                                        </p>
                                    </div>
                                    <span css={tw`text-neutral-500 text-xs ml-4`}>›</span>
                                </div>
                            ))}
                        </div>
                    </ContentBox>
                )}
            </div>
        </PageContentBlock>
    );
};
