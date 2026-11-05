# 05. Alur Git dan Jira

## Branch

| Branch | Fungsi | Siapa yang boleh merge |
|---|---|---|
| `main` | Versi stabil di akhir tiap sprint, yang didemokan | Galih |
| `develop` | Gabungan pekerjaan semua anggota selama sprint | Galih, lewat pull request |
| `<Nama>_Branch` | Branch kerja masing-masing anggota | Pemilik branch |

Branch anggota yang sudah ada: `Galih_Branch`, `Nara_Branch`, `Ghassan_Branch`, `Hanif_branch`, `Nesha_Branch`, `Dika_Branch`, `Agustino_Branch`. Pakai yang sudah ada, jangan membuat branch baru dengan nama lain.

Saat ini semua branch anggota masih tertinggal dari `main`. Sebelum mulai coding, sinkronkan dulu:

```bash
git checkout Nara_Branch
git fetch origin
git merge origin/develop
```

## Alur kerja satu PBI

1. Pastikan issue Jira PBI-mu sudah ada dan berstatus **In Progress**.
2. Sinkronkan branch-mu dengan `develop` (perintah di atas).
3. Kerjakan PBI, commit sesering mungkin dengan format di bawah.
4. Pastikan `php artisan test` dan `php artisan dusk` lulus.
5. Commit penutup yang menyebut key issue (lihat format resolve).
6. Push ke branch-mu, lalu buka pull request dari `<Nama>_Branch` ke `develop`.
7. Pindahkan issue ke **In Review**, lampirkan link PR, prompt AI, dan screenshot hasil test.
8. Galih me-review dan merge. Setelah merge, issue dipindah ke **Done**.

Di akhir sprint, Galih membuat PR dari `develop` ke `main`.

## Format commit

```
SPK-<nomor> <kata kerja> <apa yang dikerjakan>
```

Contoh:

```
SPK-12 tambah AntreanService untuk validasi kuota
SPK-12 tambah view form ambil antrean
SPK-12 tambah test Dusk skenario kuota penuh
```

- Satu commit untuk satu perubahan yang utuh. Hindari commit raksasa berisi semua pekerjaan.
- Kata kerja dalam Bahasa Indonesia: tambah, ubah, perbaiki, hapus, rapikan.
- Commit yang tidak terkait issue (misalnya perubahan standar) memakai awalan `STANDAR:` atau `SETUP:`.

**Commit penutup (resolve).** Asprak mensyaratkan commit resolve yang menyebut nomor issue. Format persisnya mengikuti arahan asprak. Bila memakai Jira Smart Commits, bentuknya:

```
SPK-12 #resolve PBI09 Membuat Antrean Online selesai
```

Smart Commits hanya berjalan bila GitHub sudah terhubung ke Jira dan nama transisi di workflow Jira cocok. Galih perlu memastikan ini sekali.

## Pull request

Judul: `SPK-<nomor> PBI<kode> <nama PBI>`, misalnya `SPK-12 PBI09 Membuat Antrean Online`.

Isi deskripsi:

```markdown
## PBI
PBI09 Membuat Antrean Online (SPK-12)

## Yang dikerjakan
- ...

## Cara mengetes manual
1. Login sebagai pasien contoh
2. ...

## Test
- [ ] php artisan test lulus
- [ ] php artisan dusk lulus
- Skenario normal: ...
- Skenario exception: ...

## Catatan
Keputusan terbuka yang disentuh, perubahan skema yang dibutuhkan, dll.
```

## Pemetaan PBI ke Jira

Isi kolom **Jira** di `02-pbi.md` begitu issue dibuat. Issue Sprint 1 versi awal (`SPK-3` sampai `SPK-8`) dibuat sebelum daftar PBI dirombak, jadi nomornya belum tentu sama dengan PBI yang baru.

## Larangan

- Push langsung ke `main` atau `develop`.
- Mengerjakan PBI orang lain tanpa koordinasi.
- `git push --force` ke branch selain milik sendiri.
- Commit file `.env`, `vendor/`, `node_modules/`, atau file hasil build.
- Mengubah migration, `routes/web.php`, atau `layouts/app.blade.php` tanpa memberi tahu Galih.
