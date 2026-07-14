# Seed Reference

Nilai referensi konkret untuk seeder awal (data master/referensi, bukan transaksi).
Semua nilai bisa dikonfigurasi & wajib dikonfirmasi JJ Steak sebelum go-live.

Sumber terkait:
- COA: `docs/02-database/chart-of-accounts.md`
- Posting rules: `chart-of-accounts.md` (bagian Posting Rule Minimal)
- Approval matrix: `docs/05-business-rules/approval-rules.md`
- RBAC: `docs/06-security/rbac-matrix.md`

## Roles (slug seed)

| Role | slug | Outlet-scoped |
|---|---|---|
| Owner | owner | no (konsolidasi) |
| Management | management | no |
| Finance Manager | finance-manager | no |
| Finance | finance | assigned |
| Accounting | accounting | no |
| Costcontrol | costcontrol | no |
| Purchasing Manager | purchasing-manager | no |
| Warehouse Manager | warehouse-manager | assigned |
| Outlet Manager | outlet-manager | assigned |
| Staff Outlet | staff-outlet | assigned |
| Admin IT | admin-it | no |
| Auditor/Read Only | auditor | no |

Permission slug memakai pola `<module>.<action>` dari `rbac-matrix.md`.

## Outlets

| code | name | outlet_type | pb1_enabled |
|---|---|---|---|
| BPN-A | Balikpapan A | outlet | true |
| BPN-B | Balikpapan B | outlet | false |
| CK-01 | Central Kitchen | central_kitchen | false |

Central Kitchen = storage/inventory pada MVP, bukan modul produksi.

## Unit of Measure

| code | name | base | faktor |
|---|---|---|---|
| KG | Kilogram | KG | 1 |
| GR | Gram | KG | 0.001 |
| L | Liter | L | 1 |
| ML | Mililiter | L | 0.001 |
| PCS | Pieces | PCS | 1 |
| DUS | Dus | PCS | (isi per item) |
| PORSI | Porsi | PORSI | 1 |

Konversi antar-satuan sumber selisih stock — validasi ketat saat input master item.

## Item Group (contoh, maks 2-3 level)

| code | name | parent | coa_mapping |
|---|---|---|---|
| RAW-MEAT | Daging | - | 1-3100 Inventory Bahan Baku |
| RAW-VEG | Sayur | - | 1-3100 |
| RAW-GROCERY | Grocery/Bumbu | - | 1-3100 |
| MENU-STEAK | Menu Steak | - | 4-1000 Penjualan Makanan |
| MENU-BEV | Menu Minuman | - | 4-2000 Penjualan Minuman |

## Approval Matrix

Lihat `docs/05-business-rules/approval-rules.md` — nilai threshold (Payment Request, PO,
Stock Adjustment, Manual Journal, Period Reopen) sudah didefinisikan di sana sebagai seed default.

## Catatan Seed

1. Seeder hanya untuk data referensi/master (roles, permissions, COA, UoM, outlet, item group, approval matrix).
2. Transaksi sample (daily sales, PO, dst) di `docs/08-testing/test-data.md` — untuk test/demo, bukan seed produksi.
3. Semua money `DECIMAL(18,2)`, quantity `DECIMAL(18,4)`.
