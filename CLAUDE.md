# CLAUDE.md — Digital Bookkeeping Project Instructions

## Project

JJ Steak Digital Bookkeeping — backoffice web for multi-outlet financial, inventory, and accounting control.

## Stack

- Laravel latest
- PHP latest stable supported by Laravel latest
- Inertia.js
- Vue 3 with TypeScript
- PostgreSQL
- Tailwind CSS
- Pest/PHPUnit for backend tests
- Vitest for frontend unit tests
- Playwright for E2E critical paths

## Scope Rules

1. Build backoffice web only.
2. Do not build POS replacement.
3. Do not build mobile app.
4. POS existing remains outside system; app imports or manually records daily sales summary.
5. Financial reports are primary goal: Laba Rugi and Neraca.
6. Cashflow and inventory reports are supporting analytics.
7. Every financial transaction must be journaled or explicitly marked non-posting.
8. Every posting journal must balance: total debit = total credit.
9. Closed periods are immutable except through approved reopen flow.
10. Every important action must create audit log.

## Laravel Conventions

- Use feature-based modules under `app/Modules/<ModuleName>` when module grows.
- Use Form Requests for validation.
- Use Actions for business operations that change state.
- Use Policies for authorization.
- Use Eloquent casts for Money/Date/JSON.
- Use database transactions for multi-table writes.
- Use migrations for all schema changes.
- Use seeders only for reference/master starter data.
- Do not put business logic in controllers.

## Inertia + Vue Conventions

- Pages in `resources/js/Pages/<Module>/<Page>.vue`.
- Shared components in `resources/js/Components`.
- Domain components in `resources/js/Features/<module>`.
- Use TypeScript types generated/mirrored from API resources.
- Forms use Inertia `useForm` plus server-side validation errors.
- Tables use consistent filter/sort/pagination pattern.

## Naming

- Database tables: snake_case plural (`journal_entries`).
- Model classes: singular PascalCase (`JournalEntry`).
- Enums: singular PascalCase (`PaymentStatus`).
- Routes: kebab-case (`payment-requests.index`).
- Vue components: PascalCase.
- Money fields end with `_amount`.
- Foreign keys end with `_id`.
- Timestamps use UTC in database; display WITA for users.

## Financial Safety

- Never use float for money.
- Store money as integer cents/sen if possible or DECIMAL(18,2). Pick one in ADR before implementation.
- Validate debit = credit at application and database level.
- Wrap journal creation and source transaction update in one database transaction.
- Audit before/after values for approvals, postings, edits, deletes.

## Testing Minimum

Every non-trivial financial flow needs at least one feature test:

- Daily sales posting creates balanced journal.
- Receiving creates stock movement + AP journal.
- Payment approval and execution updates bank book + journal.
- Stock adjustment updates stock + journal.
- Closing blocks edits in closed period.

## Do Not

- Do not add dependencies without checking Laravel/native alternatives.
- Do not create abstractions for one implementation.
- Do not skip validation at trust boundaries.
- Do not silently mutate closed-period data.
- Do not hard-delete financial transactions.
