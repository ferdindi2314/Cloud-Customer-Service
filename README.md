# 🎫 Cloud Customer Support - Ticketing System

[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel)](https://laravel.com)
[![Firebase](https://img.shields.io/badge/Firebase-Firestore-FFCA28?logo=firebase)](https://firebase.google.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php)](https://php.net)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?logo=bootstrap)](https://getbootstrap.com)

Sistem **Cloud Customer Support** berbasis Laravel 11 dan Firebase untuk mengelola tiket layanan pelanggan secara real-time. Platform modern untuk mencatat keluhan, assign ke agent, dan tracking progress perbaikan dengan komunikasi 2 arah.

## 🌟 Fitur Utama

- ✅ **Real-time Sync** - Firestore auto-sync untuk update instant
- 👥 **Role-Based Access** - 3 role (Admin, Agent, Customer) dengan permission berbeda
- 📊 **Dashboard Statistics** - Card analytics untuk monitoring
- 💬 **Comment System** - Komunikasi 2 arah Customer ↔ Agent
- 📎 **File Attachments** - Upload foto/dokumen bukti
- 🔐 **Authentication** - Laravel Breeze dengan email/password
- 🗄️ **Hybrid Database** - Firestore (cloud) + SQLite (local) untuk performa optimal
- 🎨 **Responsive UI** - Bootstrap 5 dengan design modern

---

## 📋 Daftar Isi

1. [Tech Stack](#-tech-stack)
2. [Arsitektur Sistem](#%EF%B8%8F-arsitektur-sistem)
3. [Role & Permission](#-role--permission)
4. [Instalasi](#-instalasi)
5. [Konfigurasi Firebase](#-konfigurasi-firebase)
6. [Struktur Database](#%EF%B8%8F-struktur-database)
7. [Alur Kerja Sistem](#-alur-kerja-sistem)
8. [API Routes](#%EF%B8%8F-api-routes)
9. [Troubleshooting](#-troubleshooting)

---

## 🚀 Tech Stack

| Layer | Teknologi | Fungsi |
|-------|-----------|--------|
| **Backend** | Laravel 11 (PHP 8.3) | Framework utama aplikasi |
| **Frontend** | Blade Templates + Bootstrap 5 | View engine & UI framework |
| **Database (Local)** | SQLite | Fast query untuk users, categories, tickets |
| **Database (Cloud)** | Firebase Firestore | Real-time sync antar user |
| **File Storage** | Firebase Storage | Cloud storage untuk attachments |
| **Authentication** | Laravel Breeze | Login, register, password reset |
| **Authorization** | Custom RoleMiddleware | Role-based access control |

---

## 🏗️ Arsitektur Sistem

### Hybrid Database Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    USER INTERFACE                       │
│              (Blade Templates + Bootstrap)              │
└────────────────────┬────────────────────────────────────┘
                     │
         ┌───────────▼───────────┐
         │   Laravel Controller  │
         │   (TicketController)  │
         └───────────┬───────────┘
                     │
         ┌───────────▼───────────┐
         │   TicketService       │
         │ (Business Logic Layer)│
         └─────┬───────────┬─────┘
               │           │
       ┌───────▼──┐    ┌──▼────────┐
       │ SQLite   │    │ Firestore │
       │ Database │◄───►│ (Cloud)   │
       └──────────┘    └───────────┘
          (Fast          (Real-time
           Query)         Sync)
```

**Keuntungan Dual Database:**
- **Firestore**: Real-time sync, cloud-based, auto-scaling
- **SQLite**: Query cepat, filtering & sorting efisien, tidak butuh internet untuk query
- **Strategi**: Setiap perubahan di Firestore auto-sync ke SQLite

---

## 👥 Role & Permission

### Role Matrix

| Role | Akses | Deskripsi |
|------|-------|-----------|
| **Customer** | Buat ticket, lihat tickets sendiri, tambah comment di tickets sendiri | User biasa yang melaporkan masalah |
| **Agent** | Lihat tickets yang di-assign, update status (in_progress, resolved), tambah comment | Teknisi yang menangani perbaikan |
| **Admin** | Full access, assign tickets ke agent, kelola users & categories, close tickets | Manager yang mengatur workflow |

### Permission Detail

| Fitur | Customer | Agent | Admin |
|-------|----------|-------|-------|
| Lihat semua tickets | ❌ | ❌ | ✅ |
| Lihat tickets sendiri | ✅ | ✅ (assigned) | ✅ |
| Buat ticket baru | ✅ | ❌ | ✅ |
| Edit ticket (status: open) | ✅ | ❌ | ✅ |
| Hapus ticket (status: open) | ✅ | ❌ | ✅ |
| Assign ticket ke agent | ❌ | ❌ | ✅ |
| Update status (in_progress) | ❌ | ✅ | ❌ |
| Update status (resolved) | ❌ | ✅ | ❌ |
| Update status (closed) | ❌ | ❌ | ✅ |
| Kelola users (CRUD) | ❌ | ❌ | ✅ |
| Kelola categories (CRUD) | ❌ | ❌ | ✅ |

---

## 📦 Instalasi

### Prasyarat

- PHP 8.3+ (atau minimal PHP 8.2)
- Composer
- Node.js & NPM (untuk build assets)
- Firebase Account (Firestore & Storage enabled)
- Git

### Step 1: Clone Repository

```bash
git clone https://github.com/ferdindi2314/Cloud-Customer-Service.git
cd Cloud-Customer-Service
```

### Step 2: Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### Step 3: Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 4: Database Setup

```bash
# Run migrations (buat tabel users, categories, tickets, ticket_comments)
php artisan migrate

# Seed data default (admin, agent, customer, categories)
php artisan db:seed
```

**Default Users Setelah Seeding:**

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@example.com | password |
| Agent 1 | agent1@example.com | password |
| Agent 2 | agent2@example.com | password |
| Customer | customer1@example.com | password |

### Step 5: Build Assets

```bash
# Development build
npm run dev

# Production build
npm run build
```

### Step 6: Run Application

```bash
# Start Laravel development server
php artisan serve
```

Buka browser: **http://localhost:8000**

---

## 🔥 Konfigurasi Firebase

### 1. Buat Firebase Project

1. Buka [Firebase Console](https://console.firebase.google.com)
2. Klik **Add Project** / **Create a project**
3. Ikuti wizard setup (nama project, enable Google Analytics - opsional)

### 2. Enable Firestore & Storage

**Firestore:**
1. Sidebar → **Firestore Database**
2. Klik **Create database**
3. Pilih mode: **Production mode** atau **Test mode** (untuk development)
4. Pilih region: **asia-southeast1** (Singapore) atau terdekat

**Storage:**
1. Sidebar → **Storage**
2. Klik **Get started**
3. Pilih **Start in test mode** (untuk development)
4. Pilih region yang sama dengan Firestore

### 3. Download Service Account JSON

1. Firebase Console → **Project Settings** (gear icon)
2. Tab **Service accounts**
3. Klik **Generate new private key**
4. Download file JSON

### 4. Simpan Credentials

1. Buat folder `storage/app/firebase/` (jika belum ada)
2. Rename file JSON yang didownload jadi `service-account.json`
3. Pindahkan ke `storage/app/firebase/service-account.json`

**⚠️ PENTING:** File ini sudah masuk `.gitignore`, jangan commit ke repository!

### 5. Update `.env`

Tambahkan konfigurasi Firebase di file `.env`:

```env
# Firebase Configuration
FIREBASE_CREDENTIALS=storage/app/firebase/service-account.json
FIREBASE_PROJECT_ID=your-project-id-here
FIREBASE_STORAGE_BUCKET=your-project-id-here.appspot.com
FIREBASE_DATABASE_URL=
FIREBASE_FIRESTORE_ENABLED=true
```

**Cara Dapatkan Info:**
- **Project ID**: Bisa dilihat di Firebase Console → Project Settings
- **Storage Bucket**: Biasanya `{project-id}.appspot.com`

### 6. Test Koneksi Firebase

```bash
# Akses endpoint test (harus login sebagai admin/agent)
http://localhost:8000/firebase-test
```

Response JSON jika sukses:
```json
{
  "ok": true,
  "project_id": "your-project-id",
  "firestore": {
    "ok": true,
    "collection": "tickets",
    "sample_exists": true
  },
  "storage": {
    "ok": true,
    "bucket": "your-project-id.appspot.com",
    "bucket_exists": true
  }
}
```

---

## 🗄️ Struktur Database

### SQLite Tables (Local Database)

#### Table: `users`
```sql
id              INT PRIMARY KEY
name            VARCHAR(255)     -- Nama lengkap
email           VARCHAR(255)     -- Email (unique)
password        VARCHAR(255)     -- Password (hashed bcrypt)
role            VARCHAR(50)      -- 'admin', 'agent', 'customer'
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

#### Table: `categories`
```sql
id              INT PRIMARY KEY
name            VARCHAR(255)     -- Nama kategori
slug            VARCHAR(255)     -- URL-friendly slug
description     TEXT             -- Deskripsi kategori
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

Default categories dari seeder:
- Perbaikan Mesin
- Quality Control
- Safety Issue
- Request Sparepart
- Lain-lain

#### Table: `tickets`
```sql
id              INT PRIMARY KEY
firebase_id     VARCHAR(255)     -- ID di Firestore (untuk sync)
title           VARCHAR(255)     -- Judul ticket
description     TEXT             -- Detail masalah
customer_id     INT              -- FK ke users
agent_id        INT NULL         -- FK ke users (agent assigned)
category_id     INT              -- FK ke categories
status          VARCHAR(50)      -- 'open','assigned','in_progress','resolved','closed'
priority        VARCHAR(50)      -- 'low', 'medium', 'high'
attachments     JSON NULL        -- Array file attachments
created_at      TIMESTAMP
updated_at      TIMESTAMP
deleted_at      TIMESTAMP NULL   -- Soft delete
```

#### Table: `ticket_comments`
```sql
id              INT PRIMARY KEY
firebase_id     VARCHAR(255)     -- ID di Firestore
ticket_id       INT              -- FK ke tickets
user_id         INT              -- FK ke users
comment         TEXT             -- Isi komentar
attachments     JSON NULL        -- Array file attachments
is_internal     BOOLEAN          -- Internal comment (admin/agent only)
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

### Firestore Collections (Cloud Database)

#### Collection: `tickets/{ticketId}`
```javascript
{
  title: "Mesin Rusak",
  description: "Mesin produksi line A tidak mau menyala sejak kemarin",
  customer_id: "1",              // String
  customer_name: "John Doe",
  agent_id: "2",                 // String, null jika belum assigned
  agent_name: "Agent Budi",
  category: "Perbaikan Mesin",
  category_id: "1",
  status: "in_progress",         // open|assigned|in_progress|resolved|closed
  priority: "high",              // low|medium|high
  attachments: [
    {
      name: "foto-mesin.jpg",
      path: "tickets/ABC123/foto-mesin.jpg",
      size: 102400,
      content_type: "image/jpeg",
      storage: "firebase"
    }
  ],
  created_at: Timestamp,
  updated_at: Timestamp
}
```

#### SubCollection: `tickets/{ticketId}/comments/{commentId}`
```javascript
{
  user_id: "2",
  user_name: "Agent Budi",
  role: "agent",
  comment: "Sedang saya cek, butuh sparepart baru dari supplier",
  attachments: [],
  is_internal: false,
  created_at: Timestamp,
  updated_at: Timestamp
}
```

---

## 🔄 Alur Kerja Sistem

### Status Lifecycle Ticket

```
open → assigned → in_progress → resolved → closed
```

**Penjelasan:**
1. **open** - Customer baru buat ticket, belum ada yang handle
2. **assigned** - Admin sudah assign ke agent tertentu
3. **in_progress** - Agent sedang mengerjakan
4. **resolved** - Agent sudah selesai, upload bukti
5. **closed** - Admin verify & tutup ticket

### Flow Diagram

```
┌──────────┐
│ Customer │ ─────► Buat Ticket (status: open)
└──────────┘              │
                          ▼
                    ┌─────────┐
                    │  Admin  │ ─────► Assign ke Agent (status: assigned)
                    └─────────┘              │
                                             ▼
                                       ┌─────────┐
                                       │  Agent  │ ─────► Kerjakan (status: in_progress)
                                       └─────────┘              │
                                                                ▼
                                                          Upload Bukti (status: resolved)
                                                                │
                                                                ▼
                                       ┌─────────┐
                                       │  Admin  │ ─────► Verify & Close (status: closed)
                                       └─────────┘
```

### User Flow Detail

#### 🙋 Customer Flow
1. **Register/Login** → Masuk sebagai customer (default role)
2. **Dashboard** → Lihat statistik tickets sendiri (total, open, in progress, resolved)
3. **Buat Ticket Baru** →
   - Isi judul & deskripsi masalah
   - Pilih kategori (dropdown)
   - Set prioritas (Low/Medium/High)
   - Upload foto bukti (optional)
4. **Lihat Daftar Tickets** → Hanya tickets yang dibuat sendiri
5. **Detail Ticket** →
   - Monitor status real-time
   - Lihat agent yang ditugaskan
   - Baca & balas comment dari agent
   - Download file attachments
6. **Edit/Hapus Ticket** → Hanya jika status masih 'open'

#### 🔧 Agent Flow
1. **Login** → Masuk sebagai agent
2. **Dashboard** → Statistik tickets yang di-assign ke agent ini
3. **Lihat Tickets Assigned** → Hanya tickets yang di-assign oleh admin
4. **Detail Ticket** →
   - Update status: assigned → in_progress → resolved
   - Tambah comment (update progress)
   - Upload bukti saat set status 'resolved'
5. **Wajib Upload Evidence** → Saat mark ticket sebagai 'resolved'

#### 👑 Admin Flow
1. **Login** → Masuk sebagai admin
2. **Dashboard** → Statistik SEMUA tickets + warning unassigned tickets
3. **Kelola Tickets** →
   - Lihat semua tickets dari semua customer
   - Assign tickets ke agent
   - Close tickets yang sudah resolved
4. **Kelola Users** → CRUD users (buat agent/customer baru, edit role, hapus)
5. **Kelola Categories** → CRUD categories untuk klasifikasi tickets

---

## 🛣️ API Routes

### Public Routes
```
GET  /                  → Landing page
GET  /login             → Login form
POST /login             → Process login
GET  /register          → Register form
POST /register          → Process register
```

### Authenticated Routes
```
GET  /dashboard         → Dashboard (role-specific stats)
GET  /profile           → User profile
```

### Ticket Routes (Auth + Role)
```
GET    /tickets                  → List tickets (filtered by role)
GET    /tickets/create           → Form buat ticket (Customer, Admin)
POST   /tickets                  → Store ticket baru
GET    /tickets/{id}             → Detail ticket
GET    /tickets/{id}/edit        → Form edit ticket (Owner, Admin, status=open)
PUT    /tickets/{id}             → Update ticket
DELETE /tickets/{id}             → Delete ticket (Owner, Admin, status=open)

POST   /tickets/{id}/assign      → Assign ke agent (Admin only)
POST   /tickets/{id}/status      → Update status (Agent, Admin)
POST   /tickets/{id}/comments    → Add comment (Owner, Agent, Admin)

GET    /tickets/{id}/attachments/download/{path}  → Download file (Signed URL)
```

### Admin Routes
```
GET    /admin/users              → List all users
GET    /admin/users/create       → Form create user
POST   /admin/users              → Store user
GET    /admin/users/{id}/edit    → Form edit user
PUT    /admin/users/{id}         → Update user
DELETE /admin/users/{id}         → Delete user

GET    /admin/categories         → List categories
GET    /admin/categories/create  → Form create category
POST   /admin/categories         → Store category
GET    /admin/categories/{id}/edit → Form edit category
PUT    /admin/categories/{id}    → Update category
DELETE /admin/categories/{id}    → Delete category
```

---

## 🐛 Troubleshooting

### Error: "Firebase credentials not found"
**Solusi:**
1. Pastikan file `storage/app/firebase/service-account.json` ada
2. Cek `.env` → `FIREBASE_CREDENTIALS` path sudah benar
3. Jalankan: `php artisan config:clear`

### Error: "Firestore connection timeout"
**Solusi:**
1. Cek internet connection
2. Pastikan Firestore sudah enabled di Firebase Console
3. Cek firewall tidak block Google Cloud API
4. Verifikasi `FIREBASE_PROJECT_ID` di `.env` benar

### Error: "Permission denied" saat upload file
**Solusi:**
1. Cek Firebase Storage rules:
```javascript
rules_version = '2';
service firebase.storage {
  match /b/{bucket}/o {
    match /{allPaths=**} {
      allow read, write: if request.auth != null;
    }
  }
}
```
2. Pastikan `FIREBASE_STORAGE_BUCKET` di `.env` benar

### Agent tidak lihat tickets
**Solusi:**
- Agent hanya lihat tickets yang DI-ASSIGN oleh admin
- Pastikan admin sudah assign ticket via "Assign Agent" button
- Cek field `agent_id` di database sudah terisi

### Customer bisa edit ticket orang lain
**Solusi:**
- Seharusnya tidak bisa, ada permission check di `TicketController@edit`
- Clear cache: `php artisan route:clear && php artisan cache:clear`
- Pastikan middleware `auth` dan role check berjalan

---

## 📚 Dokumentasi Lengkap

Untuk penjelasan lebih detail tentang alur program dan arsitektur, baca:

📄 **[PENJELASAN_ALUR.md](PENJELASAN_ALUR.md)** - Dokumentasi lengkap dengan:
- Konsep dasar sistem
- Alur step-by-step setiap role
- Struktur database detail
- Code flow & business rules
- Tips presentasi UAS
- FAQ & troubleshooting

---

## 🤝 Contributing

Project ini dibuat untuk keperluan UAS. Jika ingin contribute:

1. Fork repository
2. Buat feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

---

## 📝 License

Project ini dibuat untuk keperluan **Ujian Akhir Semester (UAS)** dan bersifat open source untuk pembelajaran.

---

## 👨‍💻 Author

**Ferdi Ndi**
- GitHub: [@ferdindi2314](https://github.com/ferdindi2314)
- Repository: [Cloud-Customer-Service](https://github.com/ferdindi2314/Cloud-Customer-Service)

---

## 🙏 Acknowledgments

- Laravel Team - Framework yang powerful & elegant
- Firebase - Real-time database & cloud storage
- Bootstrap Team - UI framework yang responsive
- Laravel Breeze - Authentication starter kit
- Komunitas Laravel Indonesia

---

## 📞 Support

Jika ada pertanyaan atau issue:
1. Buka [GitHub Issues](https://github.com/ferdindi2314/Cloud-Customer-Service/issues)
2. Baca dokumentasi di [PENJELASAN_ALUR.md](PENJELASAN_ALUR.md)
3. Cek [Troubleshooting](#-troubleshooting) section di atas

---
