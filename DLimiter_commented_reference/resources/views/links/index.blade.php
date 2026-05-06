@extends('layouts.app')

@section('title', 'Links - DLimiter')

@section('content')
    <div class="page-title">
        <div class="h1">Links</div>
        <div class="muted">Review share links, copy URLs, and revoke access.</div>
    </div>

    <div class="card-inner">
        <table class="table">
            <thead>
                <tr>
                    <th class="col-id">ID</th>
                    <th>File</th>
                    <th>Type</th>
                    <th>Max</th>
                    <th>Used</th>
                    <th>Expires</th>
                    <th>Revoked</th>
                    <th>Download page</th>
                    <th class="col-action">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($links as $link)
                    <tr>
                        <td>{{ $link->id }}</td>
                        <td class="wrap">{{ $link->fileItem?->original_name }}</td>
                        <td>{{ $link->typeLabel() }}</td>
                        <td>{{ $link->max_downloads }}</td>
                        <td>{{ $link->downloads_count }}</td>
                        <td>{{ $link->expires_at }}</td>
                        <td>{{ $link->revoked_at ? 'Yes' : '' }}</td>
                        <td class="wrap">
                            {{-- Token is not stored; for display-only, show the route pattern. --}}
                            <div class="muted">Copy the URL from Files right after creation.</div>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('links.revoke', ['linkId' => $link->id]) }}">
                                @csrf
                                <button class="btn btn-ghost" type="submit">Revoke</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="muted">No links yet.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="hint" style="margin-top:12px;">
            Reason token is not shown here: token is never stored in DB. Only a hash is stored for security.
        </div>
    </div>
@endsection
