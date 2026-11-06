# 01. Scope dan Keputusan

## Gambaran singkat

Bonamu adalah aplikasi web untuk Klinik Bona Mitra Keluarga yang punya beberapa cabang (Bojongsoang, Baleendah, Jelekong, Cikoneng). Pasien bisa mendaftar, melihat jadwal dokter, mengambil antrean online, memantau antrean, berkonsultasi dengan dokter, membayar, dan melihat riwayat kunjungan. Admin di setiap cabang mengelola data cabangnya dan menjalankan antrean harian, termasuk pasien yang datang langsung (walk-in).

## Peran

| Peran | Bisa melakukan | Terikat cabang? |
|---|---|---|
| `pasien` | Registrasi, profil, lihat jadwal, ambil antrean online, pantau antrean, terima notifikasi, konsultasi, bayar, lihat riwayat | Tidak. Bebas memilih cabang saat mengambil antrean |
| `dokter` | Menangani konsultasi online, menulis catatan kunjungan | Ya, lewat data dokter di cabangnya |
| `admin` | Master data cabangnya, akun dokter dan admin di cabangnya, input walk-in, panggil pasien, ubah status antrean | Ya, satu admin satu cabang |

Akun pasien dibuat lewat registrasi. Akun dokter dan admin dibuat oleh admin (PBI03). Akun admin pertama tiap cabang dibuat lewat seeder.

## Keputusan final

Hal-hal di bawah sudah disepakati. AI dan anggota tidak perlu menanyakannya lagi.

| No | Keputusan |
|---|---|
| K01 | Tiga peran saja: `pasien`, `dokter`, `admin`. Fungsi petugas loket digabung ke `admin` |
| K02 | Data dikelola per cabang. Admin per cabang hanya melihat data cabangnya |
| K03 | Jadwal praktik dan kuota antrean harian melekat ke dokter |
| K04 | Satu dokter terdaftar di satu poli di satu cabang |
| K05 | Jenis pasien: BPJS dan Umum (mengikuti spreadsheet PBI04) |
| K06 | Status antrean: `MENUNGGU`, `DIPANGGIL`, `DILAYANI`, `SELESAI`, `DILEWATI`, disimpan di tabel referensi |
| K07 | Status berubah karena aksi admin. Hanya `MENUNGGU` yang dibuat sistem saat antrean dibuat |
| K08 | Status `DILAYANI` dari klik tombol "Mulai Layani", tidak otomatis saat dipanggil |
| K09 | Antrean online dan walk-in ada di satu tabel dan satu urutan nomor, dibedakan oleh kolom `tipe` |
| K10 | Notifikasi hanya di dalam aplikasi, dua jenis: saat tersisa tiga antrean dan saat nomor dipanggil |
| K11 | Halaman posisi antrean dan daftar antrean diperbarui dengan polling setiap 10 detik, bukan websocket |
| K12 | Konsultasi online berupa teks: pasien mengirim keluhan, dokter membalas satu kali (sesuai Bab 4 proposal, Skenario 9 dan 10) |
| K13 | Catatan kunjungan ringkas diisi oleh **dokter**, bukan admin |
| K14 | Riwayat yang ditampilkan ke pasien adalah riwayat kunjungan dan catatan ringkas, bukan rekam medis elektronik lengkap |
| K15 | Pembayaran memakai simulasi di dalam aplikasi, tanpa payment gateway sungguhan |
| K16 | Status referensi dan nilai tetap (role, jenis pasien, status antrean, cabang) diisi lewat seeder, bukan PBI |
| K17 | Master data dinonaktifkan (`is_active = false`), tidak dihapus, karena dirujuk foreign key |
| K18 | Stack: Laravel 12, PHP 8.2, MySQL 8, Blade, Bootstrap 5, Laravel Dusk |
| K19 | Setiap anggota bekerja di branch masing-masing yang terhubung dengan issue Jira |
| K20 | Migration dipegang oleh Galih |

## Batasan (di luar scope)

- Integrasi BPJS Kesehatan (P-Care, antrean Mobile JKN)
- Notifikasi lewat WhatsApp, SMS, email, atau push notification
- Panggilan suara atau video untuk konsultasi
- Rekam medis elektronik lengkap, triase, diagnosis, resep, dan farmasi
- Payment gateway sungguhan
- Aplikasi mobile native
- Laporan dan dashboard statistik (tidak ada di spreadsheet PBI)

## Keputusan terbuka

Jangan diputuskan sendiri saat coding. Kalau PBI-mu menyentuh salah satu poin ini, bawa ke grup atau ke Galih dulu. Bila belum diputuskan sampai kamu harus mulai, pakai **default sementara** dan beri komentar `// TODO keputusan terbuka T0x` di kode.

| No | Pertanyaan | Default sementara | PBI terdampak |
|---|---|---|---|
| T01 | Cakupan "satu antrean aktif": per pasien per poli per hari (KNF-01 di Bab 3) atau per pasien per dokter per tanggal (Skenario 4 di Bab 4)? | Per pasien per poli per tanggal (mengikuti KNF-01) | PBI09, PBI13 |
| T02 | Status mana yang dihitung "aktif"? | `MENUNGGU`, `DIPANGGIL`, `DILAYANI`. `DILEWATI` tidak dihitung aktif | PBI09, PBI13 |
| T03 | Batas panggil ulang | Satu kali per antrean (Skenario 7 di Bab 4) | PBI16 |
| T04 | Definisi "tersisa tiga antrean" | Notifikasi dikirim saat jumlah antrean `MENUNGGU` di depan pasien pada dokter dan tanggal yang sama menjadi 3 atau kurang untuk pertama kalinya. Hanya dikirim sekali per antrean | PBI18 |
| T05 | Apakah konsultasi online memakai kuota yang sama dengan antrean tatap muka? | Kuota terpisah: kolom `kuota_konsultasi` di jadwal praktik | PBI10 |
| T06 | Apakah konsultasi online berbayar? | Ya, memakai layanan "Konsultasi Online" di tabel layanan | PBI10, PBI19 |
| T07 | Siapa membuat tagihan, dan kapan? | Sistem membuat tagihan saat antrean berstatus `SELESAI` atau saat konsultasi dibalas dokter | PBI16, PBI11, PBI19 |
| T08 | Bagaimana pasien walk-in tanpa akun membayar? | Dicatat admin sebagai pembayaran di loket (metode `TUNAI`) | PBI19 |
| T09 | Dokter menulis catatan kunjungan belum punya PBI sendiri | Usulkan PBI baru (PBI21) ke Galih. Sementara tabelnya sudah disiapkan di skema | PBI20 |
| T10 | Pasien BPJS: apakah tarifnya nol? | Kolom `tarif_bpjs` di layanan, boleh bernilai 0 | PBI12, PBI19 |
| T11 | Alur pull request: branch anggota ke `develop` lalu ke `main`, atau langsung ke `main`? | Ke `develop` dulu (lihat `05-alur-git-dan-jira.md`) | Semua |
| T12 | PIC untuk PBI yang masih kosong di spreadsheet | Lihat `02-pbi.md` | Semua |
