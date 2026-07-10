# ADR-001: Use Laravel + Inertia + Vue

## Status

Accepted

## Context

Project requires a maintainable backoffice web application with strong server-side validation, RBAC, form-heavy workflows, and fast development speed.

## Decision

Use Laravel latest for backend, Inertia.js as bridge, Vue 3 + TypeScript for frontend, PostgreSQL for database.

## Consequences

Positive:

1. Laravel provides mature auth, validation, migrations, queues, policies.
2. Inertia avoids separate API complexity for backoffice workflows.
3. Vue gives reactive UI for forms, tables, dashboards.
4. PostgreSQL supports financial constraints, JSONB audit logs, strong consistency.

Negative:

1. Inertia is less suitable for public API clients unless separate API layer is added.
2. Tight coupling between web frontend and backend routes.
