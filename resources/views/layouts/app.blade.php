<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'DLimiter')</title>

  <style>
    :root{
      --bgA:#e9eef6;
      --bgB:#f6f7f9;
      --paper:#ffffff;
      --ink:#111111;
      --muted: rgba(0,0,0,0.55);
      --line: rgba(0,0,0,0.08);
      --line2: rgba(0,0,0,0.06);
      --shadow: 0 18px 55px rgba(0,0,0,0.18);
      --radius: 22px;
      --accent:#e11d48;
    }
    *{ box-sizing:border-box; }
    html, body{ height:100%; }
    body{
      margin:0;
      font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial;
      color: var(--ink);
      background: linear-gradient(180deg, var(--bgA) 0%, var(--bgB) 100%);
    }
    a{ color: inherit; text-decoration:none; }
    a:hover{ text-decoration: underline; }

    .page{
      min-height:100%;
      display:flex;
      align-items:center;
      justify-content:center;
      padding: 34px 16px;
    }

    .frame{
      width: min(1100px, 100%);
      background: var(--paper);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      overflow:hidden;
      position:relative;

      background-image:
        linear-gradient(to right, transparent 0%, transparent 99.6%, var(--line2) 99.6%),
        linear-gradient(to right, transparent 0%, transparent 99.6%, var(--line2) 99.6%),
        linear-gradient(to right, transparent 0%, transparent 99.6%, var(--line2) 99.6%),
        linear-gradient(to right, transparent 0%, transparent 99.6%, var(--line2) 99.6%),
        linear-gradient(to right, transparent 0%, transparent 99.6%, var(--line2) 99.6%);
      background-size: 20% 100%;
      background-position: 0 0, 20% 0, 40% 0, 60% 0, 80% 0;
    }

    .top{
      display:flex;
      align-items:center;
      justify-content:space-between;
      padding: 20px 24px;
      gap: 14px;
    }

    .brand{
      display:flex;
      align-items:center;
      gap: 10px;
      font-weight: 900;
      letter-spacing: 0.2px;
      white-space: nowrap;
    }
    .mark{
      width: 12px;
      height: 12px;
      border-radius: 3px;
      background: var(--accent);
      box-shadow: 0 0 0 5px rgba(225,29,72,0.08);
    }

    .nav a{
  padding: 6px 10px;
  border-radius: 999px;
  transition: background .15s ease, opacity .15s ease;
    }
    .nav a:hover{
    text-decoration: none;
    background: rgba(0,0,0,0.05);
    }

    /* ✅ Active page indicator */
    .nav a.active{
    background: rgba(0,0,0,0.08);
    border: 1px solid rgba(0,0,0,0.14);
    }
    .nav a.active::after{
    content: "";
    display:inline-block;
    width: 6px;
    height: 6px;
    border-radius: 999px;
    background: var(--accent);
    margin-left: 8px;
    vertical-align: middle;
    }

    .contentWrap{
      padding: 10px 24px 26px;
      min-height: 520px;
    }

    .pageTitle{
      font-size: 22px;
      font-weight: 900;
      letter-spacing: -0.2px;
      margin: 8px 0 4px;
    }
    .pageSub{
      margin: 0 0 16px;
      color: var(--muted);
      font-size: 13px;
    }

    .panel{
      border: 1px solid var(--line);
      border-radius: 18px;
      background: rgba(255,255,255,0.82);
      backdrop-filter: blur(10px);
      overflow:hidden;
    }
    .panelBody{ padding: 18px; }

    .row{ display:flex; gap:12px; flex-wrap:wrap; align-items:center; }
    .field{ display:flex; flex-direction:column; gap:6px; margin-bottom:12px; }
    label{ font-size:12px; color: var(--muted); }

    input[type="text"], input[type="email"], input[type="password"], input[type="number"], input[type="file"]{
      width: 100%;
      padding: 12px 12px;
      border-radius: 14px;
      border: 1px solid var(--line);
      outline: none;
      background: rgba(255,255,255,0.92);
    }
    input:focus{
      border-color: rgba(0,0,0,0.28);
      box-shadow: 0 0 0 4px rgba(0,0,0,0.06);
    }

    .btn{
      appearance:none;
      border: 1px solid rgba(0,0,0,0.16);
      background: #111;
      color: #fff;
      padding: 10px 14px;
      border-radius: 14px;
      cursor:pointer;
      font-weight: 800;
      letter-spacing: 0.2px;
      transition: transform .05s ease, opacity .15s ease;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap: 8px;
      text-decoration:none;
    }
    .btn:hover{ opacity: 0.92; }
    .btn:active{ transform: translateY(1px); }
    .btn-ghost{ background: rgba(0,0,0,0.04); color:#111; }
    .btn-danger{ background: #fff; color:#111; border-color: rgba(0,0,0,0.25); }
    .btn-copy{
      background: rgba(0,0,0,0.04);
      color:#111;
      border: 1px solid rgba(0,0,0,0.16);
      padding: 8px 12px;
      border-radius: 12px;
      font-weight: 800;
      cursor:pointer;
    }

    .notice{
      border: 1px solid var(--line);
      border-radius: 16px;
      padding: 12px 14px;
      margin: 12px 0;
      background: rgba(255,255,255,0.8);
    }
    .notice.ok{ border-color: rgba(0,0,0,0.16); }
    .notice.err{ border-color: rgba(0,0,0,0.30); }

    table{
      width:100%;
      border-collapse: collapse;
      border: 1px solid var(--line);
      border-radius: 16px;
      overflow:hidden;
      background: rgba(255,255,255,0.78);
    }
    th, td{
      padding: 12px 10px;
      border-bottom: 1px solid var(--line);
      text-align:left;
      vertical-align: top;
      font-size: 14px;
    }
    th{
      font-size: 12px;
      color: var(--muted);
      font-weight: 900;
      letter-spacing: 0.6px;
      text-transform: uppercase;
      background: rgba(255,255,255,0.62);
    }
    tr:last-child td{ border-bottom: none; }

    .mono{
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
      font-size: 12px;
      word-break: break-all;
    }
    .small{ font-size:12px; color: var(--muted); }
    .right{ margin-left:auto; }
    .sp12{ height:12px; }
    .sp18{ height:18px; }

    @media (max-width: 780px){
      .nav{ gap: 14px; }
      .contentWrap{ padding: 6px 14px 18px; }
      .top{ padding: 16px 14px; }
    }
  </style>
</head>

<body>
  <div class="page">
    <div class="frame">

      <div class="top">
        <div class="brand">
          <span class="mark"></span>
          <a href="/landing" style="font-weight:900;">DLimiter</a>
        </div>

        <div class="nav">
            @php
                $path = request()->path(); // e.g. "dashboard", "files", "links"
                $is = fn($p) => ($path === trim($p,'/')) || str_starts_with($path, trim($p,'/') . '/');
            @endphp

            <a href="/landing" class="{{ $is('landing') ? 'active' : '' }}">Home</a>

            @if(session()->has('api_token'))
                <a href="/dashboard" class="{{ $is('dashboard') ? 'active' : '' }}">Dashboard</a>
                <a href="/files" class="{{ $is('files') ? 'active' : '' }}">Files</a>
                <a href="/links" class="{{ $is('links') ? 'active' : '' }}">Links</a>

                @php $u = session('user'); @endphp
                @if(($u['is_admin'] ?? false))
                <a href="/admin/users" class="{{ $is('admin/users') ? 'active' : '' }}">Users</a>
                <a href="/admin/download-events" class="{{ $is('admin/download-events') ? 'active' : '' }}">Events</a>
                @endif

                <form method="POST" action="/logout" style="margin:0; display:inline;">
                @csrf
                <button class="btn btn-ghost" type="submit">Logout</button>
                </form>
            @else
                <a href="/login" class="{{ $is('login') ? 'active' : '' }}">Login</a>
                <a href="/register" class="{{ $is('register') ? 'active' : '' }}">Register</a>
            @endif
            </div>
        </div>

      <div class="contentWrap">

        @if(session('ok'))
          <div class="notice ok">{{ session('ok') }}</div>
        @endif

        @if ($errors->any())
          <div class="notice err">
            @foreach ($errors->all() as $e) <div>{{ $e }}</div> @endforeach
          </div>
        @endif

        @hasSection('hero')
          @yield('hero')
        @else
          <div class="pageTitle">@yield('heading', 'Page')</div>
          @hasSection('subheading')
            <div class="pageSub">@yield('subheading')</div>
          @else
            <div class="pageSub">&nbsp;</div>
          @endif

          <div class="panel">
            <div class="panelBody">
              @yield('content')
            </div>
          </div>
        @endif

      </div>
    </div>
  </div>

  <script>
    function copyText(text){
      navigator.clipboard.writeText(text).then(() => alert("Copied!"));
    }
  </script>
</body>
</html>