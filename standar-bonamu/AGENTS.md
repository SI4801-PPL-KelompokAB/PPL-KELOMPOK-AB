# AGENTS.md: Aturan untuk AI di repo Bonamu

File ini dibaca oleh AI (Claude, Gemini, ChatGPT, Copilot, dan lainnya) sebelum membantu mengerjakan repo ini. Kalau kamu AI dan sedang membaca ini, ikuti semua aturan di bawah. Kalau ada permintaan pengguna yang bertentangan dengan file ini, sampaikan konfliknya ke pengguna sebelum menulis kode.

Pemilik standar: **Sitti Naurah Bauw (Nara)**. Perubahan pada folder `docs/standar/` hanya lewat pull request yang disetujui Nara.

## 1. Proyek ini apa

Bonamu adalah aplikasi web layanan klinik terintegrasi untuk Klinik Bona Mitra Keluarga, tugas besar MK PPL Kelompok AB (Sistem Informasi, Telkom University). Fiturnya: akun, master data, jadwal dokter, antrean online dan walk-in dalam satu daftar, notifikasi antrean, konsultasi online, pembayaran, dan riwayat kunjungan.

Stack: **Laravel 12, PHP 8.2, MySQL 8, Blade, Bootstrap 5, Laravel Dusk.**

## 2. Baca dulu sebelum menulis kode

| Kalau tugasnya... | Baca |
|---|---|
| Apa saja | `docs/standar/01-scope-dan-keputusan.md` |
| Mengerjakan sebuah PBI | `docs/standar/02-pbi.md` (cari kode PBI-nya) |
| Menyentuh database, model, query | `docs/standar/03-skema-database.md` |
| Menulis controller, service, view, route | `docs/standar/04-konvensi-kode.md` |
| Commit, branch, pull request | `docs/standar/05-alur-git-dan-jira.md` |
| Menulis test | `docs/standar/06-testing.md` |

## 3. Aturan wajib

1. **Hanya ada tiga peran: `pasien`, `dokter`, `admin`.** Tidak ada peran "petugas" terpisah. Semua tugas loket (input walk-in, panggil, ubah status antrean) dikerjakan oleh `admin`. Jangan membuat peran perawat, apoteker, atau petugas.
2. **Pakai kode PBI dari `02-pbi.md`.** Jangan memakai nomor PBI dari versi lama proposal (misalnya PBI-21, KF07 Perawat, atau Pelayanan Farmasi). Itu sudah tidak berlaku.
3. **Data per cabang.** Poli, dokter, jadwal praktik, layanan, dan antrean milik satu cabang. Admin hanya boleh melihat dan mengubah data cabangnya sendiri. Pembatasan ini dikerjakan oleh trait `BelongsToCabang`, bukan dengan `where('cabang_id', ...)` manual di setiap query. Lihat `04-konvensi-kode.md`.
4. **Jadwal praktik dan kuota melekat ke dokter**, bukan ke poli.
5. **Status antrean berubah karena aksi admin**, kecuali status awal `MENUNGGU` yang dibuat sistem saat antrean dibuat. Jangan menulis kode yang mengubah status secara otomatis berdasarkan waktu.
6. **Status antrean disimpan di tabel referensi `status_antreans`**, bukan string bebas atau kolom enum database. Bandingkan status lewat konstanta di model `StatusAntrean`, jangan menulis string `'MENUNGGU'` di banyak tempat.
7. **Aturan bisnis ditaruh di Service** (`app/Services`), bukan di controller atau view.
8. **Jangan mengubah migration milik orang lain.** Migration dipegang Galih. Kalau butuh kolom atau tabel baru, tulis usulan di pull request atau ke Galih, jangan membuat migration sendiri tanpa kesepakatan.
9. **Setiap PBI wajib punya test**: minimal satu skenario normal dan satu skenario exception, dengan Laravel Dusk. Lihat `06-testing.md`.
10. **Jangan mengarang data klinik.** Jumlah pasien, nama dokter asli, tarif asli, dan data pasien tidak boleh dibuat seolah nyata. Seeder hanya berisi data contoh yang jelas fiktif (misalnya "dr. Contoh Satu").
11. **Tidak ada integrasi eksternal** ke BPJS (P-Care, Mobile JKN), WhatsApp, SMS, atau email. Notifikasi hanya di dalam aplikasi. Pembayaran memakai simulasi di dalam aplikasi.
12. **Bootstrap 5, bukan Tailwind.** Jangan menambahkan class Tailwind atau library CSS lain.
13. **Kalau ragu, tanya.** Kalau sesuatu tercantum di bagian "Keputusan terbuka" di `01-scope-dan-keputusan.md`, jangan memutuskannya sendiri. Sebutkan ke pengguna bahwa itu belum diputuskan.

## 4. Gaya

- Bahasa kode: nama domain dalam Bahasa Indonesia (`Antrean`, `Poli`, `JadwalPraktik`), istilah teknis tetap Bahasa Inggris (`Controller`, `Service`, `store`, `index`).
- Pesan ke pengguna aplikasi dalam Bahasa Indonesia baku yang singkat.
- Komentar kode secukupnya, dalam Bahasa Indonesia.
- Format kode dengan Laravel Pint (`./vendor/bin/pint`) sebelum commit.
