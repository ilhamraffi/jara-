# DB_CONTRACT.md — Database Schema Agreement

**Versi:** 1.0  
**Tanggal:** 28 September 2026

Dokumen ini mendefinisikan struktur database yang harus dipatuhi semua programmer. Setiap programmer dapat bekerja independent dengan schema ini sebagai kontrak.

---

## Tables & Schema

### 1. `users`
```sql
CREATE TABLE users (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL (hashed),
  role ENUM('admin', 'user') DEFAULT 'user',
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```
**Catatan:**
- Password harus di-hash (bcrypt)
- Role untuk kontrol akses berbasis peran
- is_active untuk soft-delete admin

---

### 2. `lists` (Project/Daftar Tugas)
```sql
CREATE TABLE lists (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  owner_id BIGINT NOT NULL,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
);
```
**Catatan:**
- owner_id = user yang membuat list
- Satu user bisa punya multiple lists

---

### 3. `tasks` (Tugas dalam List)
```sql
CREATE TABLE tasks (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  list_id BIGINT NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
  deadline DATE,
  status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (list_id) REFERENCES lists(id) ON DELETE CASCADE
);
```
**Catatan:**
- Priority: low, medium, high
- Status: pending, in_progress, completed
- Deadline bisa NULL

---

### 4. `list_members` (Member dalam List — Kolaborasi)
```sql
CREATE TABLE list_members (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  list_id BIGINT NOT NULL,
  user_id BIGINT NOT NULL,
  role ENUM('owner', 'member') DEFAULT 'member',
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  UNIQUE KEY unique_member (list_id, user_id),
  FOREIGN KEY (list_id) REFERENCES lists(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```
**Catatan:**
- role: 'owner' (pembuat list), 'member' (diundang)
- Unique constraint: satu user hanya bisa ada sekali di satu list
- Owner list adalah row pertama dengan role='owner'

---

## Relationships & Rules

### Hak Akses:
- **Admin**: bisa CRUD semua user
- **Owner list**: bisa invite/remove member, CRUD tasks, monitor progres
- **Member list**: bisa view/update tasks dalam list tersebut
- **User biasa**: bisa buat list sendiri (jadi owner)

### Validasi:
1. Hanya owner atau admin yang bisa edit/delete list
2. Hanya owner list yang bisa invite/remove member
3. Hanya member (owner atau invited) yang bisa akses tasks dalam list
4. Task hanya bisa dibuat di list yang user-nya adalah member

---

## Indexing untuk Performa

```sql
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_lists_owner_id ON lists(owner_id);
CREATE INDEX idx_tasks_list_id ON tasks(list_id);
CREATE INDEX idx_tasks_status ON tasks(status);
CREATE INDEX idx_tasks_priority ON tasks(priority);
CREATE INDEX idx_list_members_list_id ON list_members(list_id);
CREATE INDEX idx_list_members_user_id ON list_members(user_id);
```

---

## Catatan untuk Programmer

- **P1 (Auth & Admin)**: Fokus pada table `users` & autentikasi
- **P2 (List & Task)**: Fokus pada `lists` & `tasks` dengan validasi member di `list_members`
- **P3 (Frontend & Kolaborasi)**: Consume semua endpoint, handle `list_members` untuk invite/remove

Semua bisa bekerja parallel menggunakan schema ini sebagai referensi.
