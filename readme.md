# JARA — Aplikasi Manajemen Tugas Pribadi & Tim

**Versi:** 1.0
**Tanggal:** 28 September 2026

---

## 1. Pendahuluan

### 1.1 Tujuan
Dokumen ini mendefinisikan kebutuhan fungsional dan non-fungsional untuk aplikasi web **JARA**, sebuah sistem manajemen tugas yang memungkinkan pengguna mengelola tugas pribadi maupun tugas tim secara kolaboratif.

### 1.2 Ruang Lingkup
JARA adalah aplikasi berbasis web yang memungkinkan pengguna:
- Membuat dan mengelompokkan tugas ke dalam **daftar/proyek (list/project)**.
- Menetapkan **prioritas** dan **tenggat waktu (deadline)** pada tugas.
- Menandai tugas sebagai **selesai**.
- **Berkolaborasi** dengan mengundang pengguna lain ke dalam sebuah daftar.
- **Memantau progres** penyelesaian tugas dalam daftar yang dimiliki.
- Dikelola oleh **admin** yang bertanggung jawab atas akun pengguna dalam sistem.

### 1.3 Definisi & Aktor
| Istilah | Deskripsi |
|---|---|
| **Admin** | Pengelola sistem; menambah/menghapus akun pengguna |
| **Owner** | Pemilik daftar tugas; dapat mengundang anggota & memantau progres |
| **Member** | Anggota yang diundang ke suatu daftar; dapat mengerjakan tugas dalam daftar tsb |
| **List/Project** | Wadah pengelompokan tugas |
| **Task** | Unit pekerjaan dengan atribut prioritas, deadline, status |

---

## 2. Deskripsi Umum

### 2.1 Perspektif Produk
JARA adalah aplikasi web mandiri (standalone), diakses via browser, dengan arsitektur client-server (backend REST API / MVC + frontend web).

### 2.2 Karakteristik Pengguna
- **Admin**: staf IT/pengelola sistem, akses penuh ke manajemen akun.
- **Owner (pemilik list)**: pengguna umum yang membuat & mengelola daftar tugasnya sendiri.
- **Member (anggota list)**: pengguna yang ditambahkan owner untuk mengerjakan tugas bersama.

### 2.3 Batasan
- Satu pengguna bisa menjadi owner di satu list dan member di list lain secara bersamaan.
- Hanya owner list yang dapat menambah/menghapus member pada list miliknya.
- Hanya admin yang dapat menambah/menghapus akun pengguna dari sistem.

---

## 3. Kebutuhan Fungsional

### Modul A — Autentikasi & Manajemen Pengguna (Admin)
| ID | Kebutuhan |
|---|---|
| FR-A1 | Sistem menyediakan login untuk semua peran (admin, user) |
| FR-A2 | Admin dapat menambahkan akun pengguna baru |
| FR-A3 | Admin dapat menghapus/menonaktifkan akun pengguna |
| FR-A4 | Admin dapat melihat daftar seluruh pengguna terdaftar |
| FR-A5 | Sistem menerapkan kontrol akses berbasis peran (role-based access) |
| FR-A6 | Pengguna dapat mengelola profil dasar (nama, password) |

### Modul B — Manajemen Daftar (List/Project) & Tugas (Task)
| ID | Kebutuhan |
|---|---|
| FR-B1 | Pengguna dapat membuat daftar/proyek baru |
| FR-B2 | Pengguna dapat mengedit dan menghapus daftar miliknya |
| FR-B3 | Pengguna dapat membuat tugas di dalam suatu daftar |
| FR-B4 | Pengguna dapat mengedit/menghapus tugas |
| FR-B5 | Pengguna dapat menetapkan prioritas tugas (mis. Rendah/Sedang/Tinggi) |
| FR-B6 | Pengguna dapat menetapkan tenggat waktu (deadline) tugas |
| FR-B7 | Pengguna dapat menandai tugas sebagai selesai/belum selesai |
| FR-B8 | Sistem menampilkan daftar tugas terfilter berdasarkan status, prioritas, atau deadline |

### Modul C — Kolaborasi & Monitoring
| ID | Kebutuhan |
|---|---|
| FR-C1 | Owner dapat menambahkan pengguna lain sebagai member ke dalam daftarnya |
| FR-C2 | Owner dapat menghapus member dari daftarnya |
| FR-C3 | Member yang ditambahkan dapat melihat dan mengerjakan tugas dalam daftar tsb |
| FR-C4 | Owner dapat memantau progres penyelesaian tugas dalam daftar (mis. persentase selesai) |
| FR-C5 | Sistem menampilkan dashboard ringkasan progres per daftar |
| FR-C6 | (Opsional) Sistem mengirim notifikasi saat tugas mendekati deadline atau ada perubahan pada daftar bersama |

---

## 4. Kebutuhan Non-Fungsional
- **Keamanan**: password ter-hash, validasi akses berbasis peran & kepemilikan data.
- **Usability**: antarmuka responsif, dapat diakses via desktop/mobile browser.
- **Performa**: waktu respons operasi CRUD < 2 detik pada beban normal.
- **Skalabilitas**: struktur database mendukung penambahan jumlah list/member tanpa perubahan skema besar.