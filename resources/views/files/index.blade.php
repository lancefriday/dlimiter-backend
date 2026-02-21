<!doctype html>
<html>
<head><meta charset="utf-8"><title>Files</title></head>
<body>
  <h2>Files</h2>
  <nav><a href="/dashboard">Dashboard</a> | <a href="/links">Links</a></nav>

  @if (session('ok')) <p style="color:green">{{ session('ok') }}</p> @endif
  @if ($errors->any())
    <div style="color:red;">
      @foreach ($errors->all() as $e) <div>{{ $e }}</div> @endforeach
    </div>
  @endif

  @if (session('share_token'))
    <p style="color:blue;">
      Share link: <b>http://127.0.0.1:8000/api/d/{{ session('share_token') }}</b>
    </p>
  @endif

  <h3>Upload</h3>
  <form method="POST" action="/files/upload" enctype="multipart/form-data">
    @csrf
    <input type="file" name="file" required>
    <button type="submit">Upload</button>
  </form>

  <h3 style="margin-top:18px;">Your files</h3>
  <table border="1" cellpadding="6" cellspacing="0">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Size</th>
        <th>Create Link</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($files as $f)
        <tr>
          <td>{{ $f->id }}</td>
          <td>{{ $f->original_name }}</td>
          <td>{{ $f->size_bytes }}</td>
          <td>
            <form method="POST" action="/files/{{ $f->id }}/links">
              @csrf
              <input name="max_downloads" type="number" value="1" min="1" max="1000" style="width:80px;">
              <input name="expires_in_minutes" type="number" value="60" min="1" max="525600" style="width:120px;">
              <label><input type="checkbox" name="is_public" checked> public</label>
              <button type="submit">Create</button>
            </form>
          </td>
        </tr>
      @endforeach

      @if ($files->count() === 0)
        <tr><td colspan="4">No files yet.</td></tr>
      @endif
    </tbody>
  </table>

  <div style="margin-top:12px;">
    {{ $files->links() }}
  </div>
</body>
</html>