<section>
    <h2 class="h5 text-danger mb-2">{{ __('Delete Account') }}</h2>
    <p class="small text-muted mb-3">{{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}</p>

    <!-- Button trigger modal -->
    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
        {{ __('Delete Account') }}
    </button>

    <!-- Modal -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="confirmDeleteLabel">{{ __('Are you sure you want to delete your account?') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p class="small text-muted">{{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}</p>
            <form id="deleteAccountForm" method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')
                <div class="mb-3">
                    <label for="password" class="form-label">{{ __('Password') }}</label>
                    <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" placeholder="{{ __('Password') }}" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
            <button type="submit" form="deleteAccountForm" class="btn btn-danger">{{ __('Delete Account') }}</button>
          </div>
        </div>
      </div>
    </div>
</section>
