<section>
    <p class="text-muted small mb-4">Pastikan akun Anda menggunakan password yang kuat dan aman.</p>

    @if(session('status') === 'password-updated')
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 10px; border-left: 4px solid #16a34a;">
            ✅ Password berhasil diperbarui.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="mb-3">
            <label for="update_password_current_password" class="form-label fw-semibold"><span class="text-primary">🔑</span> Password Saat Ini</label>
            <input id="update_password_current_password" name="current_password" type="password" class="form-control @error('current_password') is-invalid @enderror" style="border-radius: 10px; padding: 12px;" autocomplete="current-password" placeholder="Masukkan password saat ini">
            @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="update_password_password" class="form-label fw-semibold"><span class="text-primary">🆕</span> Password Baru</label>
            <input id="update_password_password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" style="border-radius: 10px; padding: 12px;" autocomplete="new-password" placeholder="Minimal 8 karakter">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="update_password_password_confirmation" class="form-label fw-semibold"><span class="text-primary">✔️</span> Konfirmasi Password</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control @error('password_confirmation') is-invalid @enderror" style="border-radius: 10px; padding: 12px;" autocomplete="new-password" placeholder="Ulangi password baru">
            @error('password_confirmation')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary" style="border-radius: 10px; padding: 10px 24px;">🔒 Update Password</button>
        </div>
    </form>
</section>
