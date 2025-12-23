@extends('layouts.sidebar')

@section('page-title', '👤 Edit Profile')
@section('title', 'Edit Profile')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Informasi Akun</h5>
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Password</h5>
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title text-danger">Hapus Akun</h5>
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="fw-semibold">Petunjuk</h6>
                    <p class="small text-muted">
                        Gunakan form di sebelah kiri untuk memperbarui profil dan password Anda. Menghapus akun bersifat permanen.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
