# Railway deployment notes

## Required app variables

- `APP_ENV=production`
- `APP_TIMEZONE=America/Costa_Rica`
- `BASE_URL=/`

## Database variables

This app supports both generic variables and Railway MySQL variables:

- Generic: `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`
- Railway MySQL: `MYSQLHOST`, `MYSQLPORT`, `MYSQLDATABASE`, `MYSQLUSER`, `MYSQLPASSWORD`

## Mailtrap variables (optional)

- `MAILTRAP_ENABLED=true`
- `MAILTRAP_TRANSPORT=smtp`
- `MAILTRAP_SMTP_HOST=sandbox.smtp.mailtrap.io`
- `MAILTRAP_SMTP_PORT=587`
- `MAILTRAP_SMTP_USERNAME=...`
- `MAILTRAP_SMTP_PASSWORD=...`
- `MAILTRAP_SMTP_ENCRYPTION=tls`
- `MAILTRAP_FROM_EMAIL=no-reply@lacomanda.local`
- `MAILTRAP_FROM_NAME=La Comanda`

## Database initialization

Railway app + Railway MySQL are separate services. After creating the MySQL service, import `db/la_comanda.sql` into the database once.

This repo does not auto-run the SQL import on startup because the SQL file contains `DROP DATABASE` / `CREATE DATABASE` statements, which are not safe to execute automatically in production.
