@extends('layouts.sidebar')

@section('page-title', '📊 Dashboard')

@section('content')
{{-- DASHBOARD HEADER --}}
@php($role = auth()->user()->role ?? 'customer')

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">Dashboard</h1>
        <div class="text-muted">Selamat datang, <strong>{{ auth()->user()->name }}</strong></div>
    </div>
    <div>
        @if($role === 'admin')
            <span class="badge text-bg-danger">Admin</span>
        @elseif($role === 'agent')
            <span class="badge text-bg-warning">Agent</span>
        @else
            <span class="badge text-bg-success">Customer</span>
        @endif
    </div>
</div>

{{-- STATISTIK CARDS --}}
<div class="row g-3 mb-4">
    {{-- Card 1: Total Tickets --}}
    <div class="col-md-6 col-lg-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small">Total Tickets</div>
                        <h2 class="mb-0">{{ $stats['total'] ?? 0 }}</h2>
                    </div>
                    <div style="font-size: 2rem;">📋</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Card 2: Open (Belum Ditangani) --}}
    <div class="col-md-6 col-lg-3">
        <div class="card text-white bg-secondary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small">Open</div>
                        <h2 class="mb-0">{{ $stats['open'] ?? 0 }}</h2>
                    </div>
                    <div style="font-size: 2rem;">🆕</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Card 3: In Progress (Sedang Dikerjakan) --}}
    <div class="col-md-6 col-lg-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small">In Progress</div>
                        <h2 class="mb-0">{{ $stats['in_progress'] ?? 0 }}</h2>
                    </div>
                    <div style="font-size: 2rem;">⚙️</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Card 4: Resolved (Selesai) --}}
    <div class="col-md-6 col-lg-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small">Resolved</div>
                        <h2 class="mb-0">{{ $stats['resolved'] ?? 0 }}</h2>
                    </div>
                    <div style="font-size: 2rem;">✅</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- CONTENT BERDASARKAN ROLE --}}
<div class="row g-4">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                @if($role === 'admin')
                    <h5 class="card-title">🎯 Admin Dashboard</h5>

                    @if(isset($stats['unassigned']) && $stats['unassigned'] > 0)
                        <div class="alert alert-warning mb-3">
                            <strong>⚠️</strong> {{ $stats['unassigned'] }} tiket belum ditugaskan
                        </div>
                    @endif

                    <div class="d-flex flex-wrap gap-2">
                        <a class="btn btn-primary" href="{{ route('tickets.index') }}">
                            📋 Tiket
                        </a>
                        <a class="btn btn-outline-secondary" href="{{ route('admin.users.index') }}">
                            👥 Users
                        </a>
                    </div>

                @elseif($role === 'agent')
                    <h5 class="card-title">🔧 Agent Dashboard</h5>

                    <div class="d-flex flex-wrap gap-2">
                        <a class="btn btn-primary" href="{{ route('tickets.index') }}">
                            📋 Tiket yang Harus Dikerjakan ({{ $stats['total'] }})
                        </a>
                    </div>

                @else
                    <h5 class="card-title">👤 Customer Dashboard</h5>

                    <div class="d-flex flex-wrap gap-2">
                        <a class="btn btn-primary" href="{{ route('tickets.create') }}">
                            ➕ Buat Tiket
                        </a>
                        <a class="btn btn-outline-secondary" href="{{ route('tickets.index') }}">
                            📋 Tiket Saya ({{ $stats['total'] }})
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
