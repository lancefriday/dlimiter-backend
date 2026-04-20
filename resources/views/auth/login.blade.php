@extends('layouts.app')

@section('title', 'Login')
@section('heading', 'Login')

@section('content')
  <form method="POST" action="/login">
    @csrf

    <div class="field">
      <label>Email</label>
      <input name="email" type="email" value="{{ old('email') }}" required>
    </div>

    <div class="field">
      <label>Password</label>
      <input name="password" type="password" required>
    </div>

    <div class="row">
      <button class="btn" type="submit">Login</button>
      <a class="btn btn-ghost" href="/register">Create account</a>
    </div>
  </form>
@endsection