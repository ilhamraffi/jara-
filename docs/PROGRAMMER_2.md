# PROGRAMMER_2.md — List & Task Management Module

**Programmer:** P2  
**Modul:** Manajemen Daftar (List/Project) & Tugas (Task)  
**Fitur:** FR-B1 to FR-B8

---

## Overview

P2 bertanggung jawab untuk mengimplementasikan CRUD list/project, CRUD task dengan atribut prioritas & deadline, dan filtering tasks. Pekerjaan ini bersifat standalone; gunakan mock User model jika P1 belum selesai.

---

## Fitur yang Harus Diimplementasikan

| ID | Kebutuhan | Status |
|---|---|---|
| FR-B1 | Pengguna dapat membuat daftar/proyek baru | - |
| FR-B2 | Pengguna dapat mengedit dan menghapus daftar miliknya | - |
| FR-B3 | Pengguna dapat membuat tugas di dalam suatu daftar | - |
| FR-B4 | Pengguna dapat mengedit/menghapus tugas | - |
| FR-B5 | Pengguna dapat menetapkan prioritas tugas (low/medium/high) | - |
| FR-B6 | Pengguna dapat menetapkan tenggat waktu (deadline) tugas | - |
| FR-B7 | Pengguna dapat menandai tugas sebagai selesai/belum selesai | - |
| FR-B8 | Sistem menampilkan daftar tugas terfilter berdasarkan status, prioritas, deadline | - |

---

## Database & Schema

Refer: `docs/DB_CONTRACT.md` — tables `lists`, `tasks`, `list_members`

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

**Penting:**
- Task priority: 'low', 'medium', 'high'
- Task status: 'pending', 'in_progress', 'completed'
- Deadline bisa NULL
- Validasi: hanya member/owner dapat akses tasks dalam list

---

## API Endpoints yang Harus Dibuat

### 1. Create List
```
POST /lists
Header: Authorization: Bearer <token>
Request Body:
{
  "name": "My Project",
  "description": "Project description"
}

Response (201):
{
  "success": true,
  "data": {
    "id": 1,
    "name": "My Project",
    "description": "Project description",
    "owner_id": 5,
    "created_at": "2026-09-28T12:00:00Z"
  },
  "message": "List created successfully"
}
```

### 2. Get User's Lists
```
GET /lists
Header: Authorization: Bearer <token>

Response (200):
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "My Project",
      "description": "Project description",
      "owner_id": 5,
      "member_count": 3,
      "task_count": 10,
      "completed_count": 5,
      "created_at": "2026-09-28T12:00:00Z"
    },
    ...
  ]
}
```

### 3. Get Specific List
```
GET /lists/:id
Header: Authorization: Bearer <token>

Response (200):
{
  "success": true,
  "data": {
    "id": 1,
    "name": "My Project",
    "description": "Project description",
    "owner_id": 5,
    "created_at": "2026-09-28T12:00:00Z"
  }
}

Response (403):
{
  "success": false,
  "error": "You are not a member of this list",
  "code": "ACCESS_DENIED"
}
```

### 4. Update List
```
PUT /lists/:id
Header: Authorization: Bearer <token>
Request Body:
{
  "name": "Updated Project",
  "description": "New description"
}

Response (200):
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Updated Project",
    "description": "New description",
    "updated_at": "2026-09-28T13:00:00Z"
  }
}

Response (403):
{
  "success": false,
  "error": "Only owner can update this list",
  "code": "OWNER_ONLY"
}
```

### 5. Delete List
```
DELETE /lists/:id
Header: Authorization: Bearer <token>

Response (200):
{
  "success": true,
  "message": "List deleted successfully"
}

Response (403):
{
  "success": false,
  "error": "Only owner can delete this list",
  "code": "OWNER_ONLY"
}
```

---

## Task Endpoints

### 6. Create Task in List
```
POST /lists/:id/tasks
Header: Authorization: Bearer <token>
Request Body:
{
  "title": "Task title",
  "description": "Task description",
  "priority": "high",
  "deadline": "2026-10-15"
}

Response (201):
{
  "success": true,
  "data": {
    "id": 1,
    "list_id": 1,
    "title": "Task title",
    "description": "Task description",
    "priority": "high",
    "deadline": "2026-10-15",
    "status": "pending",
    "created_at": "2026-09-28T12:00:00Z"
  }
}

Response (403):
{
  "success": false,
  "error": "You are not a member of this list",
  "code": "ACCESS_DENIED"
}
```

### 7. Get Tasks in List (with Filtering)
```
GET /lists/:id/tasks?status=pending&priority=high&deadline=2026-10-15
Header: Authorization: Bearer <token>

Query Params (all optional):
- status: pending, in_progress, completed
- priority: low, medium, high
- deadline: YYYY-MM-DD (exact date or deadline <= this date)

Response (200):
{
  "success": true,
  "data": [
    {
      "id": 1,
      "list_id": 1,
      "title": "Task title",
      "description": "Task description",
      "priority": "high",
      "deadline": "2026-10-15",
      "status": "pending",
      "created_at": "2026-09-28T12:00:00Z"
    },
    ...
  ]
}
```

### 8. Get Specific Task
```
GET /tasks/:id
Header: Authorization: Bearer <token>

Response (200):
{
  "success": true,
  "data": {
    "id": 1,
    "list_id": 1,
    "title": "Task title",
    "description": "Task description",
    "priority": "high",
    "deadline": "2026-10-15",
    "status": "pending",
    "created_at": "2026-09-28T12:00:00Z"
  }
}
```

### 9. Update Task
```
PUT /tasks/:id
Header: Authorization: Bearer <token>
Request Body:
{
  "title": "Updated title",
  "status": "completed",
  "priority": "medium",
  "deadline": "2026-10-20"
}

Response (200):
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Updated title",
    "status": "completed",
    "priority": "medium",
    "deadline": "2026-10-20",
    "updated_at": "2026-09-28T13:00:00Z"
  }
}
```

### 10. Delete Task
```
DELETE /tasks/:id
Header: Authorization: Bearer <token>

Response (200):
{
  "success": true,
  "message": "Task deleted successfully"
}
```

---

## Business Logic & Validation

### Authorization Rules
- User harus menjadi member list sebelum akses/create tasks
- Member berperan sebagai 'owner' atau 'member' (refer list_members table)
- Hanya owner list yang bisa edit/delete list

### Task Creation
- Task hanya bisa dibuat di list yang user adalah member
- Default status: 'pending', priority: 'medium'
- Deadline bisa NULL

### Filtering (FR-B8)
- Support filter by status, priority, deadline
- Combine multiple filters dengan AND logic
- Example: `?status=pending&priority=high` → pending tasks dengan priority high

---

## File Structure (Expected)

```
app/
├── Models/
│   ├── List.php
│   ├── Task.php
│   └── ListMember.php
├── Http/
│   ├── Controllers/
│   │   ├── ListController.php
│   │   └── TaskController.php
│   └── Requests/
│       ├── StoreListRequest.php
│       ├── UpdateListRequest.php
│       ├── StoreTaskRequest.php
│       └── UpdateTaskRequest.php
database/
├── migrations/
│   ├── 2026_09_28_create_lists_table.php
│   ├── 2026_09_28_create_tasks_table.php
│   └── 2026_09_28_create_list_members_table.php
└── seeders/
    ├── ListSeeder.php
    ├── TaskSeeder.php
    └── MemberSeeder.php
routes/
├── lists.php
└── tasks.php
tests/
├── Feature/
│   ├── Lists/
│   │   ├── CreateListTest.php
│   │   └── UpdateListTest.php
│   └── Tasks/
│       ├── CreateTaskTest.php
│       └── FilterTaskTest.php
```

---

## Checklist

- [ ] List model dengan relationship ke User & Tasks
- [ ] Task model dengan relationship ke List
- [ ] ListMember model untuk track membership
- [ ] Migrations untuk lists, tasks, list_members
- [ ] List controller (CRUD)
- [ ] Task controller (CRUD + filtering)
- [ ] Form requests untuk validation
- [ ] Authorization logic (owner-only, member-check)
- [ ] Filter logic untuk tasks
- [ ] Seeders untuk dummy data
- [ ] Unit tests untuk models
- [ ] Feature tests untuk endpoints
- [ ] Error handling & validation
- [ ] Response format consistent

---

## Notes

- Test filtering dengan berbagai kombinasi parameter
- Validasi ownership sebelum edit/delete
- Query optimization dengan eager loading (avoid N+1)
- Document endpoints di PR description
- Coordinate dengan P1 untuk User model integration
