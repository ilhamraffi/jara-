@extends('layouts.app')

@section('title', $list->name . ' — Detail Proyek & Kolaborasi')

@section('content')
<div class="space-y-6">

    <!-- Project Breadcrumb & Header -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center space-x-2 text-xs text-slate-500 font-medium mb-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-brand-600">Dashboard</a>
                    <span>/</span>
                    <span class="text-slate-800 font-semibold">{{ $list->name }}</span>
                </div>
                <div class="flex items-center space-x-3 mt-1">
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ $list->name }}</h1>
                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-full {{ $isOwner ? 'bg-emerald-100 text-emerald-800' : 'bg-sky-100 text-sky-800' }}">
                        {{ $isOwner ? 'Role Anda: OWNER' : 'Role Anda: MEMBER' }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1 max-w-2xl">{{ $list->description ?? 'Tidak ada deskripsi proyek.' }}</p>
                <div class="flex items-center space-x-4 mt-3 text-xs text-slate-400">
                    <span>Pemilik: <strong class="text-slate-700">{{ $list->owner->name ?? 'User' }}</strong></span>
                    <span>&bull;</span>
                    <span>Total Tugas: <strong class="text-slate-700">{{ $progress['total_tasks'] }}</strong></span>
                    <span>&bull;</span>
                    <span>Selesai: <strong class="text-emerald-600">{{ $progress['completed_tasks'] }} ({{ $progress['progress_percentage'] }}%)</strong></span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center space-x-3">
                <button onclick="document.getElementById('modal-create-task').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Tugas
                </button>

                @if($isOwner)
                    <button onclick="document.getElementById('modal-add-member').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl shadow-xs transition">
                        <svg class="w-4 h-4 mr-1.5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        Undang Member (FR-C1)
                    </button>
                @endif
            </div>
        </div>

        <!-- Navigation Tabs -->
        @php
            $activeTab = request()->get('tab', 'tasks');
        @endphp
        <div class="flex items-center space-x-2 border-b border-slate-200 mt-6 pt-2">
            <button onclick="switchTab('tasks')" id="tab-btn-tasks" class="tab-button px-4 py-2.5 text-xs font-bold border-b-2 transition {{ $activeTab === 'tasks' ? 'border-brand-600 text-brand-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                Daftar Tugas ({{ $progress['total_tasks'] }})
            </button>
            <button onclick="switchTab('members')" id="tab-btn-members" class="tab-button px-4 py-2.5 text-xs font-bold border-b-2 transition {{ $activeTab === 'members' ? 'border-brand-600 text-brand-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                Anggota & Kolaborasi (FR-C1, FR-C2)
            </button>
            <button onclick="switchTab('progress')" id="tab-btn-progress" class="tab-button px-4 py-2.5 text-xs font-bold border-b-2 transition {{ $activeTab === 'progress' ? 'border-brand-600 text-brand-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                Monitoring Progres (FR-C4)
            </button>
        </div>
    </div>

    <!-- TAB 1: DAFTAR TUGAS (TASKS) -->
    <div id="tab-content-tasks" class="{{ $activeTab === 'tasks' ? '' : 'hidden' }} space-y-4">
        <!-- Filter Bar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mr-1">Filter Status:</span>
                <button onclick="filterTasks('all')" class="task-filter-btn px-3 py-1 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 active-filter" data-filter="all">Semua</button>
                <button onclick="filterTasks('pending')" class="task-filter-btn px-3 py-1 text-xs font-semibold rounded-lg text-slate-600 hover:bg-slate-100" data-filter="pending">Belum Dimulai</button>
                <button onclick="filterTasks('in_progress')" class="task-filter-btn px-3 py-1 text-xs font-semibold rounded-lg text-slate-600 hover:bg-slate-100" data-filter="in_progress">Sedang Dikerjakan</button>
                <button onclick="filterTasks('completed')" class="task-filter-btn px-3 py-1 text-xs font-semibold rounded-lg text-slate-600 hover:bg-slate-100" data-filter="completed">Selesai</button>
            </div>
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <span>Klik checkbox untuk menandai tugas selesai</span>
            </div>
        </div>

        <!-- Task List Items -->
        <div class="space-y-3" id="task-items-container">
            @forelse($list->tasks as $task)
                @php
                    $isDone = ($task->status === 'completed');
                    $daysLeft = null;
                    if ($task->deadline) {
                        $today = \Carbon\Carbon::today();
                        $dl = \Carbon\Carbon::parse($task->deadline);
                        $daysLeft = (int) $today->diffInDays($dl, false);
                    }
                @endphp
                <div class="task-item bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between gap-4 transition hover:border-slate-300" data-status="{{ $task->status }}">
                    <div class="flex items-start space-x-3.5 flex-1 min-w-0">
                        <form action="{{ route('tasks.toggle', $task->id) }}" method="POST" class="mt-0.5">
                            @csrf
                            <input type="checkbox" onchange="this.form.submit()" {{ $isDone ? 'checked' : '' }} class="w-5 h-5 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 cursor-pointer">
                        </form>
                        <div class="flex-1 truncate">
                            <h4 class="text-sm font-bold text-slate-900 truncate {{ $isDone ? 'line-through text-slate-400' : '' }}">
                                {{ $task->title }}
                            </h4>
                            @if($task->description)
                                <p class="text-xs text-slate-500 mt-0.5 truncate">{{ $task->description }}</p>
                            @endif
                            <div class="flex flex-wrap items-center gap-2 mt-2">
                                <!-- Priority Badge -->
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full {{ $task->priority === 'high' ? 'bg-rose-100 text-rose-800' : ($task->priority === 'medium' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">
                                    Prioritas {{ ucfirst($task->priority) }}
                                </span>

                                <!-- Status Badge -->
                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $task->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : ($task->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-600') }}">
                                    {{ $task->status === 'completed' ? 'Selesai' : ($task->status === 'in_progress' ? 'Sedang Dikerjakan' : 'Belum Dimulai') }}
                                </span>

                                <!-- Deadline Tag -->
                                @if($task->deadline)
                                    <span class="text-[11px] font-medium text-slate-500 flex items-center">
                                        <svg class="w-3.5 h-3.5 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        {{ $task->deadline->format('d M Y') }}
                                        @if(!$isDone)
                                            <span class="ml-1 font-bold {{ $daysLeft < 0 ? 'text-rose-600' : ($daysLeft == 0 ? 'text-amber-600' : 'text-slate-600') }}">
                                                ({{ $daysLeft < 0 ? abs($daysLeft).' hari lalu' : ($daysLeft == 0 ? 'Hari ini' : $daysLeft.' hari lagi') }})
                                            </span>
                                        @endif
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white p-12 rounded-2xl border border-dashed border-slate-300 text-center">
                    <p class="text-sm font-bold text-slate-700">Belum ada tugas di daftar proyek ini</p>
                    <p class="text-xs text-slate-400 mt-1">Buat tugas pertama Anda untuk mulai berkolaborasi.</p>
                    <button onclick="document.getElementById('modal-create-task').classList.remove('hidden')" class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-semibold shadow-xs hover:bg-indigo-700">
                        Tambah Tugas Sekarang
                    </button>
                </div>
            @endforelse
        </div>
    </div>

    <!-- TAB 2: ANGGOTA & KOLABORASI (MEMBERS) (FR-C1, FR-C2, FR-C3) -->
    <div id="tab-content-members" class="{{ $activeTab === 'members' ? '' : 'hidden' }} space-y-6">
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Anggota Tim Kolaboratif</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar pengguna yang memiliki akses untuk melihat dan mengerjakan tugas pada daftar ini.</p>
                </div>
                @if($isOwner)
                    <button onclick="document.getElementById('modal-add-member').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        Tambah Member Baru
                    </button>
                @endif
            </div>

            <div class="mt-4 divide-y divide-slate-100">
                <!-- Owner Record -->
                <div class="py-3.5 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-xs">
                            {{ strtoupper(substr($list->owner->name ?? 'OW', 0, 2)) }}
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">{{ $list->owner->name ?? 'Owner' }}</h4>
                            <p class="text-xs text-slate-400">{{ $list->owner->email ?? '' }}</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Owner (Pemilik)
                    </span>
                </div>

                <!-- Invited Members List -->
                @forelse($list->members as $member)
                    @if($member->user && $member->user_id != $list->owner_id)
                        <div class="py-3.5 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full bg-indigo-100 text-brand-700 flex items-center justify-center font-bold text-xs">
                                    {{ strtoupper(substr($member->user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">{{ $member->user->name }}</h4>
                                    <p class="text-xs text-slate-400">{{ $member->user->email }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="text-xs font-bold px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ ucfirst($member->role) }}
                                </span>

                                @if($isOwner)
                                    <!-- FR-C2: Remove member button -->
                                    <button onclick="confirmRemoveMember({{ $list->id }}, {{ $member->user_id }}, '{{ addslashes($member->user->name) }}')" class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-xl transition" title="Keluarkan Member">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="py-6 text-center text-xs text-slate-400">
                        Belum ada anggota yang diundang ke proyek ini.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- TAB 3: MONITORING & PROGRES (PROGRESS) (FR-C4) -->
    <div id="tab-content-progress" class="{{ $activeTab === 'progress' ? '' : 'hidden' }} space-y-6">
        <!-- Progress Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Overall Progress Widget -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
                <div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Persentase Penyelesaian</h3>
                    <div class="mt-4 flex items-baseline space-x-3">
                        <span class="text-4xl font-black text-slate-900">{{ $progress['progress_percentage'] }}%</span>
                        <span class="text-xs text-slate-500 font-semibold">{{ $progress['completed_tasks'] }} dari {{ $progress['total_tasks'] }} Selesai</span>
                    </div>
                </div>
                <div class="mt-6">
                    <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                        <div class="bg-gradient-to-r from-emerald-500 to-indigo-600 h-3 rounded-full transition-all duration-500" style="width: {{ $progress['progress_percentage'] }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Task Status Breakdown -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Status Rincian Tugas</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center text-slate-600 font-medium">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mr-2"></span> Selesai
                        </span>
                        <strong class="text-slate-900">{{ $progress['tasks_by_status']['completed'] }}</strong>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center text-slate-600 font-medium">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500 mr-2"></span> Sedang Dikerjakan
                        </span>
                        <strong class="text-slate-900">{{ $progress['tasks_by_status']['in_progress'] }}</strong>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center text-slate-600 font-medium">
                            <span class="w-2.5 h-2.5 rounded-full bg-slate-400 mr-2"></span> Belum Dimulai
                        </span>
                        <strong class="text-slate-900">{{ $progress['tasks_by_status']['pending'] }}</strong>
                    </div>
                </div>
            </div>

            <!-- Task Priority Breakdown -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Distribusi Prioritas</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center text-slate-600 font-medium">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500 mr-2"></span> Prioritas Tinggi
                        </span>
                        <strong class="text-rose-700">{{ $progress['tasks_by_priority']['high'] }}</strong>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center text-slate-600 font-medium">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 mr-2"></span> Prioritas Sedang
                        </span>
                        <strong class="text-amber-700">{{ $progress['tasks_by_priority']['medium'] }}</strong>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center text-slate-600 font-medium">
                            <span class="w-2.5 h-2.5 rounded-full bg-slate-400 mr-2"></span> Prioritas Rendah
                        </span>
                        <strong class="text-slate-700">{{ $progress['tasks_by_priority']['low'] }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Deadlines List -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
            <h3 class="text-base font-bold text-slate-900 mb-1">Tenggat Waktu Tugas Terdekat (Upcoming Deadlines)</h3>
            <p class="text-xs text-slate-500 mb-4">Daftar tugas aktif yang membutuhkan perhatian segera berdasarkan tanggal deadline.</p>

            <div class="divide-y divide-slate-100">
                @forelse($progress['upcoming_deadlines'] as $deadlineTask)
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <h4 class="text-xs font-bold text-slate-800">{{ $deadlineTask['title'] }}</h4>
                            <span class="text-[11px] text-slate-400">Jatuh tempo: {{ $deadlineTask['deadline'] }}</span>
                        </div>
                        <span class="text-xs font-black px-2.5 py-0.5 rounded-full {{ $deadlineTask['days_left'] < 0 ? 'bg-rose-100 text-rose-800' : ($deadlineTask['days_left'] == 0 ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800') }}">
                            {{ $deadlineTask['days_left'] < 0 ? abs($deadlineTask['days_left']).' hari terlewat' : ($deadlineTask['days_left'] == 0 ? 'Hari ini' : $deadlineTask['days_left'].' hari tersisa') }}
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-4 text-center">Semua tugas ber-deadline telah selesai atau belum memiliki deadline.</p>
                @endforelse
            </div>
        </div>
    </div>

</div>

<!-- MODAL: ADD MEMBER (FR-C1) -->
<div id="modal-add-member" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900">Undang Anggota ke Proyek (FR-C1)</h3>
            <button onclick="document.getElementById('modal-add-member').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 text-lg">&times;</button>
        </div>
        <form id="form-add-member" onsubmit="submitAddMember(event, {{ $list->id }})" class="mt-4 space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Pilih Pengguna *</label>
                <select id="invite-user-id" required class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">-- Pilih Pengguna Terdaftar --</option>
                    @foreach($availableUsers as $availUser)
                        <option value="{{ $availUser->id }}">{{ $availUser->name }} ({{ $availUser->email }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Peran (Role)</label>
                <select id="invite-role" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="member" selected>Member (Dapat mengerjakan tugas)</option>
                    <option value="owner">Co-Owner (Dapat mengelola member & list)</option>
                </select>
            </div>
            <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-add-member').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-100">
                    Batal
                </button>
                <button type="submit" id="btn-submit-member" class="px-4 py-2 text-xs font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-xs">
                    Kirim Undangan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: CREATE TASK -->
<div id="modal-create-task" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900">Tambah Tugas Baru</h3>
            <button onclick="document.getElementById('modal-create-task').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 text-lg">&times;</button>
        </div>
        <form action="{{ route('lists.tasks.store', $list->id) }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Judul Tugas *</label>
                <input type="text" name="title" required placeholder="Contoh: Desain wireframe halaman profil" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Deskripsi</label>
                <textarea name="description" rows="2" placeholder="Rincian instruksi tugas..." class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Prioritas</label>
                    <select name="priority" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="low">Rendah</option>
                        <option value="medium" selected>Sedang</option>
                        <option value="high">Tinggi</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tenggat Waktu</label>
                    <input type="date" name="deadline" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-create-task').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-100">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-xs">
                    Simpan Tugas
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Tab switching logic
    function switchTab(tabName) {
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('border-brand-600', 'text-brand-600');
            btn.classList.add('border-transparent', 'text-slate-500');
        });
        const activeBtn = document.getElementById('tab-btn-' + tabName);
        if (activeBtn) {
            activeBtn.classList.remove('border-transparent', 'text-slate-500');
            activeBtn.classList.add('border-brand-600', 'text-brand-600');
        }

        document.getElementById('tab-content-tasks').classList.add('hidden');
        document.getElementById('tab-content-members').classList.add('hidden');
        document.getElementById('tab-content-progress').classList.add('hidden');

        document.getElementById('tab-content-' + tabName).classList.remove('hidden');

        // Update URL query param without reload
        const url = new URL(window.location);
        url.searchParams.set('tab', tabName);
        window.history.pushState({}, '', url);
    }

    // Filter Tasks logic
    function filterTasks(status) {
        document.querySelectorAll('.task-filter-btn').forEach(btn => {
            btn.classList.remove('bg-indigo-50', 'text-indigo-700');
            btn.classList.add('text-slate-600');
        });
        const currentBtn = document.querySelector(`.task-filter-btn[data-filter="${status}"]`);
        if (currentBtn) {
            currentBtn.classList.add('bg-indigo-50', 'text-indigo-700');
            currentBtn.classList.remove('text-slate-600');
        }

        document.querySelectorAll('.task-item').forEach(item => {
            if (status === 'all' || item.dataset.status === status) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }

    // Submit Add Member (FR-C1 via API endpoint)
    async function submitAddMember(e, listId) {
        e.preventDefault();
        const userId = document.getElementById('invite-user-id').value;
        const role = document.getElementById('invite-role').value;
        const submitBtn = document.getElementById('btn-submit-member');

        if (!userId) return;

        submitBtn.disabled = true;
        submitBtn.innerText = 'Menambahkan...';

        try {
            const res = await fetch(`/lists/${listId}/members`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ user_id: parseInt(userId), role: role })
            });

            const data = await res.json();

            if (res.ok && data.success) {
                alert('Berhasil mengundang member baru!');
                window.location.href = window.location.pathname + '?tab=members';
            } else {
                alert('Gagal: ' + (data.error || 'Terjadi kesalahan'));
                submitBtn.disabled = false;
                submitBtn.innerText = 'Kirim Undangan';
            }
        } catch (err) {
            console.error(err);
            alert('Terjadi kesalahan jaringan.');
            submitBtn.disabled = false;
            submitBtn.innerText = 'Kirim Undangan';
        }
    }

    // Confirm Remove Member (FR-C2 via API endpoint)
    async function confirmRemoveMember(listId, userId, userName) {
        if (!confirm(`Apakah Anda yakin ingin mengeluarkan ${userName} dari daftar proyek ini?`)) {
            return;
        }

        try {
            const res = await fetch(`/lists/${listId}/members/${userId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await res.json();

            if (res.ok && data.success) {
                alert('Member berhasil dikeluarkan.');
                window.location.href = window.location.pathname + '?tab=members';
            } else {
                alert('Gagal mengeluarkan member: ' + (data.error || 'Terjadi kesalahan'));
            }
        } catch (err) {
            console.error(err);
            alert('Terjadi kesalahan koneksi.');
        }
    }
</script>
@endpush
