@extends('layouts.bootstrap')

@section('title', 'Electronic Service - Dashboard')

@section('content')
<!-- Hero Section -->
<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 60px 20px; border-radius: 10px; margin-bottom: 50px;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 style="font-size: 3.5rem; font-weight: 700; margin-bottom: 20px;">🔧 Electronic Service Center</h1>
                <p style="font-size: 1.3rem; margin-bottom: 30px; line-height: 1.8; opacity: 0.95;">
                    Platform layanan purna jual & perbaikan perangkat elektronik berbasis cloud. Kelola ticket service, status garansi, sparepart, dan penugasan teknisi dengan cepat dan transparan.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    @guest
                        <a href="{{ route('login') }}" class="btn btn-light btn-lg fw-bold">🔐 Masuk Sekarang</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg fw-bold">📝 Daftar Akun</a>
                        @endif
                    @else
                        <a href="{{ route('tickets.index') }}" class="btn btn-light btn-lg fw-bold">🎫 Lihat Tiket</a>
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-light btn-lg fw-bold">Keluar</button>
                        </form>
                    @endguest
                </div>
            </div>
        </div>
    </div>
</div>
    <!-- Minimal Dashboard Cards -->
    <div class="container mb-5">
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card h-100" style="border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase" style="letter-spacing: 0.06em; font-size: 0.8rem;">Ticket Aktif</h6>
                        <div class="d-flex align-items-baseline gap-2">
                            <span style="font-size: 2rem; font-weight: 700; color: #667eea;">Live</span>
                            <small class="text-muted">monitor langsung</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100" style="border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase" style="letter-spacing: 0.06em; font-size: 0.8rem;">Teknisi</h6>
                        <div class="d-flex align-items-baseline gap-2">
                            <span style="font-size: 2rem; font-weight: 700; color: #764ba2;">Ready</span>
                            <small class="text-muted">penugasan otomatis</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100" style="border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase" style="letter-spacing: 0.06em; font-size: 0.8rem;">Sparepart</h6>
                        <div class="d-flex align-items-baseline gap-2">
                            <span style="font-size: 2rem; font-weight: 700; color: #12b981;">Terkontrol</span>
                            <small class="text-muted">tracking kebutuhan</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100" style="border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase" style="letter-spacing: 0.06em; font-size: 0.8rem;">Garansi</h6>
                        <div class="d-flex align-items-baseline gap-2">
                            <span style="font-size: 2rem; font-weight: 700; color: #f59e0b;">Terpantau</span>
                            <small class="text-muted">validasi cepat</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card h-100" style="border: none; box-shadow: 0 5px 20px rgba(0,0,0,0.08);">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Operasional Layanan</h5>
                        <p class="text-muted mb-4" style="line-height: 1.7;">Pantau antrean service, update status, dan komunikasikan progres ke pelanggan secara real-time.</p>
                        <div class="d-flex flex-wrap gap-3">
                            <div class="px-3 py-2 rounded" style="background:#f3f4ff; color:#4f46e5; font-weight:600;">📥 Intake Ticket</div>
                            <div class="px-3 py-2 rounded" style="background:#fdf2f8; color:#db2777; font-weight:600;">🛠️ Diagnosa & Sparepart</div>
                            <div class="px-3 py-2 rounded" style="background:#ecfdf3; color:#047857; font-weight:600;">📦 Proses & Uji</div>
                            <div class="px-3 py-2 rounded" style="background:#fff7ed; color:#c2410c; font-weight:600;">✅ Selesai & Serah</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100" style="border: none; box-shadow: 0 5px 20px rgba(0,0,0,0.08);">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Quick Actions</h5>
                        <div class="d-grid gap-2">
                            <a href="{{ route('tickets.index') }}" class="btn btn-primary btn-lg">🎫 Lihat Ticket</a>
                            <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary btn-lg">➕ Ticket Baru</a>
                            <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary">📊 Monitoring Layanan</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- CTA Section -->
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 50px 30px; border-radius: 10px; text-align: center; margin-top: 50px;">
        <h2 style="font-size: 2rem; margin-bottom: 20px;">Siap Menggunakan Electronic Service?</h2>
        <p style="font-size: 1.1rem; margin-bottom: 30px; opacity: 0.95;">Implementasikan service center berbasis cloud dengan proses terukur, transparan, dan scalable</p>
        <div class="d-flex justify-content-center flex-wrap gap-3">
            @guest
                <a href="{{ route('login') }}" class="btn btn-light btn-lg fw-bold">🔐 Masuk Sekarang</a>
            @else
                <a href="{{ route('tickets.index') }}" class="btn btn-light btn-lg fw-bold">🎫 Lihat Tiket</a>
            @endguest
        </div>
    </div>
<!-- Footer Stats -->
<div style="background: #f8f9fa; padding: 30px 20px; margin-top: 50px;">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 mb-3">
                <h3 style="color: #667eea; font-weight: 700;">100%</h3>
                <p style="color: #666;">Cloud Native</p>
            </div>
            <div class="col-md-3 mb-3">
                <h3 style="color: #764ba2; font-weight: 700;">Real-time</h3>
                <p style="color: #666;">Data Sync</p>
            </div>
            <div class="col-md-3 mb-3">
                <h3 style="color: #667eea; font-weight: 700;">24/7</h3>
                <p style="color: #666;">Available</p>
            </div>
            <div class="col-md-3 mb-3">
                <h3 style="color: #764ba2; font-weight: 700;">∞</h3>
                <p style="color: #666;">Scalable</p>
            </div>
        </div>
    </div>
</div>

@endsection
