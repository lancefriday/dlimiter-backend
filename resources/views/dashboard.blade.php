<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Dashboard</title>
</head>
<body>
  <h2>Dashboard</h2>

  <p>
    Welcome:
    <b>{{ $user['name'] ?? 'Unknown' }}</b>
    ({{ $user['email'] ?? '' }})
  </p>

  <nav style="margin:12px 0;">
    <a href="/files">Files</a> |
    <a href="/links">Links</a>
  </nav>

  <form method="POST" action="/logout">
    @csrf
    <button type="submit">Logout</button>
  </form>
</body>
</html>