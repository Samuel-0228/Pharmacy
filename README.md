# Pharmacy Management System

A professional web-based **Pharmacy Management System** built with **PHP** and **MySQL** to efficiently manage pharmacy operations including inventory, sales, and customer management.

---

## Features

* Manage medicine inventory (add, update, delete)
* Record sales and generate invoices
* Track customer information
* Secure user authentication
* View reports for sales and stock

---

## Technologies

* **Backend:** PHP
* **Database:** MySQL / MariaDB
* **Frontend:** HTML, CSS, JavaScript, Bootstrap
* **Server:** Apache / XAMPP / WAMP

---

## Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/Samuel-0228/Pharmacy.git
   ```
2. Create a MySQL database (e.g., `pharmacy_db`) and import `database.sql`.
3. Configure database connection in `config.php`:

   ```php
   $host = 'localhost';
   $user = 'root';
   $password = '';
   $database = 'pharmacy_db';
   ```
4. Place the project in your server directory (e.g., `htdocs`) and run:

   ```
   http://localhost/pharmacy-system
   ```

---

## Usage

* Login with your credentials.
* Add, update, or delete medicines.
* Record sales and generate invoices.
* Track customer details and view reports.

---

## Database Structure

* `users` – stores admin and staff login credentials
* `medicines` – stores medicine details
* `sales` – stores sales transactions
* `customers` – stores customer information

---

## Contributing

1. Fork the repository.
2. Create a new branch (`git checkout -b feature/your-feature`).
3. Make your changes and commit (`git commit -m 'Add feature'`).
4. Push to the branch (`git push origin feature/your-feature`).
5. Open a Pull Request.

---

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

---

## Contact

* **Author:** Samuel
* **Email:** ytsamuael@gmail.com
* **GitHub:** https://github.com/Samuel-0228
