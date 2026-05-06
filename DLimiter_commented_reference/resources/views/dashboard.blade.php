@extends('layouts.app')

@section('title', 'Dashboard - DLimiter')

@section('content')
    <div class="page-title">
        <div class="h1">Dashboard</div>
        <div class="muted">Overview of your workspace.</div>
    </div>

    <div class="stats">
        <div class="stat">
            <div class="stat-label">Total files you own</div>
            <div class="stat-value">{{ $totalFiles }}</div>
            <div class="muted">Uploaded under your account.</div>
        </div>

        <div class="stat">
            <div class="stat-label">Total links you created</div>
            <div class="stat-value">{{ $totalLinks }}</div>
            <div class="muted">Share links you generated.</div>
        </div>

        <div class="stat">
            <div class="stat-label">Active links</div>
            <div class="stat-value">{{ $activeLinks }}</div>
            <div class="muted">Not revoked, not expired, and still downloadable.</div>
        </div>
    </div>

    <div class="card-inner">
        <div class="muted">Signed in as</div>
        <div class="user-block">
            <div class="user-name">{{ $user->name }}</div>
            <div class="muted">{{ $user->email }}</div>
        </div>

        <div class="guide">
            <div class="guide-title">Quick guide</div>
            <ul class="guide-list">
                <li>Upload files in <b>Files</b>.</li>
                <li>Create <b>public</b> or <b>restricted</b> links with expiry and download limits.</li>
            </ul>
        </div>
    </div>
@endsection
