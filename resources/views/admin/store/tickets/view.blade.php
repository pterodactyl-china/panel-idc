@extends('layouts.admin')

@section('title')
    工单 #{{ $ticket->id }}
@endsection

@section('content-header')
    <h1>工单 <code>#{{ $ticket->id }}</code><small>{{ $ticket->title }}</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">管理</a></li>
        <li><a href="{{ route('admin.store.tickets') }}">工单管理</a></li>
        <li class="active">#{{ $ticket->id }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    {{-- Ticket info + status changer --}}
    <div class="col-xs-12 col-md-4">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title">工单信息</h3></div>
            <div class="box-body">
                <dl class="dl-horizontal" style="margin:0">
                    <dt>提交人</dt>
                    <dd>
                        @if($ticket->user)
                            <a href="{{ route('admin.users.view', $ticket->user->id) }}">{{ $ticket->user->email }}</a>
                        @else <span class="text-muted">已删除</span> @endif
                    </dd>
                    <dt>优先级</dt>
                    <dd>
                        @switch($ticket->priority)
                            @case('urgent') <span class="label label-danger">紧急</span> @break
                            @case('high')   <span class="label label-warning">高</span> @break
                            @case('normal') <span class="label label-info">普通</span> @break
                            @default        <span class="label label-default">低</span>
                        @endswitch
                    </dd>
                    <dt>当前状态</dt>
                    <dd>
                        @switch($ticket->status)
                            @case('open')        <span class="label label-primary">待处理</span> @break
                            @case('in_progress') <span class="label label-warning">处理中</span> @break
                            @default             <span class="label label-default">已关闭</span>
                        @endswitch
                    </dd>
                    <dt>创建时间</dt>
                    <dd>{{ $ticket->created_at->format('Y-m-d H:i') }}</dd>
                </dl>
            </div>
            <div class="box-footer">
                <form action="{{ route('admin.store.tickets.status', $ticket->id) }}" method="POST">
                    @csrf
                    <div class="input-group input-group-sm">
                        <select name="status" class="form-control">
                            @foreach(['open'=>'待处理','in_progress'=>'处理中','closed'=>'已关闭'] as $v=>$l)
                                <option value="{{ $v }}" {{ $ticket->status === $v ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                        <div class="input-group-btn">
                            <button type="submit" class="btn btn-default btn-sm">更新状态</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Conversation --}}
    <div class="col-xs-12 col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title">工单内容与回复</h3></div>
            <div class="box-body">
                {{-- Initial message --}}
                <div class="direct-chat-msg" style="margin-bottom:16px">
                    <div class="direct-chat-info clearfix">
                        <span class="direct-chat-name pull-left">{{ $ticket->user?->username ?? '—' }}</span>
                        <span class="direct-chat-timestamp pull-right">{{ $ticket->created_at->format('Y-m-d H:i') }}</span>
                    </div>
                    <div class="direct-chat-text" style="background:#4a4a55;color:#e0e0e0;border:none">
                        {!! nl2br(e($ticket->content)) !!}
                    </div>
                </div>

                @foreach($ticket->replies as $reply)
                    <div class="direct-chat-msg {{ $reply->is_staff ? 'right' : '' }}" style="margin-bottom:16px">
                        <div class="direct-chat-info clearfix">
                            <span class="direct-chat-name {{ $reply->is_staff ? 'pull-right' : 'pull-left' }}">
                                {{ $reply->is_staff ? '工作人员' : ($reply->user?->username ?? '—') }}
                                @if($reply->is_staff) <span class="label label-warning" style="font-size:10px">Staff</span> @endif
                            </span>
                            <span class="direct-chat-timestamp {{ $reply->is_staff ? 'pull-left' : 'pull-right' }}">
                                {{ $reply->created_at->format('Y-m-d H:i') }}
                            </span>
                        </div>
                        <div class="direct-chat-text"
                            style="{{ $reply->is_staff ? 'background:#2a4a6a;color:#dde;border:none' : 'background:#4a4a55;color:#e0e0e0;border:none' }}">
                            {!! nl2br(e($reply->content)) !!}
                        </div>
                    </div>
                @endforeach
            </div>
            @if($ticket->status !== 'closed')
            <div class="box-footer">
                <form action="{{ route('admin.store.tickets.reply', $ticket->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <textarea name="content" class="form-control" rows="4" placeholder="输入回复内容..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary pull-right">发送回复</button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
