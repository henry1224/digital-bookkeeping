# Test Strategy

## Backend Tests

Gunakan Pest/PHPUnit.

Required feature tests:

1. Daily sales posting membuat balanced journal.
2. Receiving membuat stock movement dan AP journal.
3. Payment execution membuat bank transaction dan journal.
4. Stock usage mengurangi stock dan memposting HPP.
5. Stock adjustment membutuhkan approval.
6. Closed period memblokir edit.
7. Reopen period membutuhkan owner approval.
8. User tidak bisa akses data outlet yang tidak ditugaskan.
9. Manual journal membutuhkan approval sebelum posted.
10. Posted journal tidak bisa diedit langsung.
11. PB1 hanya diposting jika outlet config mengaktifkan PB1.
12. Moving average cost berubah saat receiving posted.

## Frontend Tests

Gunakan Vitest untuk components dan Playwright untuk critical flows.

Critical E2E flows:

1. Login.
2. Input daily sales.
3. Create PO, approve, receive.
4. Submit payment request, approve, pay.
5. Run stock opname dan adjustment.
6. Generate Laba Rugi dan Neraca.
7. Attempt edit closed period dan pastikan diblokir.

## UAT Criteria

1. Minimum 95% test cases pass.
2. Zero critical bugs.
3. Maximum 5 minor bugs outstanding saat handover.
4. Reports divalidasi dengan sample manual calculations.
5. Approval dan audit log divalidasi pada transaksi sample.

## Test Safety

1. Financial flow test harus assert total debit = total credit.
2. Test uang tidak boleh memakai float expectation.
3. Test outlet scope wajib mencakup user assigned dan unassigned.
4. Test closed period wajib mencakup create, update, posting, dan delete/cancel.
