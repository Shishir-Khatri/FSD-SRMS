# Student Record Management System (SMS)

A production-ready Student Record Management System built with PHP and MySQL.

## Features
*   **Role-Based Access Control (RBAC)**: Super Admin, Admin, Student.
*   **Student Management**: Full CRUD operations for student records.
*   **Attendance System**: Daily attendance marking and reporting.
*   **Authentication**: Secure login, logout, and password hashing.
*   **First-Time Login**: Mandatory password reset for new accounts.
*   **Search**: AJAX-based autocomplete search for students (Admin/Super Admin only).
*   **Responsive UI**: Fully custom, lightweight Semantic CSS (No external frameworks like Bootstrap).

## Requirements
*   PHP 7.4+ (8.0+ recommended)
*   MySQL 5.7+
*   Composer
*   Web Server (Apache/Nginx)

## Setup Instructions

1.  **Clone/Copy** the project to your web server root (e.g., `htdocs/SMS`).
2.  **Install Dependencies**:
    ```bash
    cd SMS
    composer install
    ```
3.  **Database Setup**:
    *   Create a database named `sms_db`.
    *   Import sql file to database.
    *   Configure `config/db.php` if your credentials differ from the defaults (User: `root`, Pass: ``).

4.  **Run**:
    Locally - Open your browser and navigate to `http://localhost/SMS/public/`.
    Remotely - Open your browser and navigate to `https://student.heraldcollege.edu.np/~np03cs4a240210/SMS/public/dashboard.php`.

## Styling & Architecture
The project uses a custom, semantic CSS architecture located in `assets/css/style.css`.
*   **Variables**: CSS variables for theming (Colors, Spacing, Typography).
*   **Grid**: Custom Flexbox-based grid system (`.sms-grid`, `.sms-col-*`).
*   **Components**: Reusable classes for Cards, Buttons, Forms, Tables, and Alerts.
*   **No Dependencies**: Zero dependency on Bootstrap or jQuery.

## Default Credentials
All new accounts (Admins and Students) created via the system are assigned the default password: `Password123`.

| Role | Username | Password |
|------|----------|----------|
| **Super Admin** | `superadmin` | `Password123` |
| **Admin** | `admin` | `Password123` |
| **Student** | `student1` | `Password123` |

*Note: You will be prompted to change your password upon first login.*

## Project Structure
*   `config/`: Database configuration.
*   `public/`: Publicly accessible files (Controllers).
*   `includes/`: Helper functions and Auth logic.
*   `templates/`: Twig views.
*   `assets/`: CSS and JS files.
