<?php

namespace App\Http\Controllers;

use App\Models\ListMember;
use App\Models\ProjectList;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class WebViewController extends Controller
{
    /**
     * Get or set default test user if not logged in
     */
    protected function ensureAuthenticatedUser(): User
    {
        if (Auth::check()) {
            return Auth::user();
        }

        // Default to John Doe (Owner) or first user
        $user = User::where('email', 'john@example.com')->first() ?? User::first();
        if ($user) {
            Auth::login($user);

            return $user;
        }

        // If no user exists, create one
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'is_active' => true,
        ]);
        Auth::login($user);

        return $user;
    }

    /**
     * Dashboard View (FR-C5)
     */
    public function dashboard(Request $request)
    {
        $user = $this->ensureAuthenticatedUser();

        // Owned lists
        $ownedLists = ProjectList::where('owner_id', $user->id)->with(['tasks', 'members'])->get();

        // Member lists
        $memberListIds = ListMember::where('user_id', $user->id)->pluck('list_id');
        $memberLists = ProjectList::whereIn('id', $memberListIds)
            ->where('owner_id', '!=', $user->id)
            ->with(['tasks', 'members'])
            ->get();

        $allLists = $ownedLists->merge($memberLists)->unique('id');

        $totalTasks = 0;
        $totalCompleted = 0;

        foreach ($allLists as $list) {
            $totalTasks += $list->tasks->count();
            $totalCompleted += $list->tasks->where('status', 'completed')->count();
        }

        $overallProgress = $totalTasks > 0 ? (int) round(($totalCompleted / $totalTasks) * 100) : 0;

        // Urgent notifications
        $today = Carbon::today();
        $threeDaysAhead = Carbon::today()->addDays(3);
        $urgentTasks = Task::with('list')
            ->whereIn('list_id', $allLists->pluck('id'))
            ->where('status', '!=', 'completed')
            ->whereNotNull('deadline')
            ->where('deadline', '<=', $threeDaysAhead)
            ->orderBy('deadline', 'asc')
            ->get();

        $allUsers = User::where('is_active', true)->get();

        return view('dashboard', compact(
            'user',
            'ownedLists',
            'memberLists',
            'allLists',
            'totalTasks',
            'totalCompleted',
            'overallProgress',
            'urgentTasks',
            'allUsers'
        ));
    }

    /**
     * List Detail View with Tasks, Members, and Progress Tabs (FR-C1 to FR-C4)
     */
    public function listDetail(Request $request, int $id)
    {
        $user = $this->ensureAuthenticatedUser();

        $list = ProjectList::with(['owner', 'members.user', 'tasks'])->findOrFail($id);

        // Check if user is member, owner, or admin
        if (! $user->isAdmin() && ! $list->isMember($user->id)) {
            abort(403, 'Anda bukan anggota dari daftar proyek ini.');
        }

        $isOwner = $user->isAdmin() || $list->isOwner($user->id);
        $userRole = $list->getUserRole($user->id);

        // Progress calculation
        $progress = $list->calculateProgress();

        // All users who can be invited (not already member and not owner)
        $existingMemberIds = $list->members->pluck('user_id')->push($list->owner_id)->unique()->toArray();
        $availableUsers = User::whereNotIn('id', $existingMemberIds)
            ->where('is_active', true)
            ->get();

        $allUsers = User::where('is_active', true)->get();

        return view('lists.show', compact(
            'user',
            'list',
            'isOwner',
            'userRole',
            'progress',
            'availableUsers',
            'allUsers'
        ));
    }

    /**
     * Create new list (Web action)
     */
    public function storeList(Request $request)
    {
        $user = $this->ensureAuthenticatedUser();

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $list = ProjectList::create([
            'owner_id' => $user->id,
            'name' => $request->input('name'),
            'description' => $request->input('description'),
        ]);

        return redirect()->route('lists.show', $list->id)->with('success', 'Daftar proyek berhasil dibuat!');
    }

    /**
     * Create new task in list (Web action)
     */
    public function storeTask(Request $request, int $id)
    {
        $user = $this->ensureAuthenticatedUser();
        $list = ProjectList::findOrFail($id);

        if (! $user->isAdmin() && ! $list->isMember($user->id)) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
            'deadline' => 'nullable|date',
            'status' => 'nullable|in:pending,in_progress,completed',
        ]);

        Task::create([
            'list_id' => $list->id,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'priority' => $request->input('priority', 'medium'),
            'deadline' => $request->input('deadline'),
            'status' => $request->input('status', 'pending'),
        ]);

        return redirect()->route('lists.show', ['id' => $list->id, 'tab' => 'tasks'])->with('success', 'Tugas berhasil ditambahkan!');
    }

    /**
     * Toggle task status (Web action)
     */
    public function toggleTask(Request $request, int $id)
    {
        $task = Task::with('list')->findOrFail($id);
        $user = $this->ensureAuthenticatedUser();

        if (! $user->isAdmin() && ! $task->list->isMember($user->id)) {
            abort(403, 'Akses ditolak.');
        }

        $newStatus = $task->status === 'completed' ? 'pending' : 'completed';
        $task->update(['status' => $newStatus]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'task' => $task]);
        }

        return back()->with('success', 'Status tugas diperbarui!');
    }

    /**
     * Quick Switch User for testing collaboration between Owner and Member
     */
    public function switchUser(int $id)
    {
        $user = User::findOrFail($id);
        Auth::login($user);

        return back()->with('info', "Beralih akun ke: {$user->name} ({$user->role})");
    }

    /**
     * Login view
     */
    public function loginView()
    {
        $users = User::where('is_active', true)->get();

        return view('auth.login', compact('users'));
    }

    /**
     * Perform login
     */
    public function doLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->route('dashboard')->with('success', 'Selamat datang kembali!');
        }

        return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Anda telah keluar.');
    }

    /**
     * User profile
     */
    public function profile()
    {
        $user = $this->ensureAuthenticatedUser();
        $allUsers = User::where('is_active', true)->get();

        return view('profile', compact('user', 'allUsers'));
    }

    /**
     * Update profile
     */
    public function updateProfile(Request $request)
    {
        $user = $this->ensureAuthenticatedUser();

        $request->validate([
            'name' => 'required|string|max:255',
            'old_password' => 'nullable|string',
            'new_password' => 'nullable|string|min:6',
        ]);

        $user->name = $request->input('name');

        if ($request->filled('new_password')) {
            if (! Hash::check($request->input('old_password'), $user->password)) {
                return back()->withErrors(['old_password' => 'Password lama tidak cocok.']);
            }
            $user->password = Hash::make($request->input('new_password'));
        }

        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui!');
    }
}
