# 🛡️ Multi-Tenant Secure Database Password Tool

A clean, robust, and secure implementation for encrypted database password management and dynamic connection testing in multi-tenant **Laravel** applications. 

This repository ensures that database passwords are stored securely using Laravel's native encryption, automatically synchronized with MySQL users, and dynamically connected at runtime.

---

## 📑 Table of Contents

- [✨ Features](#-features)
- [💻 System Requirements](#-system-requirements)
- [⚙️ Environment Setup](#️-environment-setup)
- [🗄️ Database Setup](#️-database-setup)
- [🚀 API Endpoints & Workflow](#-api-endpoints--workflow)
  - [1. Password Tool API](#1-password-tool-api-utility)
  - [2. Master Dynamic Password Update API](#2-master-dynamic-password-update-api)
  - [3. Test Database Connection API](#3-test-database-connection-api)
- 

---

## ✨ Features

- 🔐 **At-Rest Encryption:** Tenant passwords are never stored in plain text.
- 🔄 **Automated MySQL Sync:** Executes `ALTER USER` commands directly on the MySQL server upon password changes.
- ⚡ **Dynamic Runtime Connections:** Decrypts database credentials on-the-fly to connect to tenant databases without altering `.env` files.
- 🛠️ **Utility API:** Included tool for instant encryption/decryption testing.

---

## 💻 System Requirements

| Tool / Dependency | Recommended Version | Note |
| :--- | :--- | :--- |
| **PHP** | `^8.2` or higher | Core runtime environment |
| **Composer** | `^2.0` | Dependency Manager |
| **Database** | MySQL / MariaDB | XAMPP, WAMP, or Standalone |
| **API Client** | Postman / Insomnia | For testing API endpoints |

---

## ⚙️ Environment Setup

Follow these steps to get your local environment running:

### Step 1: Clone & Install
```bash
# Clone the repository (or open your local copy)
git clone <your-repository-url>
cd <repository-folder>

# Install composer dependencies
composer install
```

### Step 2: Configure Environment
Copy the `.env.example` file to create your local `.env` configuration, then generate a unique application key (essential for Laravel's `Crypt` facade):

```bash
cp .env.example .env
php artisan key:generate
```

### Step 3: Run Development Server
```bash
php artisan serve
```
> 📍 **Local Server Base URL:** `http://127.0.0.1:8000`

---

## 🗄️ Database Setup

Run the following SQL queries in your main database management tool (phpMyAdmin, DBeaver, or MySQL Console):

### 1. Create `deployment` Table
```sql
CREATE TABLE `deployment` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `app_name` VARCHAR(100) NOT NULL,
  `db_host` VARCHAR(100) NOT NULL DEFAULT '127.0.0.1',
  `db_port` INT(11) NOT NULL DEFAULT 3306,
  `db_database` VARCHAR(100) NOT NULL,
  `db_username` VARCHAR(100) NOT NULL,
  `db_password` TEXT NOT NULL, -- Stores encrypted string via Laravel Crypt
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 2. Insert Initial Tenant Record
> ⚠️ **Prerequisite:** Make sure the database `workintel` and user `workintel` already exist in your MySQL server.

```sql
INSERT INTO `deployment` (`app_name`, `db_host`, `db_port`, `db_database`, `db_username`, `db_password`) 
VALUES ('workintel', '127.0.0.1', 3306, 'workintel', 'workintel', 'dummy_encrypted_string');
```

---

## 🚀 API Endpoints & Workflow

Test the APIs in the sequential order outlined below to verify encryption, server synchronization, and runtime connection handling.

---

### 1. Password Tool API (Utility)

* **Method:** `POST`
* **Endpoint:** `http://127.0.0.1:8000/api/password-tool`
* **Content-Type:** `application/json`

#### Request Body
```json
{
    "string": "secret123",
    "option": 1
}
```

#### Parameter Details
| Field | Type | Description |
| :--- | :--- | :--- |
| `string` | `string` | Plaintext string to encrypt **OR** encrypted string to decrypt |
| `option` | `integer` | `1` = Encrypt (Plaintext ➔ Encrypted) <br> `2` = Decrypt (Encrypted ➔ Plaintext) |

---

### 2. Master Dynamic Password Update API
Synchronizes password updates across both the MySQL Server (`ALTER USER`) and the tenant's `deployment` database record simultaneously.

* **Method:** `POST`
* **Endpoint:** `http://127.0.0.1:8000/api/update-tenant-password`
* **Content-Type:** `application/json`

#### Request Body
```json
{
    "app_name": "workintel",
    "new_password": "secret123"
}
```

#### Parameter Details
| Field | Type | Description |
| :--- | :--- | :--- |
| `app_name` | `string` | Unique tenant identifier matching the `deployment` table |
| `new_password` | `string` | New plain text password to assign to the MySQL user |

---

### 3. Test Database Connection API
Fetches tenant details from `deployment`, decrypts the database password on-the-fly, establishes a dynamic connection to the tenant database, and verifies data accessibility.

* **Method:** `GET`
* **Endpoint:** `http://127.0.0.1:8000/api/test-db-connection`

#### Query Parameters
| Parameter | Type | Required | Value Example |
| :--- | :--- | :--- | :--- |
| `app_name` | `string` | **Yes** | `workintel` |

#### Complete Request URL Example
```http
GET http://127.0.0.1:8000/api/test-db-connection?app_name=workintel
```

---

