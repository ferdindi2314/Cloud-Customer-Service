# Electronic Service - Ticketing System (Laravel 11 + Firebase)

Project ini adalah sistem **Electronic Service** untuk manufacturer: Sistem ticketing berbasis cloud untuk mencatat keluhan elektronik, permintaan layanan, dan tracking perbaikan produk secara online dengan real-time sync.

**Tech Stack:**

-   Backend: **Laravel 11** (Auth + Role-Based Access)
-   Database: **SQLite** (Users & Categories) + **Firebase Firestore** (Tickets & Comments)
-   Storage: **Firebase Storage** (File Attachments)
-   Frontend: **Bootstrap 5.3.3** + **AdminLTE Sidebar Layout**
-   Alerts: **SweetAlert2** (Modern confirmation dialogs)

## 1) Prasyarat

-   PHP 8.3+ (atau PHP 8.2 minimal)
-   Composer
-   Node.js & NPM (untuk Vite build assets)
-   Akun Firebase + Service Account JSON
-   Database SQLite (sudah include) atau MySQL/PostgreSQL

## 2) Arsitektur & Role Management

### Hybrid Database Architecture

Project ini menggunakan **dual database** untuk optimasi:

1. **Laravel Database (SQLite)**:
    - Tabel `users` (authentication, role)
    - Tabel `categories` (kategori produk elektronik)
2. **Firebase Firestore**:

    - Collection `tickets` (tiket layanan real-time)
    - Subcollection `tickets/{id}/comments` (komunikasi customer-agent)

3. **Firebase Storage**:
    - Bucket untuk lampiran (foto produk rusak, invoice, dll)

### Role-Based Access Control (3 Roles)

| Role                 | Akses                                                | Deskripsi                                     |
| -------------------- | ---------------------------------------------------- | --------------------------------------------- |
| **Customer**         | Buat tiket, lihat tiket sendiri, tambah komentar     | User biasa yang melaporkan keluhan elektronik |
| **Operator (Agent)** | Lihat tiket assigned, update status, balas komentar  | Teknisi yang mengerjakan perbaikan            |
| **Admin**            | Full access, assign operator, kelola user & kategori | Manager yang mengatur workflow                |

## 3) Setup Firebase (Firestore + Storage)

### A. Buat Project Firebase

1. Firebase Console → Add project.
2. Aktifkan:
    - **Cloud Firestore** (Native mode)
    - **Storage**

### B. Buat Service Account

1. Firebase Console → Project settings → Service accounts.
2. Generate **Private key** (JSON) lalu simpan di project:
    - `storage/app/firebase/service-account.json`

### C. Konfigurasi `.env`

Tambahkan variabel berikut:

```env
FIREBASE_CREDENTIALS=storage/app/firebase/service-account.json
FIREBASE_PROJECT_ID=your-project-id
FIREBASE_STORAGE_BUCKET=your-project-id.appspot.com
FIREBASE_DATABASE_URL=
FIREBASE_FIRESTORE_ENABLED=true
```

**Catatan Keamanan**: File `service-account.json` sudah masuk `.gitignore` - jangan commit ke repository!

## 4) Setup Database & Migration

```bash
# Install dependencies
composer install
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations (buat tabel users & categories)
php artisan migrate

# (Opsional) Seed data kategori
php artisan db:seed --class=CategorySeeder
```

Database menggunakan **SQLite** (file: `database/database.sqlite`).

### Tabel Penting

**users** - Autentikasi & Role

-   `id`, `name`, `email`, `password`
-   `role` (admin|agent|customer) - default: customer
-   Middleware `RoleMiddleware` proteksi route per role

**categories** - Kategori Produk Elektronik

-   `id`, `name`, `slug`, `description`
-   Contoh: TV, Kulkas, AC, Mesin Cuci, dll.

## 5) Struktur Data Firestore

### Collection: `tickets`

Setiap document ticket berisi:

```javascript
{
  "title": "TV Samsung Tidak Menyala",
  "description": "Sudah 3 hari TV tidak bisa dinyalakan...",
  "category_id": 2, // ID dari tabel categories (Laravel DB)
  "priority": "high", // low|medium|high
  "status": "open", // open|in_progress|resolved|closed
  "customer_id": 5, // ID user pembuat tiket
  "customer_name": "Budi Santoso",
  "assigned_agent_id": null, // ID operator yang ditugaskan
  "assigned_agent_name": null,
  "attachments": [ // Array lampiran gambar/dokumen
    {
      "name": "foto-tv-rusak.jpg",
      "path": "tickets/abc123/foto-tv-rusak.jpg",
      "content_type": "image/jpeg",
      "size": 245678
    }
  ],
  "created_at": Timestamp,
  "updated_at": Timestamp
}
```

### Subcollection: `tickets/{ticketId}/comments`

Setiap comment document:

```javascript
{
  "user_id": 3,
  "user_name": "Teknisi Ahmad",
  "user_role": "agent",
  "message": "Sedang diperiksa, part spare siap hari Senin",
  "created_at": Timestamp,
  "updated_at": Timestamp
}
```

## 6) Alur Kerja Website (User Flow)

### 🎯 CUSTOMER FLOW (User Biasa)

1. **Register/Login** → Masuk sebagai customer (role default)
2. **Dashboard** → Lihat statistik tiket pribadi (Total, Open, Progress, Selesai)
3. **Buat Tiket** →
    - Isi judul, deskripsi masalah
    - Pilih kategori produk (TV, AC, Kulkas, dll)
    - Set prioritas (Low/Medium/High)
    - Upload foto kerusakan (opsional)
4. **Lihat Tiket Saya** → Monitoring progress tiket yang dibuat
5. **Detail Tiket** →
    - Lihat status real-time
    - Lihat operator yang ditugaskan
    - Baca & balas komentar dari operator
    - Download lampiran
6. **Logout** → SweetAlert confirmation dialog

### 🔧 OPERATOR (AGENT) FLOW

1. **Login** → Masuk sebagai agent
2. **Dashboard** → Statistik tiket yang di-assign ke operator ini
3. **Lihat Tiket Assigned** → Hanya tiket yang ditugaskan admin
4. **Detail Tiket** →
    - Update status: Open → In Progress → Resolved
    - Tambah komentar (update progress perbaikan)
    - Lihat history komunikasi dengan customer
5. **Logout**

### 👑 ADMIN FLOW

1. **Login** → Masuk sebagai admin
2. **Dashboard** → Statistik SEMUA tiket + warning tiket unassigned
3. **Kelola Tiket** →
    - Lihat semua tiket dari semua customer
    - Assign operator ke tiket tertentu
    - Monitor progress semua tiket
4. **Kelola Admin** → CRUD admin lain
5. **Kelola Operator** → CRUD operator/agent
6. **Kelola User** → CRUD customer/user biasa
7. **Kelola Kategori** → CRUD kategori produk elektronik
8. **Logout**

### 🔄 Real-time Sync Flow

```
Customer buat tiket
  ↓
Laravel Controller → Firebase Firestore (create document)
  ↓
Admin lihat di dashboard (real-time)
  ↓
Admin assign ke Operator
  ↓
Operator update status
  ↓
Customer lihat update status (real-time)
  ↓
Customer/Operator chat via comments
  ↓
Tiket selesai → status = resolved
```

## 7) Alur Program/Coding (Untuk Dokumentasi Dosen)

### 📁 File Structure Penting

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── TicketController.php        // CRUD tiket
│   │   ├── TicketCommentController.php // Komentar
│   │   └── Admin/
│   │       ├── UserController.php      // Kelola user/role
│   │       └── CategoryController.php  // Kelola kategori
│   └── Middleware/
│       └── RoleMiddleware.php          // Proteksi route per role
│
├── Models/
│   ├── User.php                        // Model user (Laravel DB)
│   └── Category.php                    // Model kategori (Laravel DB)
│
└── Services/
    └── Firebase/
        ├── FirebaseFactory.php         // Init Firebase SDK
        └── TicketService.php           // Business logic Firestore

resources/views/
├── layouts/
│   └── sidebar.blade.php               // AdminLTE layout + SweetAlert2
├── auth/
│   ├── login.blade.php                 // Login with validation alerts
│   └── register.blade.php
├── dashboard.blade.php                 // Role-based dashboard
├── tickets/
│   ├── index.blade.php                 // Daftar tiket
│   ├── create.blade.php                // Form buat tiket
│   ├── show.blade.php                  // Detail + comments
│   └── edit.blade.php                  // Edit tiket
└── admin/
    ├── users/index.blade.php           // Kelola user (filtered by role)
    └── categories/index.blade.php      // Kelola kategori

routes/
└── web.php                             // Route definitions + middleware
```

### 🔄 Alur Coding Detail

#### 1️⃣ **Authentication & Authorization**

**File**: `routes/web.php`

```php
// Proteksi route dengan middleware
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/users', [UserController::class, 'index']);
});
```

**File**: `app/Http/Middleware/RoleMiddleware.php`

```php
// Cek role user, jika tidak sesuai → abort 403
if (!in_array($user->role, $roles)) {
    abort(403, 'Unauthorized');
}
```

**Flow**:

1. User login → Laravel Auth menyimpan session
2. User akses route → Middleware `auth` cek sudah login?
3. Middleware `role` cek role sesuai? (admin/agent/customer)
4. Jika OK → lanjut ke controller, jika tidak → 403 Forbidden

---

#### 2️⃣ **Buat Tiket (Customer)**

**File**: `app/Http/Controllers/TicketController.php` → method `store()`

**Alur**:

```
1. Form submit (POST /tickets)
   ↓
2. Validasi input (title, description, category, priority, attachments)
   ↓
3. Upload file ke Firebase Storage (jika ada attachments)
   ↓
4. Prepare data ticket:
   - customer_id: auth()->user()->id
   - customer_name: auth()->user()->name
   - status: "open" (default)
   - assigned_agent_id: null (belum ditugaskan)
   ↓
5. TicketService::createTicket($data)
   - Simpan ke Firestore collection 'tickets'
   - Return document ID
   ↓
6. Redirect ke /tickets dengan success message
```

**File**: `app/Services/Firebase/TicketService.php`

```php
public function createTicket($data) {
    $firestore = $this->factory->createFirestore()->database();
    $ref = $firestore->collection('tickets')->add($data);
    return $ref->id();
}
```

---

#### 3️⃣ **Lihat Tiket (Role-Based)**

**File**: `app/Http/Controllers/TicketController.php` → method `index()`

**Alur**:

```
1. GET /tickets
   ↓
2. Cek role user:
   - Admin: getAllTickets() → semua tiket
   - Agent: getTicketsByAgent(user->id) → tiket assigned
   - Customer: getTicketsByCustomer(user->id) → tiket milik sendiri
   ↓
3. TicketService query Firestore dengan filter
   ↓
4. Convert Firestore documents → array PHP
   ↓
5. Foreach ticket: ambil data kategori dari Laravel DB
   ↓
6. Return view dengan data tickets
```

**Code Logic**:

```php
if ($user->role === 'admin') {
    $tickets = $this->ticketService->getAllTickets();
} elseif ($user->role === 'agent') {
    $tickets = $this->ticketService->getTicketsByAgent($user->id);
} else {
    $tickets = $this->ticketService->getTicketsByCustomer($user->id);
}
```

---

#### 4️⃣ **Assign Operator (Admin Only)**

**File**: `app/Http/Controllers/TicketController.php` → method `assignAgent()`

**Alur**:

```
1. Admin klik "Assign" di detail tiket
   ↓
2. POST /tickets/{id}/assign
   ↓
3. Validasi: agent_id harus user dengan role 'agent'
   ↓
4. Update Firestore document:
   - assigned_agent_id: agent_id
   - assigned_agent_name: agent->name
   ↓
5. Redirect dengan success message
```

**Middleware Check**:

```php
Route::post('/tickets/{ticket}/assign', ...)
    ->middleware('role:admin'); // Hanya admin
```

---

#### 5️⃣ **Update Status (Agent/Admin)**

**File**: `app/Http/Controllers/TicketController.php` → method `updateStatus()`

**Alur**:

```
1. Operator klik update status
   ↓
2. POST /tickets/{id}/status
   ↓
3. Validasi: status = open|in_progress|resolved|closed
   ↓
4. Update Firestore document field 'status'
   ↓
5. Update field 'updated_at' dengan timestamp sekarang
   ↓
6. Redirect kembali dengan success message
```

**Firestore Update**:

```php
$firestore->collection('tickets')
    ->document($ticketId)
    ->update([
        ['path' => 'status', 'value' => $newStatus],
        ['path' => 'updated_at', 'value' => new Timestamp(new DateTime())]
    ]);
```

---

#### 6️⃣ **Komentar Real-time**

**File**: `app/Http/Controllers/TicketCommentController.php` → method `store()`

**Alur**:

```
1. User ketik komentar di detail tiket
   ↓
2. POST /tickets/{id}/comments
   ↓
3. Prepare data:
   - user_id: auth()->user()->id
   - user_name: auth()->user()->name
   - user_role: auth()->user()->role
   - message: input('message')
   ↓
4. TicketService::addComment($ticketId, $data)
   - Simpan ke subcollection: tickets/{id}/comments
   ↓
5. Reload halaman → komentar muncul
```

**Firestore Subcollection**:

```php
$firestore->collection('tickets')
    ->document($ticketId)
    ->collection('comments')
    ->add($commentData);
```

---

#### 7️⃣ **Upload & Download Attachment**

**Upload (saat buat tiket)**:

```
1. Customer pilih file (foto/PDF)
   ↓
2. TicketService::uploadAttachments($files, $ticketId)
   ↓
3. Loop setiap file:
   - Generate unique filename
   - Upload ke Firebase Storage bucket: tickets/{ticketId}/{filename}
   - Simpan metadata: name, path, content_type, size
   ↓
4. Return array attachments → simpan di Firestore document
```

**Download**:

```
1. User klik "Download" attachment
   ↓
2. GET /tickets/{id}/attachments/download/{path} (signed URL)
   ↓
3. TicketService::getAttachmentTemporaryUrl($path)
   - Firebase Storage generate signed URL (expire 1 jam)
   ↓
4. Redirect user ke signed URL
   ↓
5. Browser download file dari Firebase Storage
```

---

#### 8️⃣ **Dashboard Statistics**

**File**: `routes/web.php` → Route `GET /dashboard`

**Alur**:

```
1. User akses /dashboard
   ↓
2. Ambil data user login: auth()->user()
   ↓
3. Cek role user:

   Admin:
   - Count semua tiket (all customers)
   - Count tiket per status (open, in_progress, resolved, closed)
   - Count tiket unassigned (belum ada operator)

   Agent:
   - Count tiket assigned ke operator ini saja
   - Filter: where('agent_id', user->id)

   Customer:
   - Count tiket milik customer ini saja
   - Filter: where('customer_id', user->id)
   ↓
4. Return view('dashboard', compact('stats'))
   ↓
5. Blade template render:
   - Card "Total Tiket": {{ $stats['total'] }}
   - Card "Open": {{ $stats['open'] }}
   - Card "In Progress": {{ $stats['in_progress'] }}
   - Card "Selesai": {{ $stats['resolved'] }}
```

**Query Example (Admin)**:

```php
$stats['open'] = \App\Models\Ticket::where('status', 'open')->count();
$stats['total'] = \App\Models\Ticket::count();
$stats['unassigned'] = \App\Models\Ticket::whereNull('agent_id')->count();
```

---

#### 9️⃣ **Role Management (Admin)**

**File**: `app/Http/Controllers/Admin/UserController.php`

**Filter by Role**:

```
1. Admin klik "Kelola Admin" di sidebar
   ↓
2. GET /admin/users?role=admin
   ↓
3. Controller: index(Request $request)
   - Cek query parameter: $request->has('role')
   - Filter: User::where('role', 'admin')->paginate()
   ↓
4. View: resources/views/admin/users/index.blade.php
   - Title dinamis: "Manajemen Admin" (jika role=admin)
   - Button: "Tambah Admin"
   ↓
5. Klik "Tambah Admin"
   ↓
6. GET /admin/users/create?role=admin
   ↓
7. Form pre-filled dengan role=admin (readonly)
   ↓
8. Submit → redirect ke /admin/users?role=admin
```

**Dynamic Title Logic**:

```blade
@php
    $pageTitle = 'Manajemen Pengguna';
    if (request('role') === 'admin') {
        $pageTitle = '🔱 Manajemen Admin';
    } elseif (request('role') === 'agent') {
        $pageTitle = '🔧 Manajemen Operator';
    }
@endphp
```

---

#### 🔟 **SweetAlert2 Integration**

**Logout Confirmation** (`layouts/sidebar.blade.php`):

```javascript
function confirmLogout() {
    Swal.fire({
        title: "Konfirmasi Logout",
        text: "Apakah Anda yakin ingin keluar?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Ya, Logout",
        cancelButtonText: "Batal",
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById("logoutForm").submit();
        }
    });
}
```

**Login Error Alerts** (`auth/login.blade.php`):

```blade
@if($errors->has('email'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Email Tidak Ditemukan',
        text: '{{ $errors->first('email') }}'
    });
</script>
@endif
```

---

### 🎨 Frontend Components

**AdminLTE Sidebar** (`resources/views/layouts/sidebar.blade.php`):

-   Fixed sidebar 260px width
-   Dynamic menu berdasarkan role
-   Sticky topbar dengan user info
-   Footer dengan logout button

**Bootstrap Cards** (Dashboard):

```html
<div class="row">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3>{{ $stats['total'] }}</h3>
                <p>Total Tiket</p>
            </div>
        </div>
    </div>
</div>
```

---

### 🔐 Security Features

1. **CSRF Protection**: Semua form punya `@csrf` token
2. **Signed URLs**: Download attachment pakai signed URL (expire 1 jam)
3. **Role Middleware**: Setiap route dilindungi sesuai role
4. **Firebase Service Account**: Credential tidak di-commit (gitignore)
5. **Password Hashing**: Laravel bcrypt automatic
6. **Input Validation**: Request validation di setiap form submit

---

### 📊 Database Optimization

**Hybrid Strategy**:

-   **Frequent Read**: Firestore (real-time, scalable)
-   **Authentication**: Laravel DB (faster, reliable)
-   **Static Data**: Laravel DB (categories tidak perlu real-time)

**Kenapa Hybrid?**

-   Firestore: Real-time sync perfect untuk tiket & komentar
-   SQL: Lebih cepat untuk auth & relasi data statis
-   Best of both worlds!

---

## 8) Testing & Demo

## 8) Testing & Demo

### Menjalankan Aplikasi

```bash
# 1. Install dependencies
composer install
npm install

# 2. Setup environment
cp .env.example .env
php artisan key:generate

# 3. Setup database
php artisan migrate
php artisan db:seed --class=CategorySeeder

# 4. Run development server
php artisan serve

# Browser: http://127.0.0.1:8000
```

### Create Test Accounts

**Via Register**:

1. Buka `/register`
2. Register sebagai customer (role default)

**Via Tinker (untuk admin/agent)**:

```bash
php artisan tinker

# Buat admin
User::create([
    'name' => 'Admin',
    'email' => 'admin@electronicservice.com',
    'password' => bcrypt('password123'),
    'role' => 'admin'
]);

# Buat operator
User::create([
    'name' => 'Operator 1',
    'email' => 'operator@electronicservice.com',
    'password' => bcrypt('password123'),
    'role' => 'agent'
]);
```

### Skenario Testing untuk Presentasi

**Scenario 1: Customer Journey**

```
1. Register → Login sebagai customer
2. Dashboard → Lihat "Buat Tiket" button
3. Klik "Buat Tiket" → Isi form:
   - Judul: "TV Samsung 43 inch tidak menyala"
   - Kategori: TV
   - Prioritas: High
   - Upload foto TV rusak
4. Submit → Redirect ke daftar tiket
5. Lihat tiket baru dengan status "Open"
6. Klik detail → Lihat informasi lengkap
7. Logout (SweetAlert confirmation muncul)
```

**Scenario 2: Admin Workflow**

```
1. Login sebagai admin
2. Dashboard → Lihat warning "X tiket belum ditugaskan"
3. Klik "Tiket" → Lihat SEMUA tiket dari semua customer
4. Klik tiket customer tadi
5. Klik "Assign to Agent" → Pilih operator
6. Submit → Tiket sekarang assigned
7. Sidebar → Klik "Kelola Operator"
8. Lihat daftar operator, tambah operator baru
9. Klik "Kelola Kategori" → Tambah kategori baru (misal: Laptop)
```

**Scenario 3: Operator Workflow**

```
1. Login sebagai operator
2. Dashboard → Lihat statistik tiket assigned
3. Klik "Tiket" → Hanya tiket yang di-assign ke operator ini
4. Klik detail tiket
5. Update status: Open → In Progress
6. Tambah komentar: "Sedang diperiksa, menunggu spare part"
7. Customer refresh halaman → Lihat update real-time
8. Update status lagi: In Progress → Resolved
9. Customer terima notifikasi tiket selesai
```

**Scenario 4: Real-time Communication**

```
1. Customer buka detail tiket
2. Operator buka detail tiket yang sama
3. Customer kirim komentar: "Kapan bisa selesai?"
4. Operator refresh → Lihat komentar baru
5. Operator balas: "Estimasi besok siang"
6. Customer refresh → Lihat balasan
(Simulasi real-time chat support)
```

---

## 9) Penjelasan untuk Dosen

### Konsep Utama

**1. Hybrid Database Architecture**

-   Mengapa tidak semua pakai Firestore? → Auth & static data lebih efisien di SQL
-   Mengapa tidak semua pakai SQL? → Firestore lebih baik untuk real-time & scalable
-   Kombinasi keduanya = best practice modern web

**2. Role-Based Access Control**

-   3 role berbeda = 3 permission level berbeda
-   Middleware Laravel otomatis proteksi route
-   Security by design (tidak bisa bypass via URL)

**3. Firebase Integration**

-   Service Account JSON = credential server-side
-   Firestore = NoSQL document database (mirip MongoDB)
-   Storage = CDN untuk file upload
-   Signed URL = secure download dengan expiry time

**4. MVC Pattern Laravel**

-   Model: User, Category (Eloquent ORM)
-   View: Blade templates (server-side rendering)
-   Controller: Business logic & routing
-   Service Layer: Firebase operations (separation of concerns)

**5. Modern UX**

-   AdminLTE: Professional dashboard template
-   SweetAlert2: User-friendly confirmation dialogs
-   Bootstrap 5: Responsive mobile-first design
-   Real-time updates: Firestore auto-sync

### Kompleksitas Kode: ⭐⭐⭐ (Medium)

**Simple Parts**:

-   Laravel routing & authentication (built-in)
-   Blade templating (straightforward)
-   Bootstrap UI (component-based)

**Medium Parts**:

-   Role middleware (conditional logic)
-   Firestore CRUD operations
-   File upload to Firebase Storage
-   Dynamic filtering (role-based queries)

**Advanced Parts**:

-   Hybrid database sync
-   Subcollection (nested documents)
-   Signed URL generation
-   Multi-role dashboard statistics

### Technology Justification

| Tech               | Why Use?                                        | Alternative                              |
| ------------------ | ----------------------------------------------- | ---------------------------------------- |
| Laravel            | Full-featured PHP framework, MVC, auth built-in | CodeIgniter (simpler but less features)  |
| Firebase Firestore | Real-time sync, scalable NoSQL                  | MySQL (traditional but no real-time)     |
| Firebase Storage   | CDN, signed URLs, unlimited storage             | Local storage (limited, no CDN)          |
| Bootstrap          | Responsive, well-documented, component-rich     | Tailwind (more custom, steeper learning) |
| SweetAlert2        | Beautiful alerts, modern UX                     | Browser alert() (ugly, basic)            |
| SQLite             | No server setup, portable                       | MySQL (requires server config)           |

### Dokumentasi Kode

Semua file penting sudah punya komentar:

**Controller Comments**:

```php
/**
 * STEP 1: Tampilkan daftar tickets
 * ATURAN:
 * - Admin: Lihat SEMUA tickets
 * - Agent: Lihat tickets yang DI-ASSIGN ke dia saja
 * - Customer: Lihat tickets MEREKA saja
 */
public function index() { ... }
```

**Route Comments**:

```php
// ALUR DASHBOARD:
// 1. Ambil data user yang login
// 2. Hitung statistik tickets berdasarkan role
// 3. Tampilkan dashboard sesuai role
Route::get('/dashboard', function () { ... });
```

**Service Comments**:

```php
// Upload file ke Firebase Storage
// Return: array metadata (name, path, type, size)
public function uploadAttachments($files, $ticketId) { ... }
```

---

## 10) Troubleshooting

### Error: "Firebase credentials not found"

```bash
# Pastikan file service-account.json ada
ls storage/app/firebase/service-account.json

# Check .env
FIREBASE_CREDENTIALS=storage/app/firebase/service-account.json
```

### Error: "SQLSTATE[HY000]: General error: 1 no such table: users"

```bash
# Jalankan migration
php artisan migrate
```

### Error: "Class 'Kreait\Firebase' not found"

```bash
# Install Firebase SDK
composer require kreait/firebase-php
```

### Tiket tidak muncul di dashboard

-   Cek Firebase Console → Firestore → Collection 'tickets' ada data?
-   Cek role user → apakah filter query benar?
-   Cek console log browser → ada error JavaScript?

### Upload gagal

-   Cek Firebase Storage rules → allow write: if request.auth != null
-   Cek ukuran file → max 10MB (config di controller)
-   Cek storage quota → Firebase free tier limit

---

## 11) Deployment (Production)

**Recommended Platform**:

-   Laravel: **Railway**, **Heroku**, atau **DigitalOcean**
-   Database: **PlanetScale** (MySQL) atau tetap SQLite
-   Firebase: Sudah cloud (no extra setup)

**Steps**:

1. Set environment production di `.env`
2. Run `php artisan config:cache`
3. Run `php artisan route:cache`
4. Set `APP_DEBUG=false`
5. Upload ke hosting
6. Point domain ke public folder

---

## 12) Future Improvements

-   [ ] Email notification (saat tiket assigned/resolved)
-   [ ] Push notification (Firebase Cloud Messaging)
-   [ ] Export tiket ke PDF/Excel
-   [ ] Dashboard analytics chart (Chart.js)
-   [ ] Mobile app (Flutter + Firebase)
-   [ ] WebSocket real-time (Laravel Echo + Pusher)
-   [ ] Multi-language support (i18n)
-   [ ] Dark mode toggle

---

## 13) Lisensi & Credits

**Framework**: Laravel 11 (MIT License)
**UI Template**: AdminLTE (MIT License)
**Alert Library**: SweetAlert2 (MIT License)
**Icons**: Font Awesome 6.4 (Free License)
**Backend Services**: Firebase by Google

**Developed by**: [Your Name]
**Course**: Pemrograman Web Lanjut
**Year**: 2025

---

## 📞 Support

Jika ada pertanyaan tentang project ini:

1. Baca dokumentasi di atas
2. Cek komentar di source code
3. Lihat Laravel official docs: https://laravel.com/docs
4. Lihat Firebase docs: https://firebase.google.com/docs

---

**Happy Coding! 🚀**
"# Cloud-Customer-Service" 
