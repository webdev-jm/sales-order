## Introduction

This web application is for the creation and management of order entries.

## Requirements

- PHP 7.3
- MySQL >= 5.7

## Packages
-   jeroennoten/laravel-adminlte ^3.8
-   intervention/image ^2.7
-   laravelcollective/html ^6.3
-   livewire/livewire ^2.10
-   maatwebsite/excel ^3.1
-   rap2hpoutre/laravel-log-viewer ^2.2
-   spatie/laravel-permission ^5.5

## Installation
1. Download this project
- via git bash
```bash
git clone https://github.com/webdev-jm/sales-order.git sales-order
```
- or directly download files

2. go to the newly created folder then run.
```bash
composer update
```
and
```bash
npm install
```

3. Generate new .env file run the ff. in the console

```bash
cp .env.example .env
php artisan key:generate
```

4. Update database configurations at generated .env file

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=(database_name)
DB_USERNAME=root
DB_PASSWORD=
```

5. Run the migration

```bash
php artisan migrate
```

6. Run database seeders to setup permissions, roles and superadmin user

```bash
php artisan db:seed
```

## Database Structure
Check the database Structure at [Sales Order Entry ERD](https://dbdiagram.io/d/62d4c341cc1bc14cc5d6590c) for your reference.
