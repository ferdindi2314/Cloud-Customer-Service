@extends('layouts.sidebar')

@php
    $role = request('role');
    $pageTitle = '👥 Manajemen Pengguna';
    $btnText = 'Tambah Pengguna';
    
    if ($role === 'admin') {
        $pageTitle = '👑 Manajemen Admin';
        $btnText = 'Tambah Admin';
    } elseif ($role === 'agent') {
        $pageTitle = '🔧 Manajemen Operator';
        $btnText = 'Tambah Operator';
    } elseif ($role === 'customer') {
        $pageTitle = '👥 Manajemen User';
        $btnText = 'Tambah User';
    }
@endphp

@section('page-title', $pageTitle)

@section('title', $pageTitle)

@section('content')
<div class="container-fluid">
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">{{ $pageTitle }}</h1>
    <div>
        <a href="{{ route('admin.users.create', ['role' => $role]) }}" class="btn btn-primary">{{ $btnText }}</a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-striped align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Peran</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                    <tr>
                            <td>{{ $u->id }}</td>
                            <td>{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td><span class="badge text-bg-secondary">{{ $u->role }}</span></td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-sm btn-outline-primary">Ubah</a>

                                <form method="POST" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('Hapus user ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" type="submit">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $users->links() }}
</div>
</div>
@endsection
