@extends('layouts.admin')

@section('title')
    商品管理
@endsection

@section('content-header')
    <h1>商品管理<small>管理商店中的商品。</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">管理</a></li>
        <li class="active">商品管理</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">积分费率说明</h3>
            </div>
            <div class="box-body">
                <p class="text-muted">
                    当前积分计算费率（每天）：
                    <strong>CPU</strong> {{ $cpu_rate }} 积分/核心 &nbsp;|&nbsp;
                    <strong>内存</strong> {{ $memory_rate }} 积分/GB &nbsp;|&nbsp;
                    <strong>磁盘</strong> {{ $disk_rate }} 积分/GB
                </p>
                <p class="text-muted small">
                    示例（取整后取最小值1）：2核 CPU + 2GB 内存 + 10GB 磁盘 ≈ max(1, ceil(2×{{ $cpu_rate }} + 2×{{ $memory_rate }} + 10×{{ $disk_rate }})) = {{ max(1, (int) ceil(2 * $cpu_rate + 2 * $memory_rate + 10 * $disk_rate)) }} 积分/天
                </p>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">商品列表</h3>
                <div class="box-tools">
                    <a href="{{ route('admin.store.products.new') }}" class="btn btn-sm btn-primary">新建商品</a>
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>名称</th>
                            <th>类型</th>
                            <th>内容</th>
                            <th>价格</th>
                            <th>状态</th>
                            <th>排序</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr>
                                <td><code>{{ $product->id }}</code></td>
                                <td><a href="{{ route('admin.store.products.edit', $product->id) }}">{{ $product->name }}</a></td>
                                <td>
                                    @if($product->type === 'points') 积分
                                    @elseif($product->type === 'server_days') 服务器天数
                                    @else 自定义
                                    @endif
                                </td>
                                <td>{{ $product->value }}</td>
                                <td>{{ $product->currency === 'CNY' ? '¥' : $product->currency }}{{ number_format($product->price, 2) }}</td>
                                <td>
                                    @if($product->is_active)
                                        <span class="label label-success">已上架</span>
                                    @else
                                        <span class="label label-default">已下架</span>
                                    @endif
                                </td>
                                <td>{{ $product->sort_order }}</td>
                                <td>
                                    <form action="{{ route('admin.store.products.delete', $product->id) }}" method="POST" style="display:inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('确定删除此商品？')">删除</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($products->hasPages())
                <div class="box-footer with-border">
                    <div class="col-md-12 text-center">{!! $products->render() !!}</div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
