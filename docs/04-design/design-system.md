# Design System

## Principles

1. Financial clarity over decoration.
2. Tables must be readable and exportable.
3. Important states must be explicit: Draft, Submitted, Approved, Posted, Closed.
4. Destructive actions need confirmation and audit reason.
5. UI must work well on desktop first.

## Design Tokens

### Colors

| Token | Hex | Usage |
|---|---|---|
| primary | #1E40AF | Main action, active navigation |
| primary-dark | #1E3A8A | Hover primary |
| success | #047857 | Approved, posted, positive |
| warning | #B45309 | Pending, variance warning |
| danger | #B91C1C | Rejected, failed, destructive |
| neutral-900 | #111827 | Primary text |
| neutral-700 | #374151 | Secondary text |
| neutral-100 | #F3F4F6 | Page background |
| border | #E5E7EB | Borders |

### Typography

| Use | Size | Weight |
|---|---:|---:|
| Page title | 28px | 700 |
| Section title | 20px | 600 |
| Card title | 16px | 600 |
| Body | 14px | 400 |
| Label | 12px | 500 |
| Table cell | 13px | 400 |

### Spacing

Use 4px base scale: 4, 8, 12, 16, 24, 32, 48.

### Radius

Small 4px, medium 8px, large 12px.

## Component Standards

### Button

Variants: primary, secondary, ghost, danger.
Sizes: sm, md, lg.
States: default, hover, disabled, loading.

### Input

Types: text, number, currency, date, select, textarea, file upload.
Every input must have label and validation error area.

### Table

Required features for data tables:

1. Search
2. Filter
3. Sort
4. Pagination
5. Export if report-like
6. Row actions at right
7. Empty state
8. Loading skeleton

### Status Badge

Use consistent labels:

- Draft: gray
- Submitted/Pending: warning
- Approved/Posted: success
- Rejected/Cancelled: danger
- Closed: neutral dark

## Layout Templates

1. Login page
2. Dashboard layout: sidebar + topbar + content
3. List page: header + filters + table
4. Create/Edit form page: sections + sticky actions
5. Detail page: summary card + tabs + audit trail
6. Report page: filters + summary cards + table/chart + export actions

## Form Rules

1. Primary action button on bottom right.
2. Cancel/back action on bottom left.
3. Required fields marked with text, not only color.
4. Save draft where flow supports draft.
5. Confirmation required for posting, approving, rejecting, closing, deleting.
