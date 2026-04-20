@extends('layouts.app')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@section('subheading', 'Overview of your workspace.')

@section('content')
  <div class="row" style="gap:12px; align-items:stretch;">
    <div class="panel" style="flex:1; min-width:220px;">
      <div class="panelBody">
        <div class="small">Total files you own</div>
        <div style="font-size:28px; font-weight:900; margin-top:6px;">
          {{ $totalFiles }}
        </div>
        <div class="small" style="margin-top:6px;">Uploaded under your account.</div>
      </div>
    </div>

    <div class="panel" style="flex:1; min-width:220px;">
      <div class="panelBody">
        <div class="small">Total links you created</div>
        <div style="font-size:28px; font-weight:900; margin-top:6px;">
          {{ $totalLinks }}
        </div>
        <div class="small" style="margin-top:6px;">Share links you generated.</div>
      </div>
    </div>

    <div class="panel" style="flex:1; min-width:220px;">
      <div class="panelBody">
        <div class="small">Active links</div>
        <div style="font-size:28px; font-weight:900; margin-top:6px;">
          {{ $activeLinks }}
        </div>
        <div class="small" style="margin-top:6px;">
          Not revoked, not expired, and still downloadable.
        </div>
      </div>
    </div>
  </div>

  <div class="sp18"></div>

  <div class="panel">
    <div class="panelBody">
      <div class="small">Signed in as</div>
      <div style="font-size:16px; font-weight:900; margin-top:2px;">
        {{ $user['name'] ?? 'User' }}
      </div>
      <div class="small" style="margin-top:2px;">
        {{ $user['email'] ?? '' }}
      </div>

      <div class="sp12"></div>

      <div class="notice" style="margin:0;">
        <div style="font-weight:800; margin-bottom:6px;">Quick guide</div>
        <ul style="margin:0; padding-left:18px; color: var(--muted); line-height:1.6;">
          <li>Upload files in <b>Files</b>.</li>
          <li>Create <b>public</b> or <b>restricted</b> links with expiry + download limits.</li>
          @if(($user['is_admin'] ?? false))
            <li>Review downloads in <b>Admin → Events</b> and manage <b>Users</b>.</li>
          @endif
        </ul>
      </div>
    </div>
  </div>
@endsection