# 📖 PENJELASAN ALUR PROGRAM - Cloud Customer Support

## 🎯 Konsep Dasar (Analogi Pabrik)

Bayangkan sistem ini seperti **Pabrik Motor Honda**:

-   **Customer** = Operator Lini (menemukan masalah)
-   **Agent** = Teknisi (memperbaiki masalah)
-   **Admin** = Manager (mengatur siapa mengerjakan apa)

---

## 🔄 ALUR LENGKAP SISTEM

### **1. Customer Buat Ticket (Laporkan Masalah)**

```
Customer → Klik "Buat Ticket" → Isi Form → Upload Foto → Submit
```

**File terkait:**

-   `resources/views/tickets/create.blade.php` (Form UI)
-   `TicketController@store` (Proses simpan)
-   `TicketService@createTicket` (Logic simpan ke Firestore + Laravel DB)

**Yang terjadi di backend:**

1. Validasi input (title, description, category, priority)
2. Simpan ke **Firestore** (real-time database)
3. Auto-sync ke **Laravel Database** (untuk query cepat)
4. Upload file ke **Firebase Storage** (jika ada attachment)
5. Redirect ke halaman detail ticket

---

### **2. Admin Lihat & Assign ke Agent**

```
Admin → Dashboard → Lihat Daftar Tickets → Pilih Ticket → Assign Agent
```

**File terkait:**

-   `resources/views/dashboard.blade.php` (Dashboard dengan stats)
-   `resources/views/tickets/index.blade.php` (Daftar tickets)
-   `resources/views/tickets/show.blade.php` (Detail ticket)
-   `TicketController@index` (Tampilkan daftar)
-   `TicketController@show` (Tampilkan detail)

**Yang terjadi di backend:**

1. Admin lihat statistik: berapa ticket open, in progress, resolved
2. Admin lihat daftar semua tickets
3. Admin klik ticket tertentu → lihat detail
4. Admin assign ke agent tertentu (update field `agent_id`)
5. Update disimpan ke Firestore + Laravel DB

---

### **3. Agent Kerjakan Ticket**

```
Agent → Dashboard → Lihat "Tickets Saya" → Buka Ticket → Update Status → Tambah Komentar
```

**File terkait:**

-   `resources/views/tickets/show.blade.php` (Detail + form komentar)
-   `TicketController@updateStatus` (Update status)
-   `TicketCommentController@store` (Tambah komentar)
-   `TicketService@addComment` (Logic simpan komentar)

**Yang terjadi di backend:**

1. Agent lihat tickets yang di-assign ke dia
2. Agent update status: Open → In Progress → Resolved
3. Agent tambah komentar: "Sedang diperbaiki, butuh sparepart X"
4. Agent upload foto hasil perbaikan
5. Semua update langsung terlihat oleh Customer & Admin (real-time)

---

### **4. Customer Pantau Progress**

```
Customer → Dashboard → Ticket Saya → Klik Ticket → Lihat Status & Komentar
```

**Yang terjadi:**

-   Customer lihat status terkini: "In Progress"
-   Customer baca komentar dari Agent: "Sedang diperbaiki"
-   Customer lihat foto/bukti dari Agent
-   Customer bisa balas komentar jika ada pertanyaan

---

## 🗂️ STRUKTUR DATABASE

### **1. Laravel Database (SQLite)**

```
users          → Data user (customer, agent, admin)
categories     → Kategori ticket (Perbaikan, Quality, Safety, dll)
tickets        → Data ticket (title, status, priority, dll)
ticket_comments → Komentar di ticket
```

### **2. Firestore (Firebase)**

```
/tickets/{ticketId}
  - title
  - description
  - customer_id
  - agent_id
  - status
  - priority
  - attachments[]

/tickets/{ticketId}/comments/{commentId}
  - user_id
  - message
  - attachments[]
```

---

## 📊 DASHBOARD STATISTICS

**Cara hitung statistik (di `routes/web.php`):**

```php
// Admin lihat SEMUA tickets
$stats['open'] = Ticket::where('status', 'open')->count();
$stats['in_progress'] = Ticket::where('status', 'in_progress')->count();

// Agent hanya lihat tickets yang di-assign ke dia
$stats['open'] = Ticket::where('agent_id', $user->id)
                       ->where('status', 'open')
                       ->count();

// Customer hanya lihat tickets milik dia
$stats['open'] = Ticket::where('customer_id', $user->id)
                       ->where('status', 'open')
                       ->count();
```

---

## 🎨 STATUS & PRIORITY BADGES

### **Status:**

-   🆕 **Open** (Biru) = Ticket baru, belum ditangani
-   ⚙️ **In Progress** (Kuning) = Sedang dikerjakan agent
-   ✅ **Resolved** (Hijau) = Sudah selesai
-   🔒 **Closed** (Abu) = Ditutup/arsip

### **Priority:**

-   ⬇️ **Low** (Abu) = Tidak urgent
-   🔵 **Medium** (Biru) = Normal
-   🟠 **High** (Orange) = Penting
-   🔴 **Critical** (Merah) = Sangat urgent!

---

## 🔐 ROLE & PERMISSIONS

| Role     | Bisa Apa?                                    |
| -------- | -------------------------------------------- |
| Admin    | Lihat semua, assign agent, kelola users      |
| Agent    | Lihat tickets assigned ke dia, update status |
| Customer | Buat ticket baru, lihat tickets milik dia    |

---

## 🚀 TEKNOLOGI YANG DIPAKAI

1. **Laravel 11** - Backend framework
2. **Firebase Firestore** - Real-time database
3. **Firebase Storage** - File storage (foto/video)
4. **SQLite** - Local database (untuk fast query)
5. **Bootstrap 5** - UI framework
6. **Blade Templates** - View engine

---

## 📝 CARA JELASIN KE DOSEN

### **1. Konsep:**

> "Sistem ini seperti papan pengumuman di pabrik. Customer lapor masalah, Admin tugaskan ke Teknisi, Teknisi kerjakan & update progress. Semua orang lihat real-time."

### **2. Database Hybrid:**

> "Saya pakai Firestore untuk real-time sync antar user, tapi juga sync ke Laravel database untuk query cepat dan reporting."

### **3. Alur Sederhana:**

> "Flow-nya simple: Customer buat ticket → Admin assign → Agent kerjakan → Selesai. Setiap step tercatat dan bisa dilacak."

### **4. Role-Based Access:**

> "Setiap role punya akses berbeda. Customer cuma lihat ticket mereka, Agent lihat yang di-assign ke dia, Admin lihat semua."

---

## 🎓 POIN PENTING UNTUK UAS

✅ **Sistem Hybrid** - Pakai Firestore (cloud) + Laravel DB (local)  
✅ **Real-time Updates** - Firestore auto-sync  
✅ **File Upload** - Support foto/video bukti  
✅ **Role-Based** - 3 role dengan permission berbeda  
✅ **Clean Code** - Ada komentar di setiap function  
✅ **Dashboard Stats** - Visual cards untuk monitoring

---

## 📧 LOGIN CREDENTIALS

| Role     | Email                 | Password |
| -------- | --------------------- | -------- |
| Admin    | admin@example.com     | password |
| Agent    | agent1@example.com    | password |
| Customer | customer1@example.com | password |

---

**Semoga sukses UAS-nya! 🎉**
