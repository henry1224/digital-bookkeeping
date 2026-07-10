# POS Integration Specification

## Scope

Import daily sales summary from existing POS. This system does not replace POS.

## Supported Modes

1. Manual input through form.
2. Excel/CSV import.
3. API import if POS vendor supports it.

## Required Daily Sales Fields

1. Outlet code
2. Sale date
3. Shift or cashier name (optional)
4. Item/menu code
5. Item/menu name
6. Quantity sold
7. Gross amount
8. Discount amount
9. Tax/PB1 amount
10. Net amount
11. Payment method breakdown: cash, debit, credit, QRIS, transfer
12. Receipt number range (optional)

## Import Validation

1. Outlet code must exist.
2. Item code must exist or go to mapping queue.
3. Sale date must not be in closed period.
4. Payment total must equal net sales total.
5. Duplicate import for same outlet/date/shift must be blocked unless re-import approved.

## Error Handling

Invalid rows are collected in import error report. Valid rows may be imported only if business approves partial import; default is all-or-nothing.
