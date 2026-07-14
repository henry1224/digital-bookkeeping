# Model Domain

## Bounded Context

### Master Data

Mengelola data referensi yang dipakai semua modul.

Entities: Outlet, Item, ItemGroup, Supplier, Customer, BankAccount, Account(COA), UnitOfMeasure, User, Role, Permission.

### Finance

Mengelola cash/bank movement, payment request, visibility AP/AR, dan pencatatan daily sales.

Entities: DailySale, DailySaleLine, BankTransaction, PaymentRequest, PaymentApproval, Expenditure, Payable, Receivable.

### Logistics

Mengelola purchase order, receiving, stock, ingredient usage, stock opname, adjustment, dan recipe.

Entities: PurchaseOrder, PurchaseOrderLine, Receiving, ReceivingLine, StockMovement, StockBalance, StockCount, StockAdjustment, Recipe, RecipeIngredient.

### Manufacturing

Fase opsional setelah MVP untuk Central Kitchen production.

Entities fase 2: ProductionOrder, RawMaterialIssue, FinishedGoodReceipt, DeliveryOrder, InternalInvoice.

Catatan MVP: Central Kitchen tetap boleh ada sebagai outlet/storage untuk stock dan purchasing, tetapi belum memiliki workflow produksi.

### Accounting

Mengelola journal, ledger, trial balance, financial statements, dan period closing.

Entities: Journal, JournalEntry, LedgerBalance, TrialBalance, PeriodClosing.

### Reporting

Mengelola read model, dashboard, dan export.

Entities: ReportSnapshot, ExportJob, ScheduledReport.

## Aturan Lintas Context

1. Source modules membuat accounting journals melalui Accounting service.
2. Accounting tidak mengubah source transactions.
3. Source transaction dan journal creation harus terjadi dalam satu database transaction.
4. Reporting membaca source tables dan accounting tables; reporting tidak memiliki business transaction.
5. Closed period memblokir perubahan source context kecuali period direopen.
6. Semua financial transaction harus journaled atau ditandai eksplisit `non_posting` dengan reason.
7. Manufacturing entities dan stock movement production disiapkan untuk fase 2, bukan MVP.
