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
| Flash Cost | Fase 2 | Detail rumus disiapkan setelah usage dan stock opname stabil. |
| Reconcile HPP | Fase 2 | Detail input/output disiapkan setelah closing stock stabil. |
| Multi Outlet | Ada outlet scoping, policy bisnis belum tajam | Putuskan bank, stock, COA, report per outlet vs konsolidasi. |

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
