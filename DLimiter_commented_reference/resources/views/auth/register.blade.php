@extends('layouts.app')

@section('title', 'Register - DLimiter')

@section('content')
    <div class="page-title">
        <div class="h1">Register</div>
        <div class="muted">Create a new account.</div>
    </div>

    <div class="card-inner">
        <form method="POST" action="{{ route('register.submit') }}" class="form">
            @csrf

            <label class="label">Name</label>
            <input class="input" type="text" name="name" value="{{ old('name') }}" required>

            <label class="label">Email</label>
            <input class="input" type="email" name="email" value="{{ old('email') }}" required>

            <label class="label">Password (min 8 chars)</label>
            <input class="input" type="password" name="password" required>

            <label class="label">Confirm Password</label>
            <input class="input" type="password" name="password_confirmation" required>

            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Create account</button>
                <a class="btn btn-ghost" href="{{ route('login.form') }}">Back to login</a>
            </div>
        </form>
    </div>
@endsection
