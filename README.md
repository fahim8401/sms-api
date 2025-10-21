# HPLink SMS Server PRO

A comprehensive SMS API Server built with Laravel 11, featuring admin panel, reseller system, and RESTful API endpoints.

## Features

- ✅ Laravel 11 Backend with Bootstrap 5 Frontend
- ✅ Public API (Maestro-style)
- ✅ DigitalSquare Gateway Integration
- ✅ DLR + Balance API
- ✅ Reseller System + Credit Management
- ✅ Per-User SMS Rates
- ✅ DLR Webhook Auto Update
- ✅ PDF Invoice Generator for Resellers
- ✅ Role-based Access Control (Admin, Reseller, User)

## Tech Stack

- Laravel 11 (Backend + Blade Frontend)
- Bootstrap 5 via CDN
- MySQL Database
- Laravel Breeze Authentication
- DigitalSquare SMS Gateway
- barryvdh/laravel-dompdf for PDF generation
- Queue: Redis or Database

## Installation

### Requirements

- PHP 8.2 or higher
- Composer
- MySQL 5.7+
- Node.js & NPM

### Setup Instructions

1. **Clone the repository**
```bash
git clone <repository-url>
cd sms-api
```

2. **Install dependencies**
```bash
composer install
npm install && npm run build
```

3. **Configure environment**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Update .env file** with your database and gateway credentials:
```env
APP_NAME=HPLink_SMS
APP_URL=https://sms.hplink.com.bd

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hplink_sms
DB_USERNAME=root
DB_PASSWORD=

DIGITALSQUARE_APIKEY=your_api_key_here
DIGITALSQUARE_SECRET=your_secret_here
DIGITALSQUARE_BASEURL=http://isms.digitalsquare.ltd:5683

SMS_COST_DEFAULT=0.30
PDF_STORAGE=storage/invoices
QUEUE_CONNECTION=database
```

5. **Run migrations and seeders**
```bash
php artisan migrate
php artisan db:seed --class=InitialDataSeeder
```

6. **Create storage link**
```bash
php artisan storage:link
```

7. **Start the application**
```bash
php artisan serve
```

## Default Credentials

After running the seeder, you can login with:

- **Admin**: admin@hplink.com.bd / admin123
- **Reseller**: reseller@hplink.com.bd / reseller123
- **User**: user@hplink.com.bd / user123

## API Endpoints

### Send SMS
```
GET https://sms.hplink.com.bd/api/smsapi2
Parameters:
  - api_key: Your API key
  - type: text or unicode
  - contacts: Phone number
  - senderid: Sender ID
  - msg: Message text
```

### Check Balance
```
GET https://sms.hplink.com.bd/api/getBalance
Parameters:
  - api_key: Your API key
```

### Get Delivery Report
```
GET https://sms.hplink.com.bd/api/getDLR
Parameters:
  - message_id: Message ID from send response
```

### DLR Webhook (for DigitalSquare)
```
POST https://sms.hplink.com.bd/api/webhook/dlr
Body (JSON):
{
  "apikey": "YOUR_API_KEY",
  "secretkey": "YOUR_SECRET",
  "Message_ID": "31771702",
  "text": "DELIVRD"
}
```

## Database Structure

### users
- id, name, email, password, api_key, balance, rate, role, parent_id, status, created_at

### sms_logs
- id, user_id, to, senderid, message, cost, status, message_id, delivery_text, created_at

### gateways
- id, name, api_url, api_key, secret_key, status, created_at

### transactions
- id, user_id, type, amount, description, created_at

## Admin Panel

Access the admin panel at `/admin/dashboard` with admin credentials.

**Features:**
- Dashboard with statistics
- SMS logs with filtering
- User management (create, edit, add credits)
- Gateway settings
- View all transactions

## Reseller Panel

Access the reseller panel at `/reseller/dashboard` with reseller credentials.

**Features:**
- Dashboard with statistics
- Create sub-users
- Transfer credits to sub-users
- View SMS logs
- Generate PDF invoices

## User Dashboard

Regular users can access their dashboard at `/dashboard` to:
- View balance and API key
- See SMS rate
- Access API documentation
- View API endpoints

## Credit System

- Each user has a custom `rate` (e.g., 0.30 per SMS)
- Sending an SMS automatically deducts user balance
- Resellers can transfer credits to sub-users
- Admin can set reseller and user rates individually
- All transactions are logged

## PDF Invoice Generation

Resellers can download monthly transaction reports as PDF from `/reseller/invoices`.

The invoice includes:
- User information
- All transactions for the selected month
- SMS details (up to 50 records shown)
- Summary with totals

## Queue Setup

For production, configure queue workers:

```bash
php artisan queue:work --daemon
```

Or use supervisor to keep the queue worker running.

## Production Deployment

1. **Optimize the application**
```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

2. **Set proper permissions**
```bash
chmod -R 755 storage bootstrap/cache
```

3. **Configure web server** (Nginx/Apache)

4. **Setup SSL certificate** (Let's Encrypt recommended)

5. **Configure queue worker** with Supervisor

## Security

- API authentication via unique API keys
- Role-based access control
- CSRF protection on web routes
- Password hashing with bcrypt
- Rate limiting on API routes (recommended)
- Force HTTPS in production

## Support

For issues and questions, please open an issue on GitHub.

## License

This project is open-source software.