@extends('layouts.app')

@section('title', 'Files - DLimiter')

@section('content')
    <div class="page-title">
        <div class="h1">Files</div>
        <div class="muted">Upload files, create share links, and delete items.</div>
    </div>

    <div class="card-inner">
        <div class="h2">Upload</div>

        <form method="POST" action="{{ route('files.upload') }}" enctype="multipart/form-data" class="upload">
            @csrf
            <input class="input" type="file" name="file" required>
            <button class="btn btn-primary" type="submit">Upload</button>
        </form>
    </div>

    @if(session('created_link_url'))
        <div class="card-inner">
            <div class="h2">Download page</div>
            <div class="copy-row">
                <div class="copy-text" id="latestLink">{{ session('created_link_url') }}</div>
                <button class="btn btn-ghost" type="button" onclick="copyText(document.getElementById('latestLink').innerText)">Copy</button>
            </div>
        </div>
    @endif

    <div class="card-inner">
        <div class="h2">Your files</div>

        <table class="table">
            <thead>
                <tr>
                    <th class="col-id">ID</th>
                    <th>Name</th>
                    <th class="col-size">Size</th>
                    <th>Create link</th>
                    <th class="col-action">Delete</th>
                </tr>
            </thead>
            <tbody>
                @forelse($files as $file)
                    <tr>
                        <td>{{ $file->id }}</td>
                        <td class="wrap">{{ $file->original_name }}</td>
                        <td>{{ $file->sizeHuman() }}</td>
                        <td>
                            <form method="POST" action="{{ route('files.links.create', ['fileId' => $file->id]) }}" class="link-form">
                                @csrf

                                <div class="grid">
                                    <div>
                                        <label class="label small">Max downloads</label>
                                        <input class="input" type="number" name="max_downloads" value="1" min="1" max="50" required>
                                        <div class="hint">How many times it can be downloaded.</div>
                                    </div>

                                    <div>
                                        <label class="label small">Expires (minutes)</label>
                                        <input class="input" type="number" name="expires_in_minutes" value="60" min="1" max="10080" required>
                                        <div class="hint">60 = 1 hour, 1440 = 1 day.</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <label class="checkbox">
                                        <input type="checkbox" name="is_public" value="1" checked>
                                        <span>Public</span>
                                    </label>
                                    <div class="hint">Uncheck to require login.</div>
                                </div>

                                <div class="row">
                                    <label class="label small">Restrict to email (optional)</label>
                                    <input class="input" type="email" name="restrict_email" placeholder="someone@email.com">
                                    <div class="hint">If filled, only that user can download.</div>
                                </div>

                                <button class="btn btn-ghost" type="submit">Create</button>
                            </form>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('files.delete', ['fileId' => $file->id]) }}">
                                @csrf
                                <button class="btn btn-ghost" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">No files yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
