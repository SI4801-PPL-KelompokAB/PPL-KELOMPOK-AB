# 03. Skema Database

Spesifikasi ini adalah acuan migration. **Migration ditulis oleh Galih.** Anggota lain membaca file ini untuk menulis model dan query, tapi tidak membuat migration sendiri.

Database: MySQL 8, charset `utf8mb4`. Nama tabel mengikuti pluralisasi bawaan Laravel (`polis`, `antreans`) supaya model tidak perlu properti `$table`.

## Perubahan dari migration yang sudah ada di repo

Migration tanggal 27 Oktober 2026 dibuat sebelum keputusan jadwal per dokter dan data per cabang. Perlu disesuaikan:

| Migration lama | Tindakan |
|---|---|
| `create_polis_table` | Tambah `cabang_id`. Ubah unique `kode_poli` menjadi unique per cabang. Hapus unique `nama_poli` |
| `create_jadwal_operasionals_table` | Ganti dengan `jadwal_praktiks` yang melekat ke dokter |
| `create_kuota_antreans_table` | Hapus. Kuota pindah ke kolom `jadwal_praktiks.kuota_antrean` |
| Model `JamOperasional`, `KuotaAntrean` | Hapus, diganti `JadwalPraktik` |
| `create_users_table` | Sudah punya `no_hp` dan `is_active`. Ganti kolom `role` (string) dengan `role_id` (FK ke `roles`), lalu tambah `cabang_id` |

Karena belum ada data produksi, migration lama boleh langsung diubah lalu dijalankan ulang dengan `php artisan migrate:fresh --seed`.

## Diagram relasi (ringkas)

```
cabangs 1──* polis 1──* dokters 1──* jadwal_praktiks
   │                      │                │
   ├──* users (admin)     └── users (akun dokter, 1:1)
   ├──* layanans                           │
   └──* antreans *──1 jadwal_praktiks ─────┘
          │ *──1 users (pasien, nullable)
          │ *──1 status_antreans
          ├──* notifikasis
          ├──1 catatan_kunjungans
          └──* pembayarans

users (pasien) 1──1 profil_pasiens *──1 jenis_pasiens
users (pasien) 1──* konsultasis *──1 jadwal_praktiks
```

## Tabel referensi (diisi seeder, tidak ada CRUD)

### `roles`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| kode | varchar(20) unique | `pasien`, `dokter`, `admin` |
| nama | varchar(50) | |

### `jenis_pasiens`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| kode | varchar(20) unique | `BPJS`, `UMUM` |
| nama | varchar(50) | |

### `status_antreans`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| kode | varchar(20) unique | `MENUNGGU`, `DIPANGGIL`, `DILAYANI`, `SELESAI`, `DILEWATI` |
| nama | varchar(50) | Label tampilan, misalnya "Menunggu" |
| urutan | tinyint | Untuk sorting tampilan |

### `cabangs`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| kode | varchar(20) unique | `BJS`, `BLD`, `JLK`, `CKN` |
| nama | varchar(100) | "Bona Mitra Keluarga Bojongsoang", dan seterusnya |
| alamat | text nullable | Jangan diisi alamat karangan. Biarkan kosong bila tidak tahu |
| is_active | boolean default true | |

## Tabel utama

### `users`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| name | varchar(255) | |
| email | varchar(255) unique | |
| password | varchar(255) | Hash, lewat cast `hashed` |
| no_hp | varchar(20) nullable | |
| role_id | FK `roles` | |
| cabang_id | FK `cabangs` nullable | Wajib untuk `admin`. Kosong untuk `pasien`. Untuk `dokter` boleh diisi agar mudah difilter |
| is_active | boolean default true | |
| email_verified_at, remember_token, timestamps | | Bawaan Laravel |

### `profil_pasiens`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| user_id | FK `users` unique | Satu pasien satu profil |
| jenis_pasien_id | FK `jenis_pasiens` | |
| tanggal_lahir | date | |
| jenis_kelamin | char(1) | `L` atau `P` |
| alamat | text | |
| no_bpjs | varchar(20) nullable | Wajib bila jenis BPJS |
| timestamps | | |

### `polis`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| cabang_id | FK `cabangs` | |
| kode_poli | varchar(20) | Unique bersama `cabang_id` |
| nama_poli | varchar(100) | |
| deskripsi | text nullable | |
| is_active | boolean default true | |
| timestamps | | |

### `dokters`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| user_id | FK `users` unique | Akun ber-role `dokter` |
| cabang_id | FK `cabangs` | Harus sama dengan cabang poli |
| poli_id | FK `polis` | |
| nama | varchar(100) | Nama tampilan, misalnya "dr. Contoh Satu, Sp.A" |
| spesialisasi | varchar(100) nullable | |
| is_active | boolean default true | |
| timestamps | | |

### `jadwal_praktiks`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| cabang_id | FK `cabangs` | Disalin dari dokter, untuk scope cabang |
| dokter_id | FK `dokters` | |
| hari | tinyint | 1 = Senin sampai 7 = Minggu (ISO, sama dengan `Carbon::dayOfWeekIso`) |
| jam_mulai | time | |
| jam_selesai | time | Harus setelah `jam_mulai` |
| kuota_antrean | smallint unsigned | Minimal 1 |
| kuota_konsultasi | smallint unsigned default 0 | Lihat T05 |
| is_active | boolean default true | |
| timestamps | | |
| index | (`dokter_id`, `hari`) | |

### `antreans`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| cabang_id | FK `cabangs` | Disalin dari jadwal, untuk scope cabang |
| poli_id | FK `polis` | |
| dokter_id | FK `dokters` | |
| jadwal_praktik_id | FK `jadwal_praktiks` | |
| tanggal | date | Tanggal kunjungan |
| nomor | smallint unsigned | Berurutan per dokter per tanggal |
| tipe | varchar(10) | `ONLINE` atau `MANUAL` |
| status_antrean_id | FK `status_antreans` | |
| user_id | FK `users` nullable | Kosong untuk walk-in tanpa akun |
| nama_pasien_manual | varchar(100) nullable | Diisi untuk tipe `MANUAL` |
| dibuat_oleh | FK `users` nullable | Admin yang menginput walk-in |
| jumlah_panggil_ulang | tinyint default 0 | Untuk batas T03 |
| waktu_dipanggil | datetime nullable | |
| waktu_mulai | datetime nullable | |
| waktu_selesai | datetime nullable | |
| timestamps | | |
| unique | (`dokter_id`, `tanggal`, `nomor`) | Penjaga terakhir nomor ganda |
| index | (`cabang_id`, `tanggal`) dan (`user_id`, `tanggal`) | |

### `notifikasis`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| user_id | FK `users` | Penerima |
| antrean_id | FK `antreans` | |
| jenis | varchar(20) | `SISA_3` atau `DIPANGGIL` |
| pesan | varchar(255) | |
| dibaca_at | datetime nullable | |
| timestamps | | |

Sengaja tidak memakai tabel `notifications` bawaan Laravel, supaya strukturnya sama dengan ERD di proposal.

### `catatan_kunjungans`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| antrean_id | FK `antreans` unique | Satu kunjungan satu catatan |
| dokter_id | FK `dokters` | Penulis catatan (K13) |
| isi | text | Catatan ringkas, bukan rekam medis lengkap |
| timestamps | | |

### `konsultasis`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| nomor | varchar(20) unique | `KON-YYYYMMDD-XXXX` |
| cabang_id | FK `cabangs` | |
| user_id | FK `users` | Pasien |
| dokter_id | FK `dokters` | |
| jadwal_praktik_id | FK `jadwal_praktiks` | |
| tanggal | date | |
| keluhan | text | 10 sampai 1000 karakter |
| status | varchar(10) | `MENUNGGU` atau `DIBALAS` |
| balasan | text nullable | 10 sampai 2000 karakter |
| dibalas_at | datetime nullable | |
| timestamps | | |
| unique | (`user_id`, `jadwal_praktik_id`, `tanggal`) | Cegah konsultasi ganda |

### `layanans`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| cabang_id | FK `cabangs` | |
| nama | varchar(100) | |
| tarif_umum | decimal(12,2) | |
| tarif_bpjs | decimal(12,2) default 0 | Lihat T10 |
| is_active | boolean default true | |
| timestamps | | |

### `pembayarans`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| kode_referensi | varchar(30) unique | |
| cabang_id | FK `cabangs` | |
| user_id | FK `users` nullable | Kosong untuk walk-in tanpa akun |
| layanan_id | FK `layanans` | |
| antrean_id | FK `antreans` nullable | Salah satu dari `antrean_id` atau `konsultasi_id` terisi |
| konsultasi_id | FK `konsultasis` nullable | |
| jumlah | decimal(12,2) | Disalin dari tarif saat tagihan dibuat |
| metode | varchar(20) nullable | `SIMULASI` atau `TUNAI` (T08) |
| status | varchar(15) | `MENUNGGU`, `LUNAS`, `GAGAL`, `KEDALUWARSA` |
| dibayar_at | datetime nullable | |
| kedaluwarsa_at | datetime nullable | |
| timestamps | | |

## Aturan foreign key

- Semua FK ke master data memakai `restrictOnDelete()`. Master tidak pernah dihapus, hanya dinonaktifkan.
- FK `user_id` dan `dibuat_oleh` yang nullable memakai `nullOnDelete()`.

## Seeder

| Seeder | Isi |
|---|---|
| `RoleSeeder` | 3 role |
| `JenisPasienSeeder` | BPJS, UMUM |
| `StatusAntreanSeeder` | 5 status beserta urutannya |
| `CabangSeeder` | Bojongsoang, Baleendah, Jelekong, Cikoneng |
| `ReferensiSeeder` | Memanggil empat seeder di atas. Dipakai juga oleh test |
| `AdminSeeder` | Satu admin per cabang, misalnya `admin.bjs@bonamu.test` dengan password `password` |
| `DemoSeeder` (opsional, hanya lokal) | Poli, dokter, jadwal, layanan, dan pasien contoh dengan nama yang jelas fiktif |

Setiap model wajib punya factory, karena dipakai oleh test.
