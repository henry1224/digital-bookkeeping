# User Flow: Period Closing

## Flow

1. Finance confirms all daily sales posted.
2. Warehouse confirms stock opname and adjustments completed.
3. System runs pre-closing checklist.
4. Logistics closes stock period.
5. Accounting reviews Trial Balance.
6. Accounting posts adjustment journals if needed.
7. Accounting closes accounting period.
8. System locks source transactions in that period.
9. System generates final Laba Rugi and Neraca.
10. Owner/Management can view final report.

## Pre-Closing Checklist (langkah 3)

Semua item wajib centang sebelum periode boleh ditutup:

- [ ] Semua daily sales periode ter-posting (tidak ada draft/submitted tertinggal).
- [ ] Payment request pending sudah direview (approved/rejected, tidak menggantung).
- [ ] Stock opname periode selesai direview.
- [ ] Semua stock adjustment ter-approve dan ter-posting.
- [ ] Semua journal periode balance (debit = credit).
- [ ] Buku bank direkonsiliasi/direview.
- [ ] Inventory snapshot (`inventory_snapshots`) dibuat saat Closing Stock.
- [ ] Report snapshot (`report_snapshots`: trial_balance, laba_rugi, neraca) dibuat saat Closing Laporan.

Urutan wajib: Closing Stock (Logistik) dulu → baru Closing Laporan (Accounting).

## Reopen Flow

1. Accounting requests reopen.
2. Owner approves reopen with reason.
3. System logs approval and marks period reopened.
4. Corrections are posted.
5. Period is closed again.
