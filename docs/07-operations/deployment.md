# Deployment Guide

## Environments

1. Local
2. Staging
3. Production

## Required Services

1. PHP runtime yang kompatibel dengan Laravel terbaru
2. PostgreSQL
3. Redis untuk queue/cache jika diaktifkan
4. Queue worker
5. Scheduler cron
6. Web server (Nginx/Apache)
7. SSL certificate
8. Backup job untuk database dan storage

## Environment Variables

Lihat `.env.example` pada implementasi project.

Critical values:

1. APP_KEY
2. APP_ENV
3. APP_URL
4. DB_* credentials
5. MAIL_* credentials
6. FILESYSTEM_DISK
7. QUEUE_CONNECTION
8. BACKUP_* settings
9. SESSION_SECURE_COOKIE untuk production
10. LOG_CHANNEL/LOG_LEVEL

## Deploy Steps

1. Pull latest release tag.
2. Install PHP dependencies.
3. Install/build frontend assets.
4. Run migrations.
5. Run seeders khusus reference/master jika diperlukan.
6. Clear dan cache config/routes/views.
7. Restart queue workers.
8. Run smoke test.
9. Monitor logs.

## Smoke Test Minimal

1. Login sebagai Admin IT.
2. Login sebagai Accounting.
3. Buka dashboard.
4. Buka COA.
5. Buat draft daily sales di staging.
6. Pastikan journal posting test balance.
7. Generate Laba Rugi sample.
8. Cek queue worker aktif.

## Rollback

1. Aktifkan maintenance mode jika diperlukan.
2. Restore previous release.
3. Rollback migration hanya jika aman; jika tidak, restore database backup.
4. Restart services.
5. Verifikasi smoke test.
6. Catat incident dan action di runbook.
