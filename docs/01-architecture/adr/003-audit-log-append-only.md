# ADR-003: Audit Log Append-Only

## Status

Accepted

## Context

Financial system requires traceability for create, update, delete, approve, reject, post, close, reopen, export, login activities.

## Decision

Audit logs are append-only. No update/delete from application. Store before/after data and actor metadata.

## Consequences

Positive:

1. Better audit readiness.
2. Tamper evidence possible through hash chain.
3. Supports investigation of fraud or mistakes.

Negative:

1. Audit table grows quickly.
2. Sensitive data must be redacted or encrypted.
