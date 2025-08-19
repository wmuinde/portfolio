# Portfolio Website with Admin Panel

A complete PHP/MySQL portfolio website with a full-featured admin panel for content management.

## Features

### Public Portfolio
- **Responsive Design**: Mobile-friendly Bootstrap 5 interface
- **Hero Section**: Eye-catching landing area with profile image
- **About Section**: Personal information and contact details
- **Skills Section**: Animated progress bars showing skill levels
- **Projects Section**: Showcase of work with images and links
- **Contact Form**: Functional contact form storing messages in database

### Admin Panel
- **Dashboard**: Overview with statistics and recent messages
- **About Management**: Update personal information and profile image
- **Project Management**: Add, edit, delete projects with image uploads
- **Skills Management**: Manage technical skills and proficiency levels
- **Message Management**: View and manage contact form submissions
- **Secure Login**: Session-based authentication

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- PHP extensions: PDO, PDO_MySQL

## Installation

### 1. Database Setup
```sql
-- Import the database.sql file or run these commands:
CREATE DATABASE portfolio_db;
USE portfolio_db;
-- Then run all the SQL commands from database.sql
```

### 2. Configuration
Edit `config.php` to match your database settings:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'portfolio_db');
```

### 3. Admin Credentials
Default admin login (change in `config.php`):
- **Username**: admin
- **Password**: admin123

**Important**: Change these credentials in production!

### 4. File Permissions
Ensure the `uploads/` directory is writable:
```bash
chmod 755 uploads/
```

## File Structure
```
portfolio-website/
├── admin/
│   ├── index.php          # Admin dashboard
│   ├── login.php          # Admin login page
│   ├── logout.php         # Logout handler
│   ├── projects.php       # Project management
│   ├── skills.php         # Skills management (to be created)
│   ├── about.php          # About management (to be created)
│   └── messages.php       # Message management (to be created)
├── css/
│   └── style.css          # Main stylesheet
├── js/
│   └── script.js          # JavaScript functionality
├── images/                # Static images
├── uploads/               # Uploaded files
├── config.php             # Database configuration
├── index.php              # Main portfolio page
├── contact.php            # Contact form handler
├── database.sql           # Database schema and sample data
└── README.md             # This file
```

## Usage

### Public Portfolio
1. Visit `index.php` to view the portfolio
2. Navigate through sections using the top menu
3. Submit messages via the contact form

### Admin Panel
1. Visit `admin/` to access the login page
2. Login with admin credentials
3. Use the sidebar to navigate between sections:
   - **Dashboard**: View statistics and recent activity
   - **About Me**: Update personal information
   - **Projects**: Add, edit, or delete projects
   - **Skills**: Manage technical skills
   - **Messages**: View contact form submissions

### Adding Content

#### Projects
1. Go to Admin → Projects
2. Fill in the project details:
   - Title (required)
   - Description (required)
   - Technologies used
   - Demo URL
   - GitHub URL
   - Project image

#### Skills
1. Go to Admin → Skills
2. Add skills with:
   - Skill name
   - Proficiency level (0-100)
   - Category (Frontend, Backend, Tools, etc.)

## Customization

### Styling
- Edit `css/style.css` to customize the appearance
- Colors, fonts, and layouts can be modified
- Bootstrap 5 classes are used throughout

### Database Schema
- Modify `database.sql` to add new fields
- Update corresponding PHP files to handle new fields

### Admin Features
- Add new sections by creating PHP files in the `admin/` directory
- Follow the existing pattern for authentication and layout

## Security Notes

1. **Change default admin credentials** in `config.php`
2. **Use HTTPS** in production
3. **Validate all user inputs** (basic validation included)
4. **Hash passwords** for production use
5. **Restrict file upload types** (implemented for images)
6. **Set proper file permissions** on the server

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers

## Technologies Used

- **Backend**: PHP 7.4+, MySQL
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Framework**: Bootstrap 5
- **Icons**: Bootstrap Icons
- **Database**: MySQL with PDO

## License

This project is open source and available under the MIT License.

## Support

For questions or issues, please check the code comments or create an issue in the repository.
