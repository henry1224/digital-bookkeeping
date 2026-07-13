# Aturan Finansial

## Aturan Journal

1. Setiap posted journal harus balance: total debit = total credit.
2. Journal date tidak boleh berada di closed period.
3. Manual journal membutuhkan approval sebelum posted.
4. Reversal membuat journal pembalik baru; tidak mengubah posted journal lama.
5. Source transaction dan journal creation harus atomic dalam satu database transaction.
6. Posted journal immutable.
7. Draft journal boleh diedit oleh authorized user selama period belum closed.

## Posting Daily Sales

Contoh journal yang diharapkan:

Debit:

- Cash/Bank accounts berdasarkan payment method

Credit:

- Sales revenue
- Tax/PB1 payable jika outlet config mengaktifkan PB1

Aturan:

1. Payment breakdown total harus sama dengan net sales + PB1 jika PB1 enabled.
2. Duplicate daily sales untuk outlet/date/shift diblokir kecuali approved re-import.
3. Posting membuat bank transaction dan journal.
4. Daily sales bisa berasal dari POS export atau input manual.
5. Jika transaksi ditandai non-posting, `non_posting_reason` wajib diisi dan audit log dibuat.

## Payment Execution

Contoh journal yang diharapkan:

Debit:

- AP atau expense account

Credit:

- Bank account

Aturan:

1. Hanya approved payment request yang bisa dieksekusi.
2. Payment date tidak boleh berada di closed period.
3. Paid request tidak boleh diedit.
4. Execution membuat bank transaction dan journal dalam satu database transaction.
5. Payment ke supplier PO memakai AP. Payment expense langsung memakai expense account.
6. `supplier_payment` wajib memiliki `supplier_id` dan tidak boleh memakai `expense_account_id` sebagai sumber debit.
7. `non_stock_expense` wajib memiliki `expense_account_id` dan boleh tanpa `supplier_id`.

## Receiving Stock

Contoh journal yang diharapkan:

Debit:

- Inventory account

Credit:

- Accounts payable

Aturan:

1. Receiving bisa partial.
2. Receiving quantity tidak boleh melebihi PO outstanding tanpa override approval.
3. Receiving memperbarui moving average cost.
4. Receiving posted membuat stock movement dan AP journal.
5. Receiving date tidak boleh berada di closed period.

## HPP dan Ingredient Usage

Contoh journal yang diharapkan:

Debit:

- HPP Bahan Baku

Credit:

- Inventory Bahan Baku

Aturan:

1. MVP memposting HPP dari actual ingredient usage.
2. Recipe dipakai untuk standard HPP dan variance comparison, bukan sumber posting utama MVP.
3. Usage date tidak boleh berada di closed period.
4. Usage mengurangi stock dan membuat journal.
5. Negative stock hanya boleh dengan approved override.

## Period Closing

1. Status period: open, closing, closed, reopened.
2. Closed period memblokir create/update/delete source transaction dan journal backdate.
3. Reopen period membutuhkan approval Owner dan reason.
4. Koreksi di reopened period harus jelas: reversal, adjustment journal, atau source correction yang diaudit.
5. Setelah koreksi, period harus ditutup kembali.
6. Close checklist minimal: all journals balanced, no pending approval critical, bank reconciliation reviewed, stock opname/adjustment reviewed.

## Aturan Report

1. Laba Rugi dan Neraca dihasilkan dari posted journals.
2. Draft/pending/cancelled journals tidak masuk laporan keuangan resmi.
3. Report outlet memakai journal/source outlet scope.
4. Report consolidated menggabungkan semua outlet sesuai permission user.
