# MyOrmawa - Organisasi Mahasiswa Management System

MyOrmawa is a comprehensive web-based application designed to manage student organizations (Ormawa) in universities, primarily developed for Politeknik Negeri Jember. The system provides a centralized platform for managing multiple student organizations, events, competitions, recruitment processes, and attendance tracking.

## Table of Contents
- [Overview](#overview)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Project Structure](#project-structure)
- [Installation](#installation)
- [Database Structure](#database-structure)
- [API Endpoints](#api-endpoints)
- [Usage](#usage)
- [Contributing](#contributing)
- [License](#license)

## Overview

MyOrmawa is built to streamline the management of student organizations at universities. The platform offers different access levels for Super Admins, Admins, and regular users, with features for managing events, competitions, recruitment processes, and attendance systems. The system uses a role-based access control mechanism to ensure proper authorization across different levels of users.

## Features

### Core Features
- **Multi-level User Management**: Super Admin, Admin, Pengurus, and regular Member levels
- **ORMAWA Management**: Manage multiple student organizations with their details, categories, and information
- **Event Management**: Create and manage events for different organizations
- **Competition Management**: Handle competitions with registration, guides, and materials
- **Attendance System**: QR code based check-in system with location validation
- **Recruitment Management**: Open Recruitment (OpRec) system for both organization members and event committees
- **Document Management**: Manage documents related to organizations and activities
- **Email Verification**: OTP-based email verification for user registration
- **Excel Export**: Export submissions and data to Excel format

### Authentication & Security
- User registration with email verification
- Password reset functionality
- Multi-level access control
- Session management
- OTP-based verification for sensitive operations

### Attendance System
- QR code-based check-in system
- Location-based validation using GPS coordinates
- Check-in history tracking
- Distance calculation from designated location
- Support for both online and offline check-in scenarios

### Form Builder & Submissions
- Dynamic form builder for recruitment
- Submission management with approval/rejection system
- Excel export of form submissions
- File upload support for submissions
- Status tracking (pending/approved/rejected)

## Technology Stack

- **Backend**: PHP 7.4+ 
- **Frontend**: HTML5, CSS3, JavaScript (Bootstrap 5, jQuery)
- **Database**: MySQL
- **Framework**: Custom PHP framework with Bootstrap UI components
- **External Libraries**:
  - PHPMailer: For email functionality
  - PhpSpreadsheet: For Excel export functionality
  - Bootstrap: For responsive UI components
  - Font Awesome: For icons
  - Chart.js: For data visualization
  - DataTables: For responsive tables
- **API**: RESTful API endpoints for various functionalities
- **Authentication**: Session and token-based authentication

## Project Structure

```
MyOrmawa/
├── API/                     # REST API endpoints
│   ├── attendance.php       # Attendance functionality
│   ├── auth.php             # Authentication endpoints
│   ├── calendar.php         # Calendar events
│   └── competition.php      # Competition management
├── App/                     # Application views and logic
│   ├── View/                # View files for different user levels
│   │   ├── LandingPage/     # Public landing page
│   │   ├── SuperAdmin/      # Super admin interface
│   │   ├── Admin/           # Organization admin interface
│   │   ├── User/            # Pengurus interface
│   │   └── Member/          # Regular member interface
├── Asset/                   # CSS, JavaScript, and image assets
├── Config/                  # Configuration files
├── Function/                # PHP functions and utilities
├── includes/                # Helper functions and utilities
├── logs/                    # Log files
├── uploads/                 # File uploads (images, documents)
├── vendor/                  # Composer dependencies
├── composer.json            # Project dependencies
├── .htaccess               # Apache configuration
└── index.php               # Entry point
```

## Installation

1. **Prerequisites**
   - Apache Web Server (e.g., XAMPP, WAMP, LAMPP)
   - PHP 7.4 or higher
   - MySQL 5.7 or higher
   - Composer

2. **Setup Instructions**

   ```bash
   # Clone the repository
   git clone [repository-url]
   
   # Navigate to project directory
   cd MyOrmawa
   
   # Install dependencies
   composer install
   ```

3. **Database Setup**
   - Create a MySQL database (e.g., `myormawa_db_complete`)
   - Import the database schema (if provided)
   - Update database credentials in `Config/ConnectDB.php`

4. **Configuration**
   - Set your database credentials in `Config/ConnectDB.php`
   - Configure email settings in `includes/email_sender.php`
   - Set proper file permissions for upload directories

5. **Run the Application**
   - Start your web server
   - Access the application via `http://localhost/MyOrmawa/`
   - The landing page will redirect to the appropriate login page

## Database Structure

The application uses a MySQL database with the following key tables:

- `user` - Stores user information with different access levels
- `ormawa` - Contains student organization data
- `event` - Event information for organizations
- `kehadiran` - Attendance sessions for check-ins
- `absensi_log` - Check-in logs and records
- `kompetisi` - Competition management
- `lokasi_absen` - Attendance location data
- `form_info` - Form templates for recruitment
- `form` - Individual form fields
- `submit` - Form submission data
- `dokumen` - Document management
- `otp_codes` - One-time password codes for verification
- `login_sessions` - User session management

## API Endpoints

### Authentication API (`API/auth.php`)
- `POST /auth.php?action=login` - User login
- `POST /auth.php?action=register` - User registration
- `POST /auth.php?action=verify_otp` - OTP verification
- `POST /auth.php?action=forgot_password` - Password reset request
- `POST /auth.php?action=reset_password` - Password reset
- `POST /auth.php?action=change_password` - Change password
- `POST /auth.php?action=change_email` - Change email address
- `POST /auth.php?action=resend_otp` - Resend OTP

### Attendance API (`API/attendance.php`)
- `POST /attendance.php?action=verify_qr` - Verify QR code for check-in
- `POST /attendance.php?action=check_in` - Perform check-in
- `GET /attendance.php?action=get_history&user_id={id}` - Get check-in history

### Calendar API (`API/calendar.php`)
- `GET /calendar.php` - Get upcoming events for calendar

### Competition API (`API/competition.php`)
- `GET /competition.php` - Get all upcoming competitions
- `GET /competition.php?id={id}` - Get specific competition
- `GET /competition.php?ormawa_id={id}` - Get competitions by ORMWA
- `POST /competition.php` - Create new competition
- `PUT /competition.php` - Update competition
- `DELETE /competition.php` - Delete competition

## Usage

### User Registration
1. Navigate to the registration page
2. Fill in required information (NIM, full name, email, program study)
3. Verify email through OTP sent to the provided email address

### Attendance System
1. Organizers create attendance sessions with QR codes
2. Attendees scan QR codes using the attendance application
3. System validates location (if required) and records check-in
4. Admins can view attendance reports and history

### Recruitment Process
1. Admins create recruitment forms with various question types
2. Candidates fill out the forms online
3. Admins review submissions and approve/reject applications
4. Submissions can be exported to Excel for further processing

### Event Management
- Super Admin manages all organizations and their events
- Admins can create and manage events for their specific organizations
- Users can view events and participate in them

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

For support, email myormawa@gmail.com or create an issue in the repository.

---
*Developed with ❤️ for Politeknik Negeri Jember student organizations*