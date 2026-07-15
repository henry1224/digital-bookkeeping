# Digital Bookkeeping

Backoffice web untuk kontrol keuangan, inventory, dan akuntansi multi-outlet Digital Bookkeeping.

## Stack

- Backend: Laravel versi terbaru
- Frontend: Inertia.js + Vue 3 + TypeScript
- Database: PostgreSQL
- Styling: Tailwind CSS + shadcn-vue
- Auth: Laravel starter kit + Fortify, disesuaikan untuk RBAC
- Tests: PHPUnit + phpstan + eslint/typecheck (Pest/Playwright dapat ditambah saat flow stabil)

## Status Keputusan Fondasi

- Primary key default: `BIGINT` auto-increment.
- Penyimpanan uang: `DECIMAL(18,2)`; tidak boleh memakai float.
- Costing inventory MVP: moving average.
- MVP tidak mencakup manufacturing/central kitchen production.
- Central Kitchen diperlakukan sebagai outlet/storage untuk inventory.
- Approval matrix menjadi seed default yang bisa dikonfigurasi.
- PB1/tax dikonfigurasi per outlet.
- HPP MVP diposting dari actual ingredient usage; recipe dipakai untuk Flash Cost dan Reconcile HPP.
- Multi-outlet per-outlet penuh: bank, stock, COA saldo, dan report per outlet + konsolidasi.

## Setup Lokal

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# siapkan PostgreSQL database: digital_bookkeeping
php artisan migrate
npm run dev
php artisan serve
```

Default database: PostgreSQL (`digital_bookkeeping`). Jika pakai Herd/DB GUI, buat database dulu sebelum migrate.

## Urutan Baca untuk Pengembangan AI

Untuk tugas/menu spesifik, gunakan `docs/07-operations/document-reading-standard.md` agar hanya membaca dokumen yang relevan.

1. `CLAUDE.md` — instruksi proyek untuk AI
2. `docs/01-architecture/adr-001-keputusan-fondasi.md` — keputusan fondasi MVP
3. `docs/00-project/glossary.md` — bahasa bisnis
4. `docs/05-business-rules/global-rules.md` — aturan global
5. `docs/02-database/erd.md` — blueprint database
6. `docs/02-database/data-dictionary.md` — kamus data
7. `docs/02-database/chart-of-accounts.md` — struktur COA awal
8. `docs/04-design/design-system.md` — standar UI
9. `docs/06-security/rbac-matrix.md` — role dan permission
10. `docs/09-modules/overview.md` — urutan modul MVP
11. `docs/00-project/review-action-plan.md` — gap review Excel/screenshot dan checklist sign-off

## Scope

Aplikasi hanya backoffice web. Bukan pengganti POS. Bukan mobile app. POS existing tetap menjadi sumber export penjualan harian atau input ringkasan manual.
