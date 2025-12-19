@extends('layouts.bootstrap')

@section('title', 'Tickets')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Daftar Ticket</h1>
    <a class="btn btn-primary" href="{{ route('tickets.create') }}">Buat Ticket</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-striped align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Prioritas</th>
                    <th>Status</th>
                    <th>Customer</th>
                    <th>Assigned</th>
                    <th>Updated</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $t)
                    <tr>
                        <td><span class="text-muted">{{ $t['id'] }}</span></td>
                        <td>{{ $t['title'] ?? '-' }}</td>
                        <td>{{ $t['category'] ?? '-' }}</td>
                        <td>{{ $t['priority'] ?? '-' }}</td>
                        <td><span class="badge text-bg-secondary">{{ $t['status'] ?? 'open' }}</span></td>
                        <td>{{ $t['customer_id'] ?? '-' }}</td>
                        <td>{{ $t['assigned_agent_id'] ?? '-' }}</td>
                        <td><span class="text-muted">{{ $t['updated_at_iso'] ?? '' }}</span></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('tickets.show', $t['id']) }}">Detail</a>
                            @if(auth()->user()->role === 'admin' || auth()->user()->role === 'agent' || (string)($t['customer_id'] ?? '') === (string)auth()->id())
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('tickets.edit', $t['id']) }}">Edit</a>
                                @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">Belum ada ticket.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
