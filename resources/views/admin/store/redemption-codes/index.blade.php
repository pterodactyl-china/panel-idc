@extends('layouts.admin')

@section('title')
    兑换码管理
@endsection

@section('content-header')
    <h1>兑换码管理<small>创建和管理用户兑换码。</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">管理</a></li>
        <li class="active">兑换码管理</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-md-5">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">创建兑换码</h3>
            </div>
            <form action="{{ route('admin.store.redemption-codes') }}" method="POST">
                @csrf
                <div class="box-body">
                    <div class="form-group">
                        <label for="pCode">兑换码（留空自动生成）</label>
                        <input type="text" id="pCode" name="code" class="form-control" value="{{ old('code') }}" maxlength="64" placeholder="留空则自动生成">
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="pType">奖励类型 <span class="text-danger">*</span></label>
                            <select id="pType" name="type" class="form-control">
                                <option value="points" {{ old('type') === 'points' ? 'selected' : '' }}>积分</option>
                                <option value="days" {{ old('type') === 'days' ? 'selected' : '' }}>天数</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="pValue">奖励数量 <span class="text-danger">*</span></label>
                            <input type="number" id="pValue" name="value" class="form-control" value="{{ old('value', 100) }}" min="1" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="pUsesTotal">可用次数 <span class="text-danger">*</span></label>
                            <input type="number" id="pUsesTotal" name="uses_total" class="form-control" value="{{ old('uses_total', 1) }}" min="1" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="pExpiresAt">到期时间</label>
                            <input type="datetime-local" id="pExpiresAt" name="expires_at" class="form-control" value="{{ old('expires_at') }}">
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary pull-right">创建兑换码</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-xs-12 col-md-7">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">兑换码列表</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>兑换码</th>
                            <th>类型</th>
                            <th>数量</th>
                            <th>剩余次数</th>
                            <th>到期时间</th>
                            <th>状态</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($codes as $code)
                            <tr>
                                <td><code>{{ $code->id }}</code></td>
                                <td><code>{{ $code->code }}</code></td>
                                <td>{{ $code->type === 'points' ? '积分' : '天数' }}</td>
                                <td>{{ $code->value }}</td>
                                <td>{{ $code->uses_remaining }} / {{ $code->uses_total }}</td>
                                <td>{{ $code->expires_at ? $code->expires_at->format('Y-m-d H:i') : '永久' }}</td>
                                <td>
                                    @if($code->is_active && ($code->expires_at === null || $code->expires_at->isFuture()) && $code->uses_remaining > 0)
                                        <span class="label label-success">有效</span>
                                    @else
                                        <span class="label label-default">已失效</span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('admin.store.redemption-codes.delete', $code->id) }}" method="POST" style="display:inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('确定删除此兑换码？')">删除</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($codes->hasPages())
                <div class="box-footer with-border">
                    <div class="col-md-12 text-center">{!! $codes->render() !!}</div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
