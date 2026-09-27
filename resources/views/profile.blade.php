@extends('layouts.app')

@section('title', 'Profil Pengguna')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs">
        <h2 class="text-xl font-bold text-slate-900 pb-3 border-b border-slate-100">Profil Saya</h2>

        <form action="{{ route('profile.update') }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alamat Email</label>
                <input type="email" value="{{ $user->email }}" disabled class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50 text-slate-500 cursor-not-allowed">
                <span class="text-[11px] text-slate-400 mt-1 block">Email hanya dapat diubah oleh Administrator sistem.</span>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Peran Akun (Role)</label>
                <span class="inline-block px-3 py-1 text-xs font-bold rounded-full {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-indigo-100 text-brand-800' }}">
                    {{ strtoupper($user->role) }}
                </span>
            </div>

            <div class="pt-4 border-t border-slate-100">
                <h3 class="text-sm font-bold text-slate-900 mb-2">Ganti Kata Sandi</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Password Lama</label>
                        <input type="password" name="old_password" placeholder="Kosongkan jika tidak ingin mengganti" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Password Baru</label>
                        <input type="password" name="new_password" placeholder="Minimal 6 karakter" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-4 border-t border-slate-100">
                <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-xs transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
