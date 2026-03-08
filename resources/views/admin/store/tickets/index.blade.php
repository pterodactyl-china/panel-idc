@extends('layouts.admin')

@section('title')
    工单管理
@endsection

@section('content-header')
    <h1>工单管理<small>查看和处理用户提交的支持工单。</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">管理</a></li>
        <li class="active">工单管理</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">工单列表</h3>
                <div class="box-tools search01">
                    <form action="{{ route('admin.store.tickets') }}" method="GET">
                        <div class="input-group input-group-sm">
                            <select name="filter[status]" class="form-control" style="width:auto">
                                <option value="">全部状态</option>
                                @foreach(['open'=>'待处理','in_progress'=>'处理中','closed'=>'已关闭'] as $v=>$l)
                                    <option value="{{ $v }}" {{ request()->input('filter.status') === $v ? 'selected' : '' }}>{{ $l }}</option>
                                @endforeach
                            </select>
                            <select name="filter[priority]" class="form-control" style="width:auto">
                                <option value="">全部优先级</option>
                                @foreach(['low'=>'低','normal'=>'普通','high'=>'高','urgent'=>'紧急'] as $v=>$l)
                                    <option value="{{ $v }}" {{ request()->input('filter.priority') === $v ? 'selected' : '' }}>{{ $l }}</option>
                                @endforeach
                            </select>
                            <div class="input-group-btn">
                                <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>标题</th>
                            <th>用户</th>
                            <th>优先级</th>
                            <th>状态</th>
                            <th>回复数</th>
                            <th>最后回复</th>
                            <th>创建时间</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tickets as $ticket)
                            <tr>
                                <td><code>#{{ $ticket->id }}</code></td>
                                <td><a href="{{ route('admin.store.tickets.view', $ticket->id) }}">{{ $ticket->title }}</a></td>
                                <td>
                                    @if($ticket->user)
                                        <a href="{{ route('admin.users.view', $ticket->user->id) }}">{{ $ticket->user->email }}</a>
                                    @else
                                        <span class="text-muted">已删除</span>
                                    @endif
                                </td>
                                <td>
                                    @switch($ticket->priority)
                                        @case('urgent') <span class="label label-danger">紧急</span> @break
                                        @case('high')   <span class="label label-warning">高</span> @break
                                        @case('normal') <span class="label label-info">普通</span> @break
                                        @default        <span class="label label-default">低</span>
                                    @endswitch
                                </td>
                                <td>
                                    @switch($ticket->status)
                                        @case('open')        <span class="label label-primary">待处理</span> @break
                                        @case('in_progress') <span class="label label-warning">处理中</span> @break
                                        @default             <span class="label label-default">已关闭</span>
                                    @endswitch
                                </td>
                                <td class="text-center">{{ $ticket->replies_count }}</td>
                                <td>{{ $ticket->last_reply_at ? $ticket->last_reply_at->format('m-d H:i') : '—' }}</td>
                                <td>{{ $ticket->created_at->format('m-d H:i') }}</td>
                                <td>
                                    <form action="{{ route('admin.store.tickets.delete', $ticket->id) }}" method="POST" style="display:inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('删除此工单？')">删除</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($tickets->hasPages())
                <div class="box-footer with-border">
                    <div class="col-md-12 text-center">{!! $tickets->appends(request()->input())->render() !!}</div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
