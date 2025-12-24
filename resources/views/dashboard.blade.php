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
<style>
    .stat-card { border: none; border-radius: 14px; box-shadow: 0 10px 24px rgba(15,23,42,0.08); height: 100%; }
    .stat-card .card-body { min-height: 120px; display: flex; justify-content: space-between; align-items: center; }
    .stat-icon { font-size: 1.8rem; }
    .stat-label { font-size: .9rem; letter-spacing: .02em; text-transform: uppercase; }
    
    /* Responsive dashboard */
    @media (max-width: 992px) {
        .col-lg-2 {
            width: 50%;
        }
        
        .stat-card .card-body {
            min-height: 100px;
        }
        
        .stat-icon {
            font-size: 1.5rem;
        }
    }
    
    @media (max-width: 768px) {
        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 10px;
            text-align: center;
        }
        
        h1.h3 {
            font-size: 1.25rem;
        }
        
        .col-md-4 {
            width: 50%;
        }
        
        .stat-card .card-body {
            min-height: 90px;
            padding: 12px;
        }
        
        h3 {
            font-size: 1.5rem;
        }
    }
    
    @media (max-width: 576px) {
        .col-md-4, .col-lg-2 {
            width: 100%;
        }
        
        .stat-label {
            font-size: 0.8rem;
        }
        
        .stat-icon {
            font-size: 1.3rem;
        }
    }
</style>

<div class="row g-3 mb-4">
    <div class="col-md-4 col-lg-2 d-flex flex-column">
        <div class="card stat-card text-white" style="background: linear-gradient(135deg,#2563eb,#1d4ed8);">
            <div class="card-body">
                <div>
                    <div class="stat-label">Total</div>
                    <h3 class="mb-0">{{ $stats['total'] ?? 0 }}</h3>
                </div>
                <div class="stat-icon">📋</div>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-lg-2 d-flex flex-column">
        <div class="card stat-card text-white" style="background: linear-gradient(135deg,#6b7280,#4b5563);">
            <div class="card-body">
                <div>
                    <div class="stat-label">Open</div>
                    <h3 class="mb-0">{{ $stats['open'] ?? 0 }}</h3>
                </div>
                <div class="stat-icon">🆕</div>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-lg-2 d-flex flex-column">
        <div class="card stat-card text-white" style="background: linear-gradient(135deg,#f59e0b,#d97706);">
            <div class="card-body">
                <div>
                    <div class="stat-label">Assigned</div>
                    <h3 class="mb-0">{{ $stats['assigned'] ?? 0 }}</h3>
                </div>
                <div class="stat-icon">📌</div>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-lg-2 d-flex flex-column">
        <div class="card stat-card text-white" style="background: linear-gradient(135deg,#f97316,#ea580c);">
            <div class="card-body">
                <div>
                    <div class="stat-label">In Progress</div>
                    <h3 class="mb-0">{{ $stats['in_progress'] ?? 0 }}</h3>
                </div>
                <div class="stat-icon">⚙️</div>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-lg-2 d-flex flex-column">
        <div class="card stat-card text-white" style="background: linear-gradient(135deg,#16a34a,#15803d);">
            <div class="card-body">
                <div>
                    <div class="stat-label">Resolved</div>
                    <h3 class="mb-0">{{ $stats['resolved'] ?? 0 }}</h3>
                </div>
                <div class="stat-icon">✅</div>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-lg-2 d-flex flex-column">
        <div class="card stat-card text-white" style="background: linear-gradient(135deg,#0f172a,#1f2937);">
            <div class="card-body">
                <div>
                    <div class="stat-label">Closed</div>
                    <h3 class="mb-0">{{ $stats['closed'] ?? 0 }}</h3>
                </div>
                <div class="stat-icon">🔒</div>
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

                    @if($stats['total'] > 0)
                        <div class="d-flex flex-wrap gap-2">
                            <a class="btn btn-primary" href="{{ route('tickets.index') }}">
                                📋 Tiket yang Harus Dikerjakan ({{ $stats['total'] }})
                            </a>
                        </div>
                    @else
                        <div class="alert alert-success">
                            <strong>✅ Semua tiket sudah selesai!</strong><br>
                            Tidak ada tiket yang perlu dikerjakan saat ini.
                        </div>
                    @endif

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
