@extends('layouts.app')

@section('title', 'Admin Download Events - DLimiter')

@section('content')
    <div class="page-title">
        <div class="h1">Admin: Download events</div>
        <div class="muted">Audit trail of downloads through share links.</div>
    </div>

    <div class="card-inner">
        <table class="table">
            <thead>
                <tr>
                    <th class="col-id">ID</th>
                    <th>File</th>
                    <th>User</th>
                    <th>ShareLink ID</th>
                    <th>IP</th>
                    <th>User Agent</th>
                    <th>When</th>
                </tr>
            </thead>
            <tbody>
                @foreach($events as $ev)
                    <tr>
                        <td>{{ $ev->id }}</td>
                        <td class="wrap">{{ $ev->file_name }}</td>
                        <td class="wrap">{{ $ev->user_email ?: 'public' }}</td>
                        <td>{{ $ev->share_link_id }}</td>
                        <td>{{ $ev->ip }}</td>
                        <td class="wrap">{{ $ev->user_agent }}</td>
                        <td>{{ $ev->downloaded_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top:14px;">
            {{ $events->links() }}
        </div>
    </div>
@endsection
