# Database Setup - Rekap Hastag

## Sistem Database

Aplikasi menggunakan **SQLite** untuk menyimpan data user. SQLite adalah database file-based yang tidak memerlukan server database terpisah.

## Struktur Database

### Tabel: `users`
- `id` - Primary key
- `username` - Username untuk login (unique)
- `password` - Password yang di-hash
- `full_name` - Nama lengkap user
- `email` - Email user
- `role` - Role user (admin/user)
- `is_active` - Status aktif (1/0)
- `created_at` - Tanggal dibuat
- `updated_at` - Tanggal diupdate

## Default User

Saat pertama kali diakses, sistem akan membuat user default:
- **Username:** `admin`
- **Password:** `admin123`
- **Role:** `admin`

## Lokasi Database

Database file disimpan di: `database/rekap_hastag.db`

Folder `database/` akan dibuat otomatis saat pertama kali aplikasi diakses.

## Fitur

1. **Login System** - Authentication menggunakan database
2. **User Management** - Admin dapat menambah/edit user (akses di menu User Management)
3. **Password Hashing** - Password disimpan dengan hash (PASSWORD_DEFAULT)
4. **Role-based Access** - Support untuk admin dan user role

## Cara Menggunakan

1. Akses aplikasi, database akan dibuat otomatis
2. Login dengan default credentials: `admin` / `admin123`
3. Untuk menambah user baru, login sebagai admin dan akses menu "User Management"

## Keamanan

- Password di-hash menggunakan `password_hash()` dengan `PASSWORD_DEFAULT`
- Session management untuk authentication
- Role-based access control
- SQL injection protection dengan prepared statements

## Catatan

- Database SQLite file-based, mudah untuk backup (tinggal copy file .db)
- Tidak perlu setup database server
- Cocok untuk aplikasi kecil hingga menengah

