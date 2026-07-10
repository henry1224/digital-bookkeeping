# Deployment Guide

## Environments

1. Local
2. Staging
3. Production

## Required Services

1. PHP runtime compatible with Laravel latest
2. PostgreSQL
3. Redis (queue/cache optional but recommended)
4. Queue worker
5. Scheduler cron
6. Web server (Nginx/Apache)
7. SSL certificate

## Environment Variables

See `.env.example` in project implementation.

Critical values:

1. APP_KEY
2. APP_ENV
3. APP_URL
4. DB_* credentials
5. MAIL_* credentials
6. FILESYSTEM_DISK
7. QUEUE_CONNECTION
8. BACKUP_* settings

## Deploy Steps

1. Pull latest release tag.
2. Install PHP dependencies.
3. Install/build frontend assets.
4. Run migrations.
5. Clear and cache config/routes/views.
6. Restart queue workers.
7. Run smoke test.
8. Monitor logs.

## Rollback

1. Put app in maintenance mode if needed.
2. Restore previous release.
3. Rollback migration only if safe; otherwise restore database backup.
4. Restart services.
