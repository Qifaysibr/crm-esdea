# CRM Esdea - Deployment Checklist

## 📋 Pre-Deployment

- [ ] Upload semua file ke hosting
- [ ] Create database MySQL
- [ ] Copy `.env.example` ke `.env`
- [ ] Configure `.env` (database, APP_URL, etc.)

## 🔧 Installation Commands

```bash
# Install dependencies
composer install --optimize-autoloader --no-dev

# Generate app key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Seed database
php artisan db:seed

# Link storage
php artisan storage:link

# Build frontend (if Node.js available)
npm install
npm run build

# Set permissions
chmod -R 755 storage bootstrap/cache
```

## ✅ Post-Deployment

- [ ] Test login dengan default accounts
- [ ] Change all default passwords
- [ ] Test create lead
- [ ] Test create quotation
- [ ] Test PDF generation
- [ ] Test commission calculation
- [ ] Verify role-based access

## 🔐 Default Logins

**Email** / **Password**
- admin@esdea.com / password
- manager@esdea.com / password
- leader@esdea.com / password
- sales@esdea.com / password

⚠️ **WAJIB ubah semua password setelah login!**

## 📊 Seeded Data

- 4 Roles (Admin, Manager, Leader, Sales)
- 4 Users
- 6 Lead Statuses
- 2 Product Categories
- 5 Products
- Commission Rules

## 🚀 Production Optimizations

```bash
# Cache config
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache
```

## 🐛 Troubleshooting

**Issue**: 500 Error
- Check `.env` file exists and configured
- Check storage folder permissions
- Check database connection

**Issue**: Styles not loading
- Run `npm run build`
- Check public/build folder exists
- Clear browser cache

**Issue**: Login not working
- Run `php artisan migrate`
- Run `php artisan db:seed`
- Check users table in database

## 📞 Support

Refer to:
- `README.md` - Installation guide
- `DEVELOPMENT_SUMMARY.md` - Technical details
- `walkthrough.md` - Feature documentation
