# ADR-001 — Keputusan Fondasi MVP

## Status

Diterima untuk MVP.

## Konteks

Dokumentasi awal masih memiliki beberapa pilihan terbuka: tipe primary key, format penyimpanan uang, costing inventory, scope manufacturing, treatment Central Kitchen, PB1, dan approval matrix. Keputusan ini perlu dikunci sebelum implementasi Laravel agar migration, API, tests, dan laporan konsisten.

## Keputusan

1. Primary key default memakai `BIGINT` auto-increment.
2. Semua foreign key mengikuti primary key `BIGINT` dan memakai suffix `_id`.
3. Semua uang disimpan sebagai `DECIMAL(18,2)` di PostgreSQL.
4. Application layer tidak boleh memakai float untuk uang; gunakan string/decimal-safe handling.
5. Inventory costing MVP memakai moving average.
6. Manufacturing tidak masuk MVP.
7. Central Kitchen pada MVP adalah outlet/storage, bukan modul produksi.
8. PB1/tax dikonfigurasi per outlet: `pb1_enabled`, `pb1_rate`.
9. Approval matrix menjadi seed default yang bisa dikonfigurasi lewat admin/owner flow.
10. HPP MVP diposting dari actual ingredient usage. Recipe dipakai untuk standard cost comparison dan variance analysis.
11. Posted journal immutable. Koreksi memakai reversal journal atau adjustment journal.
12. Closed period immutable kecuali lewat owner-approved reopen flow.

## Konsekuensi

1. OpenAPI memakai `integer` untuk semua `*_id`.
2. Migration memakai `id()`/`foreignId()` style Laravel kecuali ada alasan kuat.
3. Money request/response boleh berupa string agar tidak kehilangan precision di frontend.
4. Average cost harus diupdate saat receiving dan stock adjustment bernilai.
5. Production stock movement type disimpan sebagai reserved phase 2, bukan flow aktif MVP.
6. Report Laba Rugi dan Neraca harus berbasis posted journals, bukan kalkulasi bebas dari source tables.

## Ditinjau Ulang Saat

Keputusan ini bisa ditinjau ulang saat:

1. Jumlah data menuntut distributed ID.
2. Ada kebutuhan production Central Kitchen penuh.
3. Ada integrasi pajak/bank langsung.
4. Ada kebutuhan multi-currency.
