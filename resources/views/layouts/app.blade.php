<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'JARA') — Manajemen Tugas & Kolaborasi Tim</title>
    <!-- Tailwind CSS CDN for instant out-of-the-box rendering -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col font-sans">

    <!-- Top Navigation Bar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand Logo -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-500 flex items-center justify-center text-white font-black text-xl shadow-md">
                            J
                        </div>
                        <span class="font-extrabold text-2xl tracking-tight bg-gradient-to-r from-brand-600 to-indigo-600 bg-clip-text text-transparent">
                            JARA
                        </span>
                    </a>
                    <span class="hidden md:inline-block px-2.5 py-0.5 text-xs font-semibold rounded-full bg-indigo-50 text-brand-700 border border-brand-200">
                        v1.0 • P3 Active
                    </span>
                </div>

                <!-- Navigation Links -->
                <nav class="hidden md:flex items-center space-x-6">
                    <a href="{{ route('dashboard') }}" class="text-sm font-medium {{ request()->routeIs('dashboard') ? 'text-brand-600 font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('dashboard') }}#proyek" class="text-sm font-medium text-slate-600 hover:text-slate-900">
                        Daftar Proyek
                    </a>
                </nav>

                <!-- Right Side Actions & User Switcher -->
                <div class="flex items-center space-x-4">
                    <!-- Notification Bell (FR-C6) -->
                    <div class="relative" id="notification-wrapper">
                        <button onclick="toggleNotifications()" class="p-2 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 relative focus:outline-none transition" title="Notifikasi Deadline">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span id="notif-badge" class="hidden absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-rose-500 rounded-full ring-2 ring-white"></span>
                        </button>

                        <!-- Notification Dropdown Panel -->
                        <div id="notif-dropdown" class="hidden absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-xl border border-slate-200 py-3 z-50">
                            <div class="px-4 py-2 border-b border-slate-100 flex items-center justify-between">
                                <h4 class="text-sm font-bold text-slate-800">Notifikasi Deadline (FR-C6)</h4>
                                <span id="notif-count-badge" class="text-xs bg-brand-50 text-brand-700 px-2 py-0.5 rounded-full font-semibold">0</span>
                            </div>
                            <div id="notif-items" class="max-h-80 overflow-y-auto divide-y divide-slate-100">
                                <p class="text-center py-6 text-sm text-slate-400">Memuat notifikasi...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Test User Switcher (Helps verify Owner vs Member interactions) -->
                    <div class="hidden sm:flex items-center space-x-1 bg-slate-100 p-1 rounded-xl border border-slate-200">
                        <span class="text-xs text-slate-500 px-2 font-medium">Beralih Akun:</span>
                        <a href="{{ route('switch.user', 2) }}" class="px-2.5 py-1 text-xs font-semibold rounded-lg transition {{ (auth()->id() == 2) ? 'bg-white text-indigo-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}" title="Owner Website Redesign & Mobile App">
                            John (Owner)
                        </a>
                        <a href="{{ route('switch.user', 3) }}" class="px-2.5 py-1 text-xs font-semibold rounded-lg transition {{ (auth()->id() == 3) ? 'bg-white text-indigo-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}" title="Member di Project John, Owner Brand Project">
                            Jane (Member)
                        </a>
                        <a href="{{ route('switch.user', 1) }}" class="px-2.5 py-1 text-xs font-semibold rounded-lg transition {{ (auth()->id() == 1) ? 'bg-white text-indigo-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}" title="System Admin">
                            Admin
                        </a>
                    </div>

                    <!-- User Profile / Logout -->
                    @if(auth()->check())
                        <div class="flex items-center space-x-3 pl-2 border-l border-slate-200">
                            <a href="{{ route('profile') }}" class="flex items-center space-x-2 text-sm font-semibold text-slate-700 hover:text-brand-600 transition">
                                <div class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-xs">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                </div>
                                <span class="hidden lg:inline-block max-w-[120px] truncate">{{ auth()->user()->name }}</span>
                            </a>
                            <form action="{{ route('logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-100 transition" title="Keluar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-semibold text-white bg-brand-600 rounded-xl hover:bg-brand-700 shadow-sm transition">
                            Masuk
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </header>

    <!-- Flash Notifications / Alerts -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if(session('success'))
            <div class="p-4 mb-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-xs">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800">&times;</button>
            </div>
        @endif

        @if(session('info'))
            <div class="p-4 mb-4 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-800 flex items-center justify-between shadow-xs">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                    <span class="text-sm font-medium">{{ session('info') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-indigo-500 hover:text-indigo-800">&times;</button>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 mb-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 shadow-xs">
                <div class="flex items-center space-x-2 mb-1">
                    <svg class="w-5 h-5 text-rose-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span class="text-sm font-bold">Terjadi Kesalahan:</span>
                </div>
                <ul class="list-disc list-inside text-xs space-y-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-12 py-6 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
            <p><strong>JARA</strong> &copy; 2026. Aplikasi Manajemen Tugas Pribadi & Kolaborasi Tim.</p>
            <p class="text-slate-400">P3: Modul C (FR-C1–FR-C6) & Frontend UI</p>
        </div>
    </footer>

    <!-- Global Client Script for Notifications & Interactivity -->
    <script>
        function toggleNotifications() {
            const dropdown = document.getElementById('notif-dropdown');
            dropdown.classList.toggle('hidden');
            if (!dropdown.classList.contains('hidden')) {
                loadNotifications();
            }
        }

        async function loadNotifications() {
            try {
                const res = await fetch('/notifications', {
                    headers: { 'Accept': 'application/json' }
                });
                const json = await res.json();
                const container = document.getElementById('notif-items');
                const badge = document.getElementById('notif-badge');
                const countBadge = document.getElementById('notif-count-badge');

                if (json.success && json.data.notifications) {
                    const notifs = json.data.notifications;
                    countBadge.innerText = notifs.length;

                    if (notifs.length > 0) {
                        badge.classList.remove('hidden');
                        container.innerHTML = notifs.map(n => `
                            <div class="p-3 hover:bg-slate-50 transition">
                                <div class="flex items-start justify-between">
                                    <span class="text-xs font-bold px-2 py-0.5 rounded ${n.urgency === 'overdue' ? 'bg-rose-100 text-rose-700' : (n.urgency === 'today' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700')}">
                                        ${n.urgency.toUpperCase()}
                                    </span>
                                    <span class="text-[11px] text-slate-400">${n.deadline}</span>
                                </div>
                                <p class="text-xs text-slate-700 font-medium mt-1">${n.message}</p>
                            </div>
                        `).join('');
                    } else {
                        badge.classList.add('hidden');
                        container.innerHTML = '<p class="text-center py-6 text-xs text-slate-400">Tidak ada tugas mendekati deadline 🎉</p>';
                    }
                }
            } catch (e) {
                console.error('Error fetching notifications:', e);
            }
        }

        // Auto load notifications on boot
        document.addEventListener('DOMContentLoaded', loadNotifications);

        // Close dropdown when clicked outside
        document.addEventListener('click', function(event) {
            const wrapper = document.getElementById('notification-wrapper');
            if (wrapper && !wrapper.contains(event.target)) {
                document.getElementById('notif-dropdown').classList.add('hidden');
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
