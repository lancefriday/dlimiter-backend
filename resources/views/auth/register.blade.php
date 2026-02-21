<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Register</title>
</head>
<body>
  <h2>Register</h2>

  @if ($errors->any())
    <div style="color:red;">
      @foreach ($errors->all() as $e) <div>{{ $e }}</div> @endforeach
    </div>
  @endif

  <form method="POST" action="/register">
    @csrf
    <div>
      <label>Name</label><br>
      <input name="name" value="{{ old('name') }}" required>
    </div>
    <div style="margin-top:8px;">
      <label>Email</label><br>
      <input name="email" type="email" value="{{ old('email') }}" required>
    </div>
    <div style="margin-top:8px;">
      <label>Password</label><br>
      <input name="password" type="password" required>
    </div>
    <div style="margin-top:8px;">
      <label>Confirm Password</label><br>
      <input name="password_confirmation" type="password" required>
    </div>
    <button style="margin-top:12px;" type="submit">Create Account</button>
  </form>

  <p style="margin-top:12px;">
    Already have an account? <a href="/login">Login</a>
  </p>
</body>
</html>