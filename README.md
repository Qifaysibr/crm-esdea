# CRM & Sales Management System - PT Esdea Assistance Management

Sistem manajemen CRM dan Sales untuk PT Esdea Assistance Management dengan fitur multi-role, quotation generator, tracking komisi, dan analytics.

## Fitur Utama

- **Dashboard Analytics** - Funnel metrics, grafik penjualan, leaderboard
- **Lead Management** - CRUD leads, import/export Excel, WhatsApp integration
- **Quotation Generator** - Multi-product, diskon, auto PDF 3 halaman
- **Earnings & Commission** - Tracking komisi dengan logika double commission
- **Team Monitor** - Monitoring performa tim dan individu
- **Marketing Assets** - Library untuk sales kit dan materi promosi

## Requirements

- PHP >= 8.2
- MySQL >= 5.7
- Composer
- Node.js & NPM (untuk build assets)

## Installation (Web Hosting)

1. Upload semua file ke hosting Anda
2. Buat database MySQL baru
3. Copy `.env.example` ke `.env` dan konfigurasi:
   ```
   DB_DATABASE=nama_database_anda
   DB_USERNAME=user_database
   DB_PASSWORD=password_database
   APP_URL=https://domain-anda.com
   ```

4. Jalankan via SSH atau terminal hosting:
   ```bash
   composer install --optimize-autoloader --no-dev
   php artisan key:generate
   php artisan migrate --force
   php artisan db:seed
   php artisan storage:link
   ```

5. Build assets (jika ada perubahan):
   ```bash
   npm install
   npm run build
   ```

6. Set permission folder:
   ```bash
   chmod -R 755 storage bootstrap/cache
   ```

## Default Login

Setelah seeding, gunakan:
- **Admin**: admin@esdea.com / password
- **Manager**: manager@esdea.com / password
- **Leader**: leader@esdea.com / password
- **Sales**: sales@esdea.com / password

⚠️ **PENTING**: Ubah password default setelah login pertama kali!

## Role & Permissions

- **Admin** - Full access ke semua fitur
- **Manager** - Team monitoring, approve quotations, view all earnings
- **Leader** - Team performance, own team data
- **Sales** - Lead management, create quotations, view own earnings

## Tech Stack

- Laravel 11.x
- TailwindCSS + Alpine.js
- Chart.js untuk visualisasi
- DomPDF untuk PDF generation
- PhpSpreadsheet untuk Excel import/export
- MySQL database

## Support

Untuk bantuan teknis, hubungi developer atau baca dokumentasi di `/docs` folder.
