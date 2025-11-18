⭐ MCAS — Machap Computer Accessories System

MCAS (Machap Computer Accessories System) is a web-based platform that allows customers to browse and purchase various computer accessories such as keyboards, mice, SSDs, and other related hardware components. This system excludes laptop sales and focuses only on computer peripherals and accessories.

📌 Features
🛒 Customer Features

User Registration & Login — Customers can create an account and log in securely.

View Products — Browse all available computer accessories.

Product Detail Page — View detailed information of each item.

Add to Cart — Add selected products to the shopping cart.

Place Order — Confirm orders and proceed with payment.

Online Payment — Integrated payment page with transaction handling.

Order History — Customers can view their past orders.

Receipt Generation — System automatically generates PDF receipts after payment.

Profile Page — Users can view or update their profile information.

🧰 Technologies Used
Technology	Description
HTML	Front-end page structure
CSS	Page styling and layout
PHP	Backend processing and server-side logic
MySQL	Database used for user accounts, products, orders, and payments

🔧 How the System Works
1️⃣ User Browsing

Users can browse all products through products.php.

Each item can be viewed in detail through product_detail.php.

2️⃣ Shopping Cart & Checkout

Selected items can be added into the cart.

Users proceed to checkout and place an order.

3️⃣ Payment Processing

After a successful payment, the system:

Saves the transaction

Creates an order record

Generates a PDF receipt

Directs users to payment_success.php

4️⃣ Order Viewing

Users can check their order history through orders.php or order_details.php.

🚀 How to Run the System
Requirements

XAMPP / WAMP / Any local PHP server

MySQL database

Web browser

Setup Steps

Clone or download the project into your web server folder (e.g., htdocs for XAMPP).

Import the MySQL database file (if included).

Update database connection details in the PHP config file (usually config.php).

Start Apache & MySQL through XAMPP.

Open your browser and go to:

http://localhost/MCAS/

📌 Future Improvements (Optional Section)

Admin dashboard for product management

Stock management system

Email order confirmation

Improved UI using Bootstrap

📝 About This Project

This project was developed for academic purposes to demonstrate understanding of web development concepts such as client-server communication, database integration, user authentication, and online transaction workflow.
