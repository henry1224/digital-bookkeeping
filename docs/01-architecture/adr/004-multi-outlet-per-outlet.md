# ADR-004: Multi-Outlet Per-Outlet Penuh

## Status

Accepted

## Konteks

Bisnis multi-outlet F&B (Balikpapan A, B, Central Kitchen). Perlu keputusan apakah bank, stock,
COA, dan report dikelola per-outlet atau tersentralisasi. Menentukan struktur tabel dan report.

## Keputusan

Per-outlet penuh:

1. **Bank/kas**: tiap outlet punya rekening & saldo sendiri (`bank_accounts.outlet_id`, `bank_transactions.outlet_id`).
2. **Stock**: saldo & mutasi per outlet (`stock_balances`, `stock_movements` wajib `outlet_id`; unik `outlet_id + item_id`).
3. **COA**: COA master bersama, tapi saldo & posting per outlet (journal & entries ber-`outlet_id`).
4. **Report**: Laba Rugi/Neraca/stock per outlet DAN konsolidasi (outlet sebagai dimensi + agregat).
5. Semua tabel transaksi wajib `outlet_id`. `period_closings.outlet_id` nullable = konsolidasi.

## Konsekuensi

- Kontrol per-cabang kuat; owner/management lihat konsolidasi.
- Query report wajib scope `outlet_id` (RBAC outlet-scoped role) atau agregat lintas outlet.
- Transfer antar outlet (Fase 2) membentuk mutasi di dua outlet — dasar AR/AP Intra Outlet.
- Beban indexing lebih besar: index komposit `(outlet_id, tanggal)` pada tabel transaksi.

## Alternatif Ditolak

- **Tersentralisasi**: outlet hanya label laporan. Ditolak — kontrol kas/stock per cabang lemah.
- **Hybrid (bank sentral, stock per-outlet)**: ditolak demi konsistensi & audit kas per cabang.
