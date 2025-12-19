# Cloud Customer Support & Ticketing System (Laravel 12 + Firebase)

Project ini adalah contoh tugas mata kuliah **Pemrograman Web Lanjut**: Sistem layanan pelanggan dan ticketing berbasis cloud untuk mencatat **keluhan**, **permintaan layanan**, **perbaikan produk**, serta **manajemen tiket** secara online.

Backend: Laravel 12 (Auth + Role)

Penyimpanan ticket: **Firebase Firestore**

Lampiran: **Firebase Storage**

Frontend modul ticketing: **Bootstrap 5** (via CDN)

## 1) Prasyarat

-   PHP 8.3+ (disarankan)
-   Composer
-   Node.js (untuk Vite, walau modul Bootstrap di ticketing pakai CDN)
-   Akun Firebase + Service Account JSON
-   Database SQL untuk tabel `users` (default Laravel; bisa MySQL/MariaDB/SQLite)

## 2) Membuat Project Laravel 12 (dari awal)

Jika memulai dari nol:

1. Buat project:
    - `composer create-project laravel/laravel cloud-ticketing`
2. Masuk folder:
    - `cd cloud-ticketing`
3. Install starter auth (opsional tapi direkomendasikan):
    - Laravel Breeze (Blade) atau starter auth lain.

Catatan: Workspace ini sudah memiliki struktur auth route (`routes/auth.php`) dan layout bawaan.

## 3) Setup Firebase (Firestore + Storage)

### A. Buat Project Firebase

1. Firebase Console → Add project.
2. Aktifkan:
    - **Cloud Firestore** (Native mode)
    - **Storage**

### B. Buat Service Account

1. Firebase Console → Project settings → Service accounts.
2. Generate **Private key** (JSON) lalu simpan di project, contoh:
    - `storage/app/firebase/private/firebase-service-account.json`

### C. Konfigurasi `.env`

Tambahkan variabel berikut:

```
FIREBASE_CREDENTIALS=storage/app/firebase/private/firebase-service-account.json
FIREBASE_PROJECT_ID=YOUR_FIREBASE_PROJECT_ID
FIREBASE_STORAGE_BUCKET=YOUR_FIREBASE_PROJECT_ID.appspot.com
FIREBASE_DATABASE_URL=
FIREBASE_FIRESTORE_ENABLED=true
```

Keterangan:

-   `FIREBASE_CREDENTIALS` boleh absolute path (Windows: `D:\...`) atau relative ke root project.
-   `FIREBASE_STORAGE_BUCKET` biasanya `project-id.appspot.com`.

## 4) Setup Database User + Role

Ticket disimpan di Firestore, tetapi user/role tetap disimpan di database SQL.

1. Atur DB di `.env` (contoh MySQL):

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cloud_ticketing
DB_USERNAME=root
DB_PASSWORD=
```

2. Jalankan migration:
    - `php artisan migrate`

Di project ini sudah ada migration role: `database/migrations/2025_12_18_171744_add_role_to_users_table.php`.

Role yang dipakai:

-   `admin`
-   `agent`
-   `customer` (default)

## 5) Struktur Data Firestore (Desain)

### Collection: `tickets`

Setiap ticket adalah document, fields contoh:

-   `title` (string)
-   `description` (string)
-   `category` (string: keluhan|permintaan_layanan|perbaikan_produk)
-   `priority` (string: low|medium|high)
-   `status` (string: open|in_progress|resolved|closed)
-   `customer_id` (string/int dari users.id)
-   `assigned_agent_id` (string/int, opsional)
-   `attachments` (array of map) → `{name, path, content_type, size}`
-   `created_at` (Timestamp)
-   `updated_at` (Timestamp)

### Subcollection: `tickets/{ticketId}/comments`

Setiap comment document, fields:

-   `user_id`
-   `user_name`
-   `role`
-   `message`
-   `created_at` / `updated_at`

## 6) Implementasi Fitur (yang sudah dipasang di workspace ini)

### A. Role Middleware

-   Middleware: `app/Http/Middleware/RoleMiddleware.php`
-   Alias middleware didaftarkan di `bootstrap/app.php`:
    -   `role` → RoleMiddleware

Pemakaian contoh:

-   `middleware(['auth', 'role:admin'])`

### B. Ticket Service (Firestore + Storage)

File: `app/Services/Firebase/TicketService.php`

Fungsi utama:

-   `getAllTickets()`
-   `getTicketsByCustomer($customerId)`
-   `getTicket($id)`
-   `createTicket($data)`
-   `updateTicket($id, $data)`
-   `deleteTicket($id)`
-   `addComment($ticketId, $data)`
-   `getComments($ticketId)`
-   `uploadAttachments($files, $ticketId)` (upload ke Storage)
-   `getAttachmentTemporaryUrl($path)` (signed URL untuk download)

### C. Controller

-   Ticket: `app/Http/Controllers/TicketController.php`
-   Comment: `app/Http/Controllers/TicketCommentController.php`

### D. Routes

File: `routes/web.php`

-   Resource: `Route::resource('tickets', TicketController::class)` untuk role `customer,admin,agent`.
-   Comment: `POST /tickets/{ticket}/comments`
-   Aksi agent/admin:
    -   Assign: `POST /tickets/{ticket}/assign` (admin)
    -   Status: `POST /tickets/{ticket}/status` (admin/agent)

### E. UI Bootstrap 5

Layout Bootstrap:

-   `resources/views/layouts/bootstrap.blade.php`

Ticket views:

-   `resources/views/tickets/index.blade.php`
-   `resources/views/tickets/create.blade.php`
-   `resources/views/tickets/show.blade.php`
-   `resources/views/tickets/edit.blade.php`

Admin view (role management):

-   `resources/views/admin/users/index.blade.php`

## 7) Cara Menjalankan (Local)

1. Install dependency PHP:
    - `composer install`
2. Buat `.env`:
    - copy dari `.env.example` lalu set DB + Firebase env.
3. Generate key:
    - `php artisan key:generate`
4. Migrate:
    - `php artisan migrate`
5. Jalankan server:
    - `php artisan serve`

Login/Register lalu akses:

-   `http://127.0.0.1:8000/tickets`

### Cara cepat coba (mencoba 3 role)

1. Isi `.env` (DB + Firebase) lalu install dependency:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

2. Jalankan migrasi dan seeder (UserSeeder akan membuat akun admin, agent, customer):

```bash
php artisan migrate
php artisan db:seed
```

3. Akun uji (default yang dibuat oleh seeder):

-   Admin: `admin@example.com` / `password`
-   Agent: `agent1@example.com` / `password`
-   Customer: `customer1@example.com` / `password`
-   Test user: `test@example.com` / `password`

4. Jalankan server lokal:

```bash
php artisan serve
# lalu buka http://127.0.0.1:8000
```

5. Saat halaman terbuka Anda akan melihat landing page. Gunakan tombol "Login" untuk masuk.

6. Setelah login:

-   Customer: akan diarahkan ke daftar ticket miliknya (atau bisa membuat ticket baru)
-   Agent/Admin: dapat melihat semua ticket, assign, dan update status

Catatan: jika ingin membuat akun lain gunakan fasilitas Register atau ubah role via menu Admin → Users.

## 8) Skenario Demo untuk Laporan

1. Register user A → role default `customer`.
2. Login admin → buka `Users` → ubah role user B menjadi `agent`.
3. Login customer → buat ticket + upload lampiran.
4. Login agent/admin → lihat semua ticket → update status.
5. Tambahkan komentar bolak-balik (customer ↔ agent/admin).

## 9) Catatan Keamanan Firebase (Wajib di Laporan)

Karena akses ke Firestore/Storage dilakukan dari server (Laravel) via service account:

-   Pastikan file JSON service account **tidak** di-commit.
-   Simpan di folder private: `storage/app/firebase/private/`.

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

-   [Simple, fast routing engine](https://laravel.com/docs/routing).
-   [Powerful dependency injection container](https://laravel.com/docs/container).
-   Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
-   Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
-   Database agnostic [schema migrations](https://laravel.com/docs/migrations).
-   [Robust background job processing](https://laravel.com/docs/queues).
-   [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

-   **[Vehikl](https://vehikl.com)**
-   **[Tighten Co.](https://tighten.co)**
-   **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
-   **[64 Robots](https://64robots.com)**
-   **[Curotec](https://www.curotec.com/services/technologies/laravel)**
-   **[DevSquad](https://devsquad.com/hire-laravel-developers)**
-   **[Redberry](https://redberry.international/laravel-development)**
-   **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
