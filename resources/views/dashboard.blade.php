@extends('layouts.bootstrap')

@section('title', 'Dashboard')

@section('content')
@php($role = auth()->user()->role ?? 'customer')

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-1">Dashboard</h1>
        <div class="text-muted">Selamat datang, {{ auth()->user()->name }}.</div>
    </div>
    <div>
        @if($role === 'admin')
            <span class="badge text-bg-danger">admin</span>
        @elseif($role === 'agent')
            <span class="badge text-bg-warning">agent</span>
        @else
            <span class="badge text-bg-success">customer</span>
        @endif
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                @if($role === 'admin')
                    <h5 class="card-title">Admin Dashboard</h5>
                    <p class="card-text">Kelola user & role, pantau ticket, assign agent, dan ubah status ticket.</p>

                    <div class="d-flex flex-wrap gap-2">
                        <a class="btn btn-primary" href="{{ route('tickets.index') }}">Lihat Semua Tickets</a>
                        <a class="btn btn-outline-secondary" href="{{ route('admin.users.index') }}">Kelola Users</a>
                    </div>
                @elseif($role === 'agent')
                    <h5 class="card-title">Agent Dashboard</h5>
                    <p class="card-text">Tangani ticket yang masuk, diskusi via komentar, dan update status sesuai proses.</p>

                    <div class="d-flex flex-wrap gap-2">
                        <a class="btn btn-primary" href="{{ route('tickets.index') }}">Buka Tickets</a>
                    </div>
                @else
                    <h5 class="card-title">Customer Dashboard</h5>
                    <p class="card-text">Buat ticket baru, unggah lampiran, dan pantau progress penyelesaian.</p>

                    <div class="d-flex flex-wrap gap-2">
                        <a class="btn btn-primary" href="{{ route('tickets.create') }}">Buat Ticket</a>
                        <a class="btn btn-outline-secondary" href="{{ route('tickets.index') }}">Ticket Saya</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Quick Info</div>
            <div class="card-body">
                <div class="small text-muted">Role aktif</div>
                <div class="fw-semibold mb-3">{{ $role }}</div>

                <div class="small text-muted">Akses utama</div>
                <ul class="mb-0">
                    <li>Tickets (Firestore)</li>
                    <li>Lampiran (Firebase Storage)</li>
                    <li>User & Role (Database Laravel)</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
