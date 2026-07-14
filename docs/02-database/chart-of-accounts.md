# Chart of Accounts Awal

COA ini adalah seed awal untuk F&B multi-outlet. Kode dapat disesuaikan sebelum go-live, tetapi struktur utama harus tetap mendukung Laba Rugi, Neraca, journal posting, dan konsolidasi.

## Aturan COA

1. `code` unik.
2. Parent account tidak postable.
3. Detail account yang dipakai journal harus `is_postable = true`.
4. Account tidak boleh dihapus jika pernah dipakai journal; nonaktifkan dengan `is_active = false`.
5. Perubahan nama/kode account penting harus masuk audit log.
6. Report mapping memakai `type` dan optional `report_group`.

## Struktur Minimal

| Code | Name | Type | Parent | Postable | Report Group |
|---|---|---|---|---:|---|
| 1-0000 | Aset | asset | null | false | balance_sheet |
| 1-1000 | Kas dan Bank | asset | 1-0000 | false | current_asset |
| 1-1100 | Kas Outlet | asset | 1-1000 | true | current_asset |
| 1-1200 | Bank Operasional | asset | 1-1000 | true | current_asset |
| 1-2000 | Piutang | asset | 1-0000 | false | current_asset |
| 1-2100 | Piutang Usaha | asset | 1-2000 | true | current_asset |
| 1-3000 | Inventory | asset | 1-0000 | false | current_asset |
| 1-3100 | Inventory Bahan Baku | asset | 1-3000 | true | current_asset |
| 1-3200 | Inventory Barang Jadi | asset | 1-3000 | true | current_asset |
| 2-0000 | Liabilitas | liability | null | false | balance_sheet |
| 2-1000 | Utang Usaha | liability | 2-0000 | true | current_liability |
| 2-2000 | Utang Pajak/PB1 | liability | 2-0000 | true | current_liability |
| 3-0000 | Ekuitas | equity | null | false | balance_sheet |
| 3-1000 | Modal Pemilik | equity | 3-0000 | true | equity |
| 3-2000 | Laba Ditahan | equity | 3-0000 | true | equity |
| 4-0000 | Revenue | revenue | null | false | profit_loss |
| 4-1000 | Penjualan Makanan | revenue | 4-0000 | true | revenue |
| 4-2000 | Penjualan Minuman | revenue | 4-0000 | true | revenue |
| 5-0000 | HPP | cogs | null | false | profit_loss |
| 5-1000 | HPP Bahan Baku | cogs | 5-0000 | true | cogs |
| 6-0000 | Expense | expense | null | false | profit_loss |
| 6-1000 | Beban Operasional Outlet | expense | 6-0000 | true | operating_expense |
| 6-2000 | Beban Administrasi | expense | 6-0000 | true | operating_expense |
| 6-3000 | Selisih Stock/Waste | expense | 6-0000 | true | operating_expense |

## Posting Rule Minimal

| Source Type | Debit | Credit | Catatan |
|---|---|---|---|
| daily_sale_cash | Kas/Bank payment method | Penjualan | Payment breakdown harus sama dengan net sales. |
| daily_sale_pb1 | Kas/Bank payment method | Utang Pajak/PB1 | Hanya jika `pb1_enabled = true`. |
| receiving | Inventory Bahan Baku | Utang Usaha | Menggunakan moving average. |
| payment_execution | Utang Usaha atau Expense | Bank Operasional | Payment request harus approved. |
| stock_usage | HPP Bahan Baku | Inventory Bahan Baku | Berdasarkan actual ingredient usage. |
| stock_adjustment_in | Inventory Bahan Baku | Selisih Stock/Waste | Setelah approval. |
| stock_adjustment_out | Selisih Stock/Waste | Inventory Bahan Baku | Setelah approval. |
| manual_journal | Sesuai line | Sesuai line | Wajib approval sebelum posted. |
