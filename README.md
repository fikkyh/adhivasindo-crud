# Adhivasindo CRUD - REST API - By Fikkyh

REST API CRUD dengan autentikasi token (Laravel Sanctum), manajemen user, dan
pencarian data secara *real-time* dari sumber data eksternal.

## Daftar Isi

- [Fitur](#fitur)
- [Requirements](#requirements)
- [Clone dan Instalasi](#clone-dan-instalasi)
- [Autentikasi](#autentikasi)
- [Dokumentasi Endpoint](#dokumentasi-endpoint)
- [Format Response](#format-response)
- [Sumber Data Eksternal](#sumber-data-eksternal)
- [Testing dengan Postman](#testing-dengan-postman)
- [Struktur Project](#struktur-project)

## Fitur

- 🔐 Autentikasi berbasis token menggunakan Laravel Sanctum
- 👤 CRUD lengkap untuk manajemen data user (Create, Read, Update, Delete)
- 🔍 Pencarian data secara real-time berdasarkan **NAMA**, **NIM**, dan **tanggal lahir (YMD)**
- 📦 Response API terstandarisasi dengan struktur `meta` + `data`
- 🛡️ Seluruh endpoint (kecuali login) terproteksi middleware `auth:sanctum`
- ♻️ Retry mechanism otomatis saat fetch data eksternal gagal/lambat

## Requirements

| Komponen | Versi |
| --------------- | --------------- |
| Laravel | 13.32.0 |
| PHP | 8.3.30 |
| Composer | 2.9.4 |
| Database | MySQL |
| Autentikasi API | Laravel Sanctum |

## Clone dan Instalasi

### 1. Clone repository

```
git clone -b adhivasindo-curd-fikkyh https://github.com/fikkyh/adhivasindo-crud.git
cd adhivasindo-crud
```

### 2. Install dependency

```
composer install
```

Project ini merupakan API murni, sehingga tidak memerlukan Node.js maupun `npm install`.

### 3. Konfigurasi environment

```
cp .env.example .env
php artisan key:generate
```

> Windows CMD: gunakan `copy .env.example .env`.

### 4. Konfigurasi database

Buat database MySQL, misalnya:

```
CREATE DATABASE adhivasindo_crud;
```

Kemudian sesuaikan konfigurasi database di `.env`.

### 5. Migration dan Seeder

```
php artisan migrate --seed
```

Perintah ini membuat tabel database sekaligus membuat akun bawaan:

* Email: `ifikkyh@gmail.com`
* Password: `fikkyh123`

> Gunakan `--seed` agar akun bawaan ikut dibuat.

Jika ingin membuat user sendiri:

```
php artisan tinker
```

```php
\App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('password123'),
]);
```

### 6. Jalankan aplikasi

```
php artisan serve
```

API tersedia di:

```
http://127.0.0.1:8000/api
```

## Autentikasi

Seluruh endpoint **kecuali `/login`** memerlukan token Bearer yang didapat dari
proses login. Sertakan token ini di setiap request pada header:

```
Authorization: Bearer <token>
Accept: application/json
```

Token didapat dari response endpoint `POST /api/login`, dan bisa dicabut kapan
saja lewat endpoint `POST /api/logout`.

## Dokumentasi Endpoint

### A. Login

| Method | Endpoint | Auth |
| --- | --- | --- |
| POST | `/api/login` | ❌ |

**Request body:**
```json
{
    "email": "ifikkyh@gmail.com",
    "password": "fikkyh123"
}
```

**Response 200:**
```json
{
    "data": {
        "user": { "id": 1, "name": "Fikkyh", "email": "ifikkyh@gmail.com" },
        "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
        "token_type": "Bearer"
    }.
    "meta": { "success": true, "code": 200, "message": "Login berhasil" }
}
```

Endpoint pendukung lain:

| Method | Endpoint | Auth | Keterangan |
| --- | --- | --- | --- |
| POST | `/api/logout` | ✅ | Mencabut token aktif |
| GET | `/api/me` | ✅ | Profil user yang sedang login |

### B. CRUD User

Seluruh endpoint di bawah ini memerlukan token (`Authorization: Bearer <token>`).

| Method | Endpoint | Keterangan |
| --- | --- | --- |
| GET | `/api/users` | Daftar semua user (paginated) |
| POST | `/api/users` | Tambah user baru |
| GET | `/api/users/{id}` | Detail satu user |
| PUT/PATCH | `/api/users/{id}` | Update user |
| DELETE | `/api/users/{id}` | Hapus user |

**Contoh request `POST /api/users`:**
```json
{
    "name": "Oliver Sykes",
    "email": "oli@example.com",
    "password": "password123"
}
```

**Contoh request `PUT /api/users/{id}`** (field bersifat opsional, cukup kirim yang mau diubah):
```json
{
    "name": "Oliver Sykes Updated"
}
```

### C, D, E. Pencarian Data Real-Time

Data diambil langsung (real-time) dari sumber eksternal pada setiap request,
tanpa disimpan ke database lokal. Endpoint ini juga memerlukan token.

| Poin | Method | Endpoint | Keterangan |
| --- | --- | --- | --- |
| C | GET | `/api/search/nama?nama=alice` | Cari data berdasarkan NAMA |
| D | GET | `/api/search/nim?nim=2030213012` | Cari data berdasarkan NIM |
| E | GET | `/api/search/ymd?ymd=19961215` | Cari data berdasarkan tanggal lahir/YMD (format `YYYYMMDD`) |

**Contoh response:**
```json
{
    "data": [
        { "nim": "0457896312", "nama": "Adams Alis", "tanggal_lahir": "20230502" },
        { "nim": "0197485623", "nama": "Adams Susano'o", "tanggal_lahir": "20231122" }
    ],
    "meta": {
        "success": true,
        "code": 200,
        "message": "Hasil pencarian berdasarkan NAMA: adams"
    }
}
```

## Format Response

Seluruh response API mengikuti struktur `meta` + `data` yang konsisten.

**Sukses (single data):**
```json
{
    "meta": { "success": true, "code": 200, "message": "..." },
    "data": { }
}
```

**Sukses (list dengan pagination):**
```json
{
    "data": [ ]
    "meta": {
        "success": true,
        "code": 200,
        "message": "...",
        "pagination": { "current_page": 1, "per_page": 10, "total": 25, "last_page": 3 }
    }
}
```

**Gagal:**
```json
{
    "meta": { "success": false, "code": 401, "message": "..." },
    "data": null
}
```

## Sumber Data Eksternal

Endpoint pencarian (poin C, D, E) mengambil data secara real-time dari:

```
https://bit.ly/48ejMhW
```

Setiap request akan melakukan fetch langsung ke sumber tersebut (tanpa cache),
dengan mekanisme retry otomatis (maksimal 4 kali percobaan) apabila response
gagal atau kosong, untuk menjaga stabilitas tanpa mengorbankan sifat real-time
dari data.

## Testing dengan Postman

Import file `postman/RestAPI-CRUD.postman_collection.json` yang tersedia di
repository ini. Alur pengujian:

1. Jalankan request **Login** pada folder *Auth*, salin `token` dari response.
2. Set token tersebut ke variable collection `token`.
3. Jalankan request lain pada folder *CRUD Users* dan *Search* — token akan
   otomatis disertakan pada header `Authorization`.

## Struktur Project

```
app/
├── Helpers/
│   └── ApiResponse.php          # Helper standarisasi response API
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php
│   │   ├── UserController.php
│   │   └── SearchController.php
│   ├── Requests/
│   │   ├── LoginRequest.php
│   │   ├── StoreUserRequest.php
│   │   └── UpdateUserRequest.php
│   └── Resources/
│       └── UserResource.php
├── Models/
│   └── User.php
└── Services/
    └── ExternalDataService.php  # Fetch & parsing data eksternal real-time

database/
└── seeders/
    ├── DatabaseSeeder.php
    └── UserSeeder.php            # Generate akun default (dijalankan via --seed)

routes/
└── api.php

postman/
└── RestAPI-CRUD.postman_collection.json
```
