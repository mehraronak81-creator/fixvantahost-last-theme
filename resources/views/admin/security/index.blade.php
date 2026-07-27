@extends('layouts.admin')

@section('title')
    Security Centre
@endsection

@section('content-header')
    <h1>Security Centre<small>Control-plane safeguards and node hardening status</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Security Centre</li>
    </ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="box" style="padding:18px;text-align:center;">
                <i class="fa fa-terminal" style="font-size:22px;color:var(--vh-accent);"></i>
                <h3 style="margin:8px 0 3px;color:var(--vh-text);">{{ $commandLimit }}/min</h3>
                <small style="color:var(--vh-text-secondary);">Per-server commands</small>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="box" style="padding:18px;text-align:center;">
                <i class="fa fa-power-off" style="font-size:22px;color:var(--vh-warning);"></i>
                <h3 style="margin:8px 0 3px;color:var(--vh-text);">{{ $powerLimit }}/min</h3>
                <small style="color:var(--vh-text-secondary);">Per-server power actions</small>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="box" style="padding:18px;text-align:center;">
                <i class="fa fa-folder-open" style="font-size:22px;color:223c4e8;"></i>
                <h3 style="margin:8px 0 3px;color:var(--vh-text);">{{ $fileMutationLimit }}/min</h3>
                <small style="color:var(--vh-text-secondary);">Per-server file changes</small>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="box" style="padding:18px;text-align:center;">
                <i class="fa fa-life-ring" style="font-size:22px;color:var(--vh-success);"></i>
                <h3 style="margin:8px 0 3px;color:var(--vh-text);">{{ $lockedBackups }}/{{ $completedBackups }}</h3>
                <small style="color:var(--vh-text-secondary);">Protected recovery backups</small>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-shield"></i> Active panel protections</h3></div>
                <div class="box-body no-padding">
                    <table class="table table-hover">
                        <tbody>
                            <tr><th>Client API ceiling</th><td>{{ $clientApiLimit }} requests per minute per client identity</td></tr>
                            <tr><th>Application API ceiling</th><td>{{ $applicationApiLimit }} requests per minute per application identity</td></tr>
                            <tr><th>Authentication protection</th><td>Login attempts are throttled and two-factor policy remains enforced by the Panel.</td></tr>
                            <tr><th>Recovery control</th><td>{{ $suspendedServers }} suspended server(s); completed backups are available in the Recovery Centre.</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="box-footer"><a class="btn btn-primary btn-sm" href="{{ route('admin.recovery.index') }}"><i class="fa fa-life-ring"></i> Open Recovery Centre</a></div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="box box-warning">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-exclamation-triangle"></i> Network protection required</h3></div>
                <div class="box-body">
                    <p style="color:var(--vh-text-secondary);">Panel rate limits do not stop traffic sent directly to a server allocation. Protect every node at the edge.</p>
                    <ol style="padding-left:18px;color:var(--vh-text-secondary);line-height:1.8;">
                        <li>Allow the Wings API only from the Panel.</li>
                        <li>Use protected TCP/UDP proxying or provider DDoS mitigation.</li>
                        <li>Set connection and bandwidth limits in the firewall/network.</li>
                        <li>Suspend an abusive server while investigating it.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="box box-primary">
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-sitemap"></i> Node response actions</h3></div>
        <div class="box-body table-responsive no-padding">
            <table class="table table-hover">
                <thead><tr><th>Node</th><th>FQDN</th><th>Servers</th><th>Maintenance</th><th>Action</th></tr></thead>
                <tbody>
                    @foreach($nodes as $node)
                        <tr>
                            <td>{{ $node->name }}</td><td>{{ $node->fqdn }}</td><td>{{ $node->servers_count }}</td>
                            <td>{!! $node->maintenance_mode ? '<span class="label label-warning">Enabled</span>' : '<span class="label label-success">Off</span>' !!}</td>
                            <td><a class="btn btn-xs btn-default" href="{{ route('admin.nodes.view.settings', $node->id) }}"><i class="fa fa-cog"></i> Node settings</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
