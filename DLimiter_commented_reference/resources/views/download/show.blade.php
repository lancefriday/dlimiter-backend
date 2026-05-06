@extends('layouts.app')

@section('title', 'Download - DLimiter')

@section('content')
    <div class="page-title">
        <div class="h1">Download</div>
        <div class="muted">Secure, limited, expiring downloads.</div>
    </div>

    <div class="card-inner">
        @if($status === 'not_found')
            <div class="alert">Link not found.</div>
        @else
            <div class="kv">
                <div><b>File:</b> {{ $file?->original_name }}</div>
                <div><b>Expires:</b> {{ $link?->expires_at }}</div>
                <div><b>Remaining downloads:</b> {{ $link?->remainingDownloads() }}</div>
            </div>

            @if($status === 'unauthorized')
                <div class="alert">Unauthorized. This link is restricted to another email.</div>
            @elseif($status === 'revoked')
                <div class="alert">Link revoked.</div>
            @elseif($status === 'expired')
                <div class="alert">Link expired.</div>
            @elseif($status === 'limit')
                <div class="alert">Download limit reached.</div>
            @elseif($status === 'ok')
                <form method="POST" action="{{ route('download.perform', ['token' => request()->route('token')]) }}">
                    @csrf
                    <button class="btn btn-primary" type="submit">Download Now</button>
                </form>
            @endif
        @endif
    </div>
@endsection
