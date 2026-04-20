@extends('layouts.app')

@section('title', 'Register')
@section('heading', 'Register')

@section('content')
  <form method="POST" action="/register">
    @csrf

    <div class="field">
      <label>Name</label>
      <input name="name" value="{{ old('name') }}" required>
    </div>

    <div class="field">
      <label>Email</label>
      <input name="email" type="email" value="{{ old('email') }}" required>
    </div>

    <div class="field">
      <label>Password (min 8 chars)</label>
      <input name="password" type="password" required>
    </div>

    <div class="field">
      <label>Confirm Password</label>
      <input name="password_confirmation" type="password" required>
    </div>

    <div class="row">
      <button class="btn" type="submit">Create account</button>
      <a class="btn btn-ghost" href="/login">Back to login</a>
    </div>
  </form>
@endsection