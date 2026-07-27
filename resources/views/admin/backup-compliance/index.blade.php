@extends('layouts.admin')

@section('title') Backup Compliance @endsection

@section('content-header')
    <h1>Backup Compliance<small>Identify active servers without a recent recovery point</small></h1>
    <ol class="breadcrumb"><li><a href="{{ route('admin.index') }}">Admin</a></li><li class="active">Backup Compliance</li></ol>
@endsection

@section('content')
    <div class="callout callout-info">
        <h4><i class="fa fa-info-circle"></i> Recovery readiness</h4>
        <p>This is a read-only safety report. A server is protected when it has a successful backup completed within the selected window. It never changes files or starts backups.</p>
    </div>

    <div class="row">
        <div class="col-md-3 col-sm-6"><div class="box" style="padding:18px;text-align:center;"><i class="fa fa-server" style="color:var(--vh-accent);font-size:22px"></i><h3>{{ $totalServers }}</h3><small>Active servers</small></div></div>
        <div class="col-md-3 col-sm-6"><div class="box" style="padding:18px;text-align:center;"><i class="fa fa-check-circle" style="color:var(--vh-success);font-size:22px"></i><h3>{{ $protectedServers }}</h3><small>Protected in {{ $days }} days</small></div></div>
        <div class="col-md-3 col-sm-6"><div class="box" style="padding:18px;text-align:center;"><i class="fa fa-exclamation-triangle" style="color:var(--vh-warning);font-size:22px"></i><h3>{{ $staleServers }}</h3><small>Backup is stale</small></div></div>
        <div class="col-md-3 col-sm-6"><div class="box" style="padding:18px;text-align:center;"><i class="fa fa-times-circle" style="color:var(--vh-danger);font-size:22px"></i><h3>{{ $serversWithoutBackup }}</h3><small>No completed backup</small></div></div>
    </div>

    <div class="box box-primary">
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-shield"></i> Servers requiring attention</h3><div class="box-tools"><form method="GET" action="{{ route('admin.backup-compliance.index') }}" class="form-inline"><label class="small">Recovery window</label> <select name="days" class="form-control input-sm" onchange="this.form.submit()">@foreach([1, 3, 7, 14, 30, 60, 90] as $window)<option value="{{ $window }}" @selected($days === $window)>{{ $window }} days</option>@endforeach</select></form></div></div>
        <div class="box-body table-responsive no-padding"><table class="table table-hover"><thead><tr><th>Server</th><th>Owner</th><th>Node</th><th>Latest completed backup</th><th class="text-right">Action</th></tr></thead><tbody>
            @forelse($atRiskServers as $server)
                <tr><td><strong>{{ $server->name }}</strong><br><small class="text-muted">{{ $server->uuidShort }}</small></td><td>{{ $server->user->username ?? 'Unknown' }}</td><td>{{ $server->node->name ?? 'Unavailable' }}</td><td>@if($server->latest_successful_backup_at)<span class="label label-warning">{{ \Carbon\Carbon::parse($server->latest_successful_backup_at)->diffForHumans() }}</span>@else<span class="label label-danger">No completed backup</span>@endif</td><td class="text-right"><a class="btn btn-xs btn-default" href="{{ route('admin.servers.view', $server->id) }}"><i class="fa fa-server"></i> Manage</a> <a class="btn btn-xs btn-primary" href="{{ route('admin.recovery.index') }}"><i class="fa fa-life-ring"></i> Recovery</a></td></tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted" style="padding:38px;"><i class="fa fa-check-circle" style="color:var(--vh-success)"></i> Every active server has a completed backup within the selected window.</td></tr>
            @endforelse
        </tbody></table></div>
        @if($atRiskServers->hasPages())<div class="box-footer text-center">{{ $atRiskServers->links() }}</div>@endif
    </div>
@endsection
