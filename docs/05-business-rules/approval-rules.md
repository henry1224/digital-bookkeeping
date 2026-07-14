# Aturan Approval

## Default Approval Matrix

Matrix ini adalah seed default. Nilai final harus bisa dikonfigurasi oleh role yang berwenang dan dikonfirmasi JJ Steak sebelum go-live.

| Transaction | Amount | Approver |
|---|---:|---|
| Payment Request | <= 5,000,000 | Outlet Manager |
| Payment Request | > 5,000,000 and <= 50,000,000 | Finance Manager |
| Payment Request | > 50,000,000 | Owner |
| Purchase Order | <= 10,000,000 | Purchasing Manager |
| Purchase Order | > 10,000,000 | Finance Manager / Owner |
| Stock Adjustment | <= 5% stock value | Warehouse Manager |
| Stock Adjustment | > 5% stock value | Finance Manager |
| Manual Journal | Any | Accounting Lead / Owner sesuai policy |
| Period Reopen | Any | Owner |

## Aturan Umum

1. Creator tidak boleh approve request sendiri kecuali policy eksplisit mengizinkan.
2. Approval action harus menyimpan timestamp, user, action, dan comment jika diwajibkan.
3. Rejection wajib comment.
4. Return for revision wajib comment.
5. Delegation harus dicatat di audit log.
6. Approval tidak boleh dilakukan jika period terkait sudah closed, kecuali approval untuk reopen/correction flow.
7. Semua approval/rejection/return harus masuk audit log.

## Alur Status

### Payment Request

`draft -> submitted -> approved -> paid`

Alternative:

- `submitted -> rejected`
- `submitted -> returned -> draft`
- `draft/submitted -> cancelled`

### Purchase Order

`draft -> submitted -> approved -> partially_received -> received`

Alternative:

- `submitted -> rejected`
- `draft/submitted/approved -> cancelled` jika belum received.

### Stock Adjustment

`draft -> submitted -> approved -> posted`

Alternative:

- `submitted -> rejected`
- `submitted -> returned -> draft`

### Manual Journal

`draft -> pending_approval -> posted`

Alternative:

- `pending_approval -> rejected`
- `posted -> reversed` lewat reversal journal.
