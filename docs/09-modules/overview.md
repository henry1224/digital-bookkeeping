# Ringkasan Modul

Progress eksekusi tracked di `docs/00-project/progress-tracker.md`.

## Urutan Implementasi MVP

1. Foundation
   - Auth
   - RBAC
   - Outlet scoping
   - Audit log
   - File upload dasar

2. Master Data
   - Outlets dan outlet configs
   - Users, roles, permissions
   - COA/accounts
   - Items, item groups, UOM
   - Suppliers
   - Bank accounts

3. Accounting Core
   - Journals dan journal entries
   - Posting rules
   - Ledger/trial balance
   - Period closing/reopen

4. Finance
   - Daily sales
   - Bank transactions
   - Payment requests
   - Purchase Non Stock via payment request + expense account
   - Payment approvals/execution
   - AP visibility

5. Logistics
   - Purchase orders
   - Receiving
   - Stock balances/movements
   - Ingredient usage
   - Stock opname
   - Stock adjustment
   - Recipe

6. Reports
   - Laba Rugi
   - Neraca
   - Cashflow support
   - Inventory valuation
   - Flash Cost (estimasi HPP dari resep vs usage aktual)
   - Reconcile HPP
   - Export

## Fase 2 (Penyempurnaan kontrol & analisa)

1. Customer/AR lifecycle, aging AR, dan **aging AP**.
2. AR/AP Intra Outlet (via Transfer Antar Outlet: satu transaksi membentuk AR di satu sisi, AP di sisi lain).
3. Fixed Asset + penyusutan.
4. Manufacturing Central Kitchen: production order, raw material issue, finished good receipt, delivery order, internal invoice.

Catatan: Flash Cost & Reconcile HPP dinaikkan ke MVP (lihat Reports).

## Fase 3 (Optimalisasi & otomasi lanjutan)

1. Budgeting vs Actual per outlet.
2. Bank Reconciliation (rekonsiliasi rekening koran) dan Direct bank API integration.
3. Dashboard analitik.
4. Full tax modules / integrasi pajak (e-Faktur, e-Bupot, PPN/PPh).

Catatan: Payroll di luar sistem (HR terpisah), masuk lewat jurnal manual.

## Definition of Done Modul Finansial

1. Validation via Form Request.
2. Business write operation via Action.
3. Database transaction untuk multi-table writes.
4. Audit log untuk action penting.
5. Policy/permission check.
6. Closed period guard.
7. Feature test minimal.
8. Jika posting: journal balance asserted.
9. Jika uang: tidak memakai float.
