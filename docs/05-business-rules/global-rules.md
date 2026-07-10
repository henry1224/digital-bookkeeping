# Global Business Rules

## Time and Currency

1. Store timestamps in UTC.
2. Display timezone as WITA (UTC+8).
3. Currency is IDR.
4. Money must not use float.
5. Display money as `Rp 1.234.567`.

## Data Integrity

1. Master data uses soft delete.
2. Financial transactions must not be hard-deleted.
3. Closed-period data cannot be edited.
4. All financial postings must be auditable.
5. All important state changes require audit log.

## Authorization

1. Every page requires authenticated user.
2. Every action requires permission.
3. Outlet-scoped users can only access assigned outlet.
4. Management/Owner can view consolidated data.
5. Admin IT manages users and roles but should not post accounting journals by default.

## Files

1. Allowed upload: PDF, JPG, PNG, XLSX, CSV.
2. Max invoice attachment size: 10MB.
3. Store files with generated names, not original names.
4. Keep original filename as metadata.
