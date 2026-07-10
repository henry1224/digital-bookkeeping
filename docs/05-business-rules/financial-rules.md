# Financial Rules

## Journal Rules

1. Every posted journal must balance: total debit = total credit.
2. Journal date must not be in closed period.
3. Manual journal requires approval.
4. Reversal creates new reversing journal; it does not mutate old posted journal.
5. Source transaction and journal creation must be atomic.

## Daily Sales Posting

Expected journal example:

Debit:

- Cash/Bank accounts by payment method

Credit:

- Sales revenue
- Tax/PB1 payable if configured

Rules:

1. Payment breakdown total must equal net sales.
2. Duplicate daily sales for same outlet/date/shift blocked unless approved re-import.
3. Posting creates bank transaction and journal.

## Payment Execution

Expected journal example:

Debit:

- AP or expense account

Credit:

- Bank account

Rules:

1. Only approved payment requests can be executed.
2. Payment date cannot be in closed period.
3. Paid request cannot be edited.

## Receiving Stock

Expected journal example:

Debit:

- Inventory account

Credit:

- Accounts payable

Rules:

1. Receiving can be partial.
2. Receiving quantity cannot exceed PO outstanding without override approval.
3. Receiving updates average cost if costing method is average.
