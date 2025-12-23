@extends('layouts.sidebar')

@section('page-title', '📋 Tiket')

@section('title', 'Tiket')

@section('content')
<?php use Carbon\Carbon; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-1">
            @if(auth()->user()->role === 'agent')
                📋 Tiket yang Harus Dikerjakan
            @else
                📋 Daftar Tiket
            @endif
        </h1>
        <div class="text-muted small">{{ count($tickets) }} tiket</div>
    </div>
    @if(auth()->user()->role === 'customer')
        <a class="btn btn-primary" href="{{ route('tickets.create') }}">➕ Buat Tiket</a>
    @endif
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Prioritas</th>
                    <th>Status</th>
                    @if(auth()->user()->role === 'admin')
                        <th>Customer</th>
                    @endif
                    <th>Update</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $statusColors = [
                        'open' => 'secondary',
                        'in_progress' => 'warning',
                        'resolved' => 'success',
                        'closed' => 'dark',
                    ];
                    $priorityColors = [
                        'low' => 'success',
                        'medium' => 'warning',
                        'high' => 'danger',
                    ];
                @endphp
                @forelse($tickets as $t)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ Str::limit($t['title'] ?? '-', 40) }}</div>
                            @if(!empty($t['attachments']))
                                <small class="text-muted">{{ count($t['attachments']) }} file</small>
                            @endif
                        </td>
                        <td><small>{{ $t['category'] ?? '-' }}</small></td>
                        <td>
                            @php
                                $prio = $t['priority'] ?? '-';
                            @endphp
                            <span class="badge text-bg-{{ $priorityColors[$prio] ?? 'secondary' }}">{{ strtoupper($prio[0] ?? '-') }}</span>
                        </td>
                        <td>
                            @php
                                $stat = $t['status'] ?? 'open';
                            @endphp
                            <span class="badge text-bg-{{ $statusColors[$stat] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $stat)) }}</span>
                        </td>
                        @if(auth()->user()->role === 'admin')
                            <td><small>{{ $t['customer_name'] ?? '-' }}</small></td>
                        @endif
                        @php
                            $ts = $t['updated_at_iso'] ?? $t['created_at_iso'] ?? null;
                            try {
                                if ($ts) {
                                    $dt = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $ts, 'Asia/Jakarta');
                                    if (! $dt) {
                                        $dt = \Carbon\Carbon::parse($ts)->setTimezone('Asia/Jakarta');
                                    }
                                } else {
                                    $dt = \Carbon\Carbon::now('Asia/Jakarta');
                                }
                            } catch (Exception $e) {
                                $dt = \Carbon\Carbon::now('Asia/Jakarta');
                            }
                        @endphp
                        <td><small class="text-muted">{{ $dt->diffForHumans(\Carbon\Carbon::now('Asia/Jakarta')) }}</small></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('tickets.show', $t['id']) }}">Buka</a>
                            @if(auth()->user()->role === 'customer')
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-edit-ticket" data-id="{{ $t['id'] }}" data-status="{{ $stat }}">Edit</button>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-ticket" data-id="{{ $t['id'] }}" data-status="{{ $stat }}">Hapus</button>

                                <form id="delete-form-{{ $t['id'] }}" action="{{ route('tickets.destroy', $t['id']) }}" method="POST" style="display:none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->role === 'admin' ? 7 : 6 }}" class="text-center py-5">
                            <div class="text-muted">Tidak ada tiket</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('click', function (e) {
        const el = e.target;
        if (!el.classList) return;
        if (el.classList.contains('btn-edit-ticket')) {
            const id = el.getAttribute('data-id');
            const status = el.getAttribute('data-status');
            if (status === 'open') {
                // navigate to edit page
                window.location.href = '/tickets/' + encodeURIComponent(id) + '/edit';
            } else {
                // show alert using SweetAlert2
                Swal.fire({
                    icon: 'info',
                    title: 'Tidak dapat diedit',
                    html: 'Ticket tidak dapat diedit karena status saat ini: <strong>' + status.replace(/_/g, ' ') + '</strong>',
                    confirmButtonText: 'OK'
                });
            }
        }

        if (el.classList.contains('btn-delete-ticket')) {
            const id = el.getAttribute('data-id');
            const status = el.getAttribute('data-status');
            if (status === 'open') {
                Swal.fire({
                    title: 'Yakin ingin menghapus tiket ini?',
                    text: 'Tindakan ini tidak dapat dibatalkan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById('delete-form-' + id);
                        if (form) form.submit();
                    }
                });
            } else {
                Swal.fire({
                    icon: 'info',
                    title: 'Tidak dapat dihapus',
                    html: 'Ticket tidak dapat dihapus karena status saat ini: <strong>' + status.replace(/_/g, ' ') + '</strong>',
                    confirmButtonText: 'OK'
                });
            }
        }
    });
</script>
@endsection
