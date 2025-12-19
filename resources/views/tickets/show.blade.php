@extends('layouts.bootstrap')

@section('title', 'Detail Ticket')

@section('content')
<div class="d-flex justify-content-between align-items-start gap-3 mb-3">
    <div>
        <h1 class="h3 mb-1">{{ $ticket['title'] ?? 'Ticket' }}</h1>
        <div class="text-muted">ID: {{ $ticket['id'] }}</div>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary" href="{{ route('tickets.index') }}">Kembali</a>
        @if(auth()->user()->role === 'admin' || auth()->user()->role === 'agent' || ($ticket['customer_id'] ?? null) === auth()->id())
            <a class="btn btn-outline-primary" href="{{ route('tickets.edit', $ticket['id']) }}">Edit</a>
        @endif
        @if(auth()->user()->role === 'admin' || ($ticket['customer_id'] ?? null) === auth()->id())
            <form method="POST" action="{{ route('tickets.destroy', $ticket['id']) }}" onsubmit="return confirm('Hapus ticket ini?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger" type="submit">Hapus</button>
            </form>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-body">
                <div class="mb-2"><span class="fw-semibold">Kategori:</span> {{ $ticket['category'] ?? '-' }}</div>
                <div class="mb-2"><span class="fw-semibold">Prioritas:</span> {{ $ticket['priority'] ?? '-' }}</div>
                <div class="mb-2"><span class="fw-semibold">Status:</span> <span class="badge text-bg-secondary">{{ $ticket['status'] ?? 'open' }}</span></div>
                <div class="mb-2"><span class="fw-semibold">Customer:</span> {{ $ticket['customer_id'] ?? '-' }}</div>
                <div class="mb-3"><span class="fw-semibold">Assigned Agent:</span> {{ $ticket['assigned_agent_id'] ?? '-' }}</div>

                <div class="fw-semibold mb-2">Deskripsi</div>
                <div class="border rounded p-3 bg-light" style="white-space: pre-wrap;">{{ $ticket['description'] ?? '-' }}</div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Lampiran</div>
            <div class="card-body">
                @if(isset($ticket['attachments']) && is_array($ticket['attachments']) && count($ticket['attachments']) > 0)
                    <ul class="mb-0">
                        @foreach($ticket['attachments'] as $att)
                            <li>
                                {{ $att['name'] ?? ($att['path'] ?? 'file') }}
                                @if(!empty($att['temp_url']))
                                    - <a href="{{ $att['temp_url'] }}" target="_blank" rel="noopener">Download</a>
                                @else
                                    <span class="text-muted">(URL tidak tersedia)</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-muted">Tidak ada lampiran.</div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">Komentar</div>
            <div class="card-body">
                @if(isset($comments) && is_array($comments) && count($comments) > 0)
                    <div class="list-group mb-3">
                        @foreach($comments as $c)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <div class="fw-semibold">
                                        {{ $c['user_name'] ?? $c['user_id'] ?? 'User' }}
                                        <span class="text-muted fw-normal">({{ $c['role'] ?? '-' }})</span>
                                    </div>
                                    <div class="text-muted small">{{ $c['created_at_iso'] ?? '' }}</div>
                                </div>
                                <div style="white-space: pre-wrap;">{{ $c['message'] ?? '' }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-muted mb-3">Belum ada komentar.</div>
                @endif

                <form method="POST" action="{{ route('tickets.comments.store', $ticket['id']) }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">Tulis komentar</label>
                        <textarea name="message" rows="3" class="form-control @error('message') is-invalid @enderror">{{ old('message') }}</textarea>
                        @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button class="btn btn-primary" type="submit">Kirim</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header">Aksi Status</div>
            <div class="card-body">
                @if(auth()->user()->role === 'admin' || auth()->user()->role === 'agent')
                    <form method="POST" action="{{ route('tickets.updateStatus', $ticket['id']) }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">Ubah Status</label>
                            <select name="status" class="form-select">
                                @php($cur = $ticket['status'] ?? 'open')
                                <option value="open" @selected($cur==='open')>open</option>
                                <option value="in_progress" @selected($cur==='in_progress')>in_progress</option>
                                <option value="resolved" @selected($cur==='resolved')>resolved</option>
                                <option value="closed" @selected($cur==='closed')>closed</option>
                            </select>
                        </div>
                        <button class="btn btn-outline-primary" type="submit">Update Status</button>
                    </form>
                @else
                    <div class="text-muted">Hanya agent/admin yang bisa ubah status.</div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">Assign Agent</div>
            <div class="card-body">
                @if(auth()->user()->role === 'admin')
                    <form method="POST" action="{{ route('tickets.assign', $ticket['id']) }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">Agent ID</label>
                            <input name="agent_id" class="form-control" placeholder="Masukkan user id agent" value="{{ old('agent_id', $ticket['assigned_agent_id'] ?? '') }}">
                        </div>
                        <button class="btn btn-outline-primary" type="submit">Assign</button>
                    </form>
                    <div class="form-text">Untuk sederhana: isi dengan ID user (kolom users.id) yang role-nya agent.</div>
                @else
                    <div class="text-muted">Hanya admin yang bisa assign agent.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
