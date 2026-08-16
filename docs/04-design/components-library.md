# Component Library

## Shared Components

1. AppLayout
2. SidebarNavigation
3. Topbar
4. Breadcrumbs
5. PageHeader
6. SurfaceCard
7. MetricCard
8. DataTable
9. FilterBar
10. Pagination
11. MoneyInput
12. DateRangePicker
13. OutletSelector
14. StatusBadge
15. ApprovalTimeline
16. AuditLogPanel
17. ConfirmDialog
18. FileUploader
19. ExportButton
20. EmptyState
21. LoadingSkeleton
22. FormSection
23. PeriodStatusBanner
24. RequiredLabel

## Domain Components

1. AccountSelector
2. ItemSelector
3. SupplierSelector
4. BankAccountSelector
5. JournalEntriesEditor
6. StockMovementTable
7. PaymentBreakdownEditor
8. RecipeIngredientEditor
9. ApprovalActionBar
10. PeriodStatusBanner
11. OutletConfigForm
12. CoaTreeTable
13. MoneySummaryCard

## Aturan Reuse

1. Jangan buat komponen domain jika shared component cukup.
2. Jangan buat abstraction untuk satu halaman saja.
3. DataTable harus menjadi pola utama untuk list page.
4. AuditLogPanel wajib tersedia di detail financial transaction.
5. ApprovalTimeline wajib tersedia di approval-based transaction.
6. JournalEntriesEditor harus memvalidasi total debit = total credit di UI sebelum submit, tetapi server tetap sumber kebenaran.
7. SurfaceCard cukup wrapper class/style; jangan bikin abstraction bila hanya dipakai satu halaman.
8. MetricCard hanya untuk dashboard/report summary, bukan pengganti table data utama.

## Standar Visual Komponen

1. `PageHeader`: eyebrow, title, description, primary action; radius `lg`, border halus, gradient tipis opsional.
2. `SurfaceCard`: `rounded-lg border bg-card shadow-sm`; dipakai untuk table, form section, dan report block.
3. `MetricCard`: angka besar rata kiri, label kecil, trend badge opsional; maksimal 4 card per row desktop.
4. `DataTable`: sticky-ish header bila table panjang, row hover soft, action kanan, angka uang rata kanan.
5. `FilterBar`: ditempatkan di header table card; search kiri, filter/status kanan, reset filter ghost button bila ada filter aktif.
