# 🚀 Project Nebula: The Sci-Fi Music Store

A server-side rendered e-commerce application built with PHP and Bootstrap for discovering and purchasing otherworldly music from across the galaxy. This platform provides a classic, robust experience for users to browse products, manage their cart, and for administrators to manage the store.

---

## 📖 Table of Contents

- [About The Project](#-about-the-project)
- [Key Features](#-key-features)
- [Tech Stack](#-built-with)
- [Getting Started](#-getting-started)
  - [Prerequisites](#prerequisites)
  - [Installation & Setup](#installation--setup)
- [Usage](#-usage)
- [Database Schema](#-database-schema)
- [Project Structure](#-project-structure)
- [License](#-license)
- [Contact](#-contact)

---

## 🌌 About The Project

Project Nebula is a conceptual e-commerce store with a science-fiction theme. The goal was to build a functional and dynamic web application using a classic and reliable web stack: PHP for the backend logic, MySQL for the database, and Bootstrap for a responsive, mobile-first frontend.

The application uses PHP sessions for user authentication and shopping cart management, and follows a multi-page application (MPA) architecture.

---

## ✨ Key Features

### For Customers:
*   **User Accounts:** Secure user registration and login system using PHP sessions.
*   **Product Catalog:** Browse, search, and view a wide range of sci-fi themed albums.
*   **Product Details:** View detailed information, track listings, and pricing for each album.
*   **Dynamic Shopping Cart:** Add/remove items and update quantities. The cart state is managed within the user's session.
*   **Order History:** Registered users can view a history of their past purchases.
*   **Responsive Design:** Using the Bootstrap grid system, the site is fully functional on desktops, tablets, and mobile phones.

### For Admins:
*   **Admin Panel:** A protected area for administrative functions.
*   **Product Management (CRUD):** Admins can create, read, update, and delete products from the store.
*   **Order Management:** View and update the status of all customer orders.

---

## 🛠️ Built With

This project is built with the following technologies:

**Frontend:**
*   [**HTML5**](https://developer.mozilla.org/en-US/docs/Web/Guide/HTML/HTML5)
*   [**CSS3**](https://developer.mozilla.org/en-US/docs/Web/CSS)
*   [**Bootstrap**](https://getbootstrap.com/) - For responsive layout and pre-built components.
*   [**JavaScript / jQuery**](https://jquery.com/) - For client-side interactivity.

**Backend:**
*   [**PHP**](https://www.php.net/) - For server-side logic and templating.
*   [**MySQL**](https://www.mysql.com/) - Relational database for storing all application data.

**Development Environment:**
*   [**XAMPP / WAMP**](https://www.apachefriends.org/) - Local server stack (Apache, MySQL, PHP).

---

## ⚙️ Getting Started

To get a local copy up and running, follow these simple steps.

### Prerequisites

You need a local server environment that can run PHP and MySQL.
*   **XAMPP** (for Windows/macOS/Linux) or a similar stack like WAMP (Windows) or MAMP (macOS).
    *   Download and install from [here](https://www.apachefriends.org/index.html).

### Installation & Setup

1.  **Clone the repository:**
    Clone the project into your local server's web directory.
    *   For **XAMPP**, this is typically the `htdocs` folder (`C:\xampp\htdocs`).
    ```sh
    git clone [Link to your repository] C:/xampp/htdocs/[your-project-folder]
    ```

2.  **Start your server:**
    Open the XAMPP Control Panel and start the **Apache** and **MySQL** modules.

3.  **Set up the database:**
    a. Open your web browser and navigate to `http://localhost/phpmyadmin/`.
    b. Create a new database. Let's call it `sci_music_store`.
    c. Select the new database and go to the **Import** tab.
    d. Click "Choose File" and select the `.sql` file from this project (e.g., `database/sci_music_store.sql`).
    e. Click "Go" to import the tables and data.

4.  **Configure the database connection:**
    a. Find the database connection file in the project (e.g., `includes/config.php` or `db_connect.php`).
    b. Open the file and update the database credentials to match your local setup.
    ```php
    <?php
    define('DB_SERVER', 'localhost');
    define('DB_USERNAME', 'root'); // Default for XAMPP
    define('DB_PASSWORD', '');     // Default for XAMPP
    define('DB_NAME', 'sci_music_store'); // The database name you created
    ?>
    ```

5.  **You're all set!**
    Open your browser and navigate to `http://localhost/[your-project-folder]`.

---

## 👨‍💻 Usage

Once the application is running:
*   Navigate to `http://localhost/[your-project-folder]` in your browser.
*   Register a new account or log in with sample credentials.
*   **Admin User:** `admin@example.com` / `admin123`
*   **Customer User:** `john@example.com` / `user123`
*   Browse the albums, add them to your cart, and simulate a checkout.
*   Log in as an admin to access the admin panel to manage the store's content.

---

## 🗄️ Database Schema

The core database tables include:

| Table     | Description                                               |
|-----------|-----------------------------------------------------------|
| `users`   | Stores user information, credentials (hashed), and roles. |
| `products`| Stores all album details like name, artist, price, image. |
| `orders`  | Stores information about each order placed by a user.     |
| `order_items`| A linking table for orders and the products they contain.|

---

## 🌳 Project Structure
