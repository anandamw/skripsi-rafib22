@extends('layouts.app')

@section('title', 'Manajemen User')
@section('page_title', 'Administrator')

@section('content')
<div class="card border-0 shadow-sm p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h6 class="fw-bold mb-0"><i class="fas fa-users-cog me-2"></i> Daftar Akun Pengguna</h6>
        <button type="button" class="btn btn-primary btn-sm rounded-3 px-3" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-plus me-2"></i> Tambah User
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle" id="userTable" style="font-size: 0.9rem;">
            <thead class="table-light">
                <tr>
                    <th>Nama Pengguna</th>
                    <th>Email</th>
                    <th>Role Akses</th>
                    <th class="text-center">Status</th>
                    <th class="text-center" width="120">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $usr)
                <tr>
                    <td class="fw-bold text-dark">
                        <i class="fas fa-user-circle text-secondary me-2 fs-5 align-middle"></i>
                        {{ $usr->nama }}
                    </td>
                    <td>{{ $usr->email }}</td>
                    <td>
                        @if($usr->role == 'manajer') <span class="badge bg-primary">Manajer</span>
                        @elseif($usr->role == 'purchasing') <span class="badge bg-info text-dark">Purchasing</span>
                        @elseif($usr->role == 'produksi') <span class="badge bg-warning text-dark">Produksi</span>
                        @elseif($usr->role == 'gudang') <span class="badge bg-success">Gudang</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($usr->aktif)
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Aktif</span>
                        @else
                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Nonaktif</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            <button type="button" class="btn btn-sm btn-light text-warning btn-edit" 
                                data-id="{{ $usr->id }}" 
                                data-nama="{{ $usr->nama }}" 
                                data-email="{{ $usr->email }}" 
                                data-role="{{ $usr->role }}" 
                                title="Edit User">
                                <i class="fas fa-edit"></i>
                            </button>
                            
                            @if(auth()->id() !== $usr->id)
                            <form action="{{ route('user.destroy', $usr->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light {{ $usr->aktif ? 'text-danger' : 'text-success' }}" 
                                    title="{{ $usr->aktif ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}"
                                    onclick="return confirm('Apakah Anda yakin ingin {{ $usr->aktif ? 'menonaktifkan' : 'mengaktifkan' }} akun ini?')">
                                    <i class="fas {{ $usr->aktif ? 'fa-ban' : 'fa-check-circle' }}"></i>
                                </button>
                            </form>
                            @else
                            <button type="button" class="btn btn-sm btn-light text-muted" disabled title="Anda tidak bisa menonaktifkan akun sendiri">
                                <i class="fas fa-ban"></i>
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

<!-- Modal Add User -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold" id="addUserModalLabel"><i class="fas fa-user-plus me-2"></i> Tambah User Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('user.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">NAMA LENGKAP</label>
                        <input type="text" name="nama" class="form-control" required placeholder="Cth: Budi Santoso">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">ALAMAT EMAIL</label>
                        <input type="email" name="email" class="form-control" required placeholder="Cth: budi@jjtop.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">ROLE AKSES</label>
                        <select name="role" class="form-select" required>
                            <option value="">Pilih Role...</option>
                            <option value="purchasing">Staff Purchasing (Pembuat PO, Cek EOQ)</option>
                            <option value="produksi">Staff Produksi (Input Pemakaian Harian)</option>
                            <option value="gudang">Admin Gudang (Input Stok Masuk/Keluar)</option>
                            <option value="manajer">Manajer (Approve PO, Evaluasi TIC, Admin)</option>
                        </select>
                    </div>
                    <div class="mb-1">
                        <label class="form-label small fw-bold text-muted">PASSWORD</label>
                        <input type="password" name="password" class="form-control" required placeholder="Minimal 8 karakter" minlength="8">
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Simpan User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit User -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark border-0">
                <h5 class="modal-title fw-bold" id="editUserModalLabel"><i class="fas fa-edit me-2"></i> Edit Data User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editUserForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">NAMA LENGKAP</label>
                        <input type="text" name="nama" id="edit_nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">ALAMAT EMAIL</label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">ROLE AKSES</label>
                        <select name="role" id="edit_role" class="form-select" required>
                            <option value="purchasing">Staff Purchasing</option>
                            <option value="produksi">Staff Produksi</option>
                            <option value="gudang">Admin Gudang</option>
                            <option value="manajer">Manajer</option>
                        </select>
                    </div>
                    <div class="mb-1">
                        <label class="form-label small fw-bold text-muted">UBAH PASSWORD</label>
                        <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin diubah" minlength="8">
                        <small class="text-muted d-block mt-1">Hanya diisi jika ingin mereset password akun ini.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning fw-bold px-4">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#userTable').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json' },
            order: [[2, 'asc'], [0, 'asc']], // Sort by role, then name
            pageLength: 25
        });

        // Edit button handler
        $('.btn-edit').on('click', function() {
            var id = $(this).data('id');
            var nama = $(this).data('nama');
            var email = $(this).data('email');
            var role = $(this).data('role');
            
            $('#edit_nama').val(nama);
            $('#edit_email').val(email);
            $('#edit_role').val(role);
            
            // Set form action dynamic URL
            var url = "{{ route('user.update', ':id') }}";
            url = url.replace(':id', id);
            $('#editUserForm').attr('action', url);
            
            $('#editUserModal').modal('show');
        });
    });
</script>
@endpush
