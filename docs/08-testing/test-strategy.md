# Test Strategy

## Backend Tests

Use Pest/PHPUnit.

Required feature tests:

1. Daily sales posting creates balanced journal.
2. Receiving creates stock movement and AP journal.
3. Payment execution creates bank transaction and journal.
4. Stock usage decreases stock and posts HPP.
5. Stock adjustment requires approval.
6. Closed period blocks edits.
7. Reopen period requires owner approval.
8. User cannot access unassigned outlet data.

## Frontend Tests

Use Vitest for components and Playwright for critical flows.

Critical E2E flows:

1. Login.
2. Input daily sales.
3. Create PO, approve, receive.
4. Submit payment request, approve, pay.
5. Run stock opname and adjustment.
6. Generate Laba Rugi and Neraca.

## UAT Criteria

1. Minimum 95% test cases pass.
2. Zero critical bugs.
3. Maximum 5 minor bugs outstanding at handover.
4. Reports validated with sample manual calculations.
