
# Printing Shop Management System

This is a web-based management system for printing shops, allowing for job tracking, client management, and financial reporting.

## Features

- User roles: Admin, Designer, Printing Press
- Job management with custom fields for different job types
- Client management
- Financial reporting and account book
- Notification system

## Installation

1. Clone this repository to your local machine or server.
2. Ensure you have PHP and MySQL installed.
3. Create a new MySQL database for the project.
4. Update the database configuration in `config.php` with your database details.
5. Run the installation script by navigating to `http://your-server/install.php` in your web browser.
6. Follow the on-screen instructions to complete the installation.

## First Login

After installation, you can log in with the following default admin credentials:

- Email: admin@printingshop.com
- Password: admin123

**Important:** Please change the admin password after your first login.

## File Structure

- `config.php`: Configuration file with database connection details and utility functions.
- `final_database.sql`: SQL file containing the database structure and initial data.
- `install.php`: Installation script to set up the database.
- `index.php`: Main entry point of the application.
- `login.php`: User login page.
- `logout.php`: User logout script.
- `admin_dashboard.php`, `designer_dashboard.php`, `printing_press_dashboard.php`: Role-specific dashboards.
- `jobs.php`, `create_job.php`, `edit_job.php`, `view_job.php`: Job management pages.
- `clients.php`, `create_client.php`, `edit_client.php`, `view_client.php`: Client management pages.
- `users.php`, `create_user.php`, `edit_user.php`, `view_user.php`: User management pages (admin only).
- `account_book.php`, `reports.php`: Financial reporting pages.
- `includes/`: Directory containing header.php, footer.php, and other included files.
- `css/`: Directory for CSS files.
- `js/`: Directory for JavaScript files.
- `uploads/`: Directory for uploaded files (e.g., design files).

## Usage

After logging in, users will be directed to their role-specific dashboard. From there, they can navigate to different sections of the system based on their permissions.

## Security

- Passwords are hashed using PHP's password_hash() function.
- SQL queries use prepared statements to prevent SQL injection.
- User input is sanitized to prevent XSS attacks.
- Role-based access control is implemented to restrict unauthorized access.

## Support

For any issues or questions, please open an issue in this repository or contact the system administrator.

