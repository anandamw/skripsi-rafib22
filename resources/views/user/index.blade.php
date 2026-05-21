@extends('layouts.app')

@section('title', 'Manajemen Pengguna')
@section('page_title', 'Administrator')

@section('content')

{{-- Stats Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-box bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <div class="fw-bold fs-4 lh-1">{{ $users->count() }}</div>
                    <small class="text-muted">Total Pengguna</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-box bg-success bg-opacity-10 text-success">
                    <i class="fas fa-user-check"></i>
                </div>
                <div>
                    <div class="fw-bold fs-4 lh-1">{{ $users->where('aktif', true)->count() }}</div>
                    <small class="text-muted">Akun Aktif</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-box bg-danger bg-opacity-10 text-danger">
                    <i class="fas fa-user-slash"></i>
                </div>
                <div>
                    <div class="fw-bold fs-4 lh-1">{{ $users->where('aktif', false)->count() }}</div>
                    <small class="text-muted">Nonaktif</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-box bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div>
                    <div class="fw-bold fs-4 lh-1">{{ $users->where('role', 'manajer')->count() }}</div>
                    <small class="text-muted">Manajer</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Main Table Card --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h6 class="fw-bold mb-0"><i class="fas fa-users-cog me-2 text-primary"></i> Daftar Akun Pengguna</h6>
                <small class="text-muted">Kelola akses dan hak pengguna sistem</small>
            </div>
            <button type="button" class="btn btn-primary rounded-3 px-4" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-user-plus me-2"></i> Tambah Pengguna
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle" id="userTable" style="font-size: 0.875rem;">
                <thead class="table-light">
                    <tr>
                        <th width="40">#</th>
                        <th>Nama Pengguna</th>
                        <th>Email</th>
                        <th>Role Akses</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" width="110">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $i => $usr)
                    <tr class="{{ !$usr->aktif ? 'opacity-50' : '' }}">
                        <td class="text-muted small">{{ $i + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($usr->nama) }}&background={{ $usr->role == 'manajer' ? '1a337e' : ($usr->role == 'gudang' ? '198754' : ($usr->role == 'purchasing' ? '0dcaf0' : 'ffc107')) }}&color=fff&size=36"
                                    class="rounded-circle" width="36" height="36" alt="{{ $usr->nama }}">
                                <div>
                                    <div class="fw-semibold text-dark">{{ $usr->nama }}</div>
                                    @if(auth()->id() === $usr->id)
                                        <small class="text-primary"><i class="fas fa-circle-dot me-1" style="font-size:0.6rem"></i>Anda</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-muted">{{ $usr->email }}</td>
                        <td>
                            @php
                                $roleConfig = [
                                    'manajer'    => ['bg-primary',   'fa-user-shield', 'Manajer'],
                                    'purchasing' => ['bg-info text-dark', 'fa-shopping-bag', 'Staff Purchasing'],
                                    'produksi'   => ['bg-warning text-dark', 'fa-industry', 'Staff Produksi'],
                                    'gudang'     => ['bg-success',   'fa-warehouse', 'Admin Gudang'],
                                ];
                                $rc = $roleConfig[$usr->role] ?? ['bg-secondary', 'fa-user', ucfirst($usr->role)];
                            @endphp
                            <span class="badge {{ $rc[0] }} rounded-pill px-3">
                                <i class="fas {{ $rc[1] }} me-1"></i> {{ $rc[2] }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($usr->aktif)
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2">
                                    <i class="fas fa-circle me-1" style="font-size:0.5rem"></i> Aktif
                                </span>
                            @else
                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2">
                                    <i class="fas fa-circle me-1" style="font-size:0.5rem"></i> Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                {{-- Edit --}}
                                <button type="button"
                                    class="btn btn-sm btn-light text-warning border btn-edit"
                                    data-id="{{ $usr->id }}"
                                    data-nama="{{ $usr->nama }}"
                                    data-email="{{ $usr->email }}"
                                    data-role="{{ $usr->role }}"
                                    title="Edit Pengguna"
                                    data-bs-toggle="tooltip">
                                    <i class="fas fa-edit"></i>
                                </button>

                                {{-- Toggle Aktif/Nonaktif --}}
                                @if(auth()->id() !== $usr->id)
                                <form action="{{ route('user.toggle', $usr->id) }}" method="POST" class="d-inline toggle-form">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="btn btn-sm btn-light border {{ $usr->aktif ? 'text-warning' : 'text-success' }}"
                                        title="{{ $usr->aktif ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}"
                                        data-nama="{{ $usr->nama }}"
                                        data-aktif="{{ $usr->aktif ? '1' : '0' }}"
                                        data-bs-toggle="tooltip">
                                        <i class="fas {{ $usr->aktif ? 'fa-ban' : 'fa-check-circle' }}"></i>
                                    </button>
                                </form>

                                {{-- Hapus Permanen --}}
                                <form action="{{ route('user.destroy', $usr->id) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="btn btn-sm btn-light border text-danger"
                                        title="Hapus Permanen"
                                        data-nama="{{ $usr->nama }}"
                                        data-bs-toggle="tooltip">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                                @else
                                <button type="button" class="btn btn-sm btn-light border text-muted" disabled title="Tidak bisa menonaktifkan akun sendiri" data-bs-toggle="tooltip">
                                    <i class="fas fa-ban"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light border text-muted" disabled title="Tidak bisa menghapus akun sendiri" data-bs-toggle="tooltip">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ===================== MODAL TAMBAH USER ===================== --}}
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="addUserModalLabel">
                        <i class="fas fa-user-plus text-primary me-2"></i> Tambah Pengguna Baru
                    </h5>
                    <small class="text-muted">Isi data lengkap untuk membuat akun baru</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('user.store') }}" method="POST">
                @csrf
                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">NAMA LENGKAP <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                            <input type="text" name="nama" class="form-control border-start-0" required placeholder="Cth: Budi Santoso">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">ALAMAT EMAIL <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                            <input type="email" name="email" class="form-control border-start-0" required placeholder="Cth: budi@jjtop.com">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">ROLE AKSES <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required id="add_role">
                            <option value="">— Pilih Role —</option>
                            <option value="manajer">🛡️ Manajer — Approve PO, Evaluasi TIC, Admin</option>
                            <option value="purchasing">🛒 Staff Purchasing — Buat PO, Lihat EOQ</option>
                            <option value="gudang">🏭 Admin Gudang — Kelola Stok, Kalkulasi EOQ</option>
                            <option value="produksi">⚙️ Staff Produksi — Input Pemakaian Harian</option>
                        </select>
                        <div id="roleHint" class="mt-2 p-2 rounded-3 bg-light small text-muted d-none"></div>
                    </div>
                    <div class="mb-1">
                        <label class="form-label small fw-bold text-muted">PASSWORD <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                            <input type="password" name="password" id="add_password" class="form-control border-start-0 border-end-0" required placeholder="Minimal 8 karakter" minlength="8">
                            <button class="btn btn-light border border-start-0" type="button" id="toggleAddPass">
                                <i class="fas fa-eye text-muted"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light rounded-bottom px-4 py-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="fas fa-save me-2"></i> Simpan Pengguna
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===================== MODAL EDIT USER ===================== --}}
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="editUserModalLabel">
                        <i class="fas fa-user-edit text-warning me-2"></i> Edit Data Pengguna
                    </h5>
                    <small class="text-muted">Perbarui informasi akun pengguna</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editUserForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">NAMA LENGKAP <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                            <input type="text" name="nama" id="edit_nama" class="form-control border-start-0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">ALAMAT EMAIL <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                            <input type="email" name="email" id="edit_email" class="form-control border-start-0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">ROLE AKSES <span class="text-danger">*</span></label>
                        <select name="role" id="edit_role" class="form-select" required>
                            <option value="manajer">🛡️ Manajer</option>
                            <option value="purchasing">🛒 Staff Purchasing</option>
                            <option value="gudang">🏭 Admin Gudang</option>
                            <option value="produksi">⚙️ Staff Produksi</option>
                        </select>
                    </div>
                    <div class="mb-1">
                        <label class="form-label small fw-bold text-muted">UBAH PASSWORD</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                            <input type="password" name="password" id="edit_password" class="form-control border-start-0 border-end-0" placeholder="Kosongkan jika tidak ingin diubah" minlength="8">
                            <button class="btn btn-light border border-start-0" type="button" id="toggleEditPass">
                                <i class="fas fa-eye text-muted"></i>
                            </button>
                        </div>
                        <small class="text-muted d-block mt-1"><i class="fas fa-info-circle me-1"></i>Hanya isi jika ingin mereset password akun ini.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light rounded-bottom px-4 py-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning fw-bold px-4 text-dark">
                        <i class="fas fa-save me-2"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {

    // DataTable
    $('#userTable').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json' },
        order: [[3, 'asc'], [1, 'asc']],
        pageLength: 25,
        columnDefs: [{ orderable: false, targets: [0, 5] }]
    });

    // Bootstrap Tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // ── Edit Button ──
    $('.btn-edit').on('click', function () {
        var id    = $(this).data('id');
        var nama  = $(this).data('nama');
        var email = $(this).data('email');
        var role  = $(this).data('role');

        $('#edit_nama').val(nama);
        $('#edit_email').val(email);
        $('#edit_role').val(role);
        $('#edit_password').val('');

        var url = "{{ route('user.update', ':id') }}".replace(':id', id);
        $('#editUserForm').attr('action', url);

        $('#editUserModal').modal('show');
    });

    // ── Toggle Aktif/Nonaktif Confirm ──
    $('.toggle-form button[type="submit"]').on('click', function (e) {
        e.preventDefault();
        var form  = $(this).closest('form');
        var nama  = $(this).data('nama');
        var aktif = $(this).data('aktif') == '1';
        var aksi  = aktif ? 'menonaktifkan' : 'mengaktifkan';
        var icon  = aktif ? 'warning' : 'success';

        Swal.fire({
            title: 'Konfirmasi',
            text: 'Apakah Anda yakin ingin ' + aksi + ' akun "' + nama + '"?',
            icon: icon,
            showCancelButton: true,
            confirmButtonColor: aktif ? '#dc3545' : '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, ' + aksi,
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (result.isConfirmed) form.submit();
        });
    });

    // ── Role Hint ──
    var roleHints = {
        'manajer':    '🛡️ Dapat menyetujui PO, melihat semua laporan, evaluasi TIC, dan mengelola pengguna.',
        'purchasing': '🛒 Dapat membuat Purchase Order dan melihat hasil kalkulasi EOQ.',
        'gudang':     '🏭 Dapat mengelola stok masuk/keluar, kalkulasi EOQ & ROP, dan evaluasi TIC.',
        'produksi':   '⚙️ Dapat menginput pemakaian bahan baku harian untuk proses produksi.',
    };
    $('#add_role').on('change', function () {
        var hint = roleHints[$(this).val()];
        if (hint) {
            $('#roleHint').text(hint).removeClass('d-none');
        } else {
            $('#roleHint').addClass('d-none');
        }
    });

    // ── Toggle Password Visibility ──
    $('#toggleAddPass').on('click', function () {
        var inp = $('#add_password');
        var icon = $(this).find('i');
        if (inp.attr('type') === 'password') {
            inp.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            inp.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    $('#toggleEditPass').on('click', function () {
        var inp = $('#edit_password');
        var icon = $(this).find('i');
        if (inp.attr('type') === 'password') {
            inp.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            inp.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

});
</script>
@endpush
