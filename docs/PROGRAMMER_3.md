# PROGRAMMER_3.md — Frontend & Collaboration Module

**Programmer:** P3  
**Modul:** Kolaborasi & Monitoring + Frontend UI  
**Fitur:** FR-C1 to FR-C6 + Frontend Implementation

---

## Overview

P3 bertanggung jawab untuk mengimplementasikan fitur kolaborasi (invite/remove member), monitoring progres, dan seluruh frontend UI aplikasi. Pekerjaan ini bersifat standalone; gunakan mock API responses jika P1/P2 belum selesai.

---

## Fitur yang Harus Diimplementasikan

| ID | Kebutuhan | Status |
|---|---|---|
| FR-C1 | Owner dapat menambahkan pengguna lain sebagai member ke dalam daftarnya | - |
| FR-C2 | Owner dapat menghapus member dari daftarnya | - |
| FR-C3 | Member yang ditambahkan dapat melihat dan mengerjakan tugas dalam daftar tsb | - |
| FR-C4 | Owner dapat memantau progres penyelesaian tugas dalam daftar | - |
| FR-C5 | Sistem menampilkan dashboard ringkasan progres per daftar | - |
| FR-C6 | (Opsional) Sistem mengirim notifikasi saat tugas mendekati deadline | - |

---

## API Endpoints yang Harus Dibuat

### Collaboration Endpoints

#### 1. Invite Member to List
```
POST /lists/:id/members
Header: Authorization: Bearer <token>
Request Body:
{
  "user_id": 7,
  "role": "member"  (optional, default "member")
}

Response (201):
{
  "success": true,
  "data": {
    "id": 5,
    "list_id": 1,
    "user_id": 7,
    "user": {
      "id": 7,
      "name": "Jane Doe",
      "email": "jane@example.com"
    },
    "role": "member",
    "created_at": "2026-09-28T12:00:00Z"
  },
  "message": "Member added successfully"
}

Response (403):
{
  "success": false,
  "error": "Only owner can invite members",
  "code": "OWNER_ONLY"
}

Response (400):
{
  "success": false,
  "error": "User already a member of this list",
  "code": "MEMBER_EXISTS"
}
```

#### 2. Remove Member from List
```
DELETE /lists/:id/members/:user_id
Header: Authorization: Bearer <token>

Response (200):
{
  "success": true,
  "message": "Member removed successfully"
}

Response (403):
{
  "success": false,
  "error": "Only owner can remove members",
  "code": "OWNER_ONLY"
}
```

#### 3. Get Members in List
```
GET /lists/:id/members
Header: Authorization: Bearer <token>

Response (200):
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 5,
      "user": {
        "id": 5,
        "name": "Owner Name",
        "email": "owner@example.com"
      },
      "role": "owner"
    },
    {
      "id": 2,
      "user_id": 7,
      "user": {
        "id": 7,
        "name": "Jane Doe",
        "email": "jane@example.com"
      },
      "role": "member"
    },
    ...
  ]
}
```

---

### Progress & Monitoring Endpoints

#### 4. Get List Progress
```
GET /lists/:id/progress
Header: Authorization: Bearer <token>

Response (200):
{
  "success": true,
  "data": {
    "list_id": 1,
    "total_tasks": 10,
    "completed_tasks": 5,
    "progress_percentage": 50,
    "tasks_by_status": {
      "pending": 3,
      "in_progress": 2,
      "completed": 5
    },
    "tasks_by_priority": {
      "low": 2,
      "medium": 5,
      "high": 3
    },
    "upcoming_deadlines": [
      {
        "id": 3,
        "title": "Task title",
        "deadline": "2026-10-05",
        "days_left": 7
      }
    ]
  }
}
```

#### 5. Get Dashboard (All Lists & Progress)
```
GET /dashboard
Header: Authorization: Bearer <token>

Response (200):
{
  "success": true,
  "data": {
    "total_lists": 3,
    "owned_lists": 2,
    "member_lists": 1,
    "lists": [
      {
        "id": 1,
        "name": "Project A",
        "owner_id": 5,
        "total_tasks": 10,
        "completed_tasks": 5,
        "progress_percentage": 50,
        "member_count": 3,
        "your_role": "owner"
      },
      {
        "id": 2,
        "name": "Project B",
        "owner_id": 6,
        "total_tasks": 8,
        "completed_tasks": 2,
        "progress_percentage": 25,
        "member_count": 2,
        "your_role": "member"
      },
      ...
    ],
    "total_tasks": 20,
    "total_completed": 8,
    "overall_progress": 40
  }
}
```

---

## Frontend UI Pages/Components

### 1. Login Page
- Form dengan email & password
- Button submit & link "forgot password" (optional)
- Token disimpan di localStorage

### 2. Dashboard
- Overview semua lists yang user-nya member
- Card per list dengan:
  - List name
  - Progress bar (completed / total tasks)
  - Member count
  - Tombol "View" untuk masuk ke list
- Summary: total tasks, completed tasks, overall progress

### 3. List Detail Page
- List name & description
- Tabs: Tasks, Members, Progress
- **Tasks Tab:**
  - List semua tasks
  - Filter by status, priority, deadline
  - Tombol "Add Task"
  - Per task: title, priority badge, deadline, status (checkbox for complete)
  - Edit/Delete buttons (untuk owner/member)
  
- **Members Tab:**
  - List semua members dengan role
  - Input "Add Member" (owner only)
  - Remove button (owner only)
  
- **Progress Tab:**
  - Progress bar
  - Stats: total/completed/pending tasks
  - Tasks by priority pie chart
  - Upcoming deadlines list

### 4. Task Create/Edit Modal
- Form dengan fields:
  - Title (required)
  - Description (textarea)
  - Priority dropdown (low/medium/high)
  - Deadline date picker
- Submit & Cancel buttons

### 5. Profile Page
- Display user info (name, email, role)
- Form update name
- Form change password
- Logout button

### 6. Admin Panel (if user is admin)
- List semua users
- Create user form
- Delete user button
- Deactivate user checkbox

---

## Frontend Technology Recommendations

- **Framework:** React, Vue, atau vanilla JS (sesuai preference)
- **State Management:** Context API, Vuex, atau Redux jika perlu
- **HTTP Client:** axios atau fetch
- **UI Components:** Bootstrap, Tailwind, atau Material UI
- **Charts (optional):** Chart.js atau similar untuk pie chart
- **Date Picker:** flatpickr atau similar

---

## Key Frontend Logic

### 1. Authentication
- Login: send POST /auth/login, store token di localStorage
- Logout: clear localStorage, redirect ke login
- Protected routes: check token existence & redirect if missing

### 2. API Consumption
- Attach token di setiap request header
- Handle error responses (401, 403, 400, 500)
- Display user-friendly error messages

### 3. List & Task Management
- Fetch lists via GET /lists
- Fetch tasks via GET /lists/:id/tasks dengan filter params
- CRUD operations via appropriate endpoints

### 4. Real-time Updates (Optional)
- Polling: refresh data setiap N detik
- WebSocket: (opsional untuk real-time collab)

### 5. Validation
- Form validation sebelum submit
- Display validation errors

---

## File Structure (Expected)

```
resources/
├── views/
│   ├── layouts/
│   │   └── app.blade.php (main layout)
│   ├── auth/
│   │   ├── login.blade.php
│   │   └── register.blade.php (optional)
│   ├── dashboard.blade.php
│   ├── lists/
│   │   ├── index.blade.php
│   │   ├── show.blade.php
│   │   ├── create.blade.php
│   │   └── edit.blade.php
│   ├── tasks/
│   │   ├── create.blade.php
│   │   ├── edit.blade.php
│   │   └── show.blade.php
│   ├── profile.blade.php
│   └── admin/
│       └── users.blade.php
└── css/ & js/
    ├── app.css
    └── app.js

public/
├── css/
│   └── style.css
├── js/
│   └── main.js
└── images/

OR (if React/Vue):

resources/
└── js/
    ├── components/
    │   ├── Dashboard.jsx
    │   ├── ListDetail.jsx
    │   ├── TaskForm.jsx
    │   ├── MemberManager.jsx
    │   └── ProgressMonitor.jsx
    ├── pages/
    │   ├── Login.jsx
    │   ├── Profile.jsx
    │   └── Admin.jsx
    └── App.jsx
```

---

## Checklist

### Collaboration Features
- [ ] Invite member endpoint
- [ ] Remove member endpoint
- [ ] Get members endpoint
- [ ] Authorization check (owner-only)
- [ ] Duplicate member check

### Progress & Monitoring
- [ ] Get list progress endpoint
- [ ] Get dashboard endpoint
- [ ] Progress calculation logic
- [ ] Status/priority statistics

### Frontend UI
- [ ] Login page
- [ ] Dashboard page
- [ ] List detail page (with tabs)
- [ ] Task CRUD modals
- [ ] Member management UI
- [ ] Profile page
- [ ] Admin panel (if admin)
- [ ] Responsive design (mobile-friendly)
- [ ] Error handling & alerts
- [ ] Loading states

### General
- [ ] Form validation
- [ ] API error handling
- [ ] Token management
- [ ] Protected routes
- [ ] Logout functionality
- [ ] Tests for components (unit/integration)

---

## Notes

- Use mock API responses during development if P1/P2 not ready
- Ensure responsive design for mobile/tablet/desktop
- Keep UI consistent throughout app
- Handle edge cases (empty states, errors, loading)
- Test all filter combinations for tasks
- Coordinate dengan P1 & P2 untuk API integration
- Document UI/UX decisions di PR description
- Consider accessibility (WCAG compliance) untuk form labels, buttons, etc.

---

## Optional Enhancements (FR-C6)

### Notifications for Upcoming Deadlines
- Backend: cron job untuk check tasks with deadline <= 3 days
- Send notification via email atau in-app notification
- Frontend: display notification banner atau badge

### Collaboration Features (Advanced)
- Real-time task updates (WebSocket)
- Comment/activity log per task
- Task assignment (task assigned_to user)
