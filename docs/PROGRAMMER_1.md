# PROGRAMMER_1.md — Authentication & Admin Module

**Programmer:** P1  
**Modul:** Autentikasi & Manajemen Pengguna (Admin)  
**Fitur:** FR-A1 to FR-A6

---

## Overview

P1 bertanggung jawab untuk mengimplementasikan sistem autentikasi, manajemen user oleh admin, dan role-based access control. Pekerjaan ini bersifat standalone dan bisa dikerjakan tanpa menunggu P2 atau P3.

---

## Fitur yang Harus Diimplementasikan

| ID | Kebutuhan | Status |
|---|---|---|
| FR-A1 | Sistem menyediakan login untuk semua peran (admin, user) | Completed |
| FR-A2 | Admin dapat menambahkan akun pengguna baru | Completed |
| FR-A3 | Admin dapat menghapus/menonaktifkan akun pengguna | Completed |
| FR-A4 | Admin dapat melihat daftar seluruh pengguna terdaftar | Completed |
| FR-A5 | Sistem menerapkan kontrol akses berbasis peran (role-based access) | Completed |
| FR-A6 | Pengguna dapat mengelola profil dasar (nama, password) | Completed |

---

## Database & Schema

Refer: `docs/DB_CONTRACT.md` — table `users`

```sql
CREATE TABLE users (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin', 'user') DEFAULT 'user',
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

**Penting:**
- Password harus di-hash menggunakan bcrypt
- Role: 'admin' atau 'user'
- is_active untuk track user aktif/nonaktif

---

## API Endpoints yang Harus Dibuat

### 1. Login
```
POST /auth/login
Request Body:
{
  "email": "user@example.com",
  "password": "password123"
}

Response (200):
{
  "success": true,
  "data": {
    "token": "eyJhbGc...",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "user"
    }
  },
  "message": "Login successful"
}

Response (401):
{
  "success": false,
  "error": "Invalid credentials",
  "code": "INVALID_CREDENTIALS"
}
```

### 2. Logout
```
POST /auth/logout
Header: Authorization: Bearer <token>

Response (200):
{
  "success": true,
  "message": "Logout successful"
}
```

### 3. List All Users (Admin Only)
```
GET /admin/users
Header: Authorization: Bearer <token>

Response (200):
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com",
      "role": "admin",
      "is_active": true,
      "created_at": "2026-09-28T00:00:00Z"
    },
    ...
  ]
}

Response (403):
{
  "success": false,
  "error": "Unauthorized. Admin access required.",
  "code": "ADMIN_ONLY"
}
```

### 4. Create User (Admin Only)
```
POST /admin/users
Header: Authorization: Bearer <token>
Request Body:
{
  "name": "New User",
  "email": "newuser@example.com",
  "password": "password123",
  "role": "user"  (optional, default "user")
}

Response (201):
{
  "success": true,
  "data": {
    "id": 5,
    "name": "New User",
    "email": "newuser@example.com",
    "role": "user",
    "is_active": true,
    "created_at": "2026-09-28T12:34:56Z"
  },
  "message": "User created successfully"
}

Response (400):
{
  "success": false,
  "error": "Email already registered",
  "code": "EMAIL_EXISTS"
}
```

### 5. Delete/Deactivate User (Admin Only)
```
DELETE /admin/users/:id
Header: Authorization: Bearer <token>

Response (200):
{
  "success": true,
  "message": "User deactivated successfully"
}

Response (404):
{
  "success": false,
  "error": "User not found",
  "code": "USER_NOT_FOUND"
}
```

### 6. Update Own Profile
```
PUT /profile
Header: Authorization: Bearer <token>
Request Body:
{
  "name": "Updated Name"
}

Response (200):
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Updated Name",
    "email": "user@example.com"
  }
}
```

### 7. Change Password
```
PUT /profile/password
Header: Authorization: Bearer <token>
Request Body:
{
  "old_password": "oldpass123",
  "new_password": "newpass456"
}

Response (200):
{
  "success": true,
  "message": "Password changed successfully"
}

Response (400):
{
  "success": false,
  "error": "Old password is incorrect",
  "code": "INVALID_PASSWORD"
}
```

---

## Middleware yang Harus Dibuat

### 1. Authentication Middleware (`authMiddleware`)
- Validasi JWT token di header `Authorization: Bearer <token>`
- Attach user data ke request
- Return 401 jika token invalid/expired

### 2. Admin Check Middleware (`adminMiddleware`)
- Validasi user role === 'admin'
- Return 403 jika bukan admin
- Harus digunakan setelah `authMiddleware`

---

## File Structure (Expected)

```
app/
├── Models/
│   └── User.php
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   └── AdminController.php
│   └── Middleware/
│       ├── Authenticate.php
│       └── AdminCheck.php
database/
├── migrations/
│   └── 2026_09_28_create_users_table.php
└── seeders/
    └── UserSeeder.php
routes/
├── api.php
├── auth.php
└── admin.php
tests/
├── Feature/
│   ├── Auth/
│   │   ├── LoginTest.php
│   │   └── LogoutTest.php
│   └── Admin/
│       ├── UserManagementTest.php
│       └── AdminAccessTest.php
```

---

## Checklist

- [x] User model dengan hashing password
- [x] Migration untuk users table
- [x] Auth controller (login, logout)
- [x] Admin controller (CRUD users)
- [x] Authentication middleware
- [x] Admin check middleware
- [x] Routes untuk auth & admin
- [x] Seeder untuk dummy data
- [x] Unit tests untuk models
- [x] Feature tests untuk endpoints
- [x] Error handling & validation
- [x] Response format consistent

---

## Notes

- Gunakan Laravel's built-in hashing & authentication jika memungkinkan
- JWT token bisa pakai package seperti `tymon/jwt-auth`
- Validate email format & password strength
- Test semua endpoint sebelum push
- Dokumentasikan di PR description
