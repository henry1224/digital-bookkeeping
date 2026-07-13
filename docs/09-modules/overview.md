# Ringkasan Modul

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
   - Export

## Fase 2 / Setelah MVP

1. Manufacturing Central Kitchen
2. Production order
3. Raw material issue
4. Finished good receipt
5. Delivery order
6. Internal invoice
7. Direct bank API integration
8. Full tax modules
9. Customer/AR lifecycle dan aging AR
10. Flash Cost dan Reconcile HPP detail

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
