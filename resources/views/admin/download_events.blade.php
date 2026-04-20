@extends('layouts.app')

@section('title', 'Admin - Download Events')
@section('heading', 'Admin · Download Events')
@section('subheading', 'Audit log for downloads (file, user, IP, device, timestamp).')

@section('content')
  <table>
    <thead>
      <tr>
        <th style="width:70px;">ID</th>
        <th>File</th>
        <th style="width:220px;">User</th>
        <th style="width:90px;">Link ID</th>
        <th style="width:140px;">IP</th>
        <th>User Agent</th>
        <th style="width:200px;">When</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($events as $e)
        <tr>
          <td>{{ $e->id }}</td>
          <td>{{ $e->file_name ?? '' }}</td>
          <td>{{ $e->user_email ?? 'public' }}</td>
          <td>{{ $e->share_link_id ?? '' }}</td>
          <td>{{ $e->ip_address ?? '' }}</td>
          <td class="mono" style="max-width:420px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
            {{ $e->user_agent ?? '' }}
          </td>
          <td>{{ $e->downloaded_at ?? ($e->created_at ?? '') }}</td>
        </tr>
      @empty
        <tr><td colspan="7">No events yet.</td></tr>
      @endforelse
    </tbody>
  </table>

  <div class="sp12"></div>
  <div>{{ $events->links() }}</div>
@endsection