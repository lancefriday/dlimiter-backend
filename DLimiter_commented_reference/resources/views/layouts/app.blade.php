<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'DLimiter')</title>

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    {{-- CSRF token for forms and JS fetch --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="page">
        <div class="card shell">
            <header class="topbar">
                <div class="brand">
                    <span class="brand-dot" aria-hidden="true"></span>
                    <span class="brand-name">DLimiter</span>
                </div>

                <nav class="nav">
                    @php
                        $route = request()->route() ? request()->route()->getName() : '';
                        $isActive = function ($name) use ($route) {
                            return $route === $name;
                        };
                    @endphp

                    <a class="nav-link {{ $isActive('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>

                    @if(auth()->check())
                        <a class="nav-link {{ $isActive('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
                        <a class="nav-link {{ $isActive('files.index') ? 'active' : '' }}" href="{{ route('files.index') }}">Files</a>
                        <a class="nav-link {{ $isActive('links.index') ? 'active' : '' }}" href="{{ route('links.index') }}">Links</a>

                        @if(auth()->user()->is_admin)
                            <a class="nav-link {{ str_starts_with($route, 'admin.') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">Admin</a>
                        @endif

                        <form class="logout-form" method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-ghost" type="submit">Logout</button>
                        </form>
                    @else
                        <a class="nav-link {{ $isActive('login.form') ? 'active' : '' }}" href="{{ route('login.form') }}">Login</a>
                        <a class="nav-link {{ $isActive('register.form') ? 'active' : '' }}" href="{{ route('register.form') }}">Register</a>
                    @endif
                </nav>
            </header>

            <main class="content">
                @if ($errors->any())
                    <div class="alert">
                        <div class="alert-title">Please review these items</div>
                        <ul class="alert-list">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('flash'))
                    <div class="alert">
                        {{ session('flash') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        // Global helper: copy text to clipboard with fallback.
        async function copyText(text) {
            try {
                await navigator.clipboard.writeText(text);
                return true;
            } catch (e) {
                const ta = document.createElement('textarea');
                ta.value = text;
                document.body.appendChild(ta);
                ta.select();
                try {
                    document.execCommand('copy');
                    document.body.removeChild(ta);
                    return true;
                } catch (err) {
                    document.body.removeChild(ta);
                    return false;
                }
            }
        }
    </script>

    @yield('scripts')
</body>
</html>
