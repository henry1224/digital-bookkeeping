# Operations Runbook

## Reset User Password

1. Admin IT opens User Management.
2. Select user.
3. Click Reset Password.
4. System sends reset link or temporary password.
5. Audit log records action.

## Reopen Closed Period

1. Accounting requests reopen with reason.
2. Owner approves.
3. System marks period reopened.
4. Correction is posted.
5. Period closed again.
6. Audit log records all actions.

## Backup Verification

1. Check daily backup job status.
2. Verify backup file exists.
3. Verify backup file size is not zero.
4. Monthly restore test to staging.

## Incident: System Down

1. Check server health.
2. Check web server logs.
3. Check application logs.
4. Check database connectivity.
5. Restore latest stable release if deploy caused outage.
6. Notify stakeholders.
