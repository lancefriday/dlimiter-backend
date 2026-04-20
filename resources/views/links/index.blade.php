@extends('layouts.app')

@section('title', 'Links')
@section('heading', 'Links')
@section('subheading', 'Review share links, copy URLs, and revoke access.')

@section('content')
  <table>
    <thead>
      <tr>
        <th style="width:70px;">ID</th>
        <th>File</th>
        <th style="width:120px;">Type</th>
        <th style="width:80px;">Max</th>
        <th style="width:100px;">Used</th>
        <th style="width:190px;">Expires</th>
        <th style="width:170px;">Revoked</th>
        <th>Download Page</th>
        <th style="width:110px;">Action</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($links as $l)
        <tr>
          <td>{{ $l->id }}</td>
          <td>{{ $l->fileItem->original_name ?? '' }}</td>

          <td>
            @if($l->is_public)
              Public
            @else
              Restricted
            @endif
          </td>

          <td>{{ $l->max_downloads }}</td>
          <td>{{ $l->downloads_count }}</td>
          <td>{{ $l->expires_at }}</td>
          <td>{{ $l->revoked_at }}</td>

          <td>
            @php
              $token = null;
              try {
                $token = $l->token_enc ? \Illuminate\Support\Facades\Crypt::decryptString($l->token_enc) : null;
              } catch (\Throwable $e) { $token = null; }
              $dlPage = $token ? url("/download/$token") : null;
            @endphp

            @if($dlPage)
              <div class="row">
                <div class="mono" style="flex:1;">
                  <a href="{{ $dlPage }}">{{ $dlPage }}</a>
                </div>
                <button class="btn-copy" type="button" onclick="copyText('{{ $dlPage }}')">Copy</button>
              </div>
            @else
              <span class="muted">(old link – token not stored)</span>
            @endif
          </td>

          <td>
            <form method="POST" action="/links/{{ $l->id }}/revoke" style="margin:0;">
              @csrf
              <button class="btn btn-ghost" type="submit">Revoke</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="9">No links yet.</td></tr>
      @endforelse
    </tbody>
  </table>

  <div class="sp12"></div>
  <div>{{ $links->links() }}</div>
@endsection