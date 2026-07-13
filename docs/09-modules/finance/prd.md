# Module PRD: Finance

## Goal

Record daily outlet sales, manage cash/bank movement, payment requests, approvals, non-stock expenses, and AP visibility.

## Features

1. Daily Sales
2. Bank Book
3. Payment Request
4. Approval Center
5. List Expenditure
6. Payment History
7. AP Reports
8. Purchase Non Stock via payment request + expense account

## Acceptance Criteria

1. Daily sales can be input manually or imported.
2. Daily sales posting creates bank movement and balanced journal.
3. Payment request follows approval route.
4. Approved payment can be executed once.
5. Payment execution creates bank transaction and journal.
6. AP report matches source transactions.
7. Non-stock expense payment requires `expense_account_id` and posts expense vs bank on execution.
