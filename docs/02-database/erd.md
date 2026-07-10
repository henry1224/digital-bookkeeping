# ERD Sketch

This is a first-pass schema blueprint. Final implementation should be expressed in Laravel migrations.

```mermaid
erDiagram
    outlets ||--o{ users : assigned
    outlets ||--o{ daily_sales : records
    outlets ||--o{ stock_balances : owns
    outlets ||--o{ bank_transactions : owns

    users ||--o{ audit_logs : performs
    users ||--o{ payment_approvals : approves

    item_groups ||--o{ items : groups
    unit_of_measures ||--o{ items : base_uom
    suppliers ||--o{ purchase_orders : receives
    bank_accounts ||--o{ bank_transactions : records
    accounts ||--o{ journal_entries : posts

    daily_sales ||--o{ daily_sale_lines : contains
    daily_sales ||--o{ journals : source

    payment_requests ||--o{ payment_approvals : has
    payment_requests ||--o{ bank_transactions : paid_by
    payment_requests ||--o{ journals : source

    purchase_orders ||--o{ purchase_order_lines : contains
    purchase_orders ||--o{ receivings : fulfilled_by
    receivings ||--o{ receiving_lines : contains
    receivings ||--o{ stock_movements : creates
    receivings ||--o{ journals : source

    items ||--o{ stock_balances : tracked
    items ||--o{ stock_movements : moves
    items ||--o{ recipe_ingredients : ingredient
    items ||--o{ recipes : sellable_item

    stock_counts ||--o{ stock_count_lines : contains
    stock_adjustments ||--o{ stock_adjustment_lines : contains
    stock_adjustments ||--o{ stock_movements : creates
    stock_adjustments ||--o{ journals : source

    journals ||--o{ journal_entries : contains
    period_closings ||--o{ journals : locks
```

## Core Tables

1. outlets
2. users
3. roles, permissions, role_user, permission_role
4. item_groups
5. unit_of_measures
6. items
7. suppliers
8. customers
9. accounts (COA)
10. bank_accounts
11. daily_sales, daily_sale_lines
12. payment_requests, payment_approvals
13. bank_transactions
14. purchase_orders, purchase_order_lines
15. receivings, receiving_lines
16. stock_balances, stock_movements
17. stock_counts, stock_count_lines
18. stock_adjustments, stock_adjustment_lines
19. recipes, recipe_ingredients
20. journals, journal_entries
21. period_closings
22. audit_logs
