@extends('layouts.bootstrap')

@section('title', 'Detail User')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Detail User</div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">ID</dt>
                        <dd class="col-sm-9">{{ $user->id }}</dd>

                        <dt class="col-sm-3">Nama</dt>
                        <dd class="col-sm-9">{{ $user->name }}</dd>

                        <dt class="col-sm-3">Email</dt>
                        <dd class="col-sm-9">{{ $user->email }}</dd>

                        <dt class="col-sm-3">Role</dt>
                        <dd class="col-sm-9">{{ $user->role }}</dd>
                    </dl>

                    <hr />

                    <h5>Firestore</h5>
                    @if($firestoreUser)
                        <div class="mb-3">
                            <strong>Doc ID:</strong> {{ $firestoreUser['id'] }}
                        </div>
                        <pre class="small bg-light p-2">{{ json_encode($firestoreUser['data'], JSON_PRETTY_PRINT) }}</pre>
                    @else
                        <div class="alert alert-secondary">Tidak ditemukan entri Firestore untuk user ini.</div>
                    @endif

                    <div class="mt-3 d-flex gap-2">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">Edit</a>
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Hapus user ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger">Hapus</button>
                        </form>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
