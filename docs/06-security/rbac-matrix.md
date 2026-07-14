# RBAC Matrix

## Roles

1. Owner
2. Management
3. Finance Manager
4. Finance
5. Accounting
6. Costcontrol
7. Purchasing Manager
8. Warehouse Manager
9. Outlet Manager
10. Staff Outlet
11. Admin IT
12. Auditor/Read Only

Catatan: daftar ini superset dari draft Excel. `Finance` (staff operasional di bawah Finance Manager)
dan `Costcontrol` (kontrol biaya & HPP) diambil dari Excel; `Outlet Manager` dan `Auditor/Read Only`
dipertahankan dari docs. `Purchasing`/`Warehouse` Excel dipetakan ke `Purchasing Manager`/`Warehouse Manager`.
Mapping level Excel: V=view, I=create/submit, A=approve, F=full (view+create+approve+post/manage), -=no access.

## Prinsip

1. Permission berbasis action, bukan hanya page.
2. Outlet-scoped role hanya boleh akses assigned outlet.
3. Owner/Management bisa melihat consolidated data.
4. Admin IT mengelola user/role, tetapi tidak post journal secara default.
5. Auditor read-only tidak boleh mengubah data.
6. Creator tidak boleh approve transaksi sendiri kecuali policy khusus.

## Matrix Ringkas

Kolom: Owner, Management, Finance Manager, Finance, Accounting, Costcontrol, Purchasing (Manager), Warehouse (Manager), Outlet Manager, Staff Outlet, Admin IT, Auditor.

| Module/Action | Own | Mgmt | FinMgr | Fin | Acct | CostCtrl | Purch | Whse | OutMgr | Staff | ITAdm | Audit |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| View dashboard consolidated | yes | yes | yes | no | yes | yes | no | no | no | no | no | yes |
| View outlet dashboard | yes | yes | yes | assigned | yes | yes | assigned | assigned | assigned | assigned | no | yes |
| Manage users/roles | no | no | no | no | no | no | no | no | no | no | yes | no |
| Manage COA | approve | no | no | no | yes | no | no | no | no | no | no | view |
| Create daily sales | no | no | no | yes | no | no | no | no | yes | yes | no | no |
| Post daily sales | yes | no | yes | yes | yes | no | no | no | yes | no | no | no |
| Create PO | no | no | no | no | no | no | yes | no | yes | no | no | no |
| Approve PO | yes | no | threshold | no | no | threshold | threshold | no | no | no | no | no |
| Receive stock | no | no | no | no | no | no | yes | yes | no | no | no | no |
| Create stock usage | no | no | no | no | no | no | no | yes | yes | yes | no | no |
| Approve stock adjustment | yes | no | threshold | no | no | no | no | threshold | no | no | no | no |
| Create payment request | no | no | yes | yes | yes | yes | yes | yes | yes | no | no | no |
| Approve payment request | yes | no | threshold | no | no | no | no | no | threshold | no | no | no |
| Execute payment | no | no | yes | yes | no | no | no | no | no | no | no | no |
| Create manual journal | no | no | no | no | yes | no | no | no | no | no | no | no |
| Approve manual journal | yes | no | no | no | lead-only | no | no | no | no | no | no | no |
| Close period | yes | no | no | no | yes | no | no | no | no | no | no | no |
| Reopen period | yes | no | no | no | request-only | no | no | no | no | no | no | no |
| Export reports | yes | yes | yes | yes | yes | yes | module-only | module-only | assigned | no | no | yes |
| View audit log | yes | yes | yes | no | yes | no | no | no | no | no | yes | yes |

## Permission Naming

Gunakan pola:

- `<module>.view`
- `<module>.create`
- `<module>.update`
- `<module>.submit`
- `<module>.approve`
- `<module>.post`
- `<module>.cancel`
- `<module>.export`
- `<module>.audit.view`

Contoh:

- `daily-sales.create`
- `daily-sales.post`
- `payment-requests.approve`
- `journals.approve`
- `periods.reopen`
- `reports.export`
