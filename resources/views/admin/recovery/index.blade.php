@extends('layouts.admin')

@section('title')
    Recovery Centre
@endsection

@section('content-header')
    <h1>Recovery Centre<small>Restore a completed backup over accidentally deleted files</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Recovery Centre</li>
    </ol>
@endsection

@section('content')
    <div class="callout callout-info">
        <h4><i class="fa fa-shield"></i> Safe file recovery</h4>
        <p>A recovery unpacks the selected backup over the current server directory; it does not request a directory wipe. Use a backup created before the folder was deleted. The server is unavailable while Wings performs the recovery.</p>
    </div>

    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-history"></i> Completed backups</h3>
        </div>
        <div class="box-body table-responsive no-padding">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Server</th>
                        <th>Owner</th>
                        <th>Backup</th>
                        <th>Size</th>
                        <th>Completed</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($backups as $backup)
                        <tr>
                            <td>{{ $backup->server->name ?? 'Deleted server' }}</td>
                            <td>{{ $backup->server->user->username ?? 'Unknown' }}</td>
                            <td>{{ $backup->name }}</td>
                            <td>{{ number_format($backup->bytes / 1048576, 1) }} MiB</td>
                            <td>{{ $backup->completed_at->diffForHumans() }}</td>
                            <td>
                                @if($backup->server && is_null($backup->server->status))
                                    <form action="{{ route('admin.recovery.restore', $backup->uuid) }}" method="POST" class="recovery-form" style="display:inline">
                                        {!! csrf_field() !!}
                                        <button type="button" class="btn btn-xs btn-primary recovery-button"><i class="fa fa-history"></i> Recover files</button>
                                    </form>
                                @else
                                    <span class="text-muted">Server busy or unavailable</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted" style="padding:40px;">No completed backups are available for recovery.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($backups->hasPages())
            <div class="box-footer text-center">{!! $backups->render() !!}</div>
        @endif
    </div>
@endsection

@section('footer-scripts')
    @parent
    <script>
        $('.recovery-button').on('click', function () {
            var form = $(this).closest('.recovery-form');
            swal({
                title: 'Start file recovery?',
                text: 'The backup will be unpacked over the current server files. The server will be unavailable until Wings completes the recovery.',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '44f8cff',
                confirmButtonText: 'Start recovery'
            }, function () { form.submit(); });
        });
    </script>
@endsection
