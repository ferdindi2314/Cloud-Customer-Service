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
<style>
    /* Responsive admin tables */
    @media (max-width: 992px) {
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        table {
            min-width: 600px;
        }
    }
    
    @media (max-width: 768px) {
        table {
            font-size: 13px;
        }
        
        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 10px;
        }
        
        .btn-primary {
            width: 100%;
        }
        
        h1.h3 {
            font-size: 1.25rem;
        }
    }
    
    @media (max-width: 576px) {
        .btn-sm {
            padding: 4px 8px;
            font-size: 11px;
        }
        
        table th, table td {
            padding: 8px 6px;
        }
        
        .btn {
            width: 100%;
            margin-bottom: 5px;
        }
    }
</style>
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
                                <button class="btn btn-sm btn-danger" onclick="confirmDelete('{{ route('admin.users.destroy', $u) }}?role={{ request('role') }}', '{{ $u->name }}')">Hapus</button>
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
    <form id="deleteForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
    </form>
</div>
@endsection

@section('scripts')
<script>
function confirmDelete(url, userName) {
    Swal.fire({
        title: '🗑️ Konfirmasi Hapus',
        html: `Apakah Anda yakin ingin menghapus pengguna <strong>${userName}</strong>?<br><small class="text-muted">Tindakan ini tidak dapat dibatalkan.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-4',
            confirmButton: 'btn btn-danger px-4',
            cancelButton: 'btn btn-secondary px-4'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('deleteForm');
            form.action = url;
            form.submit();
        }
    });
}
</script>
@endsection
