@extends('layouts.app')

@section('title', 'Files')
@section('heading', 'Files')
@section('subheading', 'Upload files, create share links, and delete items.')

@section('content')

  @php
    // Human-readable bytes: B / KB / MB / GB / TB
    $fmtBytes = function ($bytes) {
      if ($bytes === null) return '';
      $bytes = (float) $bytes;
      $units = ['B','KB','MB','GB','TB'];

      if ($bytes <= 0) return '0 B';

      $pow = (int) floor(log($bytes, 1024));
      $pow = min($pow, count($units) - 1);

      $value = $bytes / (1024 ** $pow);

      // nicer rounding
      $decimals = ($pow === 0) ? 0 : (($value < 10) ? 2 : 1);

      return number_format($value, $decimals) . ' ' . $units[$pow];
    };
  @endphp

  @if (session('share_token'))
    @php $dl = url('/download/' . session('share_token')); @endphp
    <div class="notice ok">
      <div class="row">
        <div>
          <div class="small">Download Page</div>
          <div class="mono"><a href="{{ $dl }}">{{ $dl }}</a></div>
        </div>
        <div class="right">
          <button class="btn-copy" type="button" onclick="copyText('{{ $dl }}')">Copy</button>
        </div>
      </div>
    </div>
  @endif

  <h3>Upload</h3>
  <form method="POST" action="/files/upload" enctype="multipart/form-data" class="row">
    @csrf
    <input type="file" name="file" required style="max-width:520px;">
    <button class="btn" type="submit">Upload</button>
  </form>

  <div class="sp18"></div>

  <h3>Your files</h3>
  <table>
    <thead>
      <tr>
        <th style="width:70px;">ID</th>
        <th>Name</th>
        <th style="width:140px;">Size</th>
        <th>Create Link</th>
        <th style="width:110px;">Delete</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($files as $f)
        <tr>
          <td>{{ $f->id }}</td>
          <td>{{ $f->original_name }}</td>
          <td>{{ $fmtBytes($f->size_bytes) }}</td>

          <td>
            <form method="POST" action="/files/{{ $f->id }}/links" class="row" style="align-items:flex-end;">
              @csrf

              <div style="min-width:140px;">
                <label>Max downloads</label>
                <input name="max_downloads" type="number" value="1" min="1" max="1000" style="width:140px;">
                <div class="small">How many times it can be downloaded.</div>
              </div>

              <div style="min-width:170px;">
                <label>Expires (minutes)</label>
                <input name="expires_in_minutes" type="number" value="60" min="1" max="525600" style="width:170px;">
                <div class="small">60 = 1 hour, 1440 = 1 day</div>
              </div>

              <div style="min-width:120px;">
                <label>Public?</label>
                <label class="pill" style="justify-content:center;">
                  <input type="checkbox" name="is_public" checked>
                  <span class="small">public</span>
                </label>
                <div class="small">Uncheck = login required</div>
              </div>

              <div style="min-width:260px;">
                <label>Restrict to email (optional)</label>
                <input name="downloader_email" placeholder="someone@email.com" style="width:260px;">
                <div class="small">If filled, only that user can download.</div>
              </div>

              <button class="btn btn-ghost" type="submit">Create</button>
            </form>
          </td>

          <td>
            <form method="POST" action="/files/{{ $f->id }}/delete"
                  onsubmit="return confirm('Delete this file permanently?');" style="margin:0;">
              @csrf
              <button class="btn btn-danger" type="submit">Delete</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="5">No files yet.</td></tr>
      @endforelse
    </tbody>
  </table>

  <div class="sp12"></div>
  <div>{{ $files->links() }}</div>
@endsection