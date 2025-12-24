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
                    <th>No</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Peran</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                    <tr>
                            <td>
                                @php
                                    $no = 0;
                                @endphp
                                {{-- Pagination-aware numbering --}}
                                @if(method_exists($users, 'currentPage'))
                                    {{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}
                                @else
                                    {{ $loop->iteration }}
                                @endif
                            </td>
                            <td>{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td><span class="badge text-bg-secondary">{{ $u->role }}</span></td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-sm btn-outline-primary">Ubah</a>

                                <!-- Trigger delete modal -->
                                <button type="button" class="btn btn-sm btn-danger btn-delete" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-action="{{ route('admin.users.destroy', $u) }}">Hapus</button>
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

@section('scripts')
        <script>
                // Prepare delete modal to submit the chosen delete action
                document.addEventListener('DOMContentLoaded', function(){
                        var modal = document.getElementById('confirmDeleteModal');
                        var deleteForm = document.getElementById('deleteForm');
                        var confirmBtn = document.getElementById('confirmDeleteBtn');

                        document.querySelectorAll('.btn-delete').forEach(function(btn){
                                btn.addEventListener('click', function(ev){
                                        var action = btn.getAttribute('data-action');
                                        if (deleteForm) deleteForm.action = action + (action.indexOf('?') === -1 ? '?role={{ request("role") }}' : '&role={{ request("role") }}');
                                });
                        });

                        if (confirmBtn && deleteForm){
                                confirmBtn.addEventListener('click', function(){
                                        deleteForm.submit();
                                });
                        }
                });
        </script>

        <!-- Delete confirmation modal -->
        <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" id="confirmDeleteBtn" class="btn btn-danger">OK, Hapus</button>
                    </div>
                </div>
            </div>
        </div>

        <form id="deleteForm" method="POST" style="display:none;">
                @csrf
                @method('DELETE')
        </form>
@endsection
