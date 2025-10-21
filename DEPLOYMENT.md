# HPLink SMS Server PRO - Deployment Guide

## Overview

This guide provides step-by-step instructions for deploying the HPLink SMS Server PRO application to a production environment.

## Prerequisites

- Ubuntu 20.04 or higher (or similar Linux distribution)
- Root or sudo access
- Domain name pointed to your server

## Step 1: Server Setup

### Update System Packages

```bash
sudo apt update && sudo apt upgrade -y
```

### Install Required Software

```bash
# Install Nginx
sudo apt install nginx -y

# Install PHP 8.2 and extensions
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.2-fpm php8.2-cli php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath -y

# Install MySQL
sudo apt install mysql-server -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Install Node.js and NPM
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs -y

# Install Redis (optional, for queue)
sudo apt install redis-server -y
sudo systemctl enable redis-server
```

## Step 2: MySQL Database Setup

```bash
# Login to MySQL
sudo mysql

# Create database and user
CREATE DATABASE hplink_sms;
CREATE USER 'hplink_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON hplink_sms.* TO 'hplink_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## Step 3: Application Setup

### Clone Repository

```bash
cd /var/www
sudo git clone https://github.com/fahim8401/sms-api.git hplink_sms
cd hplink_sms
sudo chown -R www-data:www-data /var/www/hplink_sms
sudo chmod -R 755 /var/www/hplink_sms
sudo chmod -R 775 /var/www/hplink_sms/storage
sudo chmod -R 775 /var/www/hplink_sms/bootstrap/cache
```

### Install Dependencies

```bash
composer install --optimize-autoloader --no-dev
npm install && npm run build
```

### Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` file:

```bash
sudo nano .env
```

Update the following values:

```env
APP_NAME=HPLink_SMS
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sms.hplink.com.bd

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hplink_sms
DB_USERNAME=hplink_user
DB_PASSWORD=strong_password_here

DIGITALSQUARE_APIKEY=your_actual_api_key
DIGITALSQUARE_SECRET=your_actual_secret
DIGITALSQUARE_BASEURL=http://isms.digitalsquare.ltd:5683

SMS_COST_DEFAULT=0.30
PDF_STORAGE=storage/invoices
QUEUE_CONNECTION=database
```

### Run Migrations and Seeders

```bash
php artisan migrate --force
php artisan db:seed --class=InitialDataSeeder
php artisan storage:link
```

### Optimize Application

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Step 4: Nginx Configuration

Create Nginx configuration:

```bash
sudo nano /etc/nginx/sites-available/hplink_sms
```

Add the following configuration:

```nginx
server {
    listen 80;
    server_name sms.hplink.com.bd;
    root /var/www/hplink_sms/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/hplink_sms /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

## Step 5: SSL Certificate Setup

Install Certbot:

```bash
sudo apt install certbot python3-certbot-nginx -y
```

Obtain SSL certificate:

```bash
sudo certbot --nginx -d sms.hplink.com.bd
```

Follow the prompts to complete SSL setup. Certbot will automatically configure Nginx for HTTPS.

## Step 6: Queue Worker Setup

Create a systemd service for the queue worker:

```bash
sudo nano /etc/systemd/system/laravel-worker.service
```

Add the following content:

```ini
[Unit]
Description=Laravel Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/hplink_sms/artisan queue:work --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
```

Enable and start the service:

```bash
sudo systemctl daemon-reload
sudo systemctl enable laravel-worker
sudo systemctl start laravel-worker
```

Check status:

```bash
sudo systemctl status laravel-worker
```

## Step 7: Cron Job for Laravel Scheduler

Add Laravel scheduler to crontab:

```bash
sudo crontab -e -u www-data
```

Add this line:

```
* * * * * cd /var/www/hplink_sms && php artisan schedule:run >> /dev/null 2>&1
```

## Step 8: Firewall Configuration

```bash
sudo ufw allow 'Nginx Full'
sudo ufw allow OpenSSH
sudo ufw enable
```

## Step 9: Security Hardening

### Disable PHP Info

Ensure `APP_DEBUG=false` in `.env`

### Set Proper File Permissions

```bash
cd /var/www/hplink_sms
sudo chown -R www-data:www-data .
sudo find . -type f -exec chmod 644 {} \;
sudo find . -type d -exec chmod 755 {} \;
sudo chmod -R 775 storage bootstrap/cache
```

### Configure PHP Security

Edit PHP configuration:

```bash
sudo nano /etc/php/8.2/fpm/php.ini
```

Update these settings:

```ini
expose_php = Off
max_execution_time = 30
max_input_time = 60
memory_limit = 256M
upload_max_filesize = 10M
post_max_size = 10M
```

Restart PHP-FPM:

```bash
sudo systemctl restart php8.2-fpm
```

## Step 10: Backup Configuration

### Database Backup Script

Create a backup script:

```bash
sudo nano /usr/local/bin/backup-hplink.sh
```

Add:

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/hplink_sms"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u hplink_user -p'strong_password_here' hplink_sms > $BACKUP_DIR/db_$DATE.sql

# Backup application files (excluding vendor and node_modules)
tar -czf $BACKUP_DIR/files_$DATE.tar.gz -C /var/www/hplink_sms --exclude=vendor --exclude=node_modules --exclude=storage/logs .

# Keep only last 7 days of backups
find $BACKUP_DIR -type f -mtime +7 -delete

echo "Backup completed: $DATE"
```

Make executable:

```bash
sudo chmod +x /usr/local/bin/backup-hplink.sh
```

Schedule daily backups:

```bash
sudo crontab -e
```

Add:

```
0 2 * * * /usr/local/bin/backup-hplink.sh >> /var/log/hplink-backup.log 2>&1
```

## Step 11: Monitoring and Logs

### View Application Logs

```bash
tail -f /var/www/hplink_sms/storage/logs/laravel.log
```

### View Nginx Logs

```bash
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log
```

### View Queue Worker Logs

```bash
sudo journalctl -u laravel-worker -f
```

## Step 12: Post-Deployment Testing

### Test Application Access

1. Visit https://sms.hplink.com.bd
2. Login with admin credentials: admin@hplink.com.bd / admin123
3. Verify admin dashboard loads

### Test API Endpoints

```bash
# Test balance check
curl "https://sms.hplink.com.bd/api/getBalance?api_key=YOUR_API_KEY"

# Test send SMS (will require valid gateway credentials)
curl "https://sms.hplink.com.bd/api/smsapi2?api_key=YOUR_API_KEY&type=text&contacts=1234567890&senderid=TEST&msg=Hello"
```

## Step 13: Maintenance

### Update Application

```bash
cd /var/www/hplink_sms
git pull origin main
composer install --optimize-autoloader --no-dev
npm install && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo systemctl restart laravel-worker
```

### Clear Caches

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Troubleshooting

### Permission Issues

```bash
sudo chown -R www-data:www-data /var/www/hplink_sms
sudo chmod -R 775 storage bootstrap/cache
```

### Queue Not Processing

```bash
sudo systemctl restart laravel-worker
sudo journalctl -u laravel-worker -n 50
```

### Application Not Loading

```bash
# Check Nginx status
sudo systemctl status nginx

# Check PHP-FPM status
sudo systemctl status php8.2-fpm

# Check error logs
tail -f /var/log/nginx/error.log
tail -f /var/www/hplink_sms/storage/logs/laravel.log
```

## Important Notes

1. **Change Default Passwords**: Immediately change all default user passwords after deployment
2. **Configure Gateway**: Update DigitalSquare API credentials in admin settings
3. **Monitor Resources**: Keep an eye on disk space, especially for logs and database
4. **Regular Updates**: Keep Laravel and dependencies updated for security patches
5. **Backup Regularly**: Ensure automated backups are working properly

## Support

For issues or questions:
- Check application logs: `/var/www/hplink_sms/storage/logs/laravel.log`
- Review Nginx logs: `/var/log/nginx/error.log`
- Check queue worker: `sudo journalctl -u laravel-worker -f`

## Security Checklist

- [ ] SSL certificate installed and working
- [ ] Default passwords changed
- [ ] Firewall configured
- [ ] APP_DEBUG=false in production
- [ ] Database credentials secured
- [ ] File permissions set correctly
- [ ] Automated backups configured
- [ ] Queue worker running
- [ ] Logs being rotated
- [ ] Server resources monitored
