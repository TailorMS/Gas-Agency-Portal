# AmodIndane - Gas Agency Management System

AmodIndane is a comprehensive web-based application designed to streamline the daily operations of a gas distribution agency. It provides a dual-dashboard system for administrators and members (employees), enabling efficient management of customers, inventory, finances, and user activities.

---

## Table of Contents

- [Key Features](#key-features)
  - [Admin Features](#admin-features)
  - [Member Features](#member-features)
- [Technology Stack](#technology-stack)
- [Project Setup](#project-setup)
  - [Prerequisites](#prerequisites)
  - [Installation](#installation)
  - [Database Setup](#database-setup)
- [Application Structure](#application-structure)
- [Usage](#usage)
  - [Admin Login](#admin-login)
  - [Member Login](#member-login)

---

## Key Features

The application is divided into two main user roles: Admin and Member.

### Admin Features

The admin has full control over the system and can perform all management tasks.

- **Dashboard:** A central hub providing quick access to all management modules.
- **Customer Management:** Add new customers, view a searchable list, edit details, and import/export customer data via CSV.
- **Member Management:** Register new members (employees), view a searchable list, and manage their profiles and access.
- **Financial Management:**
  - **Cash Deposits:** Add and view daily cash deposits using a denomination calculator.
  - **Transaction Management:** Record, view, edit, and delete all financial transactions (credit/debit).
- **Inventory Management:**
  - **Cylinder Stock:** Add and manage daily stock for various cylinder types.
  - **Other Stock:** Manage inventory for miscellaneous items like stoves, regulators, and pipes.
- **Reporting:** View graphical reports of financial data with date-range filters, including bar charts for daily flow and pie charts for income/expense by type.
- **Activity History:** Monitor a detailed log of all actions performed by members for accountability.
- **Profile Management:** Admins can update their own profile details and change their password.

### Member Features

Members have a more restricted set of permissions focused on daily operational tasks.

- **Dashboard:** A personalized dashboard with access to assigned modules.
- **Data Management:** View, edit, delete, and download records for customers, transactions, and inventory.
- **Activity Logging:** All actions performed by a member are logged and viewable by the admin.
- **Reporting:** Access to the same financial reports as the admin.
- **Profile Management:** Members can update their own password and profile picture.

---

## Technology Stack

- **Backend:** PHP
- **Database:** MySQL / MariaDB
- **Frontend:** HTML, CSS, JavaScript
- **Libraries:**
  - **Chart.js:** For rendering interactive charts in the report dashboards.
  - **Font Awesome:** For icons used throughout the user interface.

---

## Project Setup

Follow these steps to set up the project on a local development server.

### Prerequisites

- A web server environment like [XAMPP](https://www.apachefriends.org/index.html) or WAMP.
- A web browser (e.g., Chrome, Firefox).
- A code editor (e.g., VS Code).

### Installation

1.  **Clone or Download the Repository:**
    Place the project folder `amodindane` inside your web server's root directory (e.g., `C:/xampp/htdocs/`).

2.  **Start Your Web Server:**
    Open your XAMPP Control Panel and start the **Apache** and **MySQL** services.

3.  **Create Uploads Directory:**
    Inside the `amodindane` folder, create a new directory named `uploads`. This is required for storing member profile pictures.

    ```
    c:\xampp\htdocs\amodindane\uploads\
    ```

### Database Setup

1.  **Open phpMyAdmin:**
    Navigate to `http://localhost/phpmyadmin` in your web browser.

2.  **Create the Database:**
    - Click on the **Databases** tab.
    - In the "Create database" field, enter `amod_indane_db`.
    - Select `utf8mb4_general_ci` as the collation and click **Create**.

3.  **Import the Database Schema:**
    - Select the newly created `amod_indane_db` database from the left sidebar.
    - Click on the **Import** tab.
    - Click "Choose File" and select the `amod_indane_db.sql` file (if provided) from the project directory.
    - Click **Go** to import the tables and data.

4.  **Verify Database Connection:**
    The file `db_connect.php` is pre-configured for a default XAMPP setup. If your MySQL credentials are different, update them in this file:

    ```php
    define('DB_SERVER', 'localhost');
    define('DB_USERNAME', 'root');
    define('DB_PASSWORD', '');
    define('DB_NAME', 'amod_indane_db');
    ```

---

## Application Structure

A brief overview of important files and directories:

- `Admin.php`: Admin login page.
- `member_login.php`: Member login page.
- `dashboard.php`: Main dashboard for the admin.
- `member_dashboard.php`: Main dashboard for members.
- `db_connect.php`: Handles the database connection.
- `dashboard_style.css`: Primary stylesheet for all dashboard pages.
- `add_*.php` / `edit_*.php` / `view_*.php`: Files for CRUD operations on different modules.
- `download_*.php`: Scripts for handling CSV data exports.
- `import_*.php`: Scripts for handling CSV data imports.
- `uploads/`: Directory for storing uploaded files (e.g., profile photos).

---

## Usage

Once the setup is complete, you can access the application from your browser.

### Admin Login

- **URL:** `http://localhost/amodindane/Admin.php`
- Use the admin credentials to log in and access the admin dashboard.

### Member Login

- **URL:** `http://localhost/amodindane/member_login.php`
- Use the member credentials (created by an admin) to log in and access the member dashboard.
