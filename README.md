# Learnix Project

Learnix is a comprehensive Learning Management System (LMS) designed for students, instructors, and administrators. This project serves as a platform for managing courses, users, and roles with a user-friendly interface.

## Features
- Role-based access control (Students, Instructors, Admins)
- Course creation and management
- User authentication and profile management
- Responsive design for all devices

## Setup Instructions
1. Clone this repository:
   ```bash
   git clone https://github.com/yourusername/learnix.git
   ```
2. Import the `learnix_db.sql` file into your MySQL database.
3. Configure your database connection in `backend/config.php`:
   ```php
   $db_host = 'localhost';
   $db_user = 'root';
   $db_pass = 'root';
   $db_name = 'learnix_db';
   ```
4. Start a local server (e.g., XAMPP, WAMP, or MAMP) and navigate to the project's base URL in your browser.

## Folder Structure
The project follows a modular structure:
- `assets/`: Contains static files like images, CSS, and JavaScript.
- `includes/`: Shared components like the header, footer, and helper functions.
- `backend/`: Handles business logic, database operations, and configuration.
- `pages/`: Frontend pages for user interaction.
- `student/`, `instructor/`, `admin/`: Role-specific interfaces.

## License
This project is licensed under [MIT License](LICENSE).
