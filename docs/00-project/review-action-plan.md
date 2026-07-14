# Review Action Plan

Patokan review: `jj-steak/proposal/ss` dan `Spesifikasi_Modul_Aplikasi_Keuangan.xlsx`.

## Status Kesesuaian

- Dokumen sudah mengikuti alur utama: POS → Penjualan Harian → Buku Bank/Jurnal → Buku Besar → Laporan.
- Relasi inti sudah cukup paralel: outlet, item, supplier, PO, receiving, stock movement, journal, closing.
- Kontrol finansial sudah lebih kuat dari spreadsheet: audit log, immutable posted journal, reversal, closed-period guard.
- Belum siap coding penuh sebelum keputusan scope di bawah dikunci.

## Keputusan Wajib Sebelum Development

| Topik | Keputusan Saat Ini | Aksi |
|---|---|---|
| Manufacturing | Fase 2, bukan MVP | Samakan semua dokumen dan proposal. |
| Customer/AR | Fase 2 | Keluarkan dari tabel inti MVP; sisakan reserved note. |
| Purchase Non Stock | MVP | Reuse `payment_requests` + `expense_account_id`; tidak perlu tabel baru dulu. |
| Flash Cost | DIKUNCI: MVP | F&B butuh monitoring HPP harian; report estimasi dari resep vs usage aktual. |
| Reconcile HPP | DIKUNCI: MVP | Rekonsiliasi HPP masuk rilis 1 bersama closing stock. |
| Multi Outlet | DIKUNCI: per-outlet penuh | Bank, stock, COA, report per outlet + konsolidasi. Semua tabel transaksi wajib `outlet_id`. |
| Tax | DIKUNCI: PB1 MVP | PB1 per-outlet di MVP; PPN/PPh/e-Faktur Fase 3. |
| Fixed Asset | DIKUNCI: Fase 2 | Modul aset tetap + penyusutan Fase 2. |
| Payroll | DIKUNCI: di luar sistem | Gaji pakai HR terpisah; masuk lewat jurnal manual. |
| RBAC | DIKUNCI: superset 12 role | Tambah Costcontrol & Finance staff dari Excel; pertahankan Outlet Manager & Auditor. Sudah di `rbac-matrix.md`. |
| Outlet Request | DIKUNCI: masuk MVP | `purchase_requests`/`purchase_request_lines` ditambahkan; PO bisa dari request atau open PO. |

## Gap Yang Harus Ditutup

1. `Purchase Non Stock`
   - Reuse `payment_requests` dengan `expense_account_id`.
   - Status minimal: draft → submitted → approved → paid/cancelled.
   - Posting: debit expense, credit bank saat payment execution.

2. `Customer/AR`
   - Fase 2: invoice/receivable lifecycle dan aging AR.

3. `Flash Cost` dan `Reconcile HPP`
   - Fase 2.
   - Definisikan sumber data: sales, ingredient usage, recipe standard, stock opname.
   - Definisikan output: variance qty, variance cost, waste/selisih stock.

4. `Approval`
   - Lengkapi state machine untuk PO, payment request, stock adjustment, manual journal, period reopen.
   - Tegaskan creator tidak boleh approve transaksi sendiri.

5. `Closing`
   - Jadikan checklist eksplisit: sales posted, payment pending reviewed, stock opname reviewed, journals balanced, bank reviewed.
   - SELESAI: checklist ada di `docs/04-design/user-flows/period-closing-flow.md`.

6. `Konsistensi skema` (ditutup)
   - `stock_usages`/`stock_usage_lines` ditambahkan — Ingredient Usage MVP punya dokumen sumber; selaras dgn glossary & journal source_type.
   - `bank_transactions` kini punya definisi kolom di data-dictionary.
   - `report_snapshots` (Trial Balance/Laba Rugi/Neraca) dan `inventory_snapshots` (Closing Stock) ditambahkan untuk freeze nilai saat closing.

7. `Aging AP` (Fase 2)
   - Excel taruh aging AP di Fase 2 tapi docs sebelumnya hanya aging AR. Kini keduanya di roadmap Fase 2.

8. `State machine` (ditutup)
   - Ditambahkan: `stock-adjustment.md`, `manual-journal.md`, `period-reopen.md` di `docs/05-business-rules/state-machines/`. Guard creator ≠ approver ditegaskan.

## MVP Yang Disarankan

- Masuk MVP: Master Data, Penjualan Harian, Buku Bank, Payment Request, PO Stock, Receiving, Stock Opname, Adjustment, Recipe standard, Journal, Closing, Laba Rugi, Neraca.
- Masuk MVP juga: Purchase Non Stock, karena biaya operasional harian pasti terjadi.
- Tunda: Manufacturing, Customer/AR, Flash Cost, Reconcile HPP, full tax, payroll, fixed asset, budgeting, bank API, dashboard analitik.

## Checklist Sign-Off

- [ ] Finance setuju alur kas, bank, payment request, dan Purchase Non Stock.
- [ ] Operasional setuju alur PO, receiving, stock opname, adjustment, dan recipe.
- [ ] Accounting setuju COA, posting rule, journal, closing, Laba Rugi, Neraca.
- [ ] Management setuju scope MVP vs Fase 2.
- [ ] IT setuju RBAC, audit log, file upload, dan outlet scoping.
