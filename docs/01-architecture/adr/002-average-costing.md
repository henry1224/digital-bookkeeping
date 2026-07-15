# ADR-002: Use Weighted Average Costing

## Status

Proposed

## Context

F&B inventory has frequent purchases, fast-moving ingredients, and practical operations. FIFO requires batch-level tracking and higher operational discipline.

## Decision

Use Weighted Average Cost for MVP unless Digital Bookkeeping requires FIFO.

## Consequences

Positive:

1. Simpler implementation and easier outlet adoption.
2. Good fit for fast-moving F&B ingredients.
3. Easier reporting and valuation.

Negative:

1. Less batch-specific than FIFO.
2. Not suitable if strict expiry/batch valuation becomes mandatory.

## Formula

New average cost = ((old_qty × old_avg_cost) + (received_qty × received_unit_cost)) / (old_qty + received_qty)
