# Airline Booking System - Backend

This is the backend API for the Airline Booking System, built with Laravel. It provides endpoints for managing airlines, flights, bookings, and user authentication.

## Technologies Used

- **PHP 8.2+**
- **Laravel 12**
- **MySQL/MariaDB**
- **Laravel Sanctum** for API authentication
- **Vite** for asset compilation

## Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL/MariaDB
- Node.js and NPM

## Setup Instructions

### 1. Clone the repository

```bash
git clone <repository-url>
cd hackaton_is_planning/backend
```

### 2. Install dependencies

```bash
composer install
npm install
```

### 3. Configure environment variables

```bash
cp .env.example .env
```

Edit the `.env` file and configure your database settings:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password
```

### 4. Generate application key

```bash
php artisan key:generate
```

### 5. Run migrations

```bash
php artisan migrate
```

### 6. Seed the database (optional)

```bash
php artisan db:seed
```

### 7. Create symbolic link for storage

```bash
php artisan storage:link
```

## Running the Application

### Development server

```bash
php artisan serve
```

This will start the development server at http://localhost:8000

### Asset compilation

```bash
npm run dev
```

### Running both server and asset compilation concurrently

```bash
composer run dev
```

## API Endpoints

### Authentication

- `POST /api/auth/register` - Register a new user
- `POST /api/auth/login` - Login a user
- `POST /api/auth/logout` - Logout a user (requires authentication)
- `GET /api/user` - Get authenticated user details

### Airlines

- `GET /api/airlines` - Get all airlines
- `GET /api/airlines/{airline}` - Get specific airline details

### Enterprise-only endpoints (requires enterprise account)

- `POST /api/enterprise/airlines` - Create a new airline
- `PUT /api/enterprise/airlines/{airline}` - Update an airline
- `DELETE /api/enterprise/airlines/{airline}` - Delete an airline
- `POST /api/enterprise/flights` - Add a new flight
- `DELETE /api/enterprise/flights/{flight}` - Delete a flight

### Flights

- `GET /api/flights/{flight}` - Get flight details

## User Types

The system supports two types of users:
- **Client** - Regular users who can book flights
- **Enterprise** - Airline companies who can manage their flights

## License

This project is open-sourced software.
