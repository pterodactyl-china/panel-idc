@extends('layouts.admin')

@section('title')
    支付设置
@endsection

@section('content-header')
    <h1>支付设置<small>配置支付宝和微信支付的参数及开关。</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">管理</a></li>
        <li class="active">支付设置</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">支付方式配置</h3>
            </div>
            <form action="{{ route('admin.store.payment-settings') }}" method="POST">
                @csrf
                <div class="box-body">

                    {{-- Alipay Online --}}
                    <div class="callout callout-info" style="margin-bottom:20px;">
                        <h4><i class="fa fa-mobile"></i> 支付宝（在线扫码）</h4>
                    </div>
                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="alipay_enabled" value="1"
                                    {{ ($settings['alipay_enabled'] ?? '0') === '1' ? 'checked' : '' }}>
                                <strong>启用支付宝在线支付</strong>
                            </label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>应用 ID（App ID）</label>
                            <input type="text" name="alipay_app_id" class="form-control"
                                value="{{ $settings['alipay_app_id'] ?? '' }}" placeholder="2021xxxxxxxxxxxxxx">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>应用私钥（RSA2）</label>
                        <textarea name="alipay_private_key" class="form-control" rows="4"
                            placeholder="MIIEvAIBADANBgkqhkiG9w0B...">{{ $settings['alipay_private_key'] ?? '' }}</textarea>
                        <p class="help-block">请粘贴不含头尾 -----BEGIN/END----- 行的纯私钥内容。</p>
                    </div>
                    <div class="form-group">
                        <label>支付宝公钥（验签用）</label>
                        <textarea name="alipay_public_key" class="form-control" rows="3"
                            placeholder="MIIBIjANBgkqhkiG9w0B...">{{ $settings['alipay_public_key'] ?? '' }}</textarea>
                    </div>

                    <hr>

                    {{-- Alipay Face to Face --}}
                    <div class="callout callout-warning" style="margin-bottom:20px;">
                        <h4><i class="fa fa-qrcode"></i> 支付宝（面对面收款）</h4>
                    </div>
                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="alipay_face_enabled" value="1"
                                    {{ ($settings['alipay_face_enabled'] ?? '0') === '1' ? 'checked' : '' }}>
                                <strong>启用面对面收款</strong>
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>收款码链接（URL 或 base64 图片）</label>
                        <input type="text" name="alipay_face_code" class="form-control"
                            value="{{ $settings['alipay_face_code'] ?? '' }}"
                            placeholder="https://qr.alipay.com/xxxx 或 data:image/png;base64,...">
                        <p class="help-block">用户选择此支付方式时将展示该收款码图片。</p>
                    </div>

                    <hr>

                    {{-- WeChat Pay --}}
                    <div class="callout callout-success" style="margin-bottom:20px;">
                        <h4><i class="fa fa-weixin"></i> 微信支付</h4>
                    </div>
                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="wechat_enabled" value="1"
                                    {{ ($settings['wechat_enabled'] ?? '0') === '1' ? 'checked' : '' }}>
                                <strong>启用微信支付</strong>
                            </label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>AppID</label>
                            <input type="text" name="wechat_app_id" class="form-control"
                                value="{{ $settings['wechat_app_id'] ?? '' }}" placeholder="wx...">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>商户号（Mch ID）</label>
                            <input type="text" name="wechat_mch_id" class="form-control"
                                value="{{ $settings['wechat_mch_id'] ?? '' }}" placeholder="1xxxxxxxxx">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>API 密钥</label>
                            <input type="password" name="wechat_api_key" class="form-control"
                                value="{{ $settings['wechat_api_key'] ?? '' }}" placeholder="32位 API 密钥">
                        </div>
                    </div>

                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i> 保存设置</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
