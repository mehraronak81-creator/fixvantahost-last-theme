@extends('layouts.admin')

@section('title', 'Server Status Widget')

@section('content-header')
    <h1>
        Server Status Widget
        <small>Enable a live, embeddable status widget for any of your game servers.</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('/admin') }}">Admin</a></li>
        <li class="active">Server Status Widget</li>
    </ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Widget Configuration</h3>
                </div>
                <div class="box-body">
                    <p>
                        Toggle the status widget per server, choose a light or dark theme, and restrict the
                        domains that are allowed to embed it. Use the "Embed code" button to copy a ready-to-paste
                        snippet for any website. The public widget refreshes automatically and requires no login.
                    </p>
                    <div id="status-widget-admin-root"
                         data-base="{{ url('/admin/status-widget') }}"
                         data-csrf="{{ csrf_token() }}"
                         data-ui-theme="light"
                         data-config="1"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer-scripts')
    @parent
    {{-- Zero-dependency vanilla admin UI (no Vite/webpack build required). --}}
    <script src="{{ asset('js/status-widget-admin.js') }}"></script>
@endsection

