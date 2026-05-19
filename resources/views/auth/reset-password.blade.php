<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Ulang Kata Sandi - SI Persediaan JJ Top Cosmindo</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-navy: #1a337e;
            --accent-blue: #0d6efd;
            --soft-bg: #f4f7fe;
        }

        body {
            background-color: var(--soft-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
        }

        .login-container {
            width: 900px;
            max-width: 95%;
            height: 600px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            display: flex;
            overflow: hidden;
        }

        .login-sidebar {
            flex: 1;
            background: linear-gradient(135deg, var(--primary-navy) 0%, #2a4ec1 100%);
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: white;
            position: relative;
        }

        .login-sidebar::before {
            content: "";
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }

        .login-sidebar::after {
            content: "";
            position: absolute;
            bottom: -80px;
            left: -30px;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 50%;
        }

        .login-form-container {
            flex: 1;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .brand-logo {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 10px;
            letter-spacing: -1px;
        }

        .input-group {
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            background: white;
            transition: all 0.2s ease-in-out;
            display: flex;
            align-items: center;
        }

        .input-group:focus-within {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
            border-color: var(--accent-blue);
        }

        .form-control {
            border: none;
            background: transparent;
            padding: 12px 15px 12px 0;
            box-shadow: none !important;
            border-radius: 0;
        }

        .form-control:focus {
            box-shadow: none !important;
            border-color: transparent;
            background: transparent;
        }

        .input-group-text {
            background: transparent;
            border: none;
            color: #adb5bd;
            padding-left: 15px;
            padding-right: 12px;
        }

        .input-group-text.toggle-password {
            padding-left: 12px;
            padding-right: 15px;
            cursor: pointer;
            border: none;
        }

        .btn-login {
            background-color: var(--primary-navy);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            color: white;
            transition: all 0.3s;
        }

        .btn-login:hover {
            background-color: #12245a;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 51, 126, 0.3);
        }

        @media (max-width: 768px) {
            .login-sidebar {
                display: none;
            }
            .login-container {
                height: auto;
            }
        }
    </style>
</head>

<body>

    <div class="login-container">
        <!-- Sidebar Branding -->
        <div class="login-sidebar">
            <div class="brand-logo">JJ TOP</div>
            <h4 class="fw-light mb-4">Cosmindo Sidoarjo</h4>
            <p class="opacity-75">Sistem Informasi Pengelolaan Persediaan Bahan Baku Produksi Kosmetik Berbasis Web</p>
            <div class="mt-5">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-white bg-opacity-20 rounded-circle p-2 me-3">
                        <i class="fas fa-check small"></i>
                    </div>
                    <span>Metode EOQ & ROP</span>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-white bg-opacity-20 rounded-circle p-2 me-3">
                        <i class="fas fa-check small"></i>
                    </div>
                    <span>Optimasi Stok</span>
                </div>
                <div class="d-flex align-items-center">
                    <div class="bg-white bg-opacity-20 rounded-circle p-2 me-3">
                        <i class="fas fa-check small"></i>
                    </div>
                    <span>Monitoring Real-time</span>
                </div>
            </div>
        </div>

        <!-- Reset Password Form -->
        <div class="login-form-container">
            <h3 class="fw-bold mb-1">Atur Ulang Kata Sandi</h3>
            <p class="text-muted mb-4 small">Silakan masukkan kata sandi baru untuk akun Anda.</p>

            <form action="{{ route('password.update') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Kata Sandi Baru</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" id="password" class="form-control form-control-with-icon password-input" placeholder="••••••••" required minlength="8">
                        <span class="input-group-text toggle-password" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Konfirmasi Kata Sandi</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control form-control-with-icon password-input" placeholder="••••••••" required minlength="8">
                        <span class="input-group-text toggle-password" id="togglePasswordConfirm">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn btn-login w-100 mb-3">SIMPAN KATA SANDI BARU</button>
            </form>
            
            <p class="text-center small text-muted mt-auto">© 2024 PT. JJ Top Cosmindo Sidoarjo</p>
        </div>
    </div>

    @if(session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: '{{ session('error') }}',
            confirmButtonColor: '#1a337e'
        });
    </script>
    @endif

    @if($errors->any())
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Validasi Gagal',
            text: '{{ $errors->first() }}',
            confirmButtonColor: '#1a337e'
        });
    </script>
    @endif

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
</body>

</html>
