# Data Dictionary

## Global Columns

| Column | Type | Rule |
|---|---|---|
| id | UUID/BIGINT | Primary key. Pick one before implementation. |
| outlet_id | FK | Required for outlet-scoped data. |
| created_by | FK users | Required for auditable writes. |
| updated_by | FK users | Nullable. |
| created_at | timestamp | UTC. |
| updated_at | timestamp | UTC. |
| deleted_at | timestamp | Soft delete for master data only. |

## accounts

| Column | Type | Rule | Example |
|---|---|---|---|
| code | varchar(30) | unique | 1-1000 |
| name | varchar(150) | required | Kas dan Bank |
| type | enum | asset/liability/equity/revenue/expense/cogs | asset |
| parent_id | FK accounts | nullable | null |
| is_postable | boolean | true only detail accounts | true |

## items

| Column | Type | Rule | Example |
|---|---|---|---|
| sku | varchar(50) | unique uppercase | BEEF-SIRLOIN |
| name | varchar(150) | required | Sirloin Beef |
| item_type | enum | raw_material/finished_good/menu/non_stock | raw_material |
| item_group_id | FK | required | 1 |
| base_uom_id | FK | required | kg |
| standard_cost_amount | decimal(18,2) | >= 0 | 250000.00 |
| avg_cost_amount | decimal(18,2) | >= 0 | 248500.00 |
| is_active | boolean | default true | true |

## journals

| Column | Type | Rule |
|---|---|---|
| journal_no | varchar | unique |
| journal_date | date | not in closed period |
| source_type | varchar | daily_sale/receiving/payment/adjustment/manual |
| source_id | nullable | source record id |
| status | enum | draft/posted/reversed |
| total_debit_amount | decimal(18,2) | equals total_credit_amount |
| total_credit_amount | decimal(18,2) | equals total_debit_amount |

## journal_entries

| Column | Type | Rule |
|---|---|---|
| journal_id | FK | required |
| account_id | FK | postable account only |
| debit_amount | decimal(18,2) | >= 0 |
| credit_amount | decimal(18,2) | >= 0 |
| description | text | nullable |

Rule: one line cannot have both debit and credit. Journal total debit must equal total credit.
