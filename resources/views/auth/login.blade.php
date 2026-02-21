<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login</title>
</head>
<body>
  <h2>Login</h2>

  @if ($errors->any())
    <div style="color:red;">
      @foreach ($errors->all() as $e) <div>{{ $e }}</div> @endforeach
    </div>
  @endif

  <form method="POST" action="/login">
    @csrf
    <div>
      <label>Email</label><br>
      <input name="email" type="email" value="{{ old('email') }}" required>
    </div>
    <div style="margin-top:8px;">
      <label>Password</label><br>
      <input name="password" type="password" required>
    </div>
    <button style="margin-top:12px;" type="submit">Login</button>
  </form>

  <p style="margin-top:12px;">
    No account? <a href="/register">Register</a>
  </p>
</body>
</html>