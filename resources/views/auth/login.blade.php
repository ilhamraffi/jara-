@extends('layouts.app')

@section('title', 'Masuk ke JARA')

@section('content')
<div class="max-w-md mx-auto my-8">
    <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
        <div class="text-center mb-6">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-brand-600 to-indigo-500 text-white font-black text-2xl flex items-center justify-center mx-auto shadow-md">
                J
            </div>
            <h2 class="text-2xl font-black text-slate-900 mt-3">Masuk ke JARA</h2>
            <p class="text-xs text-slate-500 mt-1">Sistem Manajemen Tugas & Kolaborasi Tim</p>
        </div>

        <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', 'john@example.com') }}" required class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Password</label>
                <input type="password" name="password" id="password" value="password123" required class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <button type="submit" class="w-full py-2.5 text-sm font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-xs transition">
                Masuk
            </button>
        </form>

        <!-- Quick 1-Click Login for Development & Testing -->
        <div class="mt-8 pt-6 border-t border-slate-100">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider text-center mb-3">Pilih Akun Demo Cepat</p>
            <div class="grid grid-cols-2 gap-2">
                <button type="button" onclick="fillAccount('john@example.com', 'password123')" class="p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-left transition">
                    <p class="text-xs font-bold text-slate-800">John Doe</p>
                    <span class="text-[11px] text-emerald-600 font-medium">Owner Proyek</span>
                </button>
                <button type="button" onclick="fillAccount('jane@example.com', 'password123')" class="p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-left transition">
                    <p class="text-xs font-bold text-slate-800">Jane Smith</p>
                    <span class="text-[11px] text-indigo-600 font-medium">Member Proyek</span>
                </button>
                <button type="button" onclick="fillAccount('admin@example.com', 'password123')" class="p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-left transition">
                    <p class="text-xs font-bold text-slate-800">Admin</p>
                    <span class="text-[11px] text-purple-600 font-medium">Administrator</span>
                </button>
                <button type="button" onclick="fillAccount('bob@example.com', 'password123')" class="p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-left transition">
                    <p class="text-xs font-bold text-slate-800">Bob Johnson</p>
                    <span class="text-[11px] text-slate-500 font-medium">Member</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function fillAccount(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
    }
</script>
@endsection
