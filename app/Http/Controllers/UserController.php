<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index()
    {
        $users = User::orderBy('role', 'asc')->orderBy('nama', 'asc')->get();
        return view('user.index', compact('users'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in([User::ROLE_MANAJER, User::ROLE_PURCHASING, User::ROLE_PRODUKSI, User::ROLE_GUDANG])],
        ]);

        User::create([
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'aktif' => true,
        ]);

        return redirect()->route('user.index')->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', Rule::in([User::ROLE_MANAJER, User::ROLE_PURCHASING, User::ROLE_PRODUKSI, User::ROLE_GUDANG])],
        ]);

        $dataToUpdate = [
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if (!empty($validated['password'])) {
            $dataToUpdate['password'] = Hash::make($validated['password']);
        }

        $user->update($dataToUpdate);

        return redirect()->route('user.index')->with('success', 'Data user berhasil diperbarui.');
    }

    /**
     * Remove or disable the specified user.
     */
    public function destroy(User $user)
    {
        // Cegah manajer menghapus dirinya sendiri
        if (auth()->id() === $user->id) {
            return redirect()->back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri yang sedang login.');
        }

        // Toggle status aktif (Soft disable instead of hard delete)
        $user->update([
            'aktif' => !$user->aktif
        ]);

        $statusMsg = $user->aktif ? 'diaktifkan kembali' : 'dinonaktifkan';
        return redirect()->route('user.index')->with('success', 'Akun user berhasil ' . $statusMsg . '.');
    }

    /**
     * Tampilkan halaman profil pengguna yang sedang login.
     */
    public function profile()
    {
        $user = auth()->user();
        return view('profile.index', compact('user'));
    }

    /**
     * Perbarui kata sandi pengguna tanpa meminta kata sandi lama.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = auth()->user();
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()->route('profile.index')->with('success', 'Kata sandi Anda berhasil diperbarui.');
    }
}
