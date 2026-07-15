# Standar Baca Dokumen per Tugas

Tujuan: agent/developer membaca dokumen yang relevan saja, bukan semua dokumen, agar kerja cepat tetapi tetap aman untuk data finansial multi-user.

## Wajib Dibaca untuk Semua Tugas

| Dokumen | Kapan dipakai |
|---|---|
| `CLAUDE.md` | Selalu. Instruksi proyek, scope, keamanan, testing. |
| `docs/00-project/progress-tracker.md` | Cek status modul, gap, dan update setelah selesai. |
| `docs/09-modules/overview.md` | Cek urutan MVP dan batas fase. |
| `docs/07-operations/git-workflow.md` | Cek branch dan aturan kerja. |

## Wajib Dibaca Saat Membuat Menu UI

| Dokumen | Kapan dipakai |
|---|---|
| `docs/04-design/design-system.md` | Semua menu/halaman UI. |
| `docs/06-security/rbac-matrix.md` | Semua route/menu yang butuh akses user. |
| PRD modul di `docs/09-modules/*/prd.md` | Pilih sesuai menu yang dibuat. |
| `docs/02-database/data-dictionary.md` | Jika menu membaca/menulis data. |
| `docs/02-database/seed-reference.md` | Jika membuat seeder/demo data. |

## Routing Dokumen per Domain

| Domain | Dokumen tambahan |
|---|---|
| Master Data | `docs/09-modules/master-data/prd.md`, `docs/02-database/seed-reference.md` |
| Finance | `docs/09-modules/finance/prd.md`, `docs/05-business-rules/financial-rules.md` |
| Accounting | `docs/09-modules/accounting/prd.md`, `docs/05-business-rules/financial-rules.md`, `docs/02-database/chart-of-accounts.md` |
| Logistik/Inventory | `docs/09-modules/logistik/prd.md`, `docs/05-business-rules/stock-rules.md` |
| Reporting | `docs/09-modules/reporting/prd.md`, `docs/05-business-rules/financial-rules.md` |
| POS/import/API | `docs/03-api/integration/pos-integration.md`, `docs/03-api/openapi.yaml` |

## Routing Dokumen per Risiko

| Jika tugas menyentuh | Baca juga |
|---|---|
| Approval/status flow | `docs/05-business-rules/approval-rules.md` dan state machine terkait di `docs/05-business-rules/state-machines/` |
| Periode transaksi | `docs/05-business-rules/financial-rules.md` dan state machine `period-closing`/`period-reopen` |
| Uang/jurnal | `docs/05-business-rules/financial-rules.md`, `docs/09-modules/accounting/prd.md` |
| Stock/HPP | `docs/05-business-rules/stock-rules.md`, `docs/01-architecture/adr/002-average-costing.md` |
| Audit/security | `docs/01-architecture/adr/003-audit-log-append-only.md`, `docs/06-security/threat-model.md` |
| Multi-outlet/user scope | `docs/01-architecture/adr/004-multi-outlet-per-outlet.md`, `docs/06-security/rbac-matrix.md` |
| Schema/migration | `docs/02-database/erd.md`, `docs/02-database/data-dictionary.md` |

## Definition of Ready

- [ ] Status menu dicek di `progress-tracker.md`.
- [ ] PRD modul terkait sudah dibaca.
- [ ] Data table/field terkait sudah dicek.
- [ ] Permission/menu access sudah dicek.
- [ ] Rule finansial/stock/approval terkait sudah dicek.
- [ ] Demo seeder diperlukan atau tidak sudah diputuskan.

## Definition of Done

- [ ] UI copy Bahasa Indonesia.
- [ ] Route terlindungi auth + policy/permission.
- [ ] Query data user dibatasi outlet/role jika relevan.
- [ ] Menu yang load data database memakai server-side pagination.
- [ ] Tabel besar punya index untuk filter, sort, foreign key, dan lookup utama.
- [ ] Approval/edit data sensitif memakai optimistic locking.
- [ ] Import/API/posting memakai idempotency key.
- [ ] Write multi-table dibungkus database transaction.
- [ ] Aksi penting menulis audit log.
- [ ] Financial/stock write memblokir closed period.
- [ ] Flow non-trivial punya feature test minimal.
- [ ] Demo seeder realistis tersedia untuk menu UI.
- [ ] `progress-tracker.md` di-update.

## Standar Produksi Wajib

| Area | Standar |
|---|---|
| Pagination | Setiap menu yang membaca list dari database wajib memakai server-side pagination. Jangan load semua row ke UI. |
| Indexing | Setiap tabel besar wajib punya index untuk kolom filter, sort, foreign key, nomor dokumen, tanggal transaksi, status, outlet, dan lookup unik. |
| Optimistic locking | Approval/edit data sensitif wajib memvalidasi versi data (`lock_version`/`updated_at`) agar perubahan user lain tidak tertimpa diam-diam. |
| Idempotency | Import, API write, dan posting transaksi wajib memakai idempotency key agar retry tidak membuat duplikasi transaksi/jurnal. |

## Checklist Query List Menu

- [ ] Pagination dilakukan di query backend.
- [ ] Filter/sort memakai kolom ber-index.
- [ ] Default sort stabil, biasanya `created_at desc, id desc` atau tanggal dokumen + id.
- [ ] Query membatasi outlet/role sebelum pagination.
- [ ] Tidak ada N+1 query pada kolom yang tampil.
