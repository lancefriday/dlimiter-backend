@extends('layouts.app')

@section('title', 'Login - DLimiter')

@section('content')
    <div class="page-title">
        <div class="h1">Login</div>
        <div class="muted">Sign in to manage files and links.</div>
    </div>

    <div class="card-inner">
        <form method="POST" action="{{ route('login.submit') }}" class="form">
            @csrf

            <label class="label">Email</label>
            <input class="input" type="email" name="email" value="{{ old('email') }}" required>

            <label class="label">Password</label>
            <input class="input" type="password" name="password" required>

            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Log in</button>
                <a class="btn btn-ghost" href="{{ route('register.form') }}">Create account</a>
            </div>
        </form>
    </div>
@endsection
