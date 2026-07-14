# Aturan Bisnis Global

## Waktu dan Currency

1. Simpan timestamp dalam UTC.
2. Tampilkan timezone sebagai WITA (UTC+8 / Asia/Makassar).
3. Currency adalah IDR.
4. Uang tidak boleh memakai float.
5. Simpan uang sebagai `DECIMAL(18,2)`.
6. Display money sebagai `Rp 1.234.567`.
7. API menerima/mengembalikan money sebagai string decimal jika perlu menjaga precision.

## Integritas Data

1. Master data memakai soft delete.
2. Financial transactions tidak boleh hard-delete.
3. Closed-period data tidak boleh diedit.
4. Semua financial posting harus auditable.
5. Semua state change penting harus membuat audit log.
6. Posted journal immutable; koreksi harus lewat reversal atau adjustment.
7. Setiap financial transaction harus journaled atau marked `non_posting` dengan reason.

## Authorization

1. Setiap page membutuhkan authenticated user.
2. Setiap action membutuhkan permission.
3. User outlet-scoped hanya boleh akses outlet yang ditugaskan.
4. Management/Owner bisa melihat consolidated data.
5. Admin IT mengelola users dan roles tetapi tidak boleh post accounting journals secara default.
6. Creator tidak boleh approve request sendiri kecuali policy eksplisit mengizinkan.

## Files

1. Upload yang diizinkan: PDF, JPG, PNG, XLSX, CSV.
2. Max invoice attachment size: 10MB.
3. Simpan file dengan generated names, bukan original names.
4. Simpan original filename sebagai metadata.
5. Scan/validasi MIME di server; jangan percaya extension saja.

## Batasan MVP

1. Backoffice web saja.
2. Tidak menggantikan POS.
3. Tidak membuat mobile app.
4. Manufacturing Central Kitchen tidak termasuk MVP.
5. Central Kitchen pada MVP adalah outlet/storage.
