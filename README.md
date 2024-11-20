
# Printing Shop Management System

This is a web-based system for managing a printing shop, including job management, client management, and user management.

## Features

- User authentication and role-based access control (Admin, Designer, Printing Press)
- Job management (create, edit, view, and list jobs)
- Client management (create, edit, view, and list clients)
- User management (create, edit, view, and list users) - Admin only
- Job status tracking and updates
- Payment status tracking
- Notifications system
- Search functionality for jobs and clients
- Dashboards for different user roles (Admin, Designer, Printing Press)
- Reports generation for admins

## Project Structure

```
printing_shop_system/
├── config.php
├── index.php
├── login.php
├── logout.php
├── jobs.php
├── create_job.php
├── edit_job.php
├── view_job.php
├── update_job_status.php
├── clients.php
├── create_client.php
├── edit_client.php
├── view_client.php
├── users.php
├── create_user.php
├── edit_user.php
├── view_user.php
├── admin_dashboard.php
├── designer_dashboard.php
├── printing_press_dashboard.php
├── reports.php
├── includes/
│   ├── header.php
│   ├── footer.php
│   └── notifications.php
├── css/
│   └── style.css
├── js/
│   └── main.js
└── uploads/
```

## Setup Instructions

1. Install XAMPP on your computer.
2. Clone this repository into the `htdocs` folder of your XAMPP installation.
3. Start Apache and MySQL services from the XAMPP control panel.
4. Open phpMyAdmin and create a new database named `printing_shop_db`.
5. Import the `database_setup.sql` file into the `printing_shop_db` database.
6. Update the database connection details in `config.php` if necessary.
7. Open a web browser and navigate to `http://localhost/printing_shop_system/` to access the application.

## Default Admin Login

- Email: admin@printingshop.com
- Password: admin123

## Usage

1. Log in with the default admin credentials.
2. Create user accounts for designers and printing press staff.
3. Add clients to the system.
4. Create and manage jobs, updating their statuses as they progress through the workflow.
5. Use the role-specific dashboards to manage tasks and view relevant information.
6. Generate reports to analyze job and payment statistics.

## Security Considerations

- Passwords are hashed using PHP's `password_hash()` function.
- Prepared statements are used to prevent SQL injection attacks.
- Input validation and sanitization are implemented to prevent XSS attacks.
- Role-based access control is enforced to restrict unauthorized access to certain features.

## Future Improvements

- Implement file upload functionality for design files.
- Add a more detailed dashboard with charts and graphs.
- Implement an advanced search functionality with filters.
- Add pagination for long lists of jobs, clients, and users.
- Implement an email notification system.
- Add support for multiple languages.
- Implement a backup and restore system for the database.

