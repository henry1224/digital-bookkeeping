# Operations Runbook

## Reset User Password

1. Admin IT membuka User Management.
2. Pilih user.
3. Klik Reset Password.
4. System mengirim reset link atau temporary password.
5. Audit log mencatat action.

## Reopen Closed Period

1. Accounting mengajukan reopen dengan reason.
2. Owner melakukan approval.
3. System menandai period sebagai reopened.
4. Koreksi diposting lewat reversal/adjustment/source correction yang diaudit.
5. Period ditutup kembali.
6. Audit log mencatat semua action.

## Backup Verification

1. Cek status daily backup job.
2. Verifikasi backup file ada.
3. Verifikasi backup file size tidak nol.
4. Jalankan monthly restore test ke staging.
5. Catat hasil restore test.

## Incident: System Down

1. Cek server health.
2. Cek web server logs.
3. Cek application logs.
4. Cek database connectivity.
5. Cek queue worker dan scheduler.
6. Restore latest stable release jika outage disebabkan deploy.
7. Notify stakeholders.
8. Catat timeline incident.

## Incident: Journal Tidak Balance

1. Blokir posting journal terkait.
2. Cek source transaction dan journal entries.
3. Jangan edit posted journal langsung.
4. Jika sudah posted, buat reversal/correction journal.
5. Catat audit reason.
6. Tambahkan regression test untuk kasus tersebut.

## Incident: Salah Input di Closed Period

1. Jangan update database langsung.
2. Accounting mengajukan reopen dengan reason.
3. Owner approve reopen.
4. Lakukan koreksi terkontrol.
5. Tutup kembali period.
6. Verifikasi laporan berubah sesuai koreksi.
