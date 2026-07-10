# Domain Model

## Bounded Contexts

### Master Data
Owns reference data used by all modules.

Entities: Outlet, Item, ItemGroup, Supplier, Customer, BankAccount, Account(COA), UnitOfMeasure, User, Role, Permission.

### Finance
Owns cash/bank movement, payment requests, AP/AR visibility, and daily sales recording.

Entities: DailySale, DailySaleLine, BankTransaction, PaymentRequest, PaymentApproval, Expenditure, Payable, Receivable.

### Logistics
Owns purchase orders, receiving, stock, ingredient usage, stock opname, adjustment, recipe.

Entities: PurchaseOrder, PurchaseOrderLine, Receiving, ReceivingLine, StockMovement, StockBalance, StockCount, StockAdjustment, Recipe, RecipeIngredient.

### Manufacturing
Optional phase for central kitchen.

Entities: ProductionOrder, RawMaterialIssue, FinishedGoodReceipt, DeliveryOrder, InternalInvoice.

### Accounting
Owns journal, ledger, trial balance, financial statements, period closing.

Entities: Journal, JournalEntry, LedgerBalance, TrialBalance, PeriodClosing.

### Reporting
Read models, dashboards, exports.

Entities: ReportSnapshot, ExportJob, ScheduledReport.

## Cross-Context Rules

1. Source modules create accounting journals through Accounting service.
2. Accounting does not mutate source transactions.
3. Source transaction and journal creation must happen in one database transaction.
4. Reporting reads from source tables and accounting tables; it must not own business transactions.
5. Closed period blocks source context changes unless period is reopened.
