## Introduction

A comprehensive web-based Sales Order Management System for creating and managing sales orders, invoices, purchase orders, and related business operations.

## Tech Stack

- **PHP** 8.2+
- **Laravel** 10
- **Livewire** 2
- **MySQL** 5.7+
- **AdminLTE** 3 (UI theme)
- **Bootstrap** 5 / Laravel Mix

## Features

- Sales order creation and management
- Invoice and credit memo management
- Purchase order tracking
- Remittance tracking
- Product and brand management
- Sales dashboard and productivity reports
- Account management and login tracking
- Organizational structure and department management
- Schedules, territories, and trip planning
- Activity planning and pre-planning
- Role-based access control (RBAC)
- Activity logging and audit trail
- File upload templates
- System logs viewer
- QR code and barcode generation
- PDF generation
- Excel import/export
- PWA support

## Requirements

- PHP >= 8.2
- MySQL >= 5.7
- Node.js & NPM
- Composer

## Packages

**Backend**

- laravel/framework ^10
- livewire/livewire ^2
- spatie/laravel-permission ^6 (RBAC)
- spatie/laravel-activitylog ^4 (audit trail)
- maatwebsite/excel ^3 (Excel import/export)
- barryvdh/laravel-dompdf ^2 (PDF generation)
- milon/barcode ^10 (barcode generation)
- simplesoftwareio/simple-qrcode ~4 (QR codes)
- silviolleite/laravelpwa ^2 (PWA)
- intervention/image ^2 (image handling)
- laravel/sanctum ^3 (API authentication)
- laravel/socialite ^5 (OAuth)
- spatie/simple-excel ^3 (Excel utilities)
- opencage/geocode ^3 (geocoding)
- rap2hpoutre/laravel-log-viewer ^2

**Frontend**

- bootstrap ^5
- highcharts ^11 (charts)
- gsap ^3 (animations)
- orgchart ^3 (org chart)

## Installation

1. Clone the repository

```bash
git clone https://github.com/webdev-jm/sales-order.git sales-order
```

2. Install PHP and Node dependencies

```bash
composer install
npm install && npm run build
```

3. Set up environment

```bash
cp .env.example .env
php artisan key:generate
```

4. Configure your database in `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=root
DB_PASSWORD=
```

5. Run migrations and seeders

```bash
php artisan migrate
php artisan db:seed
```

## Database Structure

See the [Sales Order Entry ERD](https://dbdiagram.io/d/62d4c341cc1bc14cc5d6590c) for the full database schema.
