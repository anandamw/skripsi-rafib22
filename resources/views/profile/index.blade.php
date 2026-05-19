@extends('layouts.app')

@section('title', 'Profil Saya')
@section('page_title', 'Profil Pengguna')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm p-4 mb-4 rounded-4">
            <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->nama) }}&background=1a337e&color=fff&size=100" class="rounded-circle me-4 shadow-sm" alt="User Avatar" style="width: 80px; height: 80px; object-fit: cover; border: 3px solid #f4f7fe;">
                <div>
                    <h4 class="fw-bold mb-1">{{ $user->nama }}</h4>
                    <p class="text-muted mb-1"><i class="fas fa-envelope me-2"></i>{{ $user->email }}</p>
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill fw-semibold">{{ $user->role_label }}</span>
                </div>
            </div>

            <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-lock me-2"></i> Atur Ulang Kata Sandi</h6>
            <p class="text-muted small mb-4">Anda dapat memperbarui kata sandi akun Anda di bawah ini tanpa perlu memasukkan kata sandi lama.</p>

            @if($errors->any())
                <div class="alert alert-danger small rounded-3 shadow-sm mb-4">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('profile.password') }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Kata Sandi Baru</label>
                    <div class="input-group rounded-3 border bg-white d-flex align-items-center" style="overflow: hidden;">
                        <span class="input-group-text bg-transparent border-0 text-muted ps-3 pe-2"><i class="fas fa-key"></i></span>
                        <input type="password" name="password" id="password" class="form-control border-0 bg-transparent py-2 ps-1 pe-0" placeholder="Minimal 8 karakter" required minlength="8" style="box-shadow: none !important;">
                        <span class="input-group-text toggle-password bg-transparent border-0 text-muted pe-3 ps-2" id="togglePassword" style="cursor: pointer;">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Konfirmasi Kata Sandi Baru</label>
                    <div class="input-group rounded-3 border bg-white d-flex align-items-center" style="overflow: hidden;">
                        <span class="input-group-text bg-transparent border-0 text-muted ps-3 pe-2"><i class="fas fa-key"></i></span>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control border-0 bg-transparent py-2 ps-1 pe-0" placeholder="Ulangi kata sandi baru" required minlength="8" style="box-shadow: none !important;">
                        <span class="input-group-text toggle-password bg-transparent border-0 text-muted pe-3 ps-2" id="togglePasswordConfirm" style="cursor: pointer;">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary fw-bold px-4 rounded-3 shadow-sm">
                        <i class="fas fa-save me-2"></i> Simpan Kata Sandi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Toggle Password 1
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        if (togglePassword && password) {
            togglePassword.addEventListener('click', function () {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                const icon = this.querySelector('i');
                if (type === 'text') {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        }

        // Toggle Password 2
        const togglePasswordConfirm = document.querySelector('#togglePasswordConfirm');
        const passwordConfirm = document.querySelector('#password_confirmation');
        if (togglePasswordConfirm && passwordConfirm) {
            togglePasswordConfirm.addEventListener('click', function () {
                const type = passwordConfirm.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordConfirm.setAttribute('type', type);
                const icon = this.querySelector('i');
                if (type === 'text') {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        }
    });
</script>
@endpush
