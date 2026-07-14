# Design System

## UI Kit: shadcn-vue

Standar komponen memakai **shadcn-vue** (`shadcn-vue.com`) — port Vue 3 resmi dari shadcn/ui,
cocok dengan stack Inertia + Vue + Tailwind. Bukan `shadcn/ui` (React-only).

Aturan:

1. Komponen ditambahkan sebagai source ke `resources/js/Components/ui` via CLI (`npx shadcn-vue@latest add <komponen>`), bukan sebagai dependency runtime.
2. Design token di dokumen ini (colors, radius, spacing) dipetakan ke CSS variable shadcn-vue (`--primary`, `--destructive`, dll) saat `init`.
3. Komponen domain (`resources/js/Features/<module>`) menyusun dari primitif shadcn-vue, tidak menulis ulang primitif.
4. Jangan modifikasi file primitif hasil generate kecuali untuk token/tema; variasi dibuat via composition.

Setup (dijalankan saat app sudah di-scaffold, belum sekarang):

```bash
npx shadcn-vue@latest init      # buat components.json + token
npx shadcn-vue@latest add button input table dialog badge select
```

Skill AI pendamping (opsional, setelah `components.json` ada): `npx skills add shadcn/ui`
— injeksi konvensi komposisi ke asisten. Catatan: skill ini menjalankan shell directive saat
install; review dulu. Karena registry skill saat ini React-first, konfirmasi ketersediaan varian
Vue sebelum dipakai; kalau belum ada, cukup ikuti aturan di dokumen ini.

## Prinsip

1. Kejelasan finansial lebih penting daripada dekorasi.
2. Table harus mudah dibaca dan mudah diexport.
3. State penting harus eksplisit: Draft, Submitted, Approved, Posted, Closed.
4. Destructive action butuh confirmation dan audit reason.
5. UI harus optimal untuk desktop terlebih dahulu.
6. Form keuangan harus mencegah input ambigu, terutama uang, tanggal, outlet, dan akun.

## Design Tokens

### Colors

| Token | Hex | Penggunaan |
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

Gunakan 4px base scale: 4, 8, 12, 16, 24, 32, 48.

### Radius

Small 4px, medium 8px, large 12px.

## Standar Komponen

### Button

Variants: primary, secondary, ghost, danger.
Sizes: sm, md, lg.
States: default, hover, disabled, loading.

### Input

Types: text, number, currency, date, select, textarea, file upload.
Setiap input harus memiliki label dan validation error area.
Required field harus ditandai dengan teks, bukan hanya warna.

### Money Input

1. Display format: `Rp 1.234.567`.
2. Submit value: string decimal, contoh `1234567.00`.
3. Jangan submit float JavaScript.
4. Error harus jelas jika format invalid.

### Table

Fitur wajib untuk data table:

1. Search
2. Filter
3. Sort
4. Pagination
5. Export jika report-like
6. Row actions di kanan
7. Empty state
8. Loading skeleton
9. Ringkasan total untuk table keuangan bila relevan

### Status Badge

Gunakan label konsisten:

- Draft: gray
- Submitted/Pending: warning
- Approved/Posted: success
- Rejected/Cancelled: danger
- Closed: neutral dark
- Reopened: warning

## Template Layout

1. Login page
2. Dashboard layout: sidebar + topbar + content
3. List page: header + filters + table
4. Create/Edit form page: sections + sticky actions
5. Detail page: summary card + tabs + audit trail
6. Report page: filters + summary cards + table/chart + export actions

## Aturan Form

1. Primary action button di kanan bawah.
2. Cancel/back action di kiri bawah.
3. Required fields ditandai dengan teks, bukan hanya warna.
4. Save draft tersedia pada flow yang mendukung draft.
5. Confirmation wajib untuk posting, approving, rejecting, closing, deleting.
6. Closed period banner harus muncul saat record berada dalam periode tertutup.
7. Field outlet wajib terlihat pada data outlet-scoped kecuali user hanya punya satu outlet.
