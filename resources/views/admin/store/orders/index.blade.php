@extends('layouts.admin')

@section('title')
    订单管理
@endsection

@section('content-header')
    <h1>订单管理<small>查看所有用户的支付订单。</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">管理</a></li>
        <li class="active">订单管理</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">订单列表</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>订单号</th>
                            <th>用户</th>
                            <th>商品</th>
                            <th>金额</th>
                            <th>支付方式</th>
                            <th>状态</th>
                            <th>创建时间</th>
                            <th>支付时间</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td><code>{{ $order->order_no }}</code></td>
                                <td>
                                    @if($order->user)
                                        <a href="{{ route('admin.users.view', $order->user->id) }}">{{ $order->user->email }}</a>
                                    @else
                                        <span class="text-muted">已删除用户</span>
                                    @endif
                                </td>
                                <td>{{ $order->product?->name ?? $order->subject }}</td>
                                <td>{{ $order->currency === 'CNY' ? '¥' : $order->currency }}{{ number_format($order->amount, 2) }}</td>
                                <td>
                                    @switch($order->payment_method)
                                        @case('alipay') 支付宝 @break
                                        @case('alipay_face') 支付宝（面对面）@break
                                        @case('wechat') 微信支付 @break
                                        @default {{ $order->payment_method ?? '—' }}
                                    @endswitch
                                </td>
                                <td>
                                    @switch($order->status)
                                        @case('paid')
                                            <span class="label label-success">已支付</span> @break
                                        @case('pending')
                                            <span class="label label-warning">待支付</span> @break
                                        @case('cancelled')
                                            <span class="label label-default">已取消</span> @break
                                        @case('refunded')
                                            <span class="label label-info">已退款</span> @break
                                        @default
                                            <span class="label label-default">{{ $order->status }}</span>
                                    @endswitch
                                </td>
                                <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                                <td>{{ $order->paid_at ? $order->paid_at->format('Y-m-d H:i') : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($orders->hasPages())
                <div class="box-footer with-border">
                    <div class="col-md-12 text-center">{!! $orders->render() !!}</div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
