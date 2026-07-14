# Threat Model

## Assets

1. Financial transactions
2. Bank balances
3. Inventory valuation
4. User credentials
5. Data pribadi supplier/customer
6. Audit logs
7. Approval decisions
8. Financial reports

## Threats and Mitigations

| Threat | Vector | Mitigation |
|---|---|---|
| Unauthorized access | Password dicuri | 2FA untuk role sensitif, session timeout |
| Fraudulent payment approval | User approve request sendiri | Separation of duties, approval policy |
| Journal tampering | Edit posted journal | Posted journal immutable, reversal only |
| Closed period mutation | Backdated edits | Period lock, owner-approved reopen |
| Data leakage | Export oleh user tidak berwenang | Export permission, audit log setiap export |
| SQL injection | Input malicious | Eloquent/parameterized query, validation |
| File upload abuse | Attachment malicious | MIME validation, size limit, safe storage |
| Audit log tampering | Direct DB edit | Append-only app policy, DB permission separation, hash chain optional |
| Outlet data leakage | User outlet A akses outlet B | Outlet scoping di query, policy, test coverage |
| Money precision error | Float di frontend/backend | Money string/decimal, `DECIMAL(18,2)`, validation |

## Security Requirements

1. Password minimum 12 characters.
2. 2FA untuk Finance Manager, Accounting, Owner, Admin IT.
3. Semua export dicatat di audit log.
4. Semua approval dicatat di audit log.
5. Semua failed login attempts dicatat.
6. Production DB access hanya lewat restricted credentials.
7. Setiap action harus dicek permission.
8. Setiap outlet-scoped query harus memfilter assigned outlets kecuali role consolidated.
9. Uploaded file tidak boleh disajikan dari path publik tanpa authorization check.

## Referensi

Detail role dan permission ada di `docs/06-security/rbac-matrix.md`.
