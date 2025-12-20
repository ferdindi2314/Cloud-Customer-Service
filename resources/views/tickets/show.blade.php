@extends('layouts.sidebar')

@section('page-title', '📋 ' . ($ticket['title'] ?? 'Tiket'))

@section('title', 'Detail Tiket')

@section('content')
<div class="container-fluid">
<div class="d-flex justify-content-between align-items-start gap-3 mb-3">
    <div>
        <h1 class="h3 mb-1">{{ $ticket['title'] ?? 'Ticket' }}</h1>
        <div class="text-muted">ID: {{ $ticket['id'] }}</div>
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
        <div class="mt-2 d-flex flex-wrap align-items-center gap-2">
            @php($stat = $ticket['status'] ?? 'open')
            <span class="badge text-bg-{{ $statusColors[$stat] ?? 'secondary' }}">{{ $stat }}</span>
            @php($prio = $ticket['priority'] ?? '-')
            <span class="badge text-bg-{{ $priorityColors[$prio] ?? 'secondary' }}">{{ $prio }}</span>
            @if(!empty($ticket['attachments']))
                <span class="badge text-bg-light text-dark">{{ count($ticket['attachments']) }} lampiran</span>
            @endif
        </div>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary" href="{{ route('tickets.index') }}">Kembali</a>
        {{-- HANYA customer yang buat ticket DAN status masih 'open' boleh edit --}}
        @if(($ticket['customer_id'] ?? null) === auth()->id() && ($ticket['status'] ?? 'open') === 'open')
            <a class="btn btn-outline-primary" href="{{ route('tickets.edit', $ticket['id']) }}">
                ✏️ Ubah Ticket
            </a>
        @elseif(($ticket['customer_id'] ?? null) === auth()->id() && ($ticket['status'] ?? 'open') !== 'open')
            <button class="btn btn-outline-secondary" disabled title="Ticket yang sudah diproses tidak bisa diedit">
                🔒 Terkunci (sudah diproses)
            </button>
        @endif
        {{-- Admin atau customer bisa hapus --}}
        @if(auth()->user()->role === 'admin' || ($ticket['customer_id'] ?? null) === auth()->id())
            <form method="POST" action="{{ route('tickets.destroy', $ticket['id']) }}" onsubmit="return confirm('Hapus ticket ini?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger" type="submit">🗑️ Hapus</button>
            </form>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-body">
                <div class="mb-2"><span class="fw-semibold">Kategori:</span> {{ $ticket['category'] ?? '-' }}</div>
                <div class="mb-2"><span class="fw-semibold">Prioritas:</span>
                    @php($prio = $ticket['priority'] ?? '-')
                    <span class="badge text-bg-{{ $priorityColors[$prio] ?? 'secondary' }}">{{ $prio }}</span>
                </div>
                <div class="mb-2"><span class="fw-semibold">Status:</span>
                    @php($stat = $ticket['status'] ?? 'open')
                    <span class="badge text-bg-{{ $statusColors[$stat] ?? 'secondary' }}">{{ $stat }}</span>
                </div>
                <div class="mb-2"><span class="fw-semibold">Pelanggan:</span> {{ $ticket['customer_id'] ?? '-' }}</div>
                <div class="mb-3"><span class="fw-semibold">Agent Ditugaskan:</span> {{ $ticket['assigned_agent_id'] ?? '-' }}</div>

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
            <div class="card-header d-flex align-items-center gap-2">
                <span>💬 Komentar & Diskusi</span>
                <span class="badge bg-secondary">{{ isset($comments) && is_array($comments) ? count($comments) : 0 }}</span>
            </div>
            <div class="card-body">
                @if(isset($comments) && is_array($comments) && count($comments) > 0)
                    <div class="mb-4">
                        @foreach($comments as $c)
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span class="fw-semibold">{{ $c['user_name'] ?? 'User' }}</span>
                                        @if(isset($c['user_role']))
                                            @if($c['user_role'] === 'admin')
                                                <span class="badge bg-danger text-white ms-1">Admin</span>
                                            @elseif($c['user_role'] === 'agent')
                                                <span class="badge bg-warning text-dark ms-1">Agent</span>
                                            @else
                                                <span class="badge bg-success text-white ms-1">Customer</span>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="text-muted small">{{ $c['created_at_iso'] ?? '' }}</div>
                                </div>
                                <div class="ps-3" style="white-space: pre-wrap;">{{ $c['comment'] ?? $c['message'] ?? '(tidak ada komentar)' }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-info mb-3">
                        <strong>ℹ️ Belum ada komentar.</strong><br>
                        Jadilah yang pertama memberikan update atau pertanyaan!
                    </div>
                @endif

                {{-- Form Tambah Komentar --}}
                <div class="border-top pt-3">
                    <form method="POST" action="{{ route('tickets.comments.store', $ticket['id']) }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label fw-semibold">Tambah Komentar</label>
                            <textarea name="message" rows="3" class="form-control @error('message') is-invalid @enderror" placeholder="Contoh: Sedang diperbaiki, butuh sparepart X. Estimasi selesai 2 jam." required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                💡 Tips: Jelaskan progress, kendala, atau tanya ke pihak terkait
                            </div>
                        </div>
                        <button class="btn btn-primary" type="submit">
                            📤 Kirim Komentar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- CARD 1: Tugaskan Agent (Admin Only) --}}
        @if(auth()->user()->role === 'admin')
            <div class="card mb-3">
                <div class="card-header bg-warning text-dark">
                    <strong>👷 Tugaskan Agent</strong>
                </div>
                <div class="card-body">
                    @if(isset($ticket['agent_id']) && $ticket['agent_id'])
                        {{-- Sudah ada agent --}}
                        <div class="alert alert-success mb-3">
                            <strong>✅ Sudah ditugaskan ke:</strong><br>
                            <span class="fs-5">{{ $ticket['agent_name'] ?? 'Agent #'.$ticket['agent_id'] }}</span>
                        </div>
                        <p class="small text-muted mb-2">Ingin ganti agent?</p>
                    @else
                        {{-- Belum ada agent --}}
                        <div class="alert alert-warning mb-3">
                            <strong>⚠️ Belum ditugaskan!</strong><br>
                            Pilih agent untuk menangani ticket ini.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('tickets.assign', $ticket['id']) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Pilih Agent</label>
                            <select name="agent_id" class="form-select form-select-lg" required>
                                <option value="">-- Pilih Agent --</option>
                                @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}" 
                                        @selected(isset($ticket['agent_id']) && $ticket['agent_id'] == $agent->id)>
                                        {{ $agent->name }}
                                    </option>
                                @endforeach
                            </select>
                            @if(count($agents) == 0)
                                <div class="form-text text-danger">
                                    Tidak ada agent tersedia. Buat user dengan role 'agent' terlebih dahulu.
                                </div>
                            @endif
                        </div>
                        <button class="btn btn-warning w-100" type="submit" {{ count($agents) == 0 ? 'disabled' : '' }}>
                            <strong>📤 Tugaskan Agent</strong>
                        </button>
                        <div class="form-text mt-2">
                            💡 Setelah ditugaskan, ticket akan masuk ke dashboard agent tersebut.
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- CARD 2: Ubah Status (Admin & Agent) --}}
        <div class="card mb-3">
            <div class="card-header">📊 Ubah Status</div>
            <div class="card-body">
                @if(auth()->user()->role === 'admin' || auth()->user()->role === 'agent')
                    <form method="POST" action="{{ route('tickets.updateStatus', $ticket['id']) }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">Status Saat Ini</label>
                            <select name="status" class="form-select">
                                @php($cur = $ticket['status'] ?? 'open')
                                <option value="open" @selected($cur==='open')>🆕 Open (Baru)</option>
                                <option value="in_progress" @selected($cur==='in_progress')>⚙️ In Progress (Dikerjakan)</option>
                                <option value="resolved" @selected($cur==='resolved')>✅ Resolved (Selesai)</option>
                                <option value="closed" @selected($cur==='closed')>🔒 Closed (Ditutup)</option>
                            </select>
                        </div>
                        <button class="btn btn-primary w-100" type="submit">
                            💾 Perbarui Status
                        </button>
                        <div class="form-text mt-2">
                            Status membantu customer memantau progress.
                        </div>
                    </form>
                @else
                    <div class="text-muted">Hanya agent/admin yang bisa ubah status.</div>
                @endif
            </div>
        </div>
    </div>
</div>
</div>
@endsection
