# INTEGRATION_GUIDE.md — Panduan Integrasi Multi-Programmer

**Versi:** 1.0  
**Tanggal:** 28 September 2026

---

## 1. Tujuan

Dokumen ini mencegah conflict dan masalah integrasi saat multiple programmer bekerja paralel di repository yang sama.

---

## 2. File Ownership Matrix

Setiap file hanya dimiliki oleh satu programmer. Jika programmer lain perlu mengubah file milik orang lain, **harus koordinasi dulu**.

### P1 — Authentication & Admin
```
routes/auth.php          → P1 only
routes/admin.php         → P1 only
app/Http/Controllers/AuthController.php    → P1 only
app/Http/Controllers/AdminController.php   → P1 only
app/Http/Middleware/Authenticate.php       → P1 only
app/Http/Middleware/AdminCheck.php         → P1 only
app/Services/JwtService.php                → P1 only
config/auth.php          → P1 only
config/jwt.php           → P1 only
database/migrations/*_create_users_table.php → P1 only
database/seeders/UserSeeder.php            → P1 only
```

### P2 — List & Task Management
```
routes/lists.php         → P2 only
routes/tasks.php         → P2 only
app/Http/Controllers/ListController.php    → P2 only
app/Http/Controllers/TaskController.php    → P2 only
app/Models/ListModel.php  → P2 only
app/Models/Task.php       → P2 only
app/Http/Requests/StoreListRequest.php     → P2 only
app/Http/Requests/UpdateListRequest.php    → P2 only
app/Http/Requests/StoreTaskRequest.php     → P2 only
app/Http/Requests/UpdateTaskRequest.php    → P2 only
database/migrations/*_create_lists_table.php       → P2 only
database/migrations/*_create_tasks_table.php       → P2 only
database/migrations/*_create_list_members_table.php → P2 only (shared dengan P3)
database/seeders/ListSeeder.php            → P2 only
database/seeders/TaskSeeder.php            → P2 only
database/factories/ListModelFactory.php    → P2 only
database/factories/TaskFactory.php         → P2 only
```

### P3 — Collaboration & Monitoring
```
routes/collaboration.php → P3 only
app/Http/Controllers/CollaborationController.php → P3 only
app/Models/ListMember.php  → P3 only
database/seeders/MemberSeeder.php           → P3 only
database/factories/ListMemberFactory.php    → P3 only
```

### Shared Files (Dimiliki Bersama)
```
routes/api.php           → SEMUA (pakai file_exists check)
app/Http/Controllers/Controller.php → SEMUA (base class only)
app/Models/User.php       → SEMUA (tambah relationship masing-masing)
database/seeders/DatabaseSeeder.php → SEMUA (pakai class_exists check)
bootstrap/app.php         → SEMUA (jangan ubah alias middleware)
config/app.php            → SEMUA (jangan ubah providers)
```

---

## 3. Shared File Conventions

### 3.1 `routes/api.php`

P1 sudah menggunakan pattern ini. Pertahankan:

```php
$loadModules = function () {
    require __DIR__.'/auth.php';
    require __DIR__.'/admin.php';

    if (file_exists(__DIR__.'/lists.php')) {
        require __DIR__.'/lists.php';
    }

    if (file_exists(__DIR__.'/tasks.php')) {
        require __DIR__.'/tasks.php';
    }

    if (file_exists(__DIR__.'/collaboration.php')) {
        require __DIR__.'/collaboration.php';
    }
};
```

**Aturan:** Jangan hapus `file_exists` check milik programmer lain.

### 3.2 `app/Models/User.php`

Tampilkan relationship milik programmer lain dalam urutan alphabetical:

```php
public function lists()        // P2
public function listMembers()  // P2 (atau P3)
public function tasks()        // P2 (jika ada)
```

**Aturan:** Jangan hapus method milik programmer lain. Tambahkan method baru di bawah method yang sudah ada.

### 3.3 `database/seeders/DatabaseSeeder.php`

```php
public function run(): void
{
    $this->call(UserSeeder::class);

    if (class_exists(ListSeeder::class)) {
        $this->call(ListSeeder::class);
    }

    if (class_exists(TaskSeeder::class)) {
        $this->call(TaskSeeder::class);
    }

    if (class_exists(MemberSeeder::class)) {
        $this->call(MemberSeeder::class);
    }
}
```

**Aturan:** Jangan hapus `class_exists` check milik programmer lain.

### 3.4 `app/Http/Controllers/Controller.php`

Base class untuk semua controller. Hanya berisi helper methods yang dipakai bersama:

```php
abstract class Controller
{
    protected function successResponse($data, string $message = 'Operation successful', int $code = 200)
    protected function errorResponse(string $error, string $code = 'ERROR', int $statusCode = 400)
}
```

**Aturan:** Jangan tambahkan method yang hanya dipakai satu programmer. Karena semua controller butuh yang ini.

### 3.5 Middleware & Auth

P1 menggunakan `JwtService` custom. Semua programmer harus ikut konvensi ini:

| Item | Nilai | Dimiliki oleh |
|------|-------|---------------|
| Middleware alias | `auth.jwt` | P1 |
| User accessor | `request()->user()` | SEMUA |
| Token generation | `JwtService::generateToken()` | P1 |
| Token validation | `JwtService::validateToken()` | P1 |

**Aturan:** Jangan pakai `auth()->user()` di controller. Karena middleware P1 pakai `$request->setUserResolver()`.

---

## 4. Migration Naming Convention

Format: `YYYY_MM_DD_NNNNNN_create_<table_name>_table.php`

Urutan migration harus mengikuti urutan dependency:

```
1. users (P1)
2. lists (P2)
3. tasks (P2)
4. list_members (P2/P3)
```

**Aturan:** Jangan urutkan secara alphabetik. Ikuti dependency antar tabel.

---

## 5. Git Workflow untuk Integrasi

### 5.1 Kapan Pull Main?

Pull main ke branch kamu **sebelum push ke remote**, bukan di tengah development.

```bash
# 1. Selesaikan fitur kamu
# 2. Pastikan semua test pass
php artisan test
./vendor/bin/pint

# 3. Baru pull main
git checkout feat/pX-your-feature
git fetch origin
git merge origin/main

# 4. Resolve conflict jika ada
# Edit file yang conflict, lalu:
git add <file>
git commit

# 5. Pastikan test masih pass
php artisan test

# 6. Push
git push origin feat/pX-your-feature
```

### 5.2 Resolve Conflict Strategy

Jika terjadi conflict di shared file:

1. **Jangan hapus kode programmer lain**
2. **Gabungkan kedua perubahan**
3. **Test setelah resolve**

Contoh conflict di `User.php`:

```
// P1 sudah punya:
public function isAdmin(): bool { ... }

// P2 mau tambah:
public function lists() { ... }

// ✅ Gabungkan:
public function isAdmin(): bool { ... }
public function lists() { ... }

// ❌ Jangan hapus isAdmin()
```

### 5.3 Commit Message Format

```
[PX] <description>

Contoh:
[P2] Add list & task management module
[P3] Add collaboration & monitoring endpoints
```

---

## 6. Testing Sebelum Push

### 6.1 Checklist Wajib

```bash
# 1. Semua test pass
php artisan test

# 2. Code style sudah di-lint
./vendor/bin/pint

# 3. No dd() atau console.log
grep -r "dd(" app/
grep -r "console.log" app/

# 4. Migration sudah dijalankan
php artisan migrate:fresh

# 5. Seeder bisa dijalankan
php artisan db:seed
```

### 6.2 Test Isolation

Pastikan test tidak bergantung pada data dari test lain:

```php
// ✅ Benar: pakai RefreshDatabase
class MyTest extends TestCase
{
    use RefreshDatabase;
}

// ❌ Salah: pakai data dari test lain
class MyTest extends TestCase
{
    // Tanpa RefreshDatabase
}
```

---

## 7. Communication Protocol

### 7.1 Daily Sync

Setiap programmer update status:
- apa yang sedang dikerjakan
- blocker apa
- perkiraan selesai

### 7.2 Flag Changes

Jika mengubah shared file, **broadcast dulu ke tim**:

> "Saya tambah method `lists()` di User.php. Kalau ada yang perlu disesuaikan, bilang ya."

### 7.3 Merge Order

Urutan merge ke main:
1. **P1** (Auth & Admin) — foundational
2. **P2** (List & Task) — depends on users
3. **P3** (Collaboration) — depends on lists & users

---

## 8. Pre-Release Checklist

Sebelum merge ke main:

- [ ] Semua test pass
- [ ] Code linted (Pint)
- [ ] No debug statements
- [ ] DB_CONTRACT respected
- [ ] API response format consistent
- [ ] Migration & seeder tested
- [ ] File ownership respected
- [ ] Shared file conventions followed
- [ ] No merge conflict dengan main

---

## 9. Common Pitfalls

| Pitfall | Cara Mencegah |
|---------|---------------|
| Overlap file ownership | PM tentukan ownership tegas di awal |
| Auth guard tidak terdefinisi | P1 setup guard, P2/P3 jangan override |
| Pakai `auth()->user()` | P1 pakai `JwtService`, semua harus pakai `request()->user()` |
| Migration duplicate | Cek migration sebelum buat baru |
| Lupa pull main | Pull sebelup push, bukan di tengah development |
| Resolve conflict dengan hapus kode orang | Gabungkan, jangan hapus |

---

## 10. Kesuksesan Hackathon Itu...

1. ✅ PM bagi tugas **tegas** + file ownership **jelas**
2. ✅ Setiap programmer kerja sesuai bagian, **nggak overlap**
3. ✅ Pull main **sebelum push**, bukan di tengah development
4. ✅ Test **lokal** sebelum push
5. ✅ Komunikasi **cepat** saat ada blocker

---

**Dokumen ini harus dibaca dan disetujui oleh semua programmer sebelum development dimulai.**
