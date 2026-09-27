@extends('layouts.app')

@section('title', 'Dashboard Ringkasan Progres')

@section('content')
<div class="space-y-8">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Dashboard Ringkasan Progres</h1>
            <p class="text-sm text-slate-500 mt-1">Pantau seluruh daftar tugas, status kolaborasi anggota tim, dan tingkat penyelesaian (FR-C5).</p>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="document.getElementById('modal-create-list').classList.remove('hidden')" class="inline-flex items-center px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Proyek Baru
            </button>
        </div>
    </div>

    <!-- KPI Summary Metrics (FR-C5) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Metric 1: Total Lists -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Proyek</p>
                <h3 class="text-2xl font-black text-slate-900 mt-1">{{ $allLists->count() }}</h3>
                <span class="text-xs text-slate-500 mt-1 inline-block">
                    <strong class="text-indigo-600">{{ $ownedLists->count() }}</strong> Milik Sendiri &bull; <strong class="text-sky-600">{{ $memberLists->count() }}</strong> Diundang
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-brand-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
        </div>

        <!-- Metric 2: Total Tasks -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Tugas</p>
                <h3 class="text-2xl font-black text-slate-900 mt-1">{{ $totalTasks }}</h3>
                <span class="text-xs text-slate-500 mt-1 inline-block">Dalam seluruh daftar aktif</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
        </div>

        <!-- Metric 3: Completed Tasks -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tugas Selesai</p>
                <h3 class="text-2xl font-black text-emerald-600 mt-1">{{ $totalCompleted }}</h3>
                <span class="text-xs text-slate-500 mt-1 inline-block">Dari total {{ $totalTasks }} tugas</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- Metric 4: Overall Progress -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Progres Global</p>
                <span class="text-xs font-black px-2 py-0.5 rounded-full {{ $overallProgress >= 70 ? 'bg-emerald-100 text-emerald-800' : ($overallProgress >= 30 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">
                    {{ $overallProgress }}%
                </span>
            </div>
            <div class="mt-3">
                <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                    <div class="bg-gradient-to-r from-brand-600 to-indigo-500 h-3 rounded-full transition-all duration-500" style="width: {{ $overallProgress }}%"></div>
                </div>
                <span class="text-[11px] text-slate-400 mt-1.5 block">Kalkulasi otomatis dari semua tugas selesai</span>
            </div>
        </div>
    </div>

    <!-- Urgent Deadlines Alert Banner (FR-C6) -->
    @if($urgentTasks->count() > 0)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 shadow-xs">
            <div class="flex items-start space-x-3">
                <div class="p-2 bg-amber-100 rounded-xl text-amber-700 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div class="flex-1">
                    <h4 class="text-sm font-bold text-amber-900">Perhatian: Ada {{ $urgentTasks->count() }} tugas mendekati atau melewati tenggat waktu (FR-C6)</h4>
                    <div class="mt-3 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($urgentTasks->take(3) as $ut)
                            @php
                                $today = \Carbon\Carbon::today();
                                $dl = \Carbon\Carbon::parse($ut->deadline);
                                $days = (int) $today->diffInDays($dl, false);
                            @endphp
                            <div class="bg-white p-3 rounded-xl border border-amber-200/60 shadow-xs flex items-center justify-between">
                                <div class="truncate mr-2">
                                    <p class="text-xs font-bold text-slate-800 truncate">{{ $ut->title }}</p>
                                    <span class="text-[11px] text-slate-500">{{ $ut->list?->name }}</span>
                                </div>
                                <span class="text-[11px] font-black px-2 py-0.5 rounded whitespace-nowrap {{ $days < 0 ? 'bg-rose-100 text-rose-700' : ($days == 0 ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
                                    {{ $days < 0 ? abs($days).' hari lalu' : ($days == 0 ? 'Hari Ini' : $days.' hari lagi') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Project Lists Section -->
    <div id="proyek" class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-900">Daftar Proyek & Monitoring (% Selesai)</h2>
            <span class="text-xs text-slate-500 font-medium">Klik pada kartu proyek untuk mengelola tugas dan anggota</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($allLists as $list)
                @php
                    $isListOwner = ($list->owner_id == $user->id);
                    $tot = $list->tasks->count();
                    $done = $list->tasks->where('status', 'completed')->count();
                    $pct = $tot > 0 ? (int) round(($done / $tot) * 100) : 0;
                    $memberCount = $list->members->where('user_id', '!=', $list->owner_id)->count() + 1;
                @endphp
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs hover:shadow-md transition flex flex-col justify-between overflow-hidden">
                    <div class="p-6">
                        <!-- Card Header -->
                        <div class="flex items-start justify-between gap-2">
                            <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $isListOwner ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-sky-50 text-sky-700 border border-sky-200' }}">
                                {{ $isListOwner ? 'Owner' : 'Member' }}
                            </span>
                            <span class="text-xs text-slate-400 font-medium">
                                {{ $memberCount }} Anggota
                            </span>
                        </div>

                        <!-- Card Body -->
                        <h3 class="text-lg font-bold text-slate-900 mt-3 hover:text-brand-600 transition">
                            <a href="{{ route('lists.show', $list->id) }}">{{ $list->name }}</a>
                        </h3>
                        <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $list->description ?? 'Tidak ada deskripsi.' }}</p>

                        <!-- Progress Bar (FR-C4) -->
                        <div class="mt-5 space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-500 font-medium">Progres: {{ $done }}/{{ $tot }} Tugas</span>
                                <span class="font-bold text-slate-800">{{ $pct }}%</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                <div class="h-2 rounded-full transition-all duration-500 {{ $pct == 100 ? 'bg-emerald-500' : ($pct > 50 ? 'bg-indigo-500' : 'bg-amber-500') }}" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer Actions -->
                    <div class="bg-slate-50 px-6 py-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold">
                        <a href="{{ route('lists.show', $list->id) }}" class="text-brand-600 hover:text-brand-800 inline-flex items-center font-bold">
                            Buka Proyek &rarr;
                        </a>
                        @if($isListOwner)
                            <a href="{{ route('lists.show', ['id' => $list->id, 'tab' => 'members']) }}" class="text-slate-600 hover:text-slate-900 inline-flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                Kelola Anggota
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white p-12 rounded-2xl border border-dashed border-slate-300 text-center">
                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Belum ada daftar proyek</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Mulai dengan membuat daftar proyek pertama Anda untuk mengorganisir tugas dan mengundang rekan tim.</p>
                    <button onclick="document.getElementById('modal-create-list').classList.remove('hidden')" class="mt-4 px-4 py-2 bg-brand-600 text-white rounded-xl text-xs font-semibold shadow-xs hover:bg-brand-700">
                        Buat Proyek Baru
                    </button>
                </div>
            @endforelse
        </div>
    </div>

</div>

<!-- Modal Create List -->
<div id="modal-create-list" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900">Buat Daftar Proyek Baru</h3>
            <button onclick="document.getElementById('modal-create-list').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 text-lg">&times;</button>
        </div>
        <form action="{{ route('lists.store') }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Proyek *</label>
                <input type="text" name="name" required placeholder="Contoh: Desain Sistem V2" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Deskripsi Proyek</label>
                <textarea name="description" rows="3" placeholder="Jelaskan tujuan atau ruang lingkup proyek ini..." class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
            </div>
            <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-create-list').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-100">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-xs">
                    Simpan Proyek
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
