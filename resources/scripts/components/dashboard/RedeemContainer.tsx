import React, { useState } from 'react';
import tw from 'twin.macro';
import PageContentBlock from '@/components/elements/PageContentBlock';
import ContentBox from '@/components/elements/ContentBox';
import redeemCode, { RedeemResult } from '@/api/redeemCode';
import useFlash from '@/plugins/useFlash';

export default () => {
    const { clearFlashes, clearAndAddHttpError, addFlash } = useFlash();
    const [code, setCode] = useState('');
    const [loading, setLoading] = useState(false);
    const [result, setResult] = useState<RedeemResult | null>(null);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!code.trim()) return;

        clearFlashes('redeem');
        setResult(null);
        setLoading(true);

        try {
            const res = await redeemCode(code.trim());
            setResult(res);
            setCode('');
            addFlash({
                type: 'success',
                key: 'redeem',
                title: '兑换成功',
                message: res.message,
            });
        } catch (e) {
            clearAndAddHttpError({ key: 'redeem', error: e as any });
        } finally {
            setLoading(false);
        }
    };

    return (
        <PageContentBlock title={'兑换码'} showFlashKey={'redeem'}>
            <div css={tw`max-w-lg mx-auto mt-8`}>
                <ContentBox title={'输入兑换码'}>
                    <p css={tw`text-neutral-400 text-sm mb-4`}>
                        输入您的兑换码以获取积分、延长服务器有效期或其他奖励。
                    </p>
                    <form onSubmit={handleSubmit}>
                        <div css={tw`flex gap-3`}>
                            <input
                                type={'text'}
                                value={code}
                                onChange={(e) => setCode(e.target.value)}
                                placeholder={'请输入兑换码…'}
                                css={tw`flex-1 bg-neutral-800 border border-neutral-600 text-white rounded px-3 py-2 text-sm focus:outline-none focus:border-cyan-500 transition-colors duration-150`}
                                disabled={loading}
                            />
                            <button
                                type={'submit'}
                                css={tw`bg-cyan-500 hover:bg-cyan-600 text-white text-sm px-5 py-2 rounded transition-colors duration-150 disabled:opacity-50`}
                                disabled={loading || !code.trim()}
                            >
                                {loading ? '兑换中…' : '立即兑换'}
                            </button>
                        </div>
                    </form>

                    {result && (
                        <div css={tw`mt-4 p-4 bg-green-900 bg-opacity-40 border border-green-700 rounded`}>
                            <p css={tw`text-green-400 text-sm font-medium`}>{result.message}</p>
                        </div>
                    )}
                </ContentBox>
            </div>
        </PageContentBlock>
    );
};
