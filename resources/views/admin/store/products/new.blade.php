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
    <div class="col-xs-12 col-md-10">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">商品信息</h3>
            </div>
            <form action="{{ $product ? route('admin.store.products.edit', $product->id) : route('admin.store.products') }}" method="POST">
                @csrf
                @if($product) @method('POST') @endif
                <div class="box-body">
                    {{-- Base fields --}}
                    <div class="row">
                        <div class="col-md-8 form-group">
                            <label for="pName">名称 <span class="text-danger">*</span></label>
                            <input type="text" id="pName" name="name" class="form-control" value="{{ old('name', $product?->name) }}" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="pType">类型 <span class="text-danger">*</span></label>
                            <select id="pType" name="type" class="form-control" onchange="toggleServerFields(this.value)">
                                <option value="points"     {{ old('type', $product?->type) === 'points'     ? 'selected' : '' }}>积分</option>
                                <option value="server_days"{{ old('type', $product?->type) === 'server_days'? 'selected' : '' }}>服务器天数延期</option>
                                <option value="server"     {{ old('type', $product?->type) === 'server'     ? 'selected' : '' }}>服务器套餐</option>
                                <option value="custom"     {{ old('type', $product?->type) === 'custom'     ? 'selected' : '' }}>自定义</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="pDescription">描述</label>
                        <textarea id="pDescription" name="description" class="form-control" rows="3">{{ old('description', $product?->description) }}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="pValue">内容数量 <span class="text-danger">*</span></label>
                            <input type="number" id="pValue" name="value" class="form-control" value="{{ old('value', $product?->value ?? 0) }}" min="0" required>
                            <p class="text-muted small">积分类型填积分数量；天数类型填天数；服务器套餐填 0。</p>
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="pPrice">价格 <span class="text-danger">*</span></label>
                            <input type="number" id="pPrice" name="price" class="form-control" value="{{ old('price', $product?->price ?? '0.00') }}" min="0" step="0.01" required>
                        </div>
                        <div class="col-md-2 form-group">
                            <label for="pCurrency">货币</label>
                            <input type="text" id="pCurrency" name="currency" class="form-control" value="{{ old('currency', $product?->currency ?? 'CNY') }}" maxlength="8">
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="pSortOrder">排序权重</label>
                            <input type="number" id="pSortOrder" name="sort_order" class="form-control" value="{{ old('sort_order', $product?->sort_order ?? 0) }}" min="0">
                        </div>
                    </div>
                    <div class="form-group">
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

                    {{-- Server package fields (shown only when type = 'server') --}}
                    <div id="serverFields" style="{{ old('type', $product?->type) === 'server' ? '' : 'display:none' }}">
                        <hr>
                        <h4>服务器套餐配置</h4>
                        <p class="text-muted small">配置用户购买后自动创建的服务器参数（需要节点有空余配额）。</p>
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label for="pLocation">地域</label>
                                <select id="pLocation" name="location_id" class="form-control">
                                    <option value="">— 不限定地域 —</option>
                                    @foreach($locations as $loc)
                                        <option value="{{ $loc->id }}" {{ old('location_id', $product?->location_id) == $loc->id ? 'selected' : '' }}>
                                            {{ $loc->short }} — {{ $loc->long }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label for="pNode">节点</label>
                                <select id="pNode" name="node_id" class="form-control">
                                    <option value="">— 自动选择节点 —</option>
                                    @foreach($nodes as $node)
                                        <option value="{{ $node->id }}" {{ old('node_id', $product?->node_id) == $node->id ? 'selected' : '' }}>
                                            [{{ $node->location->short ?? '?' }}] {{ $node->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-muted small">指定节点后忽略地域设置。</p>
                            </div>
                            <div class="col-md-4 form-group">
                                <label for="pEgg">预设 (Egg)</label>
                                <select id="pEgg" name="egg_id" class="form-control">
                                    <option value="">— 选择 Egg —</option>
                                    @foreach($eggs as $egg)
                                        <option value="{{ $egg->id }}" {{ old('egg_id', $product?->egg_id) == $egg->id ? 'selected' : '' }}>
                                            [{{ $egg->nest->name ?? '?' }}] {{ $egg->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 form-group">
                                <label>CPU (%)</label>
                                <input type="number" name="cpu" class="form-control" value="{{ old('cpu', $product?->cpu ?? 100) }}" min="0" placeholder="100">
                                <p class="text-muted small">100 = 1核</p>
                            </div>
                            <div class="col-md-2 form-group">
                                <label>内存 (MB)</label>
                                <input type="number" name="memory" class="form-control" value="{{ old('memory', $product?->memory ?? 512) }}" min="0" placeholder="512">
                            </div>
                            <div class="col-md-2 form-group">
                                <label>磁盘 (MB)</label>
                                <input type="number" name="disk" class="form-control" value="{{ old('disk', $product?->disk ?? 5120) }}" min="0" placeholder="5120">
                            </div>
                            <div class="col-md-2 form-group">
                                <label>数据库</label>
                                <input type="number" name="databases" class="form-control" value="{{ old('databases', $product?->databases ?? 0) }}" min="0">
                            </div>
                            <div class="col-md-2 form-group">
                                <label>备份</label>
                                <input type="number" name="backups" class="form-control" value="{{ old('backups', $product?->backups ?? 0) }}" min="0">
                            </div>
                            <div class="col-md-2 form-group">
                                <label>端口分配</label>
                                <input type="number" name="allocations" class="form-control" value="{{ old('allocations', $product?->allocations ?? 1) }}" min="1">
                            </div>
                        </div>
                        <hr>
                        <h5>每日积分消耗费率 <small class="text-muted">（留空则使用全局费率；不同节点/地域可设置不同价格）</small></h5>
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label>CPU 费率 <small class="text-muted">积分/核/天</small></label>
                                <input type="number" name="points_cpu_rate" class="form-control"
                                    value="{{ old('points_cpu_rate', $product?->points_cpu_rate) }}"
                                    min="0" placeholder="留空使用全局 ({{ $cpu_rate }})">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>内存费率 <small class="text-muted">积分/GB/天</small></label>
                                <input type="number" name="points_memory_rate" class="form-control"
                                    value="{{ old('points_memory_rate', $product?->points_memory_rate) }}"
                                    min="0" placeholder="留空使用全局 ({{ $memory_rate }})">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>磁盘费率 <small class="text-muted">积分/GB/天</small></label>
                                <input type="number" name="points_disk_rate" class="form-control"
                                    value="{{ old('points_disk_rate', $product?->points_disk_rate) }}"
                                    min="0" placeholder="留空使用全局 ({{ $disk_rate }})">
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

@section('footer-scripts')
    @parent
    <script>
        function toggleServerFields(type) {
            document.getElementById('serverFields').style.display = (type === 'server') ? '' : 'none';
        }
    </script>
@endsection
