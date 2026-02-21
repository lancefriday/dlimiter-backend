<!doctype html>
<html>
<head><meta charset="utf-8"><title>Links</title></head>
<body>
  <h2>Links</h2>
  <nav><a href="/dashboard">Dashboard</a> | <a href="/files">Files</a></nav>

  @if (session('ok')) <p style="color:green">{{ session('ok') }}</p> @endif

  <table border="1" cellpadding="6" cellspacing="0" style="margin-top:12px;">
    <thead>
      <tr>
        <th>ID</th>
        <th>File</th>
        <th>Public</th>
        <th>Max</th>
        <th>Downloads</th>
        <th>Expires</th>
        <th>Revoked</th>
        <th>Download</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($links as $l)
        <tr>
          <td>{{ $l->id }}</td>
          <td>{{ $l->fileItem->original_name ?? '' }}</td>
          <td>{{ $l->is_public ? 'yes' : 'no' }}</td>
          <td>{{ $l->max_downloads }}</td>
          <td>{{ $l->downloads_count }}</td>
          <td>{{ $l->expires_at }}</td>
          <td>{{ $l->revoked_at }}</td>
          <td>
            {{-- token is not stored, so show prefix only --}}
            {{ $l->token_prefix }}...
          </td>
          <td>
            <form method="POST" action="/links/{{ $l->id }}/revoke">
              @csrf
              <button type="submit">Revoke</button>
            </form>
          </td>
        </tr>
      @endforeach

      @if ($links->count() === 0)
        <tr><td colspan="9">No links yet.</td></tr>
      @endif
    </tbody>
  </table>

  <div style="margin-top:12px;">
    {{ $links->links() }}
  </div>
</body>
</html>