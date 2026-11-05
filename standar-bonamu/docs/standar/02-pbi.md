# 02. Product Backlog Item (PBI)

Sumber: spreadsheet **Daftar PBI Kelompok AB (NEW List PBI)**. Kode PBI di file ini sama persis dengan spreadsheet. Nomor PBI dari versi proposal lama (misalnya PBI-21, Perawat, Farmasi, Laporan) sudah tidak berlaku.

Kolom **Jira** diisi Galih begitu issue-nya dibuat, supaya commit dan branch bisa merujuk ke key `SPK-xx` yang benar.

## Ringkasan

| Kode | Sprint | Modul | Aktor | PBI | PIC | Dependensi | Jira |
|---|---|---|---|---|---|---|---|
| PBI01 | 1 | Akun | Pasien | Registrasi Pasien | Putra* | - | |
| PBI02 | 1 | Akun | Pasien, Dokter, Admin | Login dan Hak Akses Pengguna | Putra* | PBI01 | |
| PBI03 | 1 | Master data | Admin | Membuat Akun Dokter dan Admin | belum ditetapkan | PBI02 | |
| PBI04 | 1 | Akun | Pasien | Mengisi Profil Identitas Pasien | belum ditetapkan | PBI01 | |
| PBI05 | 1 | Master data | Admin | Mengelola Data Poli | belum ditetapkan | PBI02 | |
| PBI06 | 1 | Master data | Admin | Mengelola Data Dokter | belum ditetapkan | PBI02, PBI03, PBI05 | |
| PBI07 | 1 | Master data | Admin | Membuat dan Mengedit Jadwal Praktik Dokter | Nara | PBI05, PBI06 | |
| PBI08 | 1 | Pendaftaran | Pasien | Melihat Jadwal Dokter | belum ditetapkan | PBI07 | |
| PBI09 | 1 | Antrean | Pasien | Membuat Antrean Online | Nara | PBI04, PBI05, PBI07 | |
| PBI10 | 1 | Konsultasi | Pasien | Melakukan Konsultasi Online dengan Dokter | Nesha | PBI02, PBI06, PBI07 | |
| PBI11 | 1 | Konsultasi | Dokter | Menangani Konsultasi Online | Nesha | PBI10 | |
| PBI12 | 1 | Master data | Admin | Mengelola Layanan dan Tarif | belum ditetapkan | PBI02 | |
| PBI13 | 2 | Antrean | Admin | Input Antrean Manual (pasien walk-in) | belum ditetapkan | PBI09 | |
| PBI14 | 2 | Antrean | Admin | Melihat Daftar Antrean Harian | belum ditetapkan | PBI09, PBI13 | |
| PBI15 | 2 | Antrean | Admin | Memanggil Pasien | belum ditetapkan | PBI14 | |
| PBI16 | 2 | Antrean | Admin | Mengubah Status Antrean | belum ditetapkan | PBI15 | |
| PBI17 | 2 | Antrean | Pasien | Memantau Posisi dan Status Antrean | belum ditetapkan | PBI09 | |
| PBI18 | 2 | Notifikasi | Pasien | Menerima Notifikasi Antrean | belum ditetapkan | PBI15, PBI17 | |
| PBI19 | 2 | Pembayaran | Pasien | Melakukan Pembayaran Online | belum ditetapkan | PBI12 | |
| PBI20 | 2 | Riwayat | Pasien | Melihat Riwayat Medis Elektronik | belum ditetapkan | PBI09, PBI16 | |

\* "Putra" di spreadsheet perlu dikonfirmasi: Galih Maha Putra atau Agustino Saputra.

### Klaim PIC di proposal yang belum masuk spreadsheet

Tabel Tim Pengembang di proposal menulis pembagian berikut. Spreadsheet tetap acuannya, jadi klaim ini perlu dikonfirmasi lalu dipindahkan ke spreadsheet.

| Anggota | Klaim di proposal | Catatan |
|---|---|---|
| Maulana Mahardika | PBI-07 dan PBI-08 | PBI07 di spreadsheet milik Nara. PBI08 masih kosong |
| Hanif Irsaddul Fikri | PBI-03 dan PBI-04, "Identitas Pasien" | Identitas Pasien adalah PBI04. PBI03 adalah Akun Dokter dan Admin |
| Muhammad Ghassan Delshadi Albarr | PBI-05 dan PBI-12 | Tidak bentrok |
| Agustino Saputra | PB-13 | Tidak bentrok |

## Koreksi dependensi

Kolom Dependensi di spreadsheet masih memakai nomor lama, sebelum "Membuat Akun Petugas dan Dokter" disisipkan sebagai PBI03. Akibatnya semua rujukan ke PBI03 sampai PBI06 bergeser satu nomor (lama 03 menjadi 04, 04 menjadi 05, 05 menjadi 06, 06 menjadi 07). Tabel di atas sudah memakai versi yang dibetulkan.

| PBI | Di spreadsheet | Dibetulkan | Alasan |
|---|---|---|---|
| PBI06 | PBI02, PBI04 | PBI02, PBI03, PBI05 | Dokter butuh poli (PBI05) dan akun dokter (PBI03), bukan profil pasien |
| PBI07 | PBI04, PBI05 | PBI05, PBI06 | Jadwal butuh poli dan dokter |
| PBI08 | PBI06 | PBI07 | Yang dibaca pasien adalah jadwal praktik |
| PBI09 | PBI03, PBI04, PBI06 | PBI04, PBI05, PBI07 | Butuh profil pasien, poli, dan jadwal beserta kuota |
| PBI10 | PBI02, PBI05 | PBI02, PBI06, PBI07 | Konsultasi memilih dokter dan jadwal (Bab 4, Skenario 9) |

**Spreadsheet perlu disesuaikan dengan tabel ini.**

Satu perubahan nama juga mengikuti keputusan K01: PBI03 di spreadsheet bernama "Membuat Akun Petugas dan Dokter". Karena peran petugas sudah digabung ke admin, di sini ditulis "Membuat Akun Dokter dan Admin".

## Urutan kerja yang disarankan

Karena dependensinya, Sprint 1 tidak bisa dikerjakan semua sekaligus dari hari pertama.

1. **Galih:** migration dan seeder semua tabel di `03-skema-database.md`, plus factory tiap model. Fitur lain bergantung ke sini.
2. **Gelombang 1** (setelah migration masuk `develop`): PBI01, PBI02, PBI05, PBI12
3. **Gelombang 2:** PBI03, PBI04, PBI06
4. **Gelombang 3:** PBI07
5. **Gelombang 4:** PBI08, PBI09, PBI10, lalu PBI11

Sambil menunggu, anggota bisa menyiapkan view Blade, Form Request, dan skenario test PBI-nya.

## Detail per PBI

Format: deskripsi dari spreadsheet, lalu **aturan** yang wajib diikuti kode. Aturan diambil dari skenario use case Bab 4 proposal dan keputusan di `01-scope-dan-keputusan.md`.

### PBI01 Registrasi Pasien
Pasien membuat akun agar bisa memakai layanan klinik.
- Input: nama, email, password (dengan konfirmasi), nomor HP.
- Email unik. Password disimpan sebagai hash.
- Akun yang dibuat selalu ber-role `pasien`. Form registrasi tidak boleh bisa membuat role lain.
- Exception: email sudah terdaftar, format tidak valid.

### PBI02 Login dan Hak Akses Pengguna
Pengguna masuk sesuai role dan hanya melihat menu yang diizinkan.
- Setelah login, arahkan ke dashboard sesuai role: `/pasien`, `/dokter`, `/admin`.
- Akses ke route role lain ditolak dengan 403.
- Akun nonaktif tidak bisa login.
- Exception: email atau password salah, akun nonaktif, membuka URL role lain.

### PBI03 Membuat Akun Dokter dan Admin
Admin membuatkan akun untuk dokter dan admin lain di cabangnya.
- Admin hanya bisa membuat akun ber-role `dokter` atau `admin`, dan `cabang_id` otomatis diisi cabang admin yang membuat.
- Exception: email sudah dipakai.

### PBI04 Mengisi Profil Identitas Pasien
Pasien melengkapi data identitas dan jenis pasien (BPJS atau Umum).
- Input: tanggal lahir, jenis kelamin, alamat, jenis pasien, nomor BPJS.
- Nomor BPJS wajib bila jenis pasien BPJS, dan harus kosong bila Umum.
- Pasien tanpa profil lengkap tidak bisa mengambil antrean (dicek di PBI09).

### PBI05 Mengelola Data Poli
Admin menambah, mengubah, menonaktifkan, dan melihat daftar poli.
- Poli milik cabang admin. Kode poli unik per cabang.
- Tidak ada hapus, hanya nonaktifkan. Poli nonaktif tidak muncul di pilihan pasien.

### PBI06 Mengelola Data Dokter
Admin menambah, mengubah, menonaktifkan, dan melihat daftar dokter.
- Dokter terhubung ke satu akun ber-role `dokter` (dari PBI03) dan satu poli di cabang yang sama.
- Satu akun dokter hanya boleh terhubung ke satu data dokter.

### PBI07 Membuat dan Mengedit Jadwal Praktik Dokter
Admin mengatur hari, jam praktik, dan kuota antrean per dokter.
- Input: dokter, hari (1 = Senin sampai 7 = Minggu), jam mulai, jam selesai, kuota antrean, kuota konsultasi.
- Jam selesai harus setelah jam mulai.
- Jadwal satu dokter di hari yang sama tidak boleh bertabrakan jamnya.
- Kuota minimal 1.
- Jadwal dinonaktifkan, tidak dihapus.

### PBI08 Melihat Jadwal Dokter
Pasien melihat jadwal praktik dokter per poli.
- Pasien memilih cabang, lalu bisa menyaring per poli atau mencari nama dokter.
- Hanya jadwal aktif dari dokter dan poli yang aktif yang ditampilkan.
- Exception: tidak ada data, tampilkan pesan "Jadwal tidak ditemukan".

### PBI09 Membuat Antrean Online
Pasien memilih poli, dokter, dan tanggal lalu mendapat nomor antrean, dengan validasi kuota dan antrean aktif ganda.
- Urutan validasi: profil lengkap, lalu dokter punya jadwal aktif di hari dari tanggal tersebut, lalu tanggal tidak lewat, lalu kuota belum penuh, lalu pasien tidak punya antrean aktif (lihat T01 dan T02).
- Nomor antrean berurutan per dokter per tanggal, dimulai dari 1, dan sama dengan urutan antrean walk-in.
- Pembuatan nomor wajib di dalam transaksi database dengan `lockForUpdate`, supaya dua pasien yang menekan tombol bersamaan tidak mendapat nomor yang sama.
- Hasil: antrean `tipe = ONLINE`, status `MENUNGGU`.
- Exception: profil belum lengkap, dokter tidak praktik, tanggal sudah lewat, kuota penuh (pesan "Jadwal Penuh"), sudah punya antrean aktif (tampilkan nomor yang ada).

### PBI10 Melakukan Konsultasi Online dengan Dokter
Pasien mengirim keluhan dan berkonsultasi dengan dokter lewat aplikasi.
- Pasien memilih poli, dokter, jadwal, dan tanggal, lalu menulis keluhan 10 sampai 1000 karakter.
- Nomor konsultasi berformat `KON-YYYYMMDD-XXXX`.
- Validasi: tanggal tidak lewat, kuota konsultasi belum penuh (T05), tidak ada konsultasi ganda pada jadwal dan tanggal yang sama.
- Hasil: konsultasi berstatus `MENUNGGU`.

### PBI11 Menangani Konsultasi Online
Dokter melihat permintaan konsultasi dan membalasnya.
- Dokter hanya melihat konsultasi yang ditujukan kepadanya.
- Balasan 10 sampai 2000 karakter, hanya satu kali. Status menjadi `DIBALAS` dan waktu dibalas dicatat.
- Exception: membuka konsultasi dokter lain (403), balasan kedua ditolak.

### PBI12 Mengelola Layanan dan Tarif
Admin mengelola daftar layanan beserta tarifnya.
- Input: nama layanan, tarif umum, tarif BPJS (T10).
- Layanan milik cabang, dinonaktifkan dan tidak dihapus.

### PBI13 Input Antrean Manual (pasien walk-in)
Admin mencatat pasien yang datang langsung ke daftar antrean yang sama dengan antrean online.
- Input: poli, dokter, nama pasien (wajib), email (opsional, bila pasien sudah punya akun).
- Validasi jadwal dan kuota sama dengan PBI09, memakai service yang sama. Jangan menyalin logikanya.
- Bila email diisi dan terdaftar, cek antrean aktif pasien tersebut dan isi `user_id`.
- Hasil: antrean `tipe = MANUAL`, status `MENUNGGU`, `dibuat_oleh` berisi admin yang menginput.
- Exception: dokter tidak praktik, kuota penuh, pasien sudah punya antrean aktif, email diisi tapi tidak terdaftar.

### PBI14 Melihat Daftar Antrean Harian
Admin melihat seluruh antrean hari ini, online dan manual, dalam satu daftar.
- Hanya antrean di cabang admin. Bisa disaring per poli, dokter, dan status.
- Diurutkan berdasarkan nomor. Halaman memperbarui data setiap 10 detik.

### PBI15 Memanggil Pasien
Admin memanggil nomor antrean, status berubah menjadi `DIPANGGIL`.
- Hanya antrean berstatus `MENUNGGU`. Waktu dipanggil dicatat.
- Memicu notifikasi `DIPANGGIL` (PBI18) bila pasien punya akun.
- Exception: tidak ada antrean `MENUNGGU`.

### PBI16 Mengubah Status Antrean
Admin mengubah status lewat aksi mulai layani, selesai, lewati, dan panggil ulang.

| Aksi | Dari | Ke | Catatan |
|---|---|---|---|
| Panggil (PBI15) | `MENUNGGU` | `DIPANGGIL` | Catat `waktu_dipanggil` |
| Mulai Layani | `DIPANGGIL` | `DILAYANI` | Catat `waktu_mulai` |
| Selesai | `DILAYANI` | `SELESAI` | Catat `waktu_selesai`, butuh pop-up konfirmasi di UI |
| Lewati | `DIPANGGIL` | `DILEWATI` | |
| Panggil Ulang | `DILEWATI` | `DIPANGGIL` | Maksimal sekali (T03) |

- Transisi selain tabel di atas wajib ditolak oleh service, bukan hanya disembunyikan tombolnya.
- Setiap perubahan yang mengurangi antrean di depan pasien lain memicu pengecekan notifikasi `SISA_3` (PBI18).

### PBI17 Memantau Posisi dan Status Antrean
Pasien melihat posisi dan status antreannya secara langsung.
- Tampilkan nomor, jumlah antrean `MENUNGGU` di depan, nomor yang sedang dilayani, dan status.
- Polling setiap 10 detik.
- Exception: tidak punya antrean aktif (tampilkan tautan ke PBI09), status `DILEWATI` (tampilkan petunjuk menghubungi loket).

### PBI18 Menerima Notifikasi Antrean
Pasien diberi tahu saat tersisa tiga antrean dan saat nomornya dipanggil.
- Dua jenis: `SISA_3` (sekali per antrean, lihat T04) dan `DIPANGGIL` (setiap kali dipanggil, termasuk panggil ulang).
- Disimpan di tabel `notifikasis`, ditampilkan di aplikasi, dan bisa ditandai sudah dibaca.
- Antrean manual tanpa akun tidak mendapat notifikasi.

### PBI19 Melakukan Pembayaran Online
Pasien membayar layanan secara online.
- Tarif diambil dari layanan sesuai jenis pasien (umum atau BPJS).
- Pembayaran disimulasikan. Status: `MENUNGGU`, `LUNAS`, `GAGAL`, `KEDALUWARSA`. Kode referensi unik.
- Lihat T06, T07, T08 untuk pembuatan tagihan dan pembayaran walk-in.

### PBI20 Melihat Riwayat Medis Elektronik
Pasien melihat riwayat kunjungan dan catatan medis ringkas.
- Daftar antrean `SELESAI` milik pasien, terbaru di atas: tanggal, cabang, poli, dokter, nomor.
- Detail kunjungan menampilkan catatan ringkas yang ditulis dokter (K13, T09).
- Pasien hanya bisa melihat riwayat miliknya sendiri.
- Exception: belum ada kunjungan, catatan belum tersedia, membuka riwayat orang lain (403).
