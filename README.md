# Dokumentasi Digital Bookkeeping

Kumpulan dokumentasi siap-AI untuk aplikasi backoffice JJ Steak.

## Stack

- Backend: Laravel versi terbaru
- Frontend: Inertia.js + Vue 3 + TypeScript
- Database: PostgreSQL
- Styling: Tailwind CSS + komponen primitif
- Auth: gaya Laravel Breeze/Jetstream, disesuaikan untuk RBAC
- Tests: Pest/PHPUnit + Vitest + Playwright

## Status Keputusan Fondasi

- Primary key default: `BIGINT` auto-increment.
- Penyimpanan uang: `DECIMAL(18,2)`; tidak boleh memakai float.
- Costing inventory MVP: moving average.
- MVP tidak mencakup manufacturing/central kitchen production.
- Central Kitchen diperlakukan sebagai outlet/storage untuk inventory.
- Approval matrix menjadi seed default yang bisa dikonfigurasi.
- PB1/tax dikonfigurasi per outlet.
- HPP MVP diposting dari actual ingredient usage; recipe dipakai untuk pembanding standar.

## Urutan Baca untuk Pengembangan AI

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
