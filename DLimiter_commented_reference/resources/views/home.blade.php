@extends('layouts.app')

@section('title', 'DLimiter')

@section('content')
    <div class="hero">
        <div class="hero-title">DLimiter<span class="dot">.</span></div>
        <div class="hero-subtitle">
            A minimal, secure file-sharing workspace with expiring links, download limits, restricted access, and audit logs.
        </div>

        @if(!auth()->check())
            <div class="hero-actions">
                <a class="btn btn-primary" href="{{ route('login.form') }}">Log in</a>
                <a class="btn btn-ghost" href="{{ route('register.form') }}">Create account</a>
            </div>
        @else
            <div class="hero-card">
                <div class="hero-card-title">Signed in as</div>
                <div class="hero-card-user">
                    <div class="hero-card-name">{{ auth()->user()->name }}</div>
                    <div class="hero-card-email">{{ auth()->user()->email }}</div>
                </div>

                <div class="quick-open">
                    <div class="quick-open-title">Quick open (paste a link)</div>
                    <div class="quick-open-sub">
                        Paste a DLimiter download link (or the token). It opens in this tab.
                    </div>

                    <form method="GET" action="{{ route('home') }}" onsubmit="return false;" class="quick-open-form">
                        <input id="quickOpenInput" class="input" type="text" placeholder="e.g. http://127.0.0.1:8000/download/abc123...   or   abc123..." />
                        <button id="quickOpenBtn" class="btn btn-ghost" type="button">Open</button>
                    </form>

                    <div class="quick-open-tip">
                        Tip: Use the top navigation to manage files and links.
                    </div>
                </div>
            </div>

            <script>
                (function () {
                    const input = document.getElementById('quickOpenInput');
                    const btn = document.getElementById('quickOpenBtn');

                    function extractToken(text) {
                        const v = (text || '').trim();
                        if (!v) return '';
                        // Accept full URL: .../download/{token}
                        const m = v.match(/\/download\/([^\/\?\#]+)/i);
                        if (m && m[1]) return m[1];
                        // Otherwise treat as raw token
                        return v;
                    }

                    btn.addEventListener('click', function () {
                        const token = extractToken(input.value);
                        if (!token) return;
                        window.location.href = '/download/' + encodeURIComponent(token);
                    });
                })();
            </script>
        @endif
    </div>
@endsection
