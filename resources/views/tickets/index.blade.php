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
                @php(
                    $statusColors = [
                        'open' => 'secondary',
                        'in_progress' => 'warning',
                        'resolved' => 'success',
                        'closed' => 'dark',
                    ]
                )
                @php(
                    $priorityColors = [
                        'low' => 'success',
                        'medium' => 'warning',
                        'high' => 'danger',
                    ]
                )
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
                            @php($prio = $t['priority'] ?? '-')
                            <span class="badge text-bg-{{ $priorityColors[$prio] ?? 'secondary' }}">{{ strtoupper($prio[0]) }}</span>
                        </td>
                        <td>
                            @php($stat = $t['status'] ?? 'open')
                            <span class="badge text-bg-{{ $statusColors[$stat] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $stat)) }}</span>
                        </td>
                        @if(auth()->user()->role === 'admin')
                            <td><small>{{ $t['customer_name'] ?? '-' }}</small></td>
                        @endif
                        <td><small class="text-muted">{{ \Carbon\Carbon::parse($t['updated_at_iso'] ?? now())->diffForHumans() }}</small></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('tickets.show', $t['id']) }}">Buka</a>
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
