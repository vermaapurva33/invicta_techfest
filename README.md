# 🚀 Techfest Management System

![PHP](https://img.shields.io/badge/Backend-PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/Database-MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/Frontend-HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![Status](https://img.shields.io/badge/Status-Completed-success?style=for-the-badge)

A robust, full-stack web application designed to digitize the operations of a college technical festival. The system features strict **Role-Based Access Control (RBAC)**, automated scoring logic, and temporal constraint enforcement for event scheduling and accommodation management.

---

## 📖 Project Overview

This project serves as a centralized portal for managing:
* **Participants:** Event registration, team formation, and accommodation booking.
* **Clubs/Organizers:** Event creation, scheduling, and sponsor management.
* **Judges:** Real-time scoring and result publication.

Unlike standard CMS tools, this system implements **complex business logic at the database level** to prevent logical errors (e.g., preventing a judge from being scheduled for two simultaneous events).

---

## ✨ Key Features

### 🔐 1. Role-Based Access Control (RBAC)
Secure login and dashboard isolation for four distinct user types:
* **Admin:** Full system oversight.
* **Club Heads:** Create events, assign judges, and manage sponsors.
* **Judges:** View assigned events and submit scores.
* **Participants:** Register for events and book stays.

### ⚙️ 2. Advanced Business Logic
* **Temporal Constraints:**
    * Accommodation Check-in/Check-out dates are validated against the fest duration.
    * *Logic:* `Fest_Start <= Check_In < Check_Out <= Fest_End`.
* **Conflict Resolution:**
    * Prevents assigning a Judge to multiple events within a 2-hour overlapping window.
* **Team Dynamics:**
    * Validates team size limits per event.
    * Ensures team members are not already registered for conflicting events.

### 🏆 3. Automated Scoring System
* **Real-time Aggregation:** When a judge submits scores, the backend automatically calculates totals.
* **Instant Result Publication:** A "Publish" trigger updates the participant dashboard with ranks and prize status immediately.

### 🏨 4. Resource Management
* **Accommodation:** Real-time tracking of available rooms/beds.
* **Sponsorship:** Management of sponsor tiers and monetary contributions.

---

## 🛠️ Tech Stack

* **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
* **Backend:** PHP (Native)
* **Database:** MySQL (Relational Schema with Foreign Key Constraints)
* **Server:** Apache (via XAMPP)

---

## 💾 Database Schema

The system utilizes a **normalized relational database** to ensure data integrity. Key relationships include:
* `Users` ↔ `Roles` (One-to-Many)
* `Events` ↔ `Judges` (Many-to-Many with temporal checks)
* `Teams` ↔ `Participants` (Many-to-Many)

---

## 🚀 Setup & Installation

### Prerequisites
* [XAMPP](https://www.apachefriends.org/index.html) installed on your local machine.

### Steps
1.  **Clone the Repository**
    Navigate to your XAMPP `htdocs` directory and clone the project:
    ```bash
    cd C:\xampp\htdocs\
    git clone [https://github.com/pryanz/techfest-system.git](https://github.com/pryanz/techfest-system.git)
    ```

2.  **Database Configuration**
    * Open **XAMPP Control Panel** and start `Apache` and `MySQL`.
    * Go to `http://localhost/phpmyadmin`.
    * Create a new database named `techfest_db` (or check `db_connect.php` for the exact name).
    * Click **Import** and select the `database/schema.sql` file from the project folder.

3.  **Launch**
    * Open your browser and visit:
      `http://localhost/techfest-system/`

---

## 👥 Contributors

* **[Priyansh Khare](https://github.com/pryanz)** - *Backend Logic, Database Architecture & Constraints*
* **[Apurva Verma](https://github.com/vermaapurva33)** - *Frontend Design & UI Implementation*
