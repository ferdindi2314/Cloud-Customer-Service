@extends('layouts.bootstrap')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">{{ __('Verify Email Address') }}</div>

            <div class="card-body">
                <p class="text-muted">{{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}</p>

                @if (session('status') == 'verification-link-sent')
                    <div class="alert alert-success">{{ __('A new verification link has been sent to the email address you provided during registration.') }}</div>
                @endif

                <div class="d-flex justify-content-between">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button class="btn btn-primary" type="submit">{{ __('Resend Verification Email') }}</button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-link">{{ __('Log Out') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
