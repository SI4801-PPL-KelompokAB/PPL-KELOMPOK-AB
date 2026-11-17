# 🏥 Bonamu — Sistem Informasi Layanan Klinik Terintegrasi

**Bonamu** adalah aplikasi web layanan klinik terintegrasi untuk **Klinik Bona Mitra Keluarga** yang dikembangkan sebagai Tugas Besar Mata Kuliah **Pengembangan Perangkat Lunak (PPL) — Kelas SI-48-01, Kelompok AB**, Program Studi Sistem Informasi, Fakultas Rekayasa Industri, **Telkom University**.

Aplikasi ini dirancang untuk mendigitalisasi operasional layanan klinik secara menyeluruh (*end-to-end*), mulai dari pengelolaan data master klinik, penjadwalan praktik dokter, pendaftaran antrean *online* maupun *walk-in* secara *real-time*, hingga layanan konsultasi *online* antara pasien dan dokter.

---

## 👥 Tim Pengembang (Kelompok AB — SI4801)

| Nama Anggota | Peran / Modul Utama | Branch Kerja |
| :--- | :--- | :--- |
| **Galih Maha Putra** | Setup Infrastruktur, Skema Database, Registrasi & Login (`PBI01`, `PBI02`) | `Galih_Branch` |
| **Sitti Naurah Bauw (Nara)** | Kelola Jadwal Praktik Dokter & Pendaftaran Antrean Online (`PBI07`, `PBI09`) | `Nara_Branch` |
| **Hanif Irsaddul Fikri** | Kelola Akun Staf/Dokter & Profil Identitas Pasien (`PBI03`, `PBI04`) | `Hanif_branch` |
| **Muhammad Ghassan Delshadi Albarr** | Kelola Data Poli & Master Cabang/Layanan (`PBI05`, `PBI12`) | `Ghassan_Branch` |
| **Maulana Mahardika** | Kelola Data Dokter & Informasi Jadwal Dokter Pasien (`PBI06`, `PBI08`) | `Dika_Branch` |
| **Nesha** | Konsultasi Online Pasien & Penanganan Konsultasi Dokter (`PBI10`, `PBI11`) | `Nesha_Branch` |
| **Agustino Saputra** | Manajemen Operasional Antrean Klinik (`PBI13`–`PBI16`) | `Agustino_Branch` |

---

## 🛠️ Tech Stack

* **Framework Backend**: Laravel 12 (PHP 8.2+)
* **Database**: MySQL 8 (`bonamu`)
* **Frontend**: Laravel Blade, Bootstrap 5.3, Bootstrap Icons
* **Automated Testing**:
  * **PHPUnit** (Feature & Unit Testing)
  * **Laravel Dusk** (Automated Visual Browser / UI End-to-End Testing dengan ChromeDriver)
* **Code Style & Linter**: Laravel Pint (PSR-12)
* **Project Management & CI Tracking**: Jira Software (`SPK`) & GitHub Pull Requests

---

## 📋 Daftar Fitur & Product Backlog Item (PBI)

### Sprint 1 — Fondasi Akun, Master Data, Jadwal, Antrean Online & Konsultasi
* **PBI-01 — Registrasi Pasien**: Pendaftaran akun mandiri untuk pasien baru.
* **PBI-02 — Login & Hak Akses Pengguna**: Autentikasi multi-peran (`admin`, `dokter`, `pasien`) dengan proteksi middleware `EnsureRole`.
* **PBI-03 — Membuat Akun Dokter & Admin**: Pembuatan akun staf internal oleh Admin cabang.
* **PBI-04 — Mengisi Profil Identitas Pasien**: Pengelolaan data identitas pasien (tanggal lahir, jenis kelamin, alamat, jenis pasien Umum/BPJS).
* **PBI-05 — Mengelola Data Poli**: Penambahan, pembaruan, dan pengaturan status aktif/nonaktif poli per cabang klinik.
* **PBI-06 — Mengelola Data Dokter**: Pengelolaan data dokter yang terhubung ke akun dokter dan nomor SIP.
* **PBI-07 — Membuat & Mengedit Jadwal Praktik Dokter**: Pengaturan hari, jam mulai–selesai, dan kuota antrean per dokter dengan validasi pencegahan jadwal bertabrakan (*overlap detection*) serta fitur aktif/nonaktif (*soft disable*).
* **PBI-08 — Melihat Jadwal Dokter**: Pencarian dan penyaringan jadwal praktik dokter aktif per poli oleh pasien.
* **PBI-09 — Membuat Antrean Online**: Pendaftaran nomor antrean *online* oleh pasien dengan validasi kelengkapan profil, kuota jadwal, pencegahan antrean aktif ganda, penomoran otomatis berurutan (`A-001`, `A-002`, ...) menggunakan transaksi database `lockForUpdate()`, serta pencetakan tiket antrean digital.
* **PBI-10 — Melakukan Konsultasi Online (Pasien)**: Pengajuan keluhan konsultasi *online* berdasarkan jadwal dokter aktif dan ruang percakapan (*chat*) konsultasi.
* **PBI-11 — Menangani Konsultasi Online (Dokter)**: Dashboard dokter untuk memulai sesi, membalas keluhan pasien, dan menyelesaikan sesi konsultasi.
* **PBI-12 — Mengelola Data Cabang / Layanan**: Pengelolaan data cabang klinik beserta status operasionalnya.

---

## ⚙️ Panduan Instalasi & Menjalankan Aplikasi

1. **Clone Repositori**
   ```bash
   git clone https://github.com/SI4801-PPL-KelompokAB/PPL-KELOMPOK-AB.git
   cd PPL-KELOMPOK-AB
   ```

2. **Instal Dependensi PHP**
   ```bash
   composer install
   ```

3. **Salin & Konfigurasi File Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Sesuaikan konfigurasi database MySQL pada file `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=bonamu
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Jalankan Migrasi & Seeder Database**
   *(Pastikan database `bonamu` sudah dibuat di phpMyAdmin / MySQL)*
   ```bash
   php artisan migrate:fresh --seed
   ```

5. **Jalankan Server Aplikasi**
   ```bash
   php artisan serve
   ```
   Buka aplikasi di browser melalui alamat: **`http://127.0.0.1:8000`**

---

## 🔑 Akun Pengujian Default (Seeder)

| Peran (Role) | Nomor HP / Email | Password | Hak Akses Utama |
| :--- | :--- | :--- | :--- |
| **Admin** | `081234567890` / `admin@test.com` | `password` / `password123` | Dashboard Admin, Kelola Poli, Cabang, Akun Staf, Jadwal Praktik Dokter |
| **Dokter** | `081200000001` / `dokter.andi@test.com` | `password` | Menangani & Membalas Konsultasi Online Pasien |
| **Pasien** | `081299999999` / `pasien@test.com` | `password` | Dashboard Pasien, Profil Pasien, Ambil Antrean Online, Konsultasi Online |

---

## 🧪 Panduan Menjalankan Automated Testing

Proyek ini dilengkapi dengan **Feature Testing (PHPUnit)** untuk menguji aturan bisnis di level *service/HTTP* dan **Browser Testing (Laravel Dusk)** untuk menguji alur pengguna secara visual di Google Chrome.

### 1. Menjalankan Feature Test (PHPUnit)
```bash
# Menjalankan seluruh Feature Test
php artisan test

# Menjalankan Feature Test spesifik per PBI
php artisan test --filter=PBI05PoliTest
php artisan test --filter=PBI07JadwalPraktikTest
php artisan test --filter=PBI09AntreanOnlineTest
```

### 2. Menjalankan Visual Browser Test (Laravel Dusk)
Pastikan server lokal sedang berjalan (`php artisan serve`), lalu buka terminal baru:

```bash
# Menjalankan seluruh Browser Test untuk satu PBI
php artisan dusk tests/Browser/PBI05PoliTest.php
php artisan dusk tests/Browser/PBI07JadwalPraktikBrowserTest.php
php artisan dusk tests/Browser/PBI09AntreanOnlineBrowserTest.php

# Menjalankan skenario tertentu satu per satu berdasarkan ID Test Case Jira
php artisan dusk --filter=tc_pbi07_01
php artisan dusk --filter=tc_pbi07_02
php artisan dusk --filter=tc_pbi09_01
php artisan dusk --filter=tc_pbi09_02
```

*(Jika terjadi ketidakcocokan versi ChromeDriver dengan browser Chrome yang terinstal, jalankan perintah `php artisan dusk:chrome-driver --detect` terlebih dahulu).*

---

## 🌿 Alur Kerja Git & Konvensi Commit

1. **Struktur Branch**:
   * `main` — Cabang rilis stabil di akhir setiap Sprint.
   * `develop` — Cabang integrasi seluruh fitur anggota selama Sprint berlangsung.
   * `<Nama>_Branch` — Cabang kerja masing-masing anggota tim.
2. **Format Pesan Commit (Terintegrasi dengan Jira)**:
   ```text
   SPK-<nomor_issue> <kata_kerja> <deskripsi_perubahan>
   ```
   Contoh:
   * `SPK-36 PBI07 tambah fitur membuat dan mengedit jadwal praktik dokter`
   * `SPK-4 PBI09 tambah fitur pembuatan antrean online dan tiket antrean`
   * `SPK-36 #resolve PBI07 Membuat dan Mengedit Jadwal Praktik Dokter selesai`
3. **Standar Pemformatan Kode**:
   Sebelum melakukan *commit* dan membuat *Pull Request* ke `develop`, jalankan:
   ```bash
   ./vendor/bin/pint
   ```
