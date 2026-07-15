# CLAUDE.md — Instruksi Proyek Digital Bookkeeping

## Proyek

Digital Bookkeeping — web backoffice untuk kontrol keuangan, inventory, dan akuntansi multi-outlet.

## Stack

- Laravel versi terbaru
- PHP versi stable terbaru yang didukung Laravel terbaru
- Inertia.js
- Vue 3 dengan TypeScript
- PostgreSQL
- Tailwind CSS
- Pest/PHPUnit untuk backend tests
- Vitest untuk frontend unit tests
- Playwright untuk E2E critical paths

## Aturan Scope

1. Bangun backoffice web saja.
2. Jangan bangun pengganti POS.
3. Jangan bangun mobile app.
4. POS existing tetap di luar sistem; aplikasi mengimpor atau mencatat manual ringkasan penjualan harian.
5. Laporan keuangan adalah tujuan utama: Laba Rugi dan Neraca.
6. Cashflow dan inventory report adalah analitik pendukung.
7. Setiap transaksi keuangan harus dijurnal atau ditandai eksplisit sebagai non-posting.
8. Setiap posting journal harus balance: total debit = total credit.
9. Periode tertutup immutable kecuali lewat reopen flow yang disetujui.
10. Setiap aksi penting harus membuat audit log.

## Keputusan Fondasi MVP

1. Primary key default memakai `BIGINT` auto-increment.
2. Nilai uang disimpan sebagai `DECIMAL(18,2)`.
3. Jangan pernah memakai float untuk uang.
4. Costing inventory MVP memakai moving average.
5. Manufacturing tidak termasuk MVP.
6. Central Kitchen pada MVP hanya outlet/storage, bukan modul produksi.
7. PB1/tax dikonfigurasi per outlet.
8. Approval matrix adalah seed default dan harus bisa dikonfigurasi.

## Bahasa UI dan Validasi

- Semua teks yang terlihat client/user wajib Bahasa Indonesia: label form, tombol, menu, judul, empty state, alert, toast, notifikasi, email, dan pesan error.
- Data referensi dari database yang ditampilkan ke user (nama role, status, tipe transaksi, kategori, report label) wajib disimpan/ditampilkan dalam Bahasa Indonesia. Kode internal boleh Bahasa Inggris/kode singkat.
- Pesan validasi Laravel wajib memakai locale `id`; custom validation message juga wajib Bahasa Indonesia.
- Backend code, class, method, table, column, enum value, dan internal exception boleh Bahasa Inggris selama tidak langsung tampil ke client.
- Jika error backend perlu dikirim ke UI, mapping dulu ke copy Bahasa Indonesia yang aman dibaca user.

## Konvensi Laravel

- Gunakan modul berbasis fitur di `app/Modules/<ModuleName>` saat modul mulai membesar.
- Gunakan Form Request untuk validasi.
- Gunakan Action untuk operasi bisnis yang mengubah state.
- Gunakan Policy untuk authorization.
- Gunakan Eloquent cast untuk Money/Date/JSON.
- Gunakan database transaction untuk write multi-table.
- Gunakan migration untuk semua perubahan schema.
- Gunakan seeder untuk data referensi/master awal.
- Setiap menu/modul UI yang dibuat harus sekaligus memiliki demo seeder dengan data dummy yang realistis seperti data operasional nyata, agar menu bisa langsung dilihat dan diuji.
- Demo seeder harus aman untuk development/testing, idempotent, dan tidak dipakai sebagai transaksi produksi.
- Jangan taruh business logic di controller.

## Konvensi Inertia + Vue

- Pages berada di `resources/js/Pages/<Module>/<Page>.vue`.
- UI kit standar: **shadcn-vue** (bukan shadcn/ui React). Primitif di `resources/js/Components/ui`, ditambah via `npx shadcn-vue@latest add`. Lihat `docs/04-design/design-system.md`.
- Shared components berada di `resources/js/Components`.
- Domain components berada di `resources/js/Features/<module>` — disusun dari primitif shadcn-vue, tidak menulis ulang.
- Gunakan TypeScript types yang dibuat/diselaraskan dari API resources.
- Form memakai Inertia `useForm` plus server-side validation errors.
- Table memakai pola filter/sort/pagination yang konsisten.

## Naming

- Database tables: snake_case plural (`journal_entries`).
- Model classes: singular PascalCase (`JournalEntry`).
- Enums: singular PascalCase (`PaymentStatus`).
- Routes: kebab-case (`payment-requests.index`).
- Vue components: PascalCase.
- Field uang berakhiran `_amount`.
- Foreign keys berakhiran `_id`.
- Timestamps disimpan UTC di database; ditampilkan WITA untuk user.

## Keamanan Finansial

- Jangan pernah memakai float untuk uang.
- Simpan uang sebagai `DECIMAL(18,2)`.
- Validasi debit = credit di application level dan database level.
- Bungkus journal creation dan source transaction update dalam satu database transaction.
- Audit before/after values untuk approval, posting, edit, delete.

## Minimum Testing

Setiap non-trivial financial flow butuh minimal satu feature test:

- Daily sales posting membuat balanced journal.
- Receiving membuat stock movement + AP journal.
- Payment approval dan execution memperbarui bank book + journal.
- Stock adjustment memperbarui stock + journal.
- Closing memblokir edit di closed period.

## Git Workflow

Ikuti `docs/07-operations/git-workflow.md`. Ringkas:

- Base branch `main`. Jangan commit langsung ke `main`.
- Ikuti standar branch seperti `perumda-eproc`: semua kerja lewat branch task dari `main` terbaru.
- Sebelum tugas baru: working tree wajib bersih; kalau ada perubahan menggantung, commit dulu di branch aktif.
- Branch per jenis kerja:
  - Dokumen: `docs/{nama-perbaikan}`
  - Fitur: `features/{phase}/{nama-menu}` (`{phase}` = `phase-1`/`phase-2`/`phase-3`)
  - Bug: `bug/{nama-bug}`
  - Perbaikan non-bug/style/config: `fix/{nama-perbaikan}` atau `style/{nama-perbaikan}`
- Siklus: branch dari `main` → commit → merge `--no-ff` ke `main` → push `origin main` → **branch tugas tetap disimpan** → branch baru dari `main` terbaru.
- Satu branch = satu tugas.

## Jangan Lakukan

- Jangan tambah dependency tanpa cek Laravel/native alternative.
- Jangan buat abstraction untuk satu implementation.
- Jangan skip validation di trust boundary.
- Jangan buat menu tanpa demo data realistis untuk development/testing.
- Jangan silently mutate closed-period data.
- Jangan hard-delete financial transactions.
