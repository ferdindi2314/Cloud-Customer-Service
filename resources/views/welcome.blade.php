@extends('layouts.bootstrap')

@section('title', config('app.name', 'Cloud Ticketing'))

@section('content')
<div class="bg-body-tertiary rounded-3 p-4 p-md-5 mb-4">
    <div class="container-fluid py-2">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <div class="badge text-bg-dark mb-3">Laravel + Firebase</div>
                <h1 class="display-5 fw-bold mb-3">Cloud Customer Support & Ticketing</h1>
                <p class="lead mb-4">Platform ticketing berbasis cloud untuk customer, agent, dan admin. Ticket disimpan di Firestore, lampiran di Firebase Storage, dan user/role di database Laravel agar autentikasi tetap stabil.</p>

                <div class="d-flex flex-wrap gap-2">
                    @guest
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg">Login</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-outline-secondary btn-lg">Register</a>
                        @endif
                    @else
                        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">Buka Dashboard</a>
                        <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary btn-lg">Lihat Tickets</a>
                    @endguest
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Akun Demo (Seeder)</h5>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Role</th>
                                        <th>Email</th>
                                        <th>Password</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span class="badge text-bg-danger">admin</span></td>
                                        <td>adminfirebase@gmail.com</td>
                                        <td>admin123</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge text-bg-warning">agent</span></td>
                                        <td>agent@gmail.com</td>
                                        <td>agent123</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge text-bg-success">customer</span></td>
                                        <td>customer@gmail.com</td>
                                        <td>customer123</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="small text-muted mt-3">Jika belum ada, jalankan: <code>php artisan migrate --seed</code></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Buat Ticket</h5>
                    <p class="card-text">Customer membuat ticket, mengunggah lampiran, dan memantau status secara transparan.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Workflow Agent</h5>
                    <p class="card-text">Agent mengelola antrian, membalas komentar, dan memproses penyelesaian ticket.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Kontrol Admin</h5>
                    <p class="card-text">Admin mengatur role user, melakukan assignment agent, dan kontrol status ticket.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Penyimpanan Cloud</h5>
                    <p class="card-text mb-0">Ticket & komentar di Firestore (dokumen), lampiran di Firebase Storage. Data user & role tetap di database Laravel agar autentikasi, session, dan policy konsisten.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">UI Bootstrap Saja</h5>
                    <p class="card-text mb-0">Landing page, auth, tickets, dan dashboard memakai layout Bootstrap agar tidak bentrok dengan Tailwind/Breeze.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
