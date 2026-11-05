Multi-Tenant Secure Database Password Management & Testing System
This repository provides a clean, secure implementation of encrypted database password management and dynamic connection testing for a multi-tenant Laravel application. It ensures passwords are securely stored as encrypted strings in the database while maintaining automated MySQL user synchronization and seamless runtime connections.

📋 System Requirements & Prerequisites
Before setting up and testing the APIs, ensure your local environment has the following installed:

PHP (v8.2 or higher)

Composer

MySQL / MariaDB (via XAMPP, WAMP, or standalone)

Postman (for API testing)

⚙️ Installation & Environment Setup
Clone or Open the Project in your terminal or editor (e.g., VS Code).

Install PHP dependencies:

Bash
composer install
Configure Environment File:
Copy .env.example to .env and generate your application encryption key (vital for Laravel's Crypt mechanism):

Bash
cp .env.example .env
php artisan key:generate
Start the Local Development Server:

Bash
php artisan serve
(The server will run locally at [http://127.0.0.1:8000](http://127.0.0.1:8000))

🗄️ Database Setup: Deployment Table & Dummy Record
Run the following SQL queries in your main database (via phpMyAdmin or MySQL console) to create the deployment table and insert a dummy tenant record:

SQL
-- 1. Create the deployment table
CREATE TABLE `deployment` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `app_name` VARCHAR(100) NOT NULL,
  `db_host` VARCHAR(100) NOT NULL DEFAULT '127.0.0.1',
  `db_port` INT(11) NOT NULL DEFAULT 3306,
  `db_database` VARCHAR(100) NOT NULL,
  `db_username` VARCHAR(100) NOT NULL,
  `db_password` TEXT NOT NULL, -- Stores Laravel encrypted string
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);

-- 2. Insert a dummy tenant record (Ensure the database 'workintel' and user 'workintel' exist in MySQL)
INSERT INTO `deployment` (`app_name`, `db_host`, `db_port`, `db_database`, `db_username`, `db_password`) 
VALUES ('workintel', '127.0.0.1', 3306, 'workintel', 'workintel', 'dummy_encrypted_string');

🚀 The 3 Core APIs: End-to-End Workflow
Test the APIs in the following order to verify encryption, server synchronization, and connection handling:

1. Password Tool API (Encryption / Decryption Utility)
Purpose: Manually test Laravel's Crypt encryption and decryption functions for any string.

HTTP Method: POST

Endpoint URL: [http://127.0.0.1:8000/api/password-tool](http://127.0.0.1:8000/api/password-tool)

Parameters (JSON Body):

JSON
{
    "string": "secret123",
    "option": 1
}
string: The plain text or encrypted string you want to convert.

option: 1 to Encrypt (plain text ➔ encrypted string), 2 to Decrypt (encrypted string ➔ plain text).

2. Master Dynamic Password Update API
Purpose: Automatically updates the MySQL server user password (ALTER USER) and securely saves the newly encrypted password in the deployment table simultaneously.

HTTP Method: POST

Endpoint URL: [http://127.0.0.1:8000/api/update-tenant-password](http://127.0.0.1:8000/api/update-tenant-password)

Parameters (JSON Body):

JSON
{
    "app_name": "workintel",
    "new_password": "secret123"
}
app_name: The unique tenant identifier matching the table record ("workintel").

new_password: The new plain text password you want to assign.

3. Test Database Connection API
Purpose: Reads the encrypted password string from the deployment table, decrypts it on the fly at runtime using Crypt::decryptString(), connects to MySQL, and fetches sample data.

HTTP Method: GET

Endpoint URL: [http://127.0.0.1:8000/api/test-db-connection?app_name=workintel](http://127.0.0.1:8000/api/test-db-connection?app_name=workintel)

Parameters (Query Parameters / URL Params):

app_name: workintel (Passed directly in the URL query string).

