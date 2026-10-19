# Get & Push – Company Blog (PHP + MySQL)

Setup
1. Import schema.sql in phpMyAdmin (creates database `getandpush`, tables and 3 sample posts).
2. Copy config.example.php to config.php (config.php is git-ignored), then edit it: database user/password, mail_to, mail_from and the admin password.
3. Run: `php -S localhost:8000` (or XAMPP htdocs / any PHP hosting) and open http://localhost:8000
4. Admin: open the Admin page and log in with admin_user / admin_pass from config.php.

Files: index.php, app.js, styles.css, tailwind-config.js, api.php, config.php, schema.sql
Contact form: saved in table `contacts` and emailed with PHP mail() (needs SMTP/sendmail on the server).
Newsletter: saved in table `subscribers`.
If PHP/MySQL is not available, the site falls back to demo mode (browser storage, no login).
