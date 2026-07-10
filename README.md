# Digital Bookkeeping Documentation

AI-ready documentation set for JJ Steak backoffice web application.

## Stack

- Backend: Laravel latest
- Frontend: Inertia.js + Vue 3 + TypeScript
- Database: PostgreSQL
- Styling: Tailwind CSS + component primitives
- Auth: Laravel Breeze/Jetstream style auth, adapted for RBAC
- Tests: Pest/PHPUnit + Vitest + Playwright

## Document Order for AI Development

1. `CLAUDE.md` — project instructions for AI
2. `docs/00-project/glossary.md` — business language
3. `docs/05-business-rules/global-rules.md` — global rules
4. `docs/02-database/erd.md` — database blueprint
5. `docs/04-design/design-system.md` — UI standard
6. Module docs under `docs/09-modules/*`

## Scope

Backoffice web only. No POS replacement. No mobile app. POS existing remains source of daily sales exports or manual daily summaries.
