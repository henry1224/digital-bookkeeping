# User Flow: Payment Request Approval

## Actors

Requester, Approver, Finance Staff.

## Flow

1. Requester opens Payment Request page.
2. Requester fills supplier/purpose, amount, due date, account category, outlet.
3. Requester uploads supporting document.
4. Requester submits request.
5. System determines approval route based on amount and role matrix.
6. Approver receives item in Approval Center.
7. Approver reviews details and attachment.
8. Approver chooses Approve, Reject, or Return for Revision.
9. If approved by all required levels, status becomes Approved.
10. Finance executes payment.
11. System creates Bank Transaction.
12. System creates balanced Journal.
13. Audit log stores full flow.

## Exceptions

1. Missing attachment: cannot submit.
2. Amount above approver limit: route to next approver.
3. Period closed: cannot execute payment in closed period.
