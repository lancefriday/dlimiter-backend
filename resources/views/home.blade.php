@extends('layouts.app')

@section('title','DLimiter')

@section('hero')
  @php
    $loggedIn = session()->has('api_token');
    $u = session('user');
  @endphp

  <div style="padding: 36px 0 40px;">
    <div style="font-size: clamp(48px, 7vw, 92px); line-height: 0.95; font-weight: 900; letter-spacing: -1px;">
      DLimiter<span style="color: var(--accent);">.</span>
    </div>

    <div class="pageSub" style="max-width: 520px; margin-top: 14px;">
      A minimal, secure file-sharing workspace with expiring links, download limits, restricted access, and audit logs.
    </div>

    <div class="sp18"></div>

    @if($loggedIn)
      <div class="notice ok" style="max-width: 680px;">
        <div class="small">Signed in as</div>
        <div style="margin-top:2px;">
          <b>{{ $u['name'] ?? 'User' }}</b> <span class="muted">({{ $u['email'] ?? '' }})</span>
        </div>

        <div class="sp12"></div>

        <div class="small" style="font-weight:800; color: rgba(0,0,0,0.70);">
          Quick Open (paste a link)
        </div>
        <div class="small" style="margin-top:4px;">
          Paste a DLimiter download link (or just the token). It will open in this same tab.
        </div>

        <div class="sp12"></div>

        <form onsubmit="return openPastedLink();" class="row" style="margin:0; align-items:flex-end;">
          <div style="flex:1; min-width:260px;">
            <label>Download link or token</label>
            <input id="quickLinkInput"
                   type="text"
                   placeholder="e.g. http://127.0.0.1:8000/download/abc123...  or  abc123..."
                   autocomplete="off">
          </div>
          <button class="btn btn-ghost" type="submit">Open</button>
        </form>

        <div class="small" style="margin-top:10px;">
          Tip: Use the top navigation to manage files and links.
        </div>
      </div>

      <script>
        function openPastedLink(){
          const el = document.getElementById('quickLinkInput');
          const raw = (el?.value || '').trim();
          if (!raw) return false;

          const origin = window.location.origin;

          // If user pasted only a token, go to /download/{token}
          const looksLikeTokenOnly =
            !raw.includes('://') &&
            !raw.startsWith('/') &&
            !raw.includes('download');

          if (looksLikeTokenOnly) {
            window.location.href = origin + '/download/' + encodeURIComponent(raw);
            return false;
          }

          try {
            // Support full URL, absolute path, or partial link
            let url;
            if (raw.startsWith('http://') || raw.startsWith('https://')) {
              url = new URL(raw);
              window.location.href = url.href; // go exactly where it points
              return false;
            }

            if (raw.startsWith('/')) {
              window.location.href = origin + raw;
              return false;
            }

            // If they pasted something like "127.0.0.1:8000/download/xxx"
            if (raw.includes('/download/')) {
              const idx = raw.indexOf('/download/');
              window.location.href = origin + raw.substring(idx);
              return false;
            }

            // fallback: treat as token
            window.location.href = origin + '/download/' + encodeURIComponent(raw);
            return false;

          } catch (e) {
            window.location.href = origin + '/download/' + encodeURIComponent(raw);
            return false;
          }
        }
      </script>
    @else
      <div class="row">
        <a class="btn" href="/login">Log in</a>
        <a class="btn btn-ghost" href="/register">Create account</a>
      </div>
    @endif
  </div>
@endsection