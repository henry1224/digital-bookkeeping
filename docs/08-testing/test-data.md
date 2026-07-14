# Test Data Set

## Minimum Seed Data

1. 3 outlets: Balikpapan A, Balikpapan B, Central Kitchen.
2. Central Kitchen bertipe outlet/storage untuk MVP, bukan production module.
3. 10 users across all roles.
4. 50 raw material items.
5. 25 menu items.
6. 20 suppliers.
7. 5 bank accounts.
8. Standard F&B Chart of Accounts.
9. 30 recipes.
10. 3 months sample transactions.
11. Outlet config dengan PB1 enabled dan disabled.
12. Approval matrix default configurable.

## Sample Flows

1. Daily sales untuk setiap outlet selama 30 hari.
2. Purchase orders dan receiving untuk 10 suppliers.
3. Ingredient usage harian untuk semua outlet.
4. Stock opname di akhir bulan.
5. Adjustments untuk waste/loss.
6. Payment requests dan approvals.
7. Month-end closing.
8. Reopen period untuk satu correction sample.
9. Manual journal approval sample.
10. Report Laba Rugi dan Neraca sample.

## Sample Validation Cases

1. Daily sales payment breakdown harus sama dengan net sales + PB1 jika PB1 enabled.
2. Receiving partial tidak boleh melewati outstanding PO tanpa approval.
3. Payment request creator tidak boleh approve sendiri.
4. Stock usage tidak boleh membuat negative stock tanpa override.
5. Closed period memblokir backdated posting.
6. Posted journal reversal membuat journal baru, bukan mengedit journal lama.
