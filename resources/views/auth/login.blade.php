@extends('layouts.bootstrap')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">{{ __('Login') }}</div>

            <div class="card-body">
                @if(session('status'))
                    <div class="alert alert-info">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">{{ __('Email') }}</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">{{ __('Password') }}</label>
                        <input id="password" type="password" name="password" required autocomplete="current-password" class="form-control">
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember_me" name="remember">
                        <label class="form-check-label" for="remember_me">{{ __('Remember me') }}</label>
                    </div>

                    <div class="d-grid">
                        <button class="btn btn-primary btn-lg" type="submit">{{ __('Log in') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@if($errors->has('email'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Email Tidak Ditemukan',
        text: '{{ $errors->first('email') }}',
        confirmButtonText: 'OK'
    });
</script>
@endif

@if($errors->has('password'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Password Salah',
        text: 'Password yang Anda masukkan tidak sesuai.',
        confirmButtonText: 'OK'
    });
</script>
@endif

@endsection
