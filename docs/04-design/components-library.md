# Component Library

## Shared Components

1. AppLayout
2. SidebarNavigation
3. Topbar
4. Breadcrumbs
5. AdminPageHeader
6. SurfaceCard
7. MetricCard
8. DataTableCard
9. DataTableSearch
10. DataTableFilterSelect
11. DataTablePagination
12. RowActionMenu
13. MoneyInput
14. DateRangePicker
15. OutletSelector
16. StatusBadge
17. ApprovalTimeline
18. AuditLogPanel
19. ConfirmDeleteDialog
20. FileUploader
21. ExportButton
22. EmptyState
23. LoadingSkeleton
24. FormSection
25. PeriodStatusBanner
26. RequiredLabel

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
3. `DataTableCard` harus menjadi pola utama untuk list page.
4. AuditLogPanel wajib tersedia di detail financial transaction.
5. ApprovalTimeline wajib tersedia di approval-based transaction.
6. JournalEntriesEditor harus memvalidasi total debit = total credit di UI sebelum submit, tetapi server tetap sumber kebenaran.
7. SurfaceCard cukup wrapper class/style; jangan bikin abstraction bila hanya dipakai satu halaman.
8. MetricCard hanya untuk dashboard/report summary, bukan pengganti table data utama.

## Standar Visual Komponen

1. `AdminPageHeader`: eyebrow, title, description, primary action; radius `lg`, border halus, gradient tipis opsional.
2. `SurfaceCard`: `rounded-lg border bg-card shadow-sm`; dipakai untuk table, form section, dan report block.
3. `MetricCard`: angka besar rata kiri, label kecil, trend badge opsional; maksimal 4 card per row desktop.
4. `DataTableCard`: table shell untuk title, description, filter, meta, table, dan footer.
5. `DataTableSearch`: search kiri di header table; clear button wajib flush langsung.
6. `DataTableFilterSelect`: filter/status kanan search; native select, bukan custom dropdown.
7. `DataTablePagination`: footer pagination standar Inertia link.
8. `RowActionMenu`: dropdown tunggal untuk seluruh tindakan pada satu baris tabel.
9. `ConfirmDeleteDialog`: standar konfirmasi hapus; pakai untuk semua action destructive di admin, jangan pakai `window.confirm`.

## Admin List Standard

1. `AdminPageHeader`: `resources/js/components/admin/AdminPageHeader.vue`.
2. `DataTableCard`: `resources/js/components/admin/DataTableCard.vue`.
3. `DataTableSearch`: `resources/js/components/admin/DataTableSearch.vue`.
4. `DataTableFilterSelect`: `resources/js/components/admin/DataTableFilterSelect.vue`.
5. `DataTablePagination`: `resources/js/components/admin/DataTablePagination.vue`.
6. `RowActionMenu`: `resources/js/components/admin/RowActionMenu.vue`.
7. Urutan halaman list: `AdminPageHeader` → `DataTableCard` filters/meta → `<thead>` + `<tbody>` → `DataTablePagination`.
8. Kolom dan cell tetap milik halaman karena tiap modul beda field; shell, filter, pagination, dan action wajib pakai komponen standar.
9. Filter request wajib pakai `resources/js/lib/debounce.ts`: search `400ms`, request filter `150ms`, clear/reset flush langsung.
10. Pilihan jumlah data `10`, `25`, `50` ditempatkan paling kiri pada baris filter.
11. Aksi tabel memakai satu `RowActionMenu`: **Lihat**, **Edit**, status, lalu **Hapus** paling bawah setelah pemisah.
12. Aksi **Lihat** selalu tersedia. Aksi tambah, edit, status, dan hapus hanya tampil jika pengguna memiliki izin terkait.

## ConfirmDeleteDialog

1. Lokasi komponen: `resources/js/components/ConfirmDeleteDialog.vue`.
2. Trigger dari row action icon `Trash2` dengan style danger.
3. Teks wajib menyebut objek dan akibat penghapusan dengan bahasa pengguna. Jangan tampilkan istilah teknis seperti `soft delete` atau `audit log`.
4. Confirm button pakai `variant="destructive"`, disabled saat `processing`.
5. Cancel button pakai `variant="outline"`, menutup modal tanpa request.
6. Master data memakai soft delete; hard delete hanya boleh jika dokumen modul menyebut eksplisit.

## Standar Bahasa Pengguna

1. Teks antarmuka menjelaskan tujuan, akibat, dan tindakan berikutnya dengan bahasa sehari-hari.
2. Jangan tampilkan istilah teknis internal seperti `soft delete`, `audit log`, `scope`, `optimistic locking`, nama tabel, route, atau permission.
3. Jika istilah bisnis perlu dipakai, berikan arti singkat saat pengguna pertama kali melihatnya.
4. Pesan gagal menyebut penyebab yang dapat dipahami dan langkah yang dapat dilakukan pengguna.
5. Dokumentasi teknis tetap boleh memakai istilah teknis; aturan ini berlaku untuk teks yang tampil kepada pengguna.
