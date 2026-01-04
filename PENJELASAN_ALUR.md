# 📖 PENJELASAN ALUR PROGRAM - Cloud Customer Support

## 🎯 Konsep Dasar (Analogi Mudah Dipahami)

Bayangkan sistem ini seperti **Kantor Layanan Pelanggan di Perusahaan**:

-   **Customer** = Pelanggan yang punya masalah/pertanyaan
-   **Agent** = Staff customer service yang menangani masalah
-   **Admin** = Manager yang mengatur pembagian tugas

**Contoh Kasus Nyata:**

1. Customer: "Produk saya rusak, minta bantuan"
2. Admin: "Baik, saya tugaskan ke Agent Budi yang ahli di produk ini"
3. Agent Budi: "Saya sedang cek masalahnya... sudah saya perbaiki!"
4. Admin: "OK, kasusnya sudah selesai, saya tutup tiketnya"

---

## 🔄 ALUR LENGKAP SISTEM (Step by Step)

### **TAHAP 1: Customer Buat Ticket (Laporkan Masalah)**

```
Customer Login → Dashboard → Klik "Buat Ticket Baru" → Isi Form → Submit
```

**File Terkait:**

-   📄 `resources/views/tickets/create.blade.php` - Form UI untuk buat ticket
-   🎮 `TicketController@store` - Method untuk proses simpan ticket
-   ⚙️ `TicketService@createTicket` - Logic simpan ke Firestore + Laravel DB
-   🗃️ `app/Models/Ticket.php` - Model data ticket

**Form yang Harus Diisi:**

-   Judul (Title): Ringkasan masalah
-   Deskripsi (Description): Penjelasan detail masalah
-   Kategori (Category): Jenis masalah (dropdown)
-   Prioritas (Priority): Low/Medium/High
-   File Lampiran (Attachments): Foto/dokumen bukti (opsional)

**Yang Terjadi di Backend (di balik layar):**

1. **Validasi Input** - Cek apakah semua field wajib sudah diisi
2. **Simpan ke Firestore** - Data disimpan ke Firebase (cloud database) untuk real-time sync
3. **Sync ke Laravel Database** - Data juga disimpan ke SQLite local untuk query cepat
4. **Upload File** - Jika ada attachment, upload ke Firebase Storage
5. **Auto-assign** - Status otomatis jadi 'open', customer_id diisi otomatis
6. **Redirect** - User diarahkan ke halaman detail ticket yang baru dibuat

**Code Flow:**

```php
// Di TicketController@store
$data = $request->only(['title', 'description', 'priority']);
$data['customer_id'] = Auth::id(); // Auto-isi customer_id
$data['category_id'] = $request->category_id;

// Simpan ke Firestore + DB
$ticketId = $this->ticketService->createTicket($data);

// Upload attachments (jika ada)
if ($request->hasFile('attachments')) {
    $attachments = $this->ticketService->uploadAttachments($files, $ticketId);
}
```

---

### **TAHAP 2: Admin Lihat & Assign ke Agent**

```
Admin Login → Dashboard → Lihat Daftar Tickets → Pilih Ticket → Assign ke Agent
```

**File Terkait:**

-   📄 `resources/views/dashboard.blade.php` - Dashboard dengan statistik
-   📄 `resources/views/tickets/index.blade.php` - List semua tickets
-   📄 `resources/views/tickets/show.blade.php` - Detail ticket + form assign
-   🎮 `TicketController@index` - Method tampilkan daftar tickets
-   🎮 `TicketController@assignAgent` - Method untuk assign ticket ke agent
-   🗺️ `routes/web.php` - Route untuk hitung statistik dashboard

**Statistik yang Dilihat Admin:**

-   📊 Total Tickets
-   🆕 Open (Belum ditangani)
-   👤 Assigned (Sudah ditugaskan)
-   ⚙️ In Progress (Sedang dikerjakan)
-   ✅ Resolved (Sudah selesai)
-   🔒 Closed (Sudah ditutup)
-   ❗ Unassigned (Belum ada yang handle)

**Yang Terjadi di Backend:**

1. **Filter Tickets** - Admin lihat SEMUA tickets (tidak ada filter)
2. **Pilih Ticket** - Admin klik ticket tertentu untuk lihat detail
3. **Pilih Agent** - Admin pilih agent dari dropdown (hanya user dengan role='agent')
4. **Update Database** - Field `agent_id` diisi dengan ID agent yang dipilih
5. **Update Status** - Status berubah dari 'open' menjadi 'assigned'
6. **Notifikasi** - Agent bisa langsung lihat ticket di dashboard mereka

**Code Flow:**

```php
// Di TicketController@assignAgent
$agent = User::find($request->agent_id);

// Update ticket: set agent_id & status
$this->ticketService->updateTicket($id, [
    'agent_id' => $agent->id,
    'status' => 'assigned',
]);
```

---

### **TAHAP 3: Agent Kerjakan Ticket**

```
Agent Login → Dashboard → Lihat "Tickets Assigned ke Saya" → Buka Ticket → Update Status → Tambah Komentar
```

**File Terkait:**

-   📄 `resources/views/tickets/show.blade.php` - Detail ticket + form comment
-   🎮 `TicketController@updateStatus` - Method untuk update status
-   🎮 `TicketCommentController@store` - Method untuk tambah comment
-   ⚙️ `TicketService@addComment` - Logic simpan comment

**Agent Bisa Apa Saja:**

1. **Lihat Detail Ticket** - Baca deskripsi masalah dari customer
2. **Update Status** - Ubah dari 'assigned' → 'in_progress' → 'resolved'
3. **Tambah Komentar** - Update progress: "Sedang diperbaiki, butuh sparepart X"
4. **Upload Bukti** - Attach foto hasil perbaikan saat set status 'resolved'
5. **Komunikasi dengan Customer** - Balas pertanyaan customer via comment

**Lifecycle Status oleh Agent:**

```
assigned (Admin assign) → in_progress (Agent mulai kerja) → resolved (Agent selesai)
```

**Yang Terjadi di Backend:**

1. **Cek Permission** - Pastikan agent hanya bisa update tickets yang di-assign ke mereka
2. **Validasi Status** - Agent tidak bisa langsung 'close' ticket (hanya bisa 'resolved')
3. **Simpan Comment** - Setiap comment disimpan ke `ticket_comments` table
4. **Upload Evidence** - Saat set 'resolved', wajib upload bukti foto
5. **Real-time Sync** - Semua update langsung terlihat customer (via Firestore)


**Code Flow:**

```php
// Di TicketController@updateStatus
// Agent set status jadi 'resolved' dengan evidence
if ($request->status === 'resolved') {
    // Wajib upload bukti foto
    $files = $request->file('evidence');
    $attachments = $this->ticketService->uploadAttachments($files, $id);
    
    // Buat comment otomatis dengan bukti
    TicketComment::create([
        'ticket_id' => $ticketId,
        'user_id' => Auth::id(),
        'comment' => $request->evidence_note,
        'attachments' => $attachments,
    ]);
}

$this->ticketService->updateTicket($id, ['status' => $request->status]);
```

---

### **TAHAP 4: Customer Pantau Progress**

```
Customer Login → Dashboard → "Tickets Saya" → Klik Ticket → Lihat Status & Komentar
```

**Yang Customer Bisa Lihat:**

-   ✅ Status terkini ticket (Open/Assigned/In Progress/Resolved/Closed)
-   💬 Semua komentar dari Agent (dengan timestamp)
-   📎 Foto bukti perbaikan dari Agent
-   📊 Timeline progress ticket (kapan dibuat, di-assign, dikerjakan, selesai)

**Yang Customer Bisa Lakukan:**

-   💬 Balas komentar Agent (jika ada pertanyaan)
-   📝 Edit ticket (hanya jika status masih 'open')
-   🗑️ Hapus ticket (hanya jika status masih 'open')
-   👀 Monitor real-time updates (via Firestore auto-sync)

**Aturan Edit/Hapus Ticket:**

-   ❌ **Tidak Boleh** edit/hapus jika status sudah 'assigned' atau lebih lanjut
-   ✅ **Boleh** edit/hapus jika status masih 'open' (belum ditangani)
-   💡 **Alasan**: Setelah di-assign, ticket sudah masuk workflow, tidak boleh diubah sepihak

---

### **TAHAP 5: Admin Tutup Ticket (Close)**

```
Admin → Lihat Ticket dengan Status 'Resolved' → Verifikasi → Klik "Close Ticket"
```

**Kapan Ticket Bisa Ditutup:**

-   ✅ Status harus sudah 'resolved' (Agent sudah selesai)
-   ✅ Admin verifikasi bahwa masalah benar-benar selesai
-   ✅ Customer tidak ada komplain lagi

**Yang Terjadi di Backend:**

1. **Validasi** - Cek apakah status = 'resolved'
2. **Update Status** - Ubah status jadi 'closed'
3. **Archive** - Ticket masuk ke arsip (tidak muncul di dashboard agent)
4. **Audit Trail** - History tetap tersimpan untuk laporan

**Code Flow:**

```php
// Di TicketController@updateStatus (Admin only)
if ($user->role === 'admin' && $request->status === 'closed') {
    // Hanya bisa close jika status saat ini = 'resolved'
    if ($currentStatus !== 'resolved') {
        return back()->with('error', 'Hanya ticket Resolved yang bisa ditutup');
    }
    
    $this->ticketService->updateTicket($id, ['status' => 'closed']);
}
```

---

## 🗂️ STRUKTUR DATABASE (Detail)

### **1. Laravel Database (SQLite) - Tabel Utama**

#### **Table: users**

```sql
id              INT PRIMARY KEY
name            VARCHAR(255)     -- Nama lengkap
email           VARCHAR(255)     -- Email (unique)
password        VARCHAR(255)     -- Password (hashed)
role            ENUM             -- 'admin', 'agent', 'customer'
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

**Seed Data Default:**

-   Admin: admin@example.com / password
-   Agent: agent1@example.com / password
-   Customer: customer1@example.com / password

#### **Table: categories**

```sql
id              INT PRIMARY KEY
name            VARCHAR(255)     -- Nama kategori (contoh: "Perbaikan Mesin")
slug            VARCHAR(255)     -- URL-friendly (contoh: "perbaikan-mesin")
description     TEXT             -- Deskripsi kategori
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

**Seed Data Default:**

-   Perbaikan Mesin
-   Quality Control
-   Safety Issue
-   Request Sparepart
-   Lain-lain

#### **Table: tickets**

```sql
id              INT PRIMARY KEY
firebase_id     VARCHAR(255)     -- ID di Firestore (untuk sync)
title           VARCHAR(255)     -- Judul ticket
description     TEXT             -- Detail masalah
customer_id     INT              -- Foreign key ke users (pembuat ticket)
agent_id        INT NULL         -- Foreign key ke users (agent yang ditugaskan)
category_id     INT              -- Foreign key ke categories
status          ENUM             -- 'open', 'assigned', 'in_progress', 'resolved', 'closed'
priority        ENUM             -- 'low', 'medium', 'high'
attachments     JSON NULL        -- Array file attachments
created_at      TIMESTAMP
updated_at      TIMESTAMP
deleted_at      TIMESTAMP NULL   -- Soft delete
```

**Status Lifecycle:**

```
open → assigned → in_progress → resolved → closed
```

#### **Table: ticket_comments**

```sql
id              INT PRIMARY KEY
firebase_id     VARCHAR(255)     -- ID di Firestore
ticket_id       INT              -- Foreign key ke tickets
user_id         INT              -- Foreign key ke users (yang comment)
comment         TEXT             -- Isi komentar
attachments     JSON NULL        -- Array file attachments
is_internal     BOOLEAN          -- True = hanya admin/agent yang lihat
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

---

### **2. Firestore (Firebase) - Koleksi Cloud**

#### **Collection: tickets/{ticketId}**

```javascript
{
  title: "Mesin Rusak",
  description: "Mesin produksi line A tidak mau menyala",
  customer_id: "1",              // String ID
  customer_name: "John Doe",     // Denormalisasi untuk performa
  agent_id: "2",                 // String ID
  agent_name: "Agent Budi",      // Denormalisasi
  category: "Perbaikan Mesin",
  category_id: "1",
  status: "in_progress",
  priority: "high",
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

#### **SubCollection: tickets/{ticketId}/comments/{commentId}**

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

**Keuntungan Firestore:**

-   ⚡ Real-time sync - Update langsung terlihat semua user
-   🌐 Cloud-based - Akses dari mana saja
-   📱 Scalable - Bisa handle banyak concurrent users
-   🔄 Auto-backup - Data aman di Google Cloud

---

## 📊 DASHBOARD STATISTICS (Cara Hitung)

### **Admin Dashboard**

```php
// Di routes/web.php - Dashboard route
$stats['total'] = Ticket::count();                          // Total semua tickets
$stats['open'] = Ticket::where('status', 'open')->count();  // Ticket baru
$stats['assigned'] = Ticket::where('status', 'assigned')->count();
$stats['in_progress'] = Ticket::where('status', 'in_progress')->count();
$stats['resolved'] = Ticket::where('status', 'resolved')->count();
$stats['closed'] = Ticket::where('status', 'closed')->count();
$stats['unassigned'] = Ticket::whereNull('agent_id')->count(); // Belum ada yang handle
```

### **Agent Dashboard**

```php
// Agent HANYA lihat tickets yang di-assign ke dia
$stats['total'] = Ticket::where('agent_id', $userId)
                        ->whereNotIn('status', ['resolved', 'closed'])
                        ->count();
                        
$stats['assigned'] = Ticket::where('agent_id', $userId)
                           ->where('status', 'assigned')
                           ->count();
                           
$stats['in_progress'] = Ticket::where('agent_id', $userId)
                              ->where('status', 'in_progress')
                              ->count();
```

### **Customer Dashboard**

```php
// Customer hanya lihat tickets MILIK dia
$stats['total'] = Ticket::where('customer_id', $userId)->count();
$stats['open'] = Ticket::where('customer_id', $userId)
                       ->where('status', 'open')
                       ->count();
```

---

## 🎨 STATUS & PRIORITY BADGES (UI/UX)

### **Status Tickets:**

| Status       | Badge Color | Icon | Arti                                   |
| ------------ | ----------- | ---- | -------------------------------------- |
| Open         | Biru        | 🆕   | Ticket baru, belum ada yang tangani    |
| Assigned     | Indigo      | 👤   | Sudah ditugaskan ke agent tertentu     |
| In Progress  | Kuning      | ⚙️   | Sedang dikerjakan oleh agent           |
| Resolved     | Hijau       | ✅   | Sudah selesai, menunggu admin verify   |
| Closed       | Abu-abu     | 🔒   | Selesai & ditutup, masuk arsip         |

### **Priority Levels:**

| Priority | Badge Color | Icon | Kapan Digunakan                        |
| -------- | ----------- | ---- | -------------------------------------- |
| Low      | Abu-abu     | ⬇️   | Tidak urgent, bisa ditangani nanti     |
| Medium   | Biru        | 🔵   | Normal, tangani dalam 1-2 hari         |
| High     | Orange      | 🟠   | Penting, butuh perhatian segera        |

**Badge Implementation (di Model):**

```php
// Di app/Models/Ticket.php
public function getStatusBadgeClass(): string
{
    return match ($this->status) {
        'open' => 'bg-blue-100 text-blue-800',
        'assigned' => 'bg-indigo-100 text-indigo-800',
        'in_progress' => 'bg-yellow-100 text-yellow-800',
        'resolved' => 'bg-green-100 text-green-800',
        'closed' => 'bg-gray-100 text-gray-800',
        default => 'bg-gray-100 text-gray-800',
    };
}
```

---

## 🔐 ROLE & PERMISSIONS (Akses Control)

### **Tabel Permission Matrix:**

| Fitur / Aksi                  | Customer | Agent     | Admin     |
| ----------------------------- | -------- | --------- | --------- |
| **TICKETS**                   |          |           |           |
| Lihat semua tickets           | ❌       | ❌        | ✅        |
| Lihat tickets sendiri         | ✅       | ✅ (assigned) | ✅    |
| Buat ticket baru              | ✅       | ❌        | ✅        |
| Edit ticket sendiri (open)    | ✅       | ❌        | ✅        |
| Hapus ticket sendiri (open)   | ✅       | ❌        | ✅        |
| Assign ticket ke agent        | ❌       | ❌        | ✅        |
| Update status (in_progress)   | ❌       | ✅        | ❌        |
| Update status (resolved)      | ❌       | ✅        | ❌        |
| Update status (closed)        | ❌       | ❌        | ✅        |
| **COMMENTS**                  |          |           |           |
| Comment di ticket sendiri     | ✅       | ✅        | ✅        |
| Comment di ticket lain        | ❌       | ❌ (only assigned) | ✅ |
| Internal comment              | ❌       | ✅        | ✅        |
| **ADMIN PANEL**               |          |           |           |
| Kelola users (CRUD)           | ❌       | ❌        | ✅        |
| Kelola categories (CRUD)      | ❌       | ❌        | ✅        |
| Lihat statistik global        | ❌       | ❌        | ✅        |

### **Implementasi Permission:**

**1. Middleware Role (di routes/web.php):**

```php
// Hanya admin yang bisa akses
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/users', [UserController::class, 'index']);
});

// Admin atau agent yang bisa akses
Route::post('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])
    ->middleware('role:admin,agent');
```

**2. Permission Check di Controller:**

```php
// Di TicketController@edit
// Customer hanya bisa edit ticket mereka sendiri
if ($ticket['customer_id'] !== Auth::id()) {
    return back()->with('error', 'Tidak bisa edit ticket orang lain');
}

// Hanya ticket status 'open' yang bisa diedit
if ($ticket['status'] !== 'open') {
    return back()->with('error', 'Ticket yang sudah diproses tidak bisa diedit');
}
```

---

## 🚀 TEKNOLOGI & ARSITEKTUR SISTEM

### **Tech Stack:**

| Layer         | Teknologi              | Fungsi                                 |
| ------------- | ---------------------- | -------------------------------------- |
| Frontend      | Blade Templates        | View engine Laravel                    |
| UI Framework  | Bootstrap 5            | Responsive design & components         |
| Backend       | Laravel 11 (PHP 8.3)   | Framework utama aplikasi               |
| Database      | SQLite                 | Local database untuk fast query        |
| Cloud DB      | Firebase Firestore     | Real-time cloud database               |
| File Storage  | Firebase Storage       | Cloud storage untuk attachments        |
| Authentication| Laravel Breeze         | Login, register, password reset        |
| Authorization | Custom RoleMiddleware  | Role-based access control              |

### **Arsitektur Hybrid Database:**

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

**Kenapa Hybrid?**

1. **Firestore (Cloud):**
   - ✅ Real-time sync antar user
   - ✅ Cloud-based, akses dari mana saja
   - ✅ Auto-scaling
   - ❌ Tidak optimal untuk query kompleks

2. **SQLite (Local):**
   - ✅ Query super cepat (filtering, sorting, aggregation)
   - ✅ Tidak butuh koneksi internet untuk query
   - ✅ Mudah untuk development
   - ❌ Tidak real-time

**Strategi Sync:**
- Setiap perubahan di Firestore → auto sync ke SQLite
- Setiap query list/filter → pakai SQLite
- Real-time updates → pakai Firestore listeners

---

## 📝 CARA JELASIN KE DOSEN (Tips Presentasi)

### **1. Konsep Sederhana (2 menit):**

> "Pak/Bu, sistem ini seperti aplikasi customer service di perusahaan.
> Customer punya masalah → bikin ticket → Admin tugaskan ke Agent → Agent selesaikan.
> Semua progress bisa dipantau real-time oleh semua pihak."

### **2. Keunggulan Teknis (3 menit):**

**a) Hybrid Database:**
> "Saya pakai 2 database sekaligus:
> - Firestore untuk real-time sync (cloud)
> - SQLite untuk query cepat (local)
> Jadi dapat keuntungan dari kedua duanya."

**b) Role-Based Access:**
> "Ada 3 role dengan permission berbeda:
> - Customer hanya lihat ticket mereka
> - Agent lihat tickets yang di-assign
> - Admin lihat semua & bisa manage system
> Ini diimplementasi pakai Middleware & Permission check di Controller."

**c) Clean Architecture:**
> "Saya pisahkan logic ke layer berbeda:
> - Controller: Handle HTTP request/response
> - Service: Business logic (TicketService)
> - Model: Data structure & relationship
> Ini buat code lebih maintainable dan testable."

### **3. Demo Flow (5 menit):**

**Step 1:** Login sebagai Customer → Buat ticket baru → Upload foto
**Step 2:** Login sebagai Admin → Lihat dashboard stats → Assign ticket ke Agent
**Step 3:** Login sebagai Agent → Update status → Upload bukti selesai
**Step 4:** Login kembali sebagai Customer → Lihat progress real-time
**Step 5:** Login sebagai Admin → Close ticket yang sudah resolved

### **4. Poin Penting yang Harus Dijelaskan:**

✅ **Database Sync** - Firestore ↔ SQLite otomatis
✅ **Real-time Updates** - Pakai Firestore listeners
✅ **File Upload** - Support foto/dokumen di Firebase Storage
✅ **Role-Based Access** - Middleware custom untuk authorization
✅ **Soft Delete** - Data tidak benar-benar dihapus (audit trail)
✅ **Code Comments** - Semua function ada penjelasan lengkap
✅ **Business Rules** - Status lifecycle, permission checks, validation

---

## 🎓 POIN PENTING UNTUK UAS

### **Fitur Utama yang Diimplementasikan:**

1. ✅ **Authentication & Authorization**
   - Login/Register dengan Laravel Breeze
   - Role-based access control (3 roles)
   - Middleware protection

2. ✅ **CRUD Tickets**
   - Create (dengan validasi & file upload)
   - Read (list + detail, filtered by role)
   - Update (dengan business rules)
   - Delete (soft delete, dengan permission)

3. ✅ **Real-time Sync**
   - Firebase Firestore untuk cloud database
   - Auto-sync ke local SQLite
   - Instant updates untuk semua user

4. ✅ **File Management**
   - Upload ke Firebase Storage
   - Download dengan signed URL (keamanan)
   - Support multiple files per ticket

5. ✅ **Comment System**
   - 2-way communication Customer ↔ Agent
   - Support file attachments di comment
   - Internal comments untuk admin/agent only

6. ✅ **Dashboard & Statistics**
   - Card statistics per role
   - Filter tickets by status
   - Visual badges untuk status & priority

7. ✅ **Business Logic**
   - Status lifecycle (open → assigned → in_progress → resolved → closed)
   - Permission checks di setiap action
   - Validation rules untuk data integrity

### **Clean Code Practices:**

✅ **Komentar di setiap file penting** (Models, Controllers, Routes)
✅ **Function docblocks** dengan penjelasan parameter & return type
✅ **Meaningful variable names** (tidak pakai singkatan aneh)
✅ **Separation of Concerns** (Controller → Service → Model)
✅ **DRY Principle** (Don't Repeat Yourself) - pakai Service layer
✅ **Error Handling** dengan try-catch & validation

---

## 📧 LOGIN CREDENTIALS (Untuk Testing)

| Role     | Email                 | Password | Akses                              |
| -------- | --------------------- | -------- | ---------------------------------- |
| Admin    | admin@example.com     | password | Semua fitur, manage users & categories |
| Agent 1  | agent1@example.com    | password | Tickets yang di-assign, update status  |
| Agent 2  | agent2@example.com    | password | Tickets yang di-assign, update status  |
| Customer | customer1@example.com | password | Buat ticket baru, lihat tickets sendiri |

**Cara Login:**
1. Buka browser → http://localhost:8000
2. Klik "Masuk" di landing page
3. Masukkan email & password sesuai tabel di atas
4. Redirect ke dashboard sesuai role

---

## 🛠️ CARA MENJALANKAN APLIKASI

### **Requirement:**
- PHP 8.3+
- Composer
- Node.js & NPM
- Firebase Account (untuk Firestore & Storage)

### **Setup Steps:**

```bash
# 1. Clone repository
git clone https://github.com/ferdindi2314/Cloud-Customer-Service.git
cd Cloud-Customer-Service

# 2. Install dependencies
composer install
npm install

# 3. Copy environment file
cp .env.example .env

# 4. Generate app key
php artisan key:generate

# 5. Setup database
php artisan migrate:fresh --seed

# 6. Build assets
npm run build

# 7. Run server
php artisan serve
```

### **Firebase Setup:**
1. Buat project di Firebase Console
2. Enable Firestore & Storage
3. Download service account JSON
4. Simpan di `storage/app/firebase/`
5. Update `.env` dengan Firebase credentials

---

## 📊 STRUKTUR FOLDER PENTING

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── TicketController.php          # CRUD tickets
│   │   ├── TicketCommentController.php   # Handle comments
│   │   └── Admin/
│   │       ├── UserController.php        # Manage users
│   │       └── CategoryController.php    # Manage categories
│   └── Middleware/
│       └── RoleMiddleware.php            # Authorization
├── Models/
│   ├── User.php                          # User model (3 roles)
│   ├── Ticket.php                        # Ticket model
│   ├── TicketComment.php                 # Comment model
│   └── Category.php                      # Category model
└── Services/
    └── Firebase/
        ├── FirebaseFactory.php           # Firebase connection
        └── TicketService.php             # Business logic

resources/
└── views/
    ├── dashboard.blade.php               # Dashboard (stats cards)
    ├── tickets/
    │   ├── index.blade.php               # List tickets
    │   ├── show.blade.php                # Detail + comments
    │   ├── create.blade.php              # Form buat ticket
    │   └── edit.blade.php                # Form edit ticket
    └── admin/
        ├── users/                        # User management views
        └── categories/                   # Category management views

routes/
└── web.php                               # Semua route definitions

database/
├── migrations/                           # Database schema
└── seeders/
    ├── UserSeeder.php                    # Seed default users
    └── CategorySeeder.php                # Seed default categories
```

---

## 💡 TROUBLESHOOTING & FAQ

### **Q: Firestore connection error?**
**A:** Cek file `storage/app/firebase/credentials.json` sudah benar & Firebase project sudah enable Firestore.

### **Q: Agent tidak lihat tickets?**
**A:** Pastikan admin sudah assign ticket ke agent tersebut. Agent hanya lihat tickets yang di-assign.

### **Q: Error saat upload file?**
**A:** Cek Firebase Storage sudah enabled & `.env` sudah set `FIREBASE_STORAGE_BUCKET`.

### **Q: Customer bisa edit ticket orang lain?**
**A:** Tidak bisa. Ada permission check di `TicketController@edit` yang cek `customer_id`.

---

**🎉 Semoga Sukses UAS-nya! Good Luck! 🎉**

**Dibuat dengan ❤️ menggunakan Laravel 11 + Firebase**
