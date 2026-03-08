@extends('layouts.admin')

@section('title')
    {{ $product ? '编辑商品' : '新建商品' }}
@endsection

@section('content-header')
    <h1>{{ $product ? '编辑商品' : '新建商品' }}<small>{{ $product ? '修改现有商品信息' : '在商店中新增商品' }}</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">管理</a></li>
        <li><a href="{{ route('admin.store.products') }}">商品管理</a></li>
        <li class="active">{{ $product ? '编辑' : '新建' }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">商品信息</h3>
            </div>
            <form action="{{ $product ? route('admin.store.products.edit', $product->id) : route('admin.store.products') }}" method="POST">
                @csrf
                @if($product) @method('POST') @endif
                <div class="box-body">
                    <div class="form-group">
                        <label for="pName">名称 <span class="text-danger">*</span></label>
                        <input type="text" id="pName" name="name" class="form-control" value="{{ old('name', $product?->name) }}" required>
                    </div>
                    <div class="form-group">
                        <label for="pDescription">描述</label>
                        <textarea id="pDescription" name="description" class="form-control" rows="3">{{ old('description', $product?->description) }}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="pType">类型 <span class="text-danger">*</span></label>
                            <select id="pType" name="type" class="form-control">
                                <option value="points" {{ old('type', $product?->type) === 'points' ? 'selected' : '' }}>积分</option>
                                <option value="server_days" {{ old('type', $product?->type) === 'server_days' ? 'selected' : '' }}>服务器天数</option>
                                <option value="custom" {{ old('type', $product?->type) === 'custom' ? 'selected' : '' }}>自定义</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="pValue">内容数量 <span class="text-danger">*</span></label>
                            <input type="number" id="pValue" name="value" class="form-control" value="{{ old('value', $product?->value ?? 0) }}" min="0" required>
                            <p class="text-muted small">如购买积分则填写积分数量；购买天数则填写天数。</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="pPrice">价格 <span class="text-danger">*</span></label>
                            <input type="number" id="pPrice" name="price" class="form-control" value="{{ old('price', $product?->price ?? '0.00') }}" min="0" step="0.01" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="pCurrency">货币</label>
                            <input type="text" id="pCurrency" name="currency" class="form-control" value="{{ old('currency', $product?->currency ?? 'CNY') }}" maxlength="8">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="pSortOrder">排序权重</label>
                            <input type="number" id="pSortOrder" name="sort_order" class="form-control" value="{{ old('sort_order', $product?->sort_order ?? 0) }}" min="0">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>上架状态</label>
                            <div>
                                <div class="radio radio-success radio-inline">
                                    <input type="radio" id="pActiveTrue" name="is_active" value="1" {{ old('is_active', $product?->is_active ?? 1) ? 'checked' : '' }}>
                                    <label for="pActiveTrue">上架</label>
                                </div>
                                <div class="radio radio-warning radio-inline">
                                    <input type="radio" id="pActiveFalse" name="is_active" value="0" {{ !old('is_active', $product?->is_active ?? 1) ? 'checked' : '' }}>
                                    <label for="pActiveFalse">下架</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <a href="{{ route('admin.store.products') }}" class="btn btn-default">返回</a>
                    <button type="submit" class="btn btn-primary pull-right">{{ $product ? '保存更改' : '创建商品' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
