@extends('layouts.app')

@section('title', 'Download')
@section('heading', 'Download')

@section('content')
  <p><b>File:</b> {{ $file->original_name ?? 'Unknown' }}</p>
  <p class="muted"><b>Expires:</b> {{ $link->expires_at }}</p>
  <p class="muted"><b>Remaining downloads:</b> {{ $remaining }}</p>

  <div class="sp12"></div>

  @if($revoked)
    <div class="notice err"><b>Link revoked.</b></div>
  @elseif($expired)
    <div class="notice err"><b>Link expired.</b></div>
  @elseif($limitReached)
    <div class="notice err"><b>Download limit reached.</b></div>
  @elseif($notAllowedUser)
    <div class="notice err"><b>You are not allowed to download this file.</b></div>
  @endif

  @if($loginRequired && !request()->user())
    <div class="notice">
      <b>This link requires login.</b>
      <div class="sp12"></div>
      <a class="btn" href="/login">Login to Download</a>
    </div>
  @elseif(!$blocked)
    <a class="btn" href="{{ $downloadUrl }}">Download Now</a>
  @endif
@endsection