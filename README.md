# Inventory & Supply Chain Management System (ISCM)

REST API untuk manajemen inventaris dan rantai pasok berbasis Laravel 12, dilengkapi JWT authentication, Role-Based Access Control, dan arsitektur Repository Pattern + Service Layer.

---

## Deskripsi

ISCM mengelola seluruh siklus inventaris dalam satu platform terpadu:

- **Request Barang** — Teknisi ajukan kebutuhan → SPV approve/reject → stok otomatis berkurang
- **Purchase Order** — Admin buat PO ke vendor → SPV approve → dikirim & dikonfirmasi (7 status lifecycle)
- **Receiving** — Catat penerimaan barang dengan quality control (good / damaged / returned) → stok otomatis bertambah
- **Inventory** — Pantau stok real-time, riwayat pergerakan, alert stok minimum
- **Stock Opname** — Rekonsiliasi stok fisik vs sistem → SPV approve → stok disesuaikan otomatis

Seluruh perubahan stok tercatat di audit trail terpusat (`stock_movements`) dengan informasi siapa, kapan, dan berapa perubahannya.

---

## Goals

| Goal | Detail |
|---|---|
| Real-time stock tracking | Stok selalu akurat karena setiap pergerakan langsung tercatat |
| Structured approval workflow | Setiap transaksi kritis memerlukan persetujuan role yang sesuai |
| Data integrity | Semua operasi kritikal dibungkus `DB::transaction()` — atomik dan aman |
| Audit trail | Setiap pergerakan stok bisa ditelusuri: sumber, jumlah, waktu, pelaku |
| Clean architecture | Repository Pattern + Service Layer: modular, testable, mudah dikembangkan |

---

## Tech Stack

| Komponen | Teknologi |
|---|---|
| Framework | Laravel 12 |
| Bahasa | PHP 8.4.4 |
| Autentikasi | JWT via `tymon/jwt-auth ^2.1` |
| Otorisasi | `spatie/laravel-permission ^6.0` |
| Database | MySQL |
| Testing | PHPUnit 11 + Mockery |

---

## Role & Akses

| Role | Akses |
|---|---|
| `admin_gudang` | Master data, Purchase Order, Receiving, Inventory, Stock Opname |
| `supervisor` | Approve/reject Request, PO, Stock Opname, pantau inventory |
| `technician` | Buat dan submit permintaan barang (request) |

---

## How to Run

### 1. Clone & Install Dependencies

```bash
git clone <repo-url> be_inventory_app
cd be_inventory_app
composer install
```

### 2. Setup Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`, sesuaikan konfigurasi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=iscm_db
DB_USERNAME=root
DB_PASSWORD=

JWT_SECRET=   # di-generate di langkah berikutnya
```

### 3. Generate JWT Secret

```bash
php artisan jwt:secret
```

### 4. Migrate & Seed Database

Buat database dulu di MySQL:

```sql
CREATE DATABASE iscm_db;
```

Lalu jalankan migration dan seeder:

```bash
php artisan migrate
php artisan db:seed
```

Akun yang tersedia setelah seed:

| Email | Password | Role |
|---|---|---|
| `admin@example` | `password` | admin_gudang |
| `supervisor@example` | `password` | supervisor |
| `technician@example` | `password` | technician |

### 5. Jalankan Server

```bash
php artisan serve
```

API tersedia di: `http://localhost:8000/api/v1`

---

## Migrate Fresh (Reset Database)

Untuk reset database dan seed ulang dari awal:

```bash
php artisan migrate:fresh --seed
```

> Perintah ini akan **menghapus semua data** dan membuat ulang seluruh tabel.

---

## Running Tests

```bash
# Jalankan semua unit test
php artisan test --testsuite=Unit

# Jalankan file tertentu
php artisan test tests/Unit/Services/AuthServiceTest.php
php artisan test tests/Unit/Services/RequestServiceTest.php

# Output detail per test case
php artisan test --testsuite=Unit --verbose
```

Test yang tersedia:

| File | Test Case |
|---|---|
| `AuthServiceTest` | Login valid, password salah, akun nonaktif, email tidak ada, logout |
| `RequestServiceTest` | Approve sukses, stok tidak cukup, request bukan submitted, qty approved = 0 |

---

## API Documentation

Dokumentasi API tersedia via **Scramble** — auto-generated dari kode, selalu sinkron dengan implementasi terbaru.

### Cara membuka

Pastikan server sudah berjalan, buka di browser:

```
http://localhost:8000/docs/api
```

Halaman ini menampilkan seluruh endpoint beserta schema request, response, dan contoh payload secara interaktif.

### Jika halaman tidak ditemukan

Install Scramble terlebih dahulu:

```bash
composer require dedoc/scramble
php artisan vendor:publish --provider="Dedoc\Scramble\ScrambleServiceProvider"
```

Lalu buka kembali `http://localhost:8000/docs/api`.

### Import ke Postman

Scramble menyediakan OpenAPI spec dalam format JSON yang bisa langsung diimport ke Postman:

```
http://localhost:8000/docs/api.json
```

Di Postman: **Import → Link → masukkan URL di atas → Import**

---

## Struktur Proyek

```
app/
├── Enums/                  # PHP 8.1 Backed Enums (status, tipe movement)
├── Exceptions/             # Global JSON error handler
├── Http/
│   ├── Controllers/Api/V1/ # 11 thin controllers
│   ├── Middleware/         # JwtMiddleware
│   ├── Requests/           # 22 Form Request classes
│   └── Resources/          # 7 API Resources
├── Interfaces/             # 11 Repository Interfaces
├── Models/                 # 13 Eloquent Models
├── Providers/
│   └── AppServiceProvider.php
├── Repositories/           # 10 Repository Implementations              
└── Services/               # 10 Service Classes

database/
├── migrations/             # 18 tabel
└── seeders/

tests/
└── Unit/Services/
    ├── AuthServiceTest.php
    └── RequestServiceTest.php
```

---

## API Endpoints Overview

Base URL: `/api/v1`

| Modul | Prefix | Akses |
|---|---|---|
| Auth | `/auth` | Public + Protected |
| Users | `/users` | admin_gudang |
| Categories | `/categories` | admin_gudang |
| Units | `/units` | admin_gudang |
| Vendors | `/vendors` | admin_gudang |
| Items | `/items` | admin_gudang |
| Requests | `/requests` | technician + spv |
| Purchase Orders | `/purchase-orders` | admin_gudang + spv |
| Receivings | `/receivings` | admin_gudang |
| Inventory | `/inventory` | admin_gudang + spv |
| Stock Opname | `/stock-opnames` | admin_gudang + spv |

---

## Catatan

Semua request ke endpoint protected harus menyertakan header:

```
Authorization: Bearer <token>
```

Format response selalu konsisten:

```json
{
  "success": true,
  "message": "...",
  "data": { ... }
}
```

Token JWT expire sesuai `JWT_TTL` di `.env` (default 60 menit). Gunakan `/auth/refresh` untuk perpanjang token tanpa login ulang.
