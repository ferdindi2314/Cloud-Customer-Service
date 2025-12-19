@extends('layouts.bootstrap')

@section('title', 'Manajemen User')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Manajemen User</h1>
    <div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Tambah User</a>
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
                    <th>Firestore Doc ID</th>
                    <th>Sync</th>
                    <th>Role</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                    <tr>
                            <td>{{ $u->id }}</td>
                            <td>{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td>{{ $u->firestore_doc_id ?? '-' }}</td>
                            <td>
                                @if(isset($u->firestore_synced) && $u->firestore_synced)
                                    <span class="badge text-bg-success" title="Sudah tersinkronisasi ke Firestore">Sudah</span>
                                @else
                                    <span class="badge text-bg-danger" title="Belum tersinkronisasi ke Firestore">Belum</span>
                                @endif
                            </td>
                            <td><span class="badge text-bg-secondary">{{ $u->role }}</span></td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.users.show', $u) }}" class="btn btn-sm btn-outline-secondary">Lihat</a>
                                <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-sm btn-outline-primary">Edit</a>

                                <form method="POST" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('Hapus user ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" type="submit">Hapus</button>
                                </form>

                                <form method="POST" action="{{ route('admin.users.updateRole', $u) }}" class="d-inline-flex gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="role" class="form-select form-select-sm" style="width: 160px;">
                                        <option value="customer" @selected($u->role==='customer')>customer</option>
                                        <option value="agent" @selected($u->role==='agent')>agent</option>
                                        <option value="admin" @selected($u->role==='admin')>admin</option>
                                    </select>
                                    <button class="btn btn-sm btn-outline-primary" type="submit">Update</button>
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
@endsection
