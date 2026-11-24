<?php

namespace App\Http\Controllers\Merchant;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public const DAYS = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];

    public function edit(Request $request)
    {
        $merchant = $request->user()->merchant;

        $hours = [];
        foreach (self::DAYS as $d) {
            $hours[$d] = $merchant->opening_hours[$d] ?? ['open' => '08:00', 'close' => '21:00', 'closed' => false];
        }

            return view('merchant.settings', [
            'merchant' => $merchant,
            'hours'    => $hours,
            'days'     => self::DAYS,
            'accounts' => User::where('merchant_id', $merchant->id)
                            ->orderByRaw('id = ? desc', [$merchant->user_id])   // pemilik di urutan pertama
                            ->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request)
    {
        $merchant = $request->user()->merchant;

        $data = $request->validate([
            'name'                => ['required', 'string', 'max:100'],
            'address'             => ['nullable', 'string'],
            'whatsapp'            => ['nullable', 'regex:/^[0-9+]{9,15}$/'],
            'business_email'      => ['nullable', 'email', 'max:100'],
            'logo'                => ['nullable', 'image', 'max:2048'],
            'banner'              => ['nullable', 'image', 'max:4096'],
            'hours.*.open'        => ['nullable', 'date_format:H:i'],
            'hours.*.close'       => ['nullable', 'date_format:H:i'],
            'bank_name'           => ['nullable', 'string', 'max:50'],
            'bank_account_number' => ['nullable', 'string', 'max:30'],
            'bank_account_name'   => ['nullable', 'string', 'max:100'],
            'qris_image'          => ['nullable', 'image', 'max:4096'],
        ]);

        // Jam operasional
        $hours = [];
        foreach (self::DAYS as $d) {
            $hours[$d] = [
                'open'   => $request->input("hours.$d.open", '08:00'),
                'close'  => $request->input("hours.$d.close", '21:00'),
                'closed' => $request->boolean("hours.$d.closed"),
            ];
        }
        $data['opening_hours'] = $hours;
        unset($data['hours']);

        // Logo & banner (unggah baru, hapus, atau biarkan)
        foreach (['logo', 'banner', 'qris_image'] as $field) {
            if ($request->hasFile($field)) {
                if ($merchant->$field) Storage::disk('public')->delete($merchant->$field);
                $data[$field] = $request->file($field)->store("merchants/{$field}", 'public');
            } elseif ($request->boolean("remove_{$field}")) {
                if ($merchant->$field) Storage::disk('public')->delete($merchant->$field);
                $data[$field] = null;
            } else {
                unset($data[$field]);
            }
        }

        $merchant->update($data);

        return back()->with('success', 'Pengaturan toko berhasil disimpan.');
    }
}