# Component Library

## Shared Components

1. AppLayout
2. SidebarNavigation
3. Topbar
4. Breadcrumbs
5. PageHeader
6. DataTable
7. FilterBar
8. Pagination
9. MoneyInput
10. DateRangePicker
11. OutletSelector
12. StatusBadge
13. ApprovalTimeline
14. AuditLogPanel
15. ConfirmDialog
16. FileUploader
17. ExportButton
18. EmptyState
19. LoadingSkeleton
20. FormSection
21. PeriodStatusBanner
22. RequiredLabel

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
