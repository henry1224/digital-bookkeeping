# Threat Model

## Assets

1. Financial transactions
2. Bank balances
3. Inventory valuation
4. User credentials
5. Supplier/customer private data
6. Audit logs

## Threats and Mitigations

| Threat | Vector | Mitigation |
|---|---|---|
| Unauthorized access | Stolen password | 2FA for sensitive roles, session timeout |
| Fraudulent payment approval | User approves own request | Separation of duties, approval policy |
| Journal tampering | Edit posted journal | Posted journals immutable, reversal only |
| Closed period mutation | Backdated edits | Period lock, owner-approved reopen |
| Data leakage | Export by unauthorized user | Export permission, audit log every export |
| SQL injection | Malicious input | Eloquent/parameterized query, validation |
| File upload abuse | Malicious attachment | MIME validation, size limit, safe storage |
| Audit log tampering | DB direct edit | Append-only app policy, DB permission separation, hash chain optional |

## Security Requirements

1. Password minimum 12 characters.
2. 2FA for Finance Manager, Accounting, Owner, Admin IT.
3. All exports logged.
4. All approvals logged.
5. All failed login attempts logged.
6. Production DB access only through restricted credentials.
