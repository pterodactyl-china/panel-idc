import React, { useState } from 'react';
import useSWR from 'swr';
import tw from 'twin.macro';
import { useParams, useHistory } from 'react-router-dom';
import PageContentBlock from '@/components/elements/PageContentBlock';
import Spinner from '@/components/elements/Spinner';
import ContentBox from '@/components/elements/ContentBox';
import { getTicket, replyTicket, closeTicket, TicketDetail } from '@/api/tickets';
import useFlash from '@/plugins/useFlash';

const statusLabel: Record<string, string> = {
    open: '待处理', in_progress: '处理中', closed: '已关闭',
};

const priorityLabel: Record<string, string> = {
    low: '低', normal: '普通', high: '高', urgent: '紧急',
};

export default () => {
    const { id } = useParams<{ id: string }>();
    const history = useHistory();
    const ticketId = parseInt(id, 10);

    const { clearFlashes, clearAndAddHttpError, addFlash } = useFlash();
    const [replyContent, setReplyContent] = useState('');
    const [submitting, setSubmitting] = useState(false);
    const [closing, setClosing] = useState(false);

    const { data, error, mutate } = useSWR<TicketDetail>(
        `/api/client/tickets/${ticketId}`,
        () => getTicket(ticketId)
    );

    React.useEffect(() => {
        if (error) clearAndAddHttpError({ key: 'ticket-detail', error });
        if (!error) clearFlashes('ticket-detail');
    }, [error]);

    const handleReply = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!replyContent.trim()) return;

        setSubmitting(true);
        try {
            await replyTicket(ticketId, replyContent.trim());
            setReplyContent('');
            addFlash({ type: 'success', key: 'ticket-detail', title: '回复已发送', message: '' });
            mutate();
        } catch (e) {
            clearAndAddHttpError({ key: 'ticket-detail', error: e as any });
        } finally {
            setSubmitting(false);
        }
    };

    const handleClose = async () => {
        if (!confirm('确定关闭此工单？关闭后将无法继续回复。')) return;
        setClosing(true);
        try {
            await closeTicket(ticketId);
            mutate();
        } catch (e) {
            clearAndAddHttpError({ key: 'ticket-detail', error: e as any });
        } finally {
            setClosing(false);
        }
    };

    if (!data && !error) {
        return <Spinner centered size={'large'} />;
    }

    if (!data) {
        return null;
    }

    return (
        <PageContentBlock title={`工单 #${data.id}`} showFlashKey={'ticket-detail'}>
            <div css={tw`mt-4 space-y-4`}>
                {/* Back button */}
                <button
                    css={tw`text-sm text-neutral-400 hover:text-white transition-colors duration-150`}
                    onClick={() => history.push('/tickets')}
                >
                    ← 返回工单列表
                </button>

                {/* Ticket header */}
                <div css={tw`bg-neutral-700 rounded-lg p-4 shadow-lg`}>
                    <div css={tw`flex items-start justify-between`}>
                        <div>
                            <h2 css={tw`text-white text-xl font-semibold`}>{data.title}</h2>
                            <div css={tw`flex gap-2 mt-2 flex-wrap`}>
                                <span css={tw`text-xs text-neutral-400`}>
                                    状态：<span css={tw`text-neutral-200`}>{statusLabel[data.status] ?? data.status}</span>
                                </span>
                                <span css={tw`text-xs text-neutral-400`}>
                                    优先级：<span css={tw`text-neutral-200`}>{priorityLabel[data.priority] ?? data.priority}</span>
                                </span>
                                <span css={tw`text-xs text-neutral-400`}>
                                    创建：<span css={tw`text-neutral-200`}>{new Date(data.created_at).toLocaleString('zh-CN')}</span>
                                </span>
                            </div>
                        </div>
                        {data.status !== 'closed' && (
                            <button
                                css={tw`bg-neutral-600 hover:bg-neutral-500 text-white text-xs px-3 py-1 rounded transition-colors duration-150 disabled:opacity-50`}
                                onClick={handleClose}
                                disabled={closing}
                            >
                                关闭工单
                            </button>
                        )}
                    </div>
                </div>

                {/* Conversation */}
                <div css={tw`space-y-3`}>
                    {/* Initial message */}
                    <div css={tw`bg-neutral-700 rounded-lg p-4`}>
                        <p css={tw`text-xs text-neutral-400 mb-2`}>
                            初始消息 · {new Date(data.created_at).toLocaleString('zh-CN')}
                        </p>
                        <p css={tw`text-neutral-200 text-sm whitespace-pre-wrap`}>{data.content}</p>
                    </div>

                    {/* Replies */}
                    {data.replies.map((reply) => (
                        <div
                            key={reply.id}
                            css={[
                                tw`rounded-lg p-4`,
                                reply.is_staff ? tw`bg-blue-900 bg-opacity-40` : tw`bg-neutral-700`,
                            ]}
                        >
                            <p css={tw`text-xs text-neutral-400 mb-2`}>
                                {reply.is_staff ? (
                                    <span css={tw`text-yellow-400 font-medium`}>工作人员</span>
                                ) : (
                                    <span css={tw`text-cyan-400`}>{reply.user_name}</span>
                                )}
                                {' · '}
                                {new Date(reply.created_at).toLocaleString('zh-CN')}
                            </p>
                            <p css={tw`text-neutral-200 text-sm whitespace-pre-wrap`}>{reply.content}</p>
                        </div>
                    ))}
                </div>

                {/* Reply form */}
                {data.status !== 'closed' && (
                    <ContentBox title={'添加回复'}>
                        <form onSubmit={handleReply} css={tw`space-y-3`}>
                            <textarea
                                value={replyContent}
                                onChange={(e) => setReplyContent(e.target.value)}
                                css={tw`w-full bg-neutral-800 border border-neutral-600 text-white rounded px-3 py-2 text-sm focus:outline-none focus:border-cyan-500`}
                                rows={5}
                                placeholder={'输入您的回复…'}
                                required
                            />
                            <button
                                type={'submit'}
                                css={tw`bg-cyan-500 hover:bg-cyan-600 text-white text-sm px-5 py-2 rounded transition-colors duration-150 disabled:opacity-50`}
                                disabled={submitting || !replyContent.trim()}
                            >
                                {submitting ? '发送中…' : '发送回复'}
                            </button>
                        </form>
                    </ContentBox>
                )}

                {data.status === 'closed' && (
                    <p css={tw`text-center text-sm text-neutral-500 py-4`}>工单已关闭，如需继续请联系管理员重新开启。</p>
                )}
            </div>
        </PageContentBlock>
    );
};
