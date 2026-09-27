# AGENT_RULES.md — Development Guidelines & Git Workflow

**Versi:** 1.0  
**Tanggal:** 28 September 2026

Dokumen ini mendefinisikan rules untuk kolaborasi git, API contract, dan komunikasi antar programmer agar pembagian tugas berjalan smooth tanpa saling slice.

---

## 1. Git Workflow

### Branch Naming Convention
```
feat/p[1-3]-[feature-name]
bug/p[1-3]-[bug-name]
```

**Contoh:**
- `feat/p1-user-login`
- `feat/p2-task-crud`
- `feat/p3-dashboard`
- `bug/p1-password-validation`

### Commit Message Format
```
[P1] Add user login endpoint
[P2] Create task model & migration
[P3] Build dashboard UI
```

### Pull Request Process
1. Push ke branch feature Anda
2. Buat PR ke `main` dengan deskripsi jelas
3. Request review dari kedua programmer lain
4. Merge setelah 1 approval

### No Force Push
- Jangan gunakan `git push -f` kecuali untuk branch sendiri yang belum di-push
- Hindari conflict dengan main branch

---

## 2. API Contract & Endpoint Structure

Semua endpoint mengikuti RESTful convention. Response format konsisten:

### Success Response (200, 201)
```json
{
  "success": true,
  "data": { /* actual data */ },
  "message": "Operation successful"
}
```

### Error Response (4xx, 5xx)
```json
{
  "success": false,
  "error": "Error message",
  "code": "ERROR_CODE"
}
```

### Authentication
- Login: `POST /auth/login` → return `token` (JWT)
- Token di header: `Authorization: Bearer <token>`
- Semua endpoint kecuali login & register perlu token

---

## 3. Endpoint Ownership & Responsibility

### P1 — Authentication & Admin Endpoints
```
POST   /auth/login              (user login)
POST   /auth/logout             (user logout)
GET    /admin/users             (list all users) — admin only
POST   /admin/users             (create user) — admin only
DELETE /admin/users/:id         (delete user) — admin only
PUT    /profile                 (update own profile)
PUT    /profile/password        (change password)
```

**Middleware P1 harus sediakan:**
- `authMiddleware` — verify JWT token
- `adminMiddleware` — check role === 'admin'

### P2 — List & Task Endpoints
```
POST   /lists                   (create list)
GET    /lists                   (get user's lists)
GET    /lists/:id               (get specific list)
PUT    /lists/:id               (update list)
DELETE /lists/:id               (delete list)

POST   /lists/:id/tasks         (create task in list)
GET    /lists/:id/tasks         (get tasks in list, with filter)
GET    /tasks/:id               (get specific task)
PUT    /tasks/:id               (update task)
DELETE /tasks/:id               (delete task)
```

**Query Params untuk Filter (FR-B8):**
```
GET /lists/:id/tasks?status=pending&priority=high&deadline=2026-10-01
```

**Validasi P2 harus implement:**
- Check user adalah member dari list sebelum return tasks
- Check user adalah owner sebelum edit/delete list

### P3 — Collaboration & Monitoring Endpoints
```
POST   /lists/:id/members       (invite member to list)
DELETE /lists/:id/members/:uid  (remove member from list)
GET    /lists/:id/members       (get members in list)
GET    /lists/:id/progress      (get progress summary)
GET    /dashboard               (get all lists & progress)
```

**Validasi P3 harus implement:**
- Check user adalah owner sebelum invite/remove member
- Progress dihitung dari completed tasks / total tasks

---

## 4. Database Seeding & Mock Data

Setiap programmer boleh bikin seeder sendiri untuk testing:

```
database/seeders/
├── UserSeeder.php
├── ListSeeder.php
├── TaskSeeder.php
└── MemberSeeder.php
```

Run: `php artisan db:seed`

---

## 5. Testing & Code Quality

### Linting & Code Style
- Gunakan Laravel default style (PSR-12)
- Run: `./vendor/bin/pint` untuk auto-fix

### Testing
- Tulis unit test untuk business logic
- Tulis feature test untuk API endpoints
- Run: `php artisan test`

### Before Pushing
```bash
php artisan test
./vendor/bin/pint
php artisan tinker (quick check model/endpoint)
```

---

## 6. Communication & Conflict Resolution

### Daily Standup (Optional tapi recommended)
- Setiap hari: apa yang dikerjakan, blocker apa, apa next
- Bisa via chat atau brief call

### If Conflict / Blocker
- Discuss di issue atau PR comment
- Agree solution, update DB_CONTRACT atau endpoint jika perlu
- No silent changes — inform team dulu

### Merge Conflict
- Resolve di local branch, test, push
- Don't commit conflict markers

---

## 7. Directory Structure (Expected)

```
jara/
├── app/
│   ├── Models/
│   │   ├── User.php        (P1)
│   │   ├── List.php        (P2)
│   │   ├── Task.php        (P2)
│   │   └── ListMember.php  (P3)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php     (P1)
│   │   │   ├── AdminController.php    (P1)
│   │   │   ├── ListController.php     (P2)
│   │   │   ├── TaskController.php     (P2)
│   │   │   └── CollaborationController.php (P3)
│   │   └── Middleware/
│   │       ├── Authenticate.php       (P1)
│   │       └── AdminCheck.php         (P1)
├── database/
│   ├── migrations/
│   └── seeders/
├── routes/
│   ├── api.php             (main routes)
│   ├── auth.php            (P1)
│   ├── admin.php           (P1)
│   ├── lists.php           (P2)
│   ├── tasks.php           (P2)
│   └── collaboration.php   (P3)
├── resources/views/        (P3 — Frontend)
└── tests/
    ├── Feature/
    ├── Unit/
```

---

## 8. Version Control & Release

- `main` branch: stable, production-ready
- Feature branch: untuk development
- Tag release: `v1.0.0`, `v1.0.1`, etc.

---

## Ringkasan Checklist Sebelum Merge

- [ ] Branch name sesuai convention
- [ ] Commit message clear
- [ ] Code linted (`./vendor/bin/pint`)
- [ ] Tests pass (`php artisan test`)
- [ ] No console.log / dd() di production code
- [ ] DB_CONTRACT respected (no schema breaking)
- [ ] API response format consistent
- [ ] Endpoint documented (comment di controller)
- [ ] Request review dari 1 programmer lain
- [ ] Merge conflict resolved (jika ada)

Selamat coding! 🚀
