# Simple Hotel System

## Description
The **Simple Hotel System** is a Laravel-based web application designed to streamline hotel management. It enables admins, managers, and receptionists to efficiently handle hotel operations, while clients can seamlessly make and manage reservations.

## Features
- **User Roles & Permissions**
  - **Admin**: Has full access to all system functionalities.
  - **Manager**: Oversees receptionists, floors, rooms, and client management.
  - **Receptionist**: Manages client approvals and reservations.
  - **Client**: Books rooms and manages personal reservations.
- **Hotel Management**
  - CRUD operations for managers, receptionists, clients, floors, and rooms.
  - Room pricing stored in cents and displayed in dollars.
- **Reservation System**
  - Clients can book available rooms with online payment (Stripe integration).
  - Receptionists handle client reservations and approvals.
- **Advanced Features**
  - DataTables with server-side pagination, search, and sorting.
  - ChartJS-powered statistics dashboard (reservations, revenue, top clients, etc.).
  - Scheduled email reminders for inactive clients.
  - API authentication using Laravel Sanctum.

## Installation
### Prerequisites
- PHP 8+
- Laravel 12+
- MySQL
- Node.js & npm
- Composer

### Setup Instructions
1. **Clone the repository**
   ```sh
   git clone https://github.com/Ali-LGendy/HotelSystem_Project.git
   cd HotelSystem_Project
   ```
2. **Install dependencies**
   ```sh
   composer install
   npm install
   ```
3. **Setup environment variables**
   ```sh
   cp .env.example .env
   ```
   - Configure database settings in `.env` file.
4. **Generate application key**
   ```sh
   php artisan key:generate
   ```
5. **Run database migrations & seeders**
   ```sh
   php artisan migrate --seed
   ```
6. **Run the application**
   ```sh
   php artisan serve
   composer run dev
   ```

## Default Admin Credentials
- **Email:** `admin@admin.com`
- **Password:** `123456`
- By default, only **one** admin exists in the system, seeded via Laravel seeders.
- Additional admins can be created using a console command:
  ```sh
  php artisan create:admin --name=admin2@admin.com --password=123456
  ```

## Usage
- **Admin**: Manages managers, receptionists, clients, floors, and rooms, and has full access to system settings.
- **Manager**: Oversees receptionists, floors, rooms, and client management.
- **Receptionist**: Approves client registrations and manages reservations.
- **Client**: Registers, makes reservations, and manages bookings.

## Technologies Used
- **Backend:** Laravel, MySQL
- **Frontend:** Inertia.js, Vue.js, Tailwind CSS
- **Charts & Graphs:** Chart.js
- **Payments:** Stripe

##  Next Steps
- **Multi-Hotel Support:** Extend the system to manage multiple hotels within one platform.
- **Enhanced Payment Options:** Add support for PayPal, Apple Pay, and Google Pay.
- **Mobile App Integration:** Develop a mobile-friendly version using Laravel API.
- **Feedback & Reviews:** Allow clients to leave ratings and reviews for their hotel stays.


## License
This project is open-source under the **MIT License**.

