[![HTML5](https://img.shields.io/badge/HTML5-E34F26?logo=html5&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/HTML)
[![CSS3](https://img.shields.io/badge/CSS3-1572B6?logo=css3&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/CSS)
[![PHP](https://img.shields.io/badge/PHP-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Version](https://img.shields.io/badge/version-1.0.0-green.svg)](https://github.com/SidqiRaafi/RoomBook)
[![License](https://img.shields.io/badge/license-MIT-orange.svg)](LICENSE)
[![Maintained](https://img.shields.io/badge/Maintained%3F-yes-success.svg)](https://github.com/SidqiRaafi/RoomBook/graphs/commit-activity)

# Room Booking System

Sistem peminjaman ruang kelas berbasis web untuk admin kampus.  
Admin dapat melihat ketersediaan ruangan, membuat booking baru, mengedit, menghapus (soft delete), dan mencetak surat peminjaman.

## Live Website Demo

https://roombook.my.id

## Video Demo (Drive)

https://drive.google.com/drive/folders/17spkrH6SqfOaRpMpWN2RdGxIdWFWP3U9?usp=sharing


## Fitur

- Login admin menggunakan session PHP.
- Dashboard ketersediaan ruang per jam (grid ruangan vs time slot).
- Form booking dengan:
  - Pilih tanggal booking.
  - Pilih ruangan.
  - Pilih jam mulai & selesai.
  - Validasi konflik booking per ruangan, tanggal, dan jam.
- Auto-update status booking:
  - `active` untuk booking yang masih berlaku.
  - `expired` otomatis jika tanggal sudah lewat.
  - `deleted` jika dihapus admin (soft delete).
- Halaman Manage Booking:
  - Lihat semua booking aktif.
  - Edit tanggal, ruangan, jam, dan keterangan.
  - Soft delete booking.
  - Cetak surat peminjaman.
- Halaman History:
  - Menampilkan semua booking (active, expired, deleted).
  - Badge warna per status.
  - Informasi siapa dan kapan booking dihapus (audit trail).
- Generate surat peminjaman siap cetak (printable).

## Tech Stack

- PHP 7.4+ (native).
- MySQL (akses via PDO).
- HTML5, CSS3.
- Vanilla JavaScript (AJAX untuk create/update/delete booking).
- Session PHP untuk autentikasi admin.

## Struktur Folder
```
RoomBook/
├── assets/
│   └── background/
│       └── bg.jpg
│
├── config/
│   └── database.php
│
├── css/
│   ├── main.css
│   └── history.css
│
├── js/
│   ├── dashboard.js
│   └── manage_bookings.js
│
├── booking_history.php
├── create_booking.php
├── dashboard.php
├── delete_booking.php
├── download_pdf.php
├── get_bookings_by_date.php
├── index.php
├── logout.php
├── manage_bookings.php
├── print_booking.php
├── README.md
└── update_booking.php
```

## Instalasi local
```
1. Clone repository:
git clone https://github.com/yourusername/RoomBook.git
cd RoomBook
```
```
2. Import database
  - buka phpmyadmin
  - buat database roombook
  - import roombook.sql
  - konfigurasi database.php
```
```
3. jalankan di XAMPP / Laragon
  - simpan folder roombook yang sudah di clone ke htdocs/ untuk XAMPP dan www/ untuk Laragon
  - akses di browser dengan http://localhost/RoomBook
```
```
4. akun default
  - username : admin
  - password : admin123
```
