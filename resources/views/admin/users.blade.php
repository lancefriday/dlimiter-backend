@extends('layouts.app')

@section('title', 'Admin - Users')
@section('heading', 'Admin · Users')
@section('subheading', 'Promote or demote users. (Safety: you cannot change your own role)')

@section('content')
  <table>
    <thead>
      <tr>
        <th style="width:70px;">ID</th>
        <th>Name</th>
        <th>Email</th>
        <th style="width:90px;">Admin</th>
        <th style="width:200px;">Created</th>
        <th style="width:170px;">Action</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($users as $u)
        <tr>
          <td>{{ $u->id }}</td>
          <td>{{ $u->name }}</td>
          <td>{{ $u->email }}</td>
          <td><b>{{ $u->is_admin ? 'YES' : 'NO' }}</b></td>
          <td>{{ $u->created_at }}</td>
          <td>
            @if ($u->id === $me->id)
              <span class="muted">(you)</span>
            @else
              <form method="POST" action="/admin/users/{{ $u->id }}/toggle-admin" style="margin:0;">
                @csrf
                <button class="btn btn-ghost" type="submit">
                  {{ $u->is_admin ? 'Demote' : 'Promote' }}
                </button>
              </form>
            @endif
          </td>
        </tr>
      @empty
        <tr><td colspan="6">No users found.</td></tr>
      @endforelse
    </tbody>
  </table>

  <div class="sp12"></div>
  <div>{{ $users->links() }}</div>
@endsection