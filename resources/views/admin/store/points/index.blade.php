@extends('layouts.admin')

@section('title')
    积分管理
@endsection

@section('content-header')
    <h1>积分管理<small>查看和调整用户积分余额。</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">管理</a></li>
        <li class="active">积分管理</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">用户积分列表</h3>
                <div class="box-tools search01">
                    <form action="{{ route('admin.store.points') }}" method="GET">
                        <div class="input-group input-group-sm">
                            <input type="text" name="filter[email]" class="form-control pull-right"
                                value="{{ request()->input('filter.email') }}" placeholder="搜索邮箱">
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
                            <th>邮箱</th>
                            <th>用户名</th>
                            <th class="text-right">积分余额</th>
                            <th class="text-center">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td><code>{{ $user->id }}</code></td>
                                <td><a href="{{ route('admin.users.view', $user->id) }}">{{ $user->email }}</a></td>
                                <td>{{ $user->username }}</td>
                                <td class="text-right">
                                    <strong>{{ number_format($user->points_balance) }}</strong>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-xs btn-primary"
                                        data-toggle="modal"
                                        data-target="#adjustModal"
                                        data-userid="{{ $user->id }}"
                                        data-email="{{ $user->email }}"
                                        data-balance="{{ $user->points_balance }}">
                                        调整积分
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($users->hasPages())
                <div class="box-footer with-border">
                    <div class="col-md-12 text-center">{!! $users->appends(request()->input())->render() !!}</div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Adjust Points Modal --}}
<div class="modal fade" id="adjustModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="adjustForm" action="" method="POST">
                @csrf
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    <h4 class="modal-title">调整积分</h4>
                </div>
                <div class="modal-body">
                    <p>用户：<strong id="modalEmail"></strong></p>
                    <p>当前余额：<strong id="modalBalance"></strong> 积分</p>
                    <div class="form-group">
                        <label for="pAmount">调整数量（正数增加，负数减少）</label>
                        <input type="number" id="pAmount" name="amount" class="form-control" value="0" required>
                    </div>
                    <div class="form-group">
                        <label for="pDescription">备注</label>
                        <input type="text" id="pDescription" name="description" class="form-control" placeholder="管理员调整" maxlength="191">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">确认调整</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
        $('#adjustModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var userId = button.data('userid');
            var email = button.data('email');
            var balance = button.data('balance');

            var modal = $(this);
            modal.find('#modalEmail').text(email);
            modal.find('#modalBalance').text(balance);
            modal.find('#adjustForm').attr('action', '/admin/store/points/' + userId + '/adjust');
            modal.find('#pAmount').val(0);
            modal.find('#pDescription').val('');
        });
    </script>
@endsection
