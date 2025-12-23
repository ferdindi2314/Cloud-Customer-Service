@extends('layouts.sidebar')

@section('page-title', '📋 ' . ($ticket['title'] ?? 'Tiket'))

@section('title', 'Detail Tiket')

@section('content')
<div class="container-fluid">
<div class="d-flex justify-content-between align-items-start gap-3 mb-3">
    <div>
        <h1 class="h3 mb-1">{{ $ticket['title'] ?? 'Ticket' }}</h1>
        <div class="text-muted">ID: {{ $ticket['id'] }}</div>
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
        <div class="mt-2 d-flex flex-wrap align-items-center gap-2">
            @php
                $stat = $ticket['status'] ?? 'open';
                $prio = $ticket['priority'] ?? '-';
            @endphp
            <span class="badge text-bg-{{ $statusColors[$stat] ?? 'secondary' }}">{{ $stat }}</span>
            <span class="badge text-bg-{{ $priorityColors[$prio] ?? 'secondary' }}">{{ $prio }}</span>
            @if(isset($ticket['attachments']) && (is_array($ticket['attachments']) || (function_exists('is_countable') && is_countable($ticket['attachments']))) && count($ticket['attachments']) > 0)
                <span class="badge text-bg-light text-dark">{{ count($ticket['attachments']) }} lampiran</span>
            @endif
        </div>
    </div>
    <div class="d-flex gap-2 align-items-start">
        <a class="btn btn-outline-secondary btn-sm" href="{{ route('tickets.index') }}">Kembali</a>
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
            <form method="POST" action="{{ route('tickets.destroy', $ticket['id']) }}" onsubmit="return confirm('Hapus ticket ini?')" class="d-inline-block ms-1">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger btn-sm" type="submit">🗑️ Hapus</button>
            </form>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title mb-3">Rincian Tiket</h5>

                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Kategori</dt>
                    <dd class="col-sm-8">{{ $ticket['category'] ?? '-' }}</dd>

                    <dt class="col-sm-4 text-muted">Prioritas</dt>
                    <dd class="col-sm-8">
                        @php
                            $prio = $ticket['priority'] ?? '-';
                        @endphp
                        <span class="badge text-bg-{{ $priorityColors[$prio] ?? 'secondary' }}">{{ ucfirst($prio) }}</span>
                    </dd>

                    <dt class="col-sm-4 text-muted">Status</dt>
                    <dd class="col-sm-8">
                        @php
                            $stat = $ticket['status'] ?? 'open';
                        @endphp
                        <span class="badge text-bg-{{ $statusColors[$stat] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $stat)) }}</span>
                    </dd>

                    <dt class="col-sm-4 text-muted">Pelanggan</dt>
                    <dd class="col-sm-8">{{ $ticket['customer_name'] ?? '-' }}</dd>

                    <dt class="col-sm-4 text-muted">Agent Ditugaskan</dt>
                    <dd class="col-sm-8">@if(!empty($ticket['agent_name'])){{ $ticket['agent_name'] }}@elseif(!empty($ticket['agent_id']))Agent #{{ $ticket['agent_id'] }}@else-@endif</dd>
                </dl>

                <hr>

                <h6 class="fw-semibold">Deskripsi</h6>
                <div class="border rounded p-3 bg-light" style="white-space: pre-wrap;">{{ $ticket['description'] ?? '-' }}</div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Tracking Status</div>
            <div class="card-body">
                @php
                    $history = $ticket['status_history'] ?? [];
                    if (!is_array($history)) {
                        $history = [];
                    }

                    // Ensure we always show an initial 'open' entry (ticket creation)
                    $displayHistory = $history;
                    $hasOpen = false;
                    foreach ($displayHistory as $h) {
                        if (isset($h['status']) && $h['status'] === 'open') {
                            $hasOpen = true;
                            break;
                        }
                    }
                    if (!$hasOpen) {
                        array_unshift($displayHistory, ['status' => 'open', 'changed_at_iso' => $ticket['created_at_iso'] ?? '-']);
                    }
                @endphp

                <ul class="list-group list-group-flush">
                    @foreach($displayHistory as $h)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ ucfirst(str_replace('_', ' ', $h['status'] ?? '-')) }}</strong>
                            </div>
                            <div class="text-muted small">{{ $h['changed_at_iso'] ?? ($ticket['created_at_iso'] ?? '') }}</div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Lampiran</div>
            <div class="card-body">
                @if(isset($ticket['attachments']) && is_array($ticket['attachments']) && count($ticket['attachments']) > 0)
                    <div class="list-group list-group-flush">
                        @foreach($ticket['attachments'] as $att)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="text-truncate" style="max-width:70%">{{ $att['name'] ?? ($att['path'] ?? 'file') }}</div>
                                <div>
                                    @if(!empty($att['temp_url']))
                                        <a class="btn btn-sm btn-outline-primary" href="{{ $att['temp_url'] }}" target="_blank" rel="noopener">Download</a>
                                    @else
                                        <span class="text-muted small">(URL tidak tersedia)</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-muted">Tidak ada lampiran.</div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex align-items-center gap-2">
                <span>💬 Komentar & Diskusi</span>
                <span class="badge bg-secondary ms-auto">{{ isset($comments) && is_array($comments) ? count($comments) : 0 }}</span>
            </div>
            <div class="card-body">
                @if(isset($comments) && is_array($comments) && count($comments) > 0)
                    <div class="mb-4">
                        @foreach($comments as $c)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-start mb-1">
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
                                <div class="border rounded p-2 bg-white" style="white-space: pre-wrap;">{{ $c['comment'] ?? $c['message'] ?? '(tidak ada komentar)' }}</div>
                                @if(isset($c['attachments']) && is_array($c['attachments']) && count($c['attachments']) > 0)
                                    <div class="mt-2">
                                        <strong>Bukti / Lampiran:</strong>
                                        <div class="list-group list-group-flush mt-1">
                                            @foreach($c['attachments'] as $attc)
                                                <div class="list-group-item d-flex justify-content-between align-items-center p-2">
                                                    <div class="text-truncate" style="max-width:70%">{{ $attc['name'] ?? basename($attc['path'] ?? 'file') }}</div>
                                                    <div>
                                                        @if(!empty($attc['temp_url']))
                                                            <a class="btn btn-sm btn-outline-primary" href="{{ $attc['temp_url'] }}" target="_blank" rel="noopener">Download</a>
                                                        @else
                                                            <span class="text-muted small">(URL tidak tersedia)</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
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
        <div class="d-grid gap-3">
            {{-- INFO: Ticket metadata --}}
            <div class="card">
                <div class="card-header">📝 Informasi Ticket</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-6 text-muted">Dibuat</dt>
                        <dd class="col-6 text-end">{{ $ticket['created_at_iso'] ?? '-' }}</dd>

                        <dt class="col-6 text-muted">Terakhir Update</dt>
                        <dd class="col-6 text-end">{{ $ticket['updated_at_iso'] ?? ($ticket['created_at_iso'] ?? '-') }}</dd>

                        <dt class="col-6 text-muted">Pelanggan</dt>
                        <dd class="col-6 text-end">{{ $ticket['customer_name'] ?? '-' }}</dd>

                        <dt class="col-6 text-muted">Agent</dt>
                        <dd class="col-6 text-end">{{ $ticket['agent_name'] ?? ($ticket['agent_id'] ? 'Agent #'.$ticket['agent_id'] : '-') }}</dd>
                    </dl>
                </div>
            </div>

            {{-- Tugaskan Agent (Admin Only) --}}
            @if(auth()->user()->role === 'admin')
                <div class="card">
                    <div class="card-header bg-warning text-dark">👷 Tugaskan Agent</div>
                    <div class="card-body">
                        @if(!empty($ticket['agent_id']))
                            <div class="alert alert-success mb-3">
                                <strong>✅ Sudah ditugaskan ke:</strong><br>
                                <span class="fs-6">{{ $ticket['agent_name'] ?? 'Agent #'.$ticket['agent_id'] }}</span>
                            </div>
                        @else
                            <div class="alert alert-warning mb-3">
                                <strong>⚠️ Belum ditugaskan!</strong>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('tickets.assign', $ticket['id']) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Pilih Agent</label>
                                <select name="agent_id" class="form-select form-select-sm" required>
                                    <option value="">-- Pilih Agent --</option>
                                    @if(isset($agents) && (is_array($agents) || (function_exists('is_countable') && is_countable($agents))))
                                        @foreach($agents as $agent)
                                            <option value="{{ $agent->id }}" @selected(isset($ticket['agent_id']) && $ticket['agent_id'] == $agent->id)>{{ $agent->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <button class="btn btn-warning w-100" type="submit" {{ !(isset($agents) && (is_array($agents) || (function_exists('is_countable') && is_countable($agents))) && count($agents) > 0) ? 'disabled' : '' }}>
                                <strong>📤 Tugaskan Agent</strong>
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Ubah Status (Admin & Agent) --}}
            <div class="card">
                <div class="card-header">📊 Ubah Status</div>
                <div class="card-body">
                    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'agent')
                        <form method="POST" action="{{ route('tickets.updateStatus', $ticket['id']) }}" enctype="multipart/form-data" id="status-form">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">Status Saat Ini</label>
                                <select name="status" class="form-select form-select-sm">
                                    @php
                                        $cur = $ticket['status'] ?? 'open';
                                        $role = auth()->user()->role ?? 'customer';
                                    @endphp

                                    @if($role === 'admin')
                                        <option value="assigned" @selected($cur==='assigned')>👥 Assigned (Ditugaskan)</option>
                                        <option value="in_progress" @selected($cur==='in_progress')>⚙️ In Progress (Dikerjakan)</option>
                                        <option value="resolved" @selected($cur==='resolved')>✅ Resolved (Selesai)</option>
                                        <option value="closed" @selected($cur==='closed')>🔒 Closed (Ditutup)</option>
                                    @elseif($role === 'agent')
                                        <option value="in_progress" @selected($cur==='in_progress')>⚙️ In Progress (Dikerjakan)</option>
                                        <option value="resolved" @selected($cur==='resolved')>✅ Resolved (Selesai)</option>
                                        <option value="closed" @selected($cur==='closed')>🔒 Closed (Ditutup)</option>
                                    @endif
                                </select>
                            </div>
                                <div id="evidence-section" style="display:none;">
                                    <div class="mb-2">
                                        <label class="form-label">Catatan Bukti Kerja (wajib jika resolved)</label>
                                        <textarea name="evidence_note" class="form-control" rows="3" placeholder="Contoh: Mesin menyala normal, level oli OK..."></textarea>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Lampiran Bukti (foto, log) — minimal 1 file</label>
                                        <input type="file" name="evidence[]" class="form-control" multiple accept="image/*,video/*,application/pdf" />
                                    </div>
                                </div>
                                <button class="btn btn-primary w-100" type="submit">💾 Perbarui Status</button>
                            </form>

                            @section('scripts')
                            <script>
                                (function(){
                                    const form = document.getElementById('status-form');
                                    if (!form) return;
                                    const select = form.querySelector('select[name="status"]');
                                    const evidence = document.getElementById('evidence-section');
                                    const initialStatus = '{{ $ticket['status'] ?? '' }}';
                                    const role = '{{ auth()->user()->role ?? '' }}';
                                    function toggleEvidence(){
                                        if (!select) return;
                                        const val = select.value;
                                        // show evidence inputs only when agent chooses resolved
                                        if (val === 'resolved' && role === 'agent'){
                                            evidence.style.display = 'block';
                                        } else {
                                            evidence.style.display = 'none';
                                        }
                                    }
                                    if (select){
                                        select.addEventListener('change', function(ev){
                                            // Prevent agent from changing a closed ticket
                                            if (initialStatus === 'closed' && role === 'agent' && select.value !== 'closed'){
                                                Swal.fire({icon:'info', title:'Ticket sudah ditutup', text:'Ticket yang telah ditutup tidak dapat diubah statusnya oleh agent.'});
                                                // revert selection
                                                select.value = 'closed';
                                                toggleEvidence();
                                                return;
                                            }
                                            toggleEvidence();
                                        });
                                        // init
                                        toggleEvidence();
                                    }
                                    // client-side pre-submit check for agents resolving
                                    form.addEventListener('submit', function(e){
                                        try{
                                            const val = select.value;
                                            if (val === 'resolved' && role === 'agent'){
                                                const note = form.querySelector('textarea[name="evidence_note"]').value.trim();
                                                const files = form.querySelector('input[name="evidence[]"]').files;
                                                if (!note){
                                                    e.preventDefault();
                                                    Swal.fire({icon:'warning', title:'Butuh catatan', text:'Tolong isi catatan bukti kerja sebelum menandai Resolved.'});
                                                    return false;
                                                }
                                                if (!files || files.length === 0){
                                                    e.preventDefault();
                                                    Swal.fire({icon:'warning', title:'Butuh bukti', text:'Lampirkan minimal 1 file bukti (foto atau log) sebelum menandai Resolved.'});
                                                    return false;
                                                }
                                            }
                                        }catch(err){/* ignore */}
                                    });
                                })();
                            </script>
                            @endsection
                        </form>
                    @else
                        <div class="text-muted">Hanya agent/admin yang bisa ubah status.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
