# Progress Tracker

Dokumen ini menjadi papan status resmi proyek Digital Bookkeeping. Update setiap selesai branch/tugas agar progres, gap, dan keputusan berikutnya terlihat jelas.

## Legend Status

| Status | Arti |
|---|---|
| `TODO` | Belum mulai. |
| `IN_PROGRESS` | Sedang dikerjakan di branch aktif. |
| `DONE` | Selesai, sudah dites, dan sudah masuk commit. |
| `BLOCKED` | Menunggu keputusan/data/akses sebelum bisa lanjut. |
| `DEFERRED` | Sengaja ditunda ke fase berikutnya. |

## Ringkasan Saat Ini

| Item | Nilai |
|---|---|
| Branch aktif | `features/phase-1/accounting-foundation` |
| Commit terakhir | `f5889ca feat(phase-1): add accounting foundation and Indonesian UI standard` |
| Base branch standar | `main` |
| Remote | `origin = https://github.com/henry1224/digital-bookkeeping.git` |
| CI terakhir | Hijau, dikonfirmasi user: 43 tests, 146 assertions |
| Bahasa UI | Bahasa Indonesia untuk semua client-facing copy |

## MVP Roadmap Status

| Area | Status | Scope | Bukti/Commit | Catatan |
|---|---|---|---|---|
| Git workflow standar | DONE | Base `main`, branch `features/phase-*`, remote `origin` | `f5889ca` | Mengikuti standar `perumda-eproc`. |
| Standar Bahasa Indonesia UI | DONE | UI copy, validasi, alert, notifikasi, label DB user-facing | `f5889ca` | Backend/internal boleh English jika tidak tampil ke client. |
| Auth scaffold | DONE | Login, password reset, settings auth existing | Existing + `f5889ca` | Register dinonaktifkan dari flow utama. |
| Login branding | DONE | Brand panel, layout, card kanan tanpa logo, shadow tipis | `f5889ca` | Perlu visual regression manual setelah deploy. |
| Foundation schema | DONE | Master data + accounting foundation migrations | `f5889ca` | Backend-only, belum UI CRUD. |
| Master Data baseline | DONE | Outlets, outlet config, item groups, UOM, items, suppliers, bank accounts | `f5889ca` | Seeder referensi awal tersedia. |
| Chart of Accounts | DONE | COA awal F&B multi-outlet | `f5889ca` | Nama akun user-facing sudah Bahasa Indonesia. |
| Posting rules baseline | DONE | Rule dasar daily sale, PB1, receiving, payment, stock, manual journal | `f5889ca` | Runtime mapping transaksi belum dibuat. |
| Journal engine | DONE | `CreateBalancedJournal`, debit=credit guard, audit log | `f5889ca` | Hitung uang pakai integer cents, bukan float. |
| Audit log baseline | DONE | Table + model + journal audit write | `f5889ca` | Policy append-only belum dibuat. |
| RBAC | TODO | Roles, permissions, matrix, middleware/policy | - | Seed-reference sudah ada di docs. |
| Outlet scoping | TODO | User-outlet access, policy/scope query | - | Perlu desain data assignment user-outlet. |
| File upload dasar | TODO | Storage config, upload validation, audit | - | Masuk foundation lanjutan bila modul dokumen butuh. |
| Master Data UI | TODO | CRUD outlets, accounts, items, suppliers, bank accounts | - | Setelah RBAC/outlet scoping minimal. |
| Daily Sales | TODO | Input/import ringkasan penjualan harian + journal | - | Kandidat modul transaksi pertama. |
| Bank Book | TODO | Bank transactions, running balance, manual bank entry | - | Depend ke bank_accounts + journal. |
| Payment Request | TODO | Supplier payment + non-stock expense | - | Butuh approval matrix. |
| Payment Approval/Execution | TODO | Approve/reject/pay + bank + journal | - | Guard creator ≠ approver. |
| Purchase Request | TODO | Outlet request → PO | - | Masuk MVP sesuai keputusan scope. |
| Purchase Order | TODO | PO stock + approval | - | Depend suppliers/items/outlets. |
| Receiving | TODO | Receiving + stock movement + AP journal | - | Butuh stock engine. |
| Stock Balance/Movement | TODO | Moving average, stock ledger | - | Core logistics. |
| Ingredient Usage | TODO | Pemakaian bahan harian + HPP journal | - | Untuk HPP aktual. |
| Stock Opname | TODO | Count + review | - | Depend stock balance. |
| Stock Adjustment | TODO | Adjustment + approval + journal | - | Financial flow; feature test wajib. |
| Recipe | TODO | Standard recipe/menu mapping | - | Dibutuhkan Flash Cost. |
| Period Closing | TODO | Checklist closing + immutable period | - | Guard closed-period wajib lintas modul. |
| Period Reopen | TODO | Reopen flow dengan approval | - | Hanya lewat approval. |
| Trial Balance | TODO | Report dari posted journal | - | Depend journal core. |
| Laba Rugi | TODO | Report revenue/cogs/expense | - | Tujuan utama MVP. |
| Neraca | TODO | Report aset/liabilitas/ekuitas | - | Tujuan utama MVP. |
| Cashflow support | TODO | Analitik kas/bank | - | Pendukung. |
| Inventory valuation | TODO | Valuasi stok closing/live | - | Depend stock snapshots. |
| Flash Cost | TODO | Estimasi HPP dari resep vs sales/usage | - | MVP sesuai keputusan scope. |
| Reconcile HPP | TODO | Rekonsiliasi HPP closing stock | - | MVP sesuai keputusan scope. |
| Export reports | TODO | Export table/report | - | Setelah report stabil. |

## Fase 2 / Deferred

| Area | Status | Catatan |
|---|---|---|
| Customer/AR lifecycle | DEFERRED | Fase 2. |
| Aging AR | DEFERRED | Fase 2. |
| Aging AP penuh | DEFERRED | Fase 2, meski visibility AP MVP. |
| AR/AP intra-outlet | DEFERRED | Fase 2. |
| Fixed Asset + depresiasi | DEFERRED | Fase 2. |
| Manufacturing Central Kitchen | DEFERRED | Fase 2; CK di MVP hanya outlet/storage. |
| Payroll | DEFERRED | Di luar sistem; masuk via jurnal manual. |

## Fase 3 / Deferred

| Area | Status | Catatan |
|---|---|---|
| Budgeting vs Actual | DEFERRED | Fase 3. |
| Bank reconciliation API | DEFERRED | Fase 3. |
| Dashboard analitik lanjutan | DEFERRED | Fase 3. |
| Full tax modules PPN/PPh/e-Faktur/e-Bupot | DEFERRED | Fase 3. PB1 masuk MVP. |

## Checklist Definition of Done per Modul

Gunakan checklist ini setiap modul baru.

| Checklist | Wajib? | Catatan |
|---|---:|---|
| Scope modul jelas di docs | Ya | Update docs modul bila ada perubahan perilaku. |
| Migration dibuat | Ya | Semua schema lewat migration. |
| Model + casts dibuat | Ya | Money/date/json wajib cast sesuai kebutuhan. |
| Seeder/reference data idempotent | Jika ada referensi | Pakai `updateOrCreate`. Tidak seed transaksi produksi. |
| Form Request | Jika ada HTTP write | Validasi trust boundary. |
| Action | Jika mengubah state | Business logic tidak di controller. |
| DB transaction | Jika multi-table write | Wajib untuk transaksi finansial. |
| Policy/permission check | Jika ada UI/route | RBAC/outlet scope. |
| Audit log | Jika aksi penting | Posting, approval, edit, delete/cancel, reopen. |
| Closed-period guard | Jika menyentuh tanggal/periode | Wajib untuk financial/stock flow. |
| Journal posting balanced | Jika posting | debit = credit, tidak pakai float. |
| UI Bahasa Indonesia | Ya | Semua copy visible user. |
| Error/validation Bahasa Indonesia | Ya | `lang/id` atau custom message Indonesia. |
| Feature test | Ya untuk financial flow | Minimal satu flow sukses + negative guard penting. |
| CI hijau | Ya | `composer ci:check`. |

## Progress Detail per Lapisan

### Foundation

| Sub-item | Status | Catatan |
|---|---|---|
| Auth scaffold | DONE | Existing Laravel/Fortify/Inertia. |
| Login UI polish | DONE | Brand Digital Bookkeeping. |
| Git workflow | DONE | Standar `main`/`origin`/`features`. |
| Bahasa Indonesia standard | DONE | Docs + locale + baseline auth UI. |
| RBAC | TODO | Belum schema/seed. |
| Outlet scoping | TODO | Belum user-outlet mapping. |
| Audit log | DONE | Table/model + journal create log. |
| File upload dasar | TODO | Belum. |

### Master Data

| Sub-item | Status | Catatan |
|---|---|---|
| Outlets | DONE | Schema/model/seeder. UI belum. |
| Outlet configs | DONE | PB1 baseline. UI belum. |
| Accounts/COA | DONE | Schema/model/seeder. UI belum. |
| Item groups | DONE | Schema/model/seeder. UI belum. |
| UOM | DONE | Schema/model/seeder. UI belum. |
| Items | DONE | Schema/model. Seeder item transaksi belum. UI belum. |
| Suppliers | DONE | Schema/model. UI belum. |
| Bank accounts | DONE | Schema/model. UI belum. |

### Accounting Core

| Sub-item | Status | Catatan |
|---|---|---|
| Journals | DONE | Schema/model. |
| Journal entries | DONE | Schema/model. |
| Balance guard | DONE | `CreateBalancedJournal`. |
| Posting rules | DONE | Schema/model/seeder. |
| Ledger | TODO | Belum. |
| Trial balance | TODO | Belum. |
| Period closing | TODO | Belum. |
| Period reopen | TODO | Belum. |

### Finance

| Sub-item | Status | Catatan |
|---|---|---|
| Daily sales | TODO | Kandidat berikutnya. |
| Bank transactions | TODO | Belum. |
| Payment requests | TODO | Belum. |
| Purchase Non Stock | TODO | Reuse `payment_requests` + `expense_account_id`. |
| Payment approvals | TODO | Belum. |
| Payment execution | TODO | Belum. |
| AP visibility | TODO | Belum. |

### Logistics

| Sub-item | Status | Catatan |
|---|---|---|
| Purchase requests | TODO | Belum. |
| Purchase orders | TODO | Belum. |
| Receiving | TODO | Belum. |
| Stock balances | TODO | Belum. |
| Stock movements | TODO | Belum. |
| Ingredient usage | TODO | Belum. |
| Stock opname | TODO | Belum. |
| Stock adjustment | TODO | Belum. |
| Recipe | TODO | Belum. |

### Reports

| Sub-item | Status | Catatan |
|---|---|---|
| Laba Rugi | TODO | Tujuan utama. |
| Neraca | TODO | Tujuan utama. |
| Cashflow support | TODO | Pendukung. |
| Inventory valuation | TODO | Pendukung. |
| Flash Cost | TODO | MVP. |
| Reconcile HPP | TODO | MVP. |
| Export | TODO | Belum. |

## Checklist Sign-Off Bisnis

| Stakeholder | Status | Scope sign-off |
|---|---|---|
| Finance | TODO | Kas, bank, payment request, purchase non-stock. |
| Operasional | TODO | PO, receiving, stock opname, adjustment, recipe. |
| Accounting | TODO | COA, posting rule, journal, closing, Laba Rugi, Neraca. |
| Management | TODO | Scope MVP vs Fase 2/Fase 3. |
| IT | TODO | RBAC, audit log, file upload, outlet scoping. |

## Cara Update Dokumen Ini

1. Update status setiap task selesai dan sudah dites.
2. Isi `Bukti/Commit` dengan hash commit atau branch aktif.
3. Jika scope berubah, update juga dokumen modul terkait.
4. Jangan tandai `DONE` bila belum ada test/CI untuk modul non-trivial.
5. Jika tertunda karena keputusan bisnis/akses/data, pakai `BLOCKED` dan tulis penyebab singkat.
