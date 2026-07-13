# Kamus Data

## Konvensi Global

| Column | Type | Rule |
|---|---|---|
| id | BIGINT | Primary key auto-increment. |
| outlet_id | FK BIGINT | Wajib untuk data outlet-scoped. |
| created_by | FK users | Wajib untuk auditable writes. |
| updated_by | FK users | Nullable. |
| created_at | timestamp | UTC. |
| updated_at | timestamp | UTC. |
| deleted_at | timestamp | Soft delete hanya untuk master data. |

## Tipe Data Keuangan

1. Semua uang memakai `DECIMAL(18,2)`.
2. Field uang wajib berakhiran `_amount`.
3. Application layer tidak boleh memakai float untuk uang.
4. Quantity boleh memakai `DECIMAL(18,4)` bila butuh pecahan bahan.
5. Rate/persentase memakai `DECIMAL(8,4)`.

## outlets

| Column | Type | Rule | Example |
|---|---|---|---|
| code | varchar(30) | unique uppercase | BPN-A |
| name | varchar(150) | required | Balikpapan A |
| outlet_type | enum | outlet/central_kitchen/storage | outlet |
| timezone | varchar(50) | default Asia/Makassar | Asia/Makassar |
| is_active | boolean | default true | true |

Catatan: `central_kitchen` pada MVP hanya lokasi inventory/storage, bukan modul produksi.

## outlet_configs

| Column | Type | Rule | Example |
|---|---|---|---|
| outlet_id | FK outlets | unique | 1 |
| pb1_enabled | boolean | default false | true |
| pb1_rate | decimal(8,4) | 0 jika disabled | 10.0000 |
| default_cash_account_id | FK accounts | nullable | 1 |
| default_bank_account_id | FK accounts | nullable | 2 |

## accounts

| Column | Type | Rule | Example |
|---|---|---|---|
| code | varchar(30) | unique | 1-1000 |
| name | varchar(150) | required | Kas dan Bank |
| type | enum | asset/liability/equity/revenue/expense/cogs | asset |
| parent_id | FK accounts | nullable | null |
| is_postable | boolean | true hanya untuk detail accounts | true |
| report_group | varchar(50) | nullable | current_asset |
| is_active | boolean | default true | true |

## items

| Column | Type | Rule | Example |
|---|---|---|---|
| sku | varchar(50) | unique uppercase | BEEF-SIRLOIN |
| name | varchar(150) | required | Sirloin Beef |
| item_type | enum | raw_material/finished_good/menu/non_stock | raw_material |
| item_group_id | FK | required | 1 |
| base_uom_id | FK | required | 1 |
| standard_cost_amount | decimal(18,2) | >= 0 | 250000.00 |
| avg_cost_amount | decimal(18,2) | >= 0 | 248500.00 |
| is_active | boolean | default true | true |

## daily_sales

| Column | Type | Rule |
|---|---|---|
| outlet_id | FK outlets | required |
| sale_date | date | tidak dalam closed period |
| shift_code | varchar(30) | nullable; bagian dari duplicate check |
| gross_sales_amount | decimal(18,2) | >= 0 |
| discount_amount | decimal(18,2) | >= 0 |
| pb1_amount | decimal(18,2) | >= 0 |
| net_sales_amount | decimal(18,2) | >= 0 |
| payment_total_amount | decimal(18,2) | harus sama dengan net_sales_amount + pb1_amount jika PB1 enabled |
| status | enum | draft/submitted/posted/cancelled |
| non_posting_reason | text | required jika marked non-posting |

Aturan unik: `outlet_id + sale_date + shift_code` unik untuk posted/submitted, kecuali re-import disetujui.

## daily_sale_lines

| Column | Type | Rule |
|---|---|---|
| daily_sale_id | FK daily_sales | required |
| item_id | FK items | required |
| qty | decimal(18,4) | > 0 |
| unit_price_amount | decimal(18,2) | >= 0 |
| subtotal_amount | decimal(18,2) | qty x unit price |

## payment_requests

| Column | Type | Rule |
|---|---|---|
| outlet_id | FK outlets | nullable untuk consolidated/admin expense |
| request_no | varchar | unique |
| request_type | enum | supplier_payment/non_stock_expense |
| requested_by | FK users | required |
| supplier_id | FK suppliers | nullable |
| expense_account_id | FK accounts | nullable; required jika bukan AP |
| amount | decimal(18,2) | > 0 |
| due_date | date | nullable |
| status | enum | draft/submitted/approved/rejected/paid/cancelled | required |
| description | text | required |

## payment_approvals

| Column | Type | Rule |
|---|---|---|
| payment_request_id | FK payment_requests | required |
| approver_id | FK users | required |
| action | enum | approved/rejected/returned | required |
| comment | text | required untuk rejected/returned |
| acted_at | timestamp | UTC |

## purchase_orders

| Column | Type | Rule |
|---|---|---|
| outlet_id | FK outlets | required |
| supplier_id | FK suppliers | required |
| po_no | varchar | unique |
| po_date | date | required |
| status | enum | draft/submitted/approved/partially_received/received/cancelled | required |
| total_amount | decimal(18,2) | >= 0 |

## receivings

| Column | Type | Rule |
|---|---|---|
| purchase_order_id | FK purchase_orders | required |
| outlet_id | FK outlets | required |
| receiving_no | varchar | unique |
| received_at | timestamp | UTC |
| status | enum | draft/posted/cancelled | required |
| total_amount | decimal(18,2) | >= 0 |

## stock_balances

| Column | Type | Rule |
|---|---|---|
| outlet_id | FK outlets | required |
| item_id | FK items | required |
| qty_on_hand | decimal(18,4) | default 0 |
| avg_cost_amount | decimal(18,2) | default 0 |
| inventory_value_amount | decimal(18,2) | qty x avg cost |

Aturan unik: `outlet_id + item_id`.

## stock_movements

| Column | Type | Rule |
|---|---|---|
| outlet_id | FK outlets | required |
| item_id | FK items | required |
| movement_date | date | tidak dalam closed period |
| movement_type | enum | receiving_in/usage_out/adjustment_in/adjustment_out/transfer_in/transfer_out/production_issue/finished_good_receipt |
| source_type | varchar | required |
| source_id | BIGINT | required |
| qty | decimal(18,4) | != 0 |
| unit_cost_amount | decimal(18,2) | >= 0 |
| total_cost_amount | decimal(18,2) | qty x unit cost |

Catatan: `production_issue` dan `finished_good_receipt` reserved untuk fase 2.

## journals

| Column | Type | Rule |
|---|---|---|
| journal_no | varchar | unique |
| journal_date | date | tidak dalam closed period |
| source_type | varchar | daily_sale/receiving/payment/adjustment/stock_usage/manual |
| source_id | BIGINT nullable | source record id |
| status | enum | draft/pending_approval/posted/reversed/cancelled |
| total_debit_amount | decimal(18,2) | equals total_credit_amount saat posted |
| total_credit_amount | decimal(18,2) | equals total_debit_amount saat posted |
| posted_at | timestamp nullable | UTC |
| posted_by | FK users nullable | required saat posted |

## journal_entries

| Column | Type | Rule |
|---|---|---|
| journal_id | FK journals | required |
| account_id | FK accounts | postable account only |
| debit_amount | decimal(18,2) | >= 0 |
| credit_amount | decimal(18,2) | >= 0 |
| description | text | nullable |

Aturan: satu line tidak boleh punya debit dan credit sekaligus. Posted journal total debit harus sama dengan total credit.

## posting_rules

| Column | Type | Rule |
|---|---|---|
| source_type | varchar | required |
| debit_account_id | FK accounts | nullable jika ditentukan runtime |
| credit_account_id | FK accounts | nullable jika ditentukan runtime |
| condition_key | varchar | nullable, misalnya payment_method/pb1_enabled |
| is_active | boolean | default true |

## period_closings

| Column | Type | Rule |
|---|---|---|
| outlet_id | FK outlets nullable | null untuk consolidated close |
| period_year | integer | required |
| period_month | integer | 1-12 |
| status | enum | open/closing/closed/reopened |
| closed_at | timestamp nullable | UTC |
| closed_by | FK users nullable | required saat closed |
| reopened_at | timestamp nullable | UTC |
| reopened_by | FK users nullable | owner approval required |
| reopen_reason | text nullable | required saat reopened |

Aturan unik: `outlet_id + period_year + period_month`.

## audit_logs

| Column | Type | Rule |
|---|---|---|
| actor_id | FK users nullable | null untuk system job |
| action | varchar(100) | required |
| auditable_type | varchar(150) | required |
| auditable_id | BIGINT nullable | target record |
| before_values | jsonb nullable | snapshot sebelum |
| after_values | jsonb nullable | snapshot sesudah |
| reason | text nullable | required untuk destructive/correction action |
| ip_address | varchar(45) nullable | request IP |
| user_agent | text nullable | request user agent |
| created_at | timestamp | UTC |

Aturan: audit log append-only di application layer. Update/delete audit log dilarang oleh policy aplikasi.
