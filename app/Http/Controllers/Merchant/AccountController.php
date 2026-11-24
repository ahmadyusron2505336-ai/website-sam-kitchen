<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    private function done(string $message)
    {
        return redirect(route('merchant.settings.edit') . '#akun')->with('success', $message);
    }

    // TAMBAH AKUN (hanya pemilik toko)
    public function store(Request $request)
    {
        $merchant = $request->user()->merchant;
        abort_unless((int) $merchant->user_id === (int) $request->user()->id, 403,
            'Hanya pemilik toko yang dapat menambah akun.');

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        User::create([
            'name'        => $data['name'],
            'email'       => $data['email'],
            'password'    => $data['password'],   // teks biasa, sesuai login tanpa hash
            'role'        => 'pengelola',
            'merchant_id' => $merchant->id,
        ]);

        return $this->done('Akun baru berhasil ditambahkan.');
    }

    // EDIT AKUN (akun sendiri, atau semua akun jika pemilik toko)
    public function update(Request $request, User $user)
    {
        $actor    = $request->user();
        $merchant = $actor->merchant;
        $isSelf   = (int) $user->id === (int) $actor->id;
        $isOwner  = (int) $merchant->user_id === (int) $actor->id;

        abort_unless((int) $user->merchant_id === (int) $merchant->id && ($isSelf || $isOwner), 403);

        $data = $request->validate([
            'name'             => ['required', 'string', 'max:100'],
            'email'            => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user->id)],
            'password'         => ['nullable', 'string', 'min:6', 'confirmed'],
            'current_password' => ['nullable', 'string'],
        ]);

        $payload = ['name' => $data['name'], 'email' => $data['email']];

        if (filled($data['password'] ?? null)) {
            // Ganti password akun sendiri wajib memasukkan password lama
            if ($isSelf && ! hash_equals((string) $user->password, (string) $request->current_password)) {
                return back()->withErrors(['current_password' => 'Password lama salah.'])->withInput();
            }
            $payload['password'] = $data['password'];
        }

        $user->update($payload);

        return $this->done($isSelf ? 'Akun Anda berhasil diperbarui.' : "Akun {$user->name} berhasil diperbarui.");
    }

    // HAPUS AKUN (hanya pemilik toko, dan akun pemilik tidak bisa dihapus)
    public function destroy(Request $request, User $user)
    {
        $actor    = $request->user();
        $merchant = $actor->merchant;

        abort_unless((int) $merchant->user_id === (int) $actor->id, 403,
            'Hanya pemilik toko yang dapat menghapus akun.');
        abort_unless((int) $user->merchant_id === (int) $merchant->id, 403);

        if ((int) $user->id === (int) $merchant->user_id) {
            return redirect(route('merchant.settings.edit') . '#akun')
                ->withErrors(['account' => 'Akun pemilik toko tidak dapat dihapus.']);
        }

        $name = $user->name;
        $user->delete();

        return $this->done("Akun {$name} berhasil dihapus.");
    }
}