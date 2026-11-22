# KIDS OWN THE BLOCK — XAMPP Setup Guide

## 1. MySQL Database Setup
1. Start **Apache** and **MySQL** in the **XAMPP Control Panel**.
2. Open `http://localhost/phpmyadmin` and create a database named:
   ```sql
   CREATE DATABASE kids_own_the_block CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Run migrations and seed the catalog + admin user:
   ```bash
   php artisan migrate --seed
   ```
4. **Default Admin Login**:
   - Email: `admin@kotb.club`
   - Password: `password`
   - Admin URL: `/admin`

## 2. Optional XAMPP Virtual Host (`http://kotb.local`)
To serve directly through XAMPP Apache without `php artisan serve`:

1. Add to `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:
   ```apache
   <VirtualHost *:80>
       ServerName kotb.local
       DocumentRoot "C:/Users/jhona/Downloads/KidsOwnTheBlock_Clothing/public"
       <Directory "C:/Users/jhona/Downloads/KidsOwnTheBlock_Clothing/public">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```
2. Add to `C:\Windows\System32\drivers\etc\hosts`:
   ```text
   127.0.0.1 kotb.local
   ```
3. Restart Apache in XAMPP and visit `http://kotb.local`.
