@extends('layouts.app')

@section('title', 'Admin Users - DLimiter')

@section('content')
    <div class="page-title">
        <div class="h1">Admin: Users</div>
        <div class="muted">Toggle admin status.</div>
    </div>

    <div class="card-inner">
        <table class="table">
            <thead>
                <tr>
                    <th class="col-id">ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Admin</th>
                    <th class="col-action">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                    <tr>
                        <td>{{ $u->id }}</td>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->is_admin ? 'Yes' : 'No' }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.users.toggle', ['userId' => $u->id]) }}">
                                @csrf
                                <button class="btn btn-ghost" type="submit">Toggle</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top:12px;">
            <a class="nav-link active" href="{{ route('admin.download_events.index') }}">View download events</a>
        </div>
    </div>
@endsection
