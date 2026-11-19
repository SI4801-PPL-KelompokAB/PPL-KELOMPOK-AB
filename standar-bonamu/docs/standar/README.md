# Standar Pengembangan Bonamu

Folder ini adalah sumber kebenaran tunggal untuk scope, PBI, database, kode, Git, dan testing Bonamu. Tujuannya supaya semua anggota, dan AI yang dipakai masing-masing anggota, bekerja dari informasi yang sama.

- **Pemilik:** Sitti Naurah Bauw (Nara)
- **Pemegang migration dan reviewer PR:** Galih Maha Putra (PM)
- **Versi:** 1.0, 3 November 2026

## Isi folder

| File | Isi | Siapa yang perlu baca |
|---|---|---|
| `../../AGENTS.md` | Aturan wajib untuk AI | Semua AI, semua anggota |
| `01-scope-dan-keputusan.md` | Scope, batasan, keputusan final, keputusan terbuka | Semua |
| `02-pbi.md` | 20 PBI, PIC, dependensi, aturan tiap PBI | Semua |
| `03-skema-database.md` | Spesifikasi tabel, kolom, relasi | Galih, dan siapa pun yang menulis query |
| `04-konvensi-kode.md` | Struktur folder, penamaan, scope cabang, Bootstrap | Semua yang coding |
| `05-alur-git-dan-jira.md` | Branch, commit, pull request, Jira | Semua |
| `06-testing.md` | Setup Dusk, aturan test per PBI | Semua yang coding |

## Cara memakai dengan AI

**Kalau AI-mu bisa membaca repo langsung** (Claude Code, Gemini CLI, Codex, Cursor, Copilot), cukup buka repo hasil clone. AI akan membaca `AGENTS.md`, `CLAUDE.md`, atau `GEMINI.md` di root secara otomatis.

**Kalau AI-mu versi chat biasa** (claude.ai, gemini.google.com, chatgpt.com), di awal setiap percakapan lampirkan:

1. `AGENTS.md`
2. `01-scope-dan-keputusan.md`
3. `02-pbi.md`
4. File yang sesuai tugas: `03` untuk database, `04` untuk coding, `06` untuk test

Lalu awali prompt dengan kode PBI, contohnya:

> Saya mengerjakan PBI09 Membuat Antrean Online. Ikuti AGENTS.md dan file standar yang saya lampirkan. Tolong buatkan AntreanService beserta Form Request untuk validasinya.

## Aturan memperbarui standar

1. Standar diubah hanya lewat pull request ke `develop` dengan judul `STANDAR: <ringkasan>`, dan disetujui Nara.
2. Setiap keputusan baru dari rapat ditulis ke `01-scope-dan-keputusan.md` paling lambat sehari setelah rapat.
3. Kalau kode dan standar berbeda, **standar yang diikuti**. Kalau standarnya yang salah, perbaiki standarnya dulu lewat PR, baru kodenya.
4. Spreadsheet PBI dan `02-pbi.md` harus selalu sama. Kalau salah satu diubah, yang lain ikut diubah di hari yang sama.

## Riwayat perubahan

| Versi | Tanggal | Perubahan |
|---|---|---|
| 1.0 | 3 Nov 2026 | Versi awal. Peran digabung jadi tiga, data per cabang, jadwal per dokter, dependensi PBI dibetulkan |
