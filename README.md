# 🏋️ GYM Hub - Gym Management System

A complete, fully-functional web application for managing gym operations including members, instructors, payments, workout plans, and more.

## 📋 Features

### 🔐 Authentication System
- Separate login/signup for Admin, Instructor, and Members
- Session-based authentication with password hashing
- Secure logout functionality

### 👤 Member Portal
- Register and login
- View and edit profile
- Browse and subscribe to packages
- View assigned workout and diet plans
- Track attendance
- View payment history
- Receive notifications

### 🏋️ Instructor Panel
- Manage assigned members
- Create and assign workout plans
- Create and assign diet plans
- Mark member attendance
- Send notifications to members
- View member progress

### 🛠️ Admin Dashboard
- Dashboard with key statistics
- Manage users
- Manage instructors
- Manage gym packages
- View and update payments
- Create announcements
- System reports and analytics

## 🗄️ Database Tables

- `users` - Gym members
- `instructors` - Gym instructors
- `admin` - Admin users
- `packages` - Membership packages
- `subscriptions` - Member subscriptions
- `payments` - Payment records
- `attendance` - Member attendance
- `workout_plans` - Assigned workout programs
- `diet_plans` - Assigned diet programs
- `notifications` - System notifications
- `announcements` - Admin announcements

## 📁 Project Structure

```
gym_management/
├── admin/                    # Admin panel pages
│   ├── dashboard.php
│   ├── users.php
│   ├── instructors.php
│   ├── packages.php
│   ├── payments.php
│   ├── announcements.php
│   └── reports.php
├── instructor/              # Instructor panel pages
│   ├── dashboard.php
│   ├── members.php
│   ├── workout_plans.php
│   ├── diet_plans.php
│   ├── attendance.php
│   └── notifications.php
├── user/                    # Member panel pages
│   ├── dashboard.php
│   ├── profile.php
│   ├── packages.php
│   ├── workout_plans.php
│   ├── diet_plans.php
│   ├── attendance.php
│   ├── payments.php
│   └── notifications.php
├── auth/                    # Authentication pages
│   ├── login.php
│   ├── login_process.php
│   ├── signup.php
│   ├── signup_process.php
│   └── logout.php
├── config/                  # Configuration files
│   ├── db.php              # Database connection
│   └── functions.php       # Common functions
├── includes/               # Reusable components
│   ├── header.php
│   ├── footer.php
│   └── admin_sidebar.php
├── assets/                 # Static files
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── main.js
├── index.php               # Home page
├── setup.sql               # Database schema
└── README.md               # This file
```

## 🚀 Installation Guide

### Step 1: Download/Extract Files
Place the gym_management folder in:
```
C:\xampp\htdocs\
```

### Step 2: Create Database

1. **Open phpMyAdmin:**
   - Start XAMPP Control Panel
   - Click "Admin" next to MySQL
   - This opens phpMyAdmin (http://localhost/phpmyadmin)

2. **Create Database and Import Schema:**
   - Click on "New" database
   - Name: `gym_management`
   - Click "Create"
   - Click on the new `gym_management` database
   - Click "Import" tab
   - Choose `setup.sql` file from gym_management folder
   - Click "Go"

### Step 3: Configure Database Connection
The database is pre-configured in `config/db.php` for XAMPP defaults:
- Host: `localhost`
- User: `root`
- Password: (empty)
- Database: `gym_management`

If you have a different MySQL password, edit `config/db.php`:
```php
define('DB_PASS', 'your_password_here');
```

### Step 4: Start the Application

1. **Start XAMPP:**
   - Open XAMPP Control Panel
   - Start Apache and MySQL

2. **Access the System:**
   - Open browser and go to: `http://localhost/gym_management/`
   - Click "Login" or use any demo account

## 🔐 Demo Credentials

After database setup, use these credentials to login:

### Admin
- Email: `admin@gym.com`
- Password: `password123`

### Instructor
- Email: `instructor@gym.com`
- Password: `password123`

### Member
- Email: `user@gym.com`
- Password: `password123`

## 📖 How to Use

### For Members
1. **Login/Register** - Create account or login
2. **Browse Packages** - View available membership packages
3. **Subscribe** - Choose a package to subscribe
4. **View Plans** - Check workout and diet plans assigned by instructor
5. **Track Progress** - View attendance and payment records
6. **Receive Updates** - Check notifications from instructors

### For Instructors
1. **Login** - Use instructor credentials
2. **View Members** - See assigned members
3. **Create Plans** - Assign workout and diet plans to members
4. **Mark Attendance** - Record member check-ins
5. **Send Messages** - Send notifications to members
6. **Track Progress** - Monitor member performance

### For Admin
1. **Login** - Use admin credentials
2. **Dashboard** - View key statistics and metrics
3. **Manage Users** - Add/edit/delete members, toggle status
4. **Manage Instructors** - Add/remove instructors
5. **Manage Packages** - Create and manage membership packages
6. **View Payments** - Track and update payment status
7. **System Reports** - View revenue, attendance, and membership reports
8. **Announcements** - Create and publish announcements

## 🔒 Security Features

- ✅ Password hashing with bcrypt
- ✅ SQL injection prevention (prepared statements)
- ✅ Session-based authentication
- ✅ Role-based access control
- ✅ Input sanitization
- ✅ XSS protection via htmlspecialchars()

## 🎨 Frontend Technologies

- **HTML5** - Semantic markup
- **CSS3** - Responsive design with Flexbox/Grid
- **Bootstrap 4** - Ready-to-use components
- **JavaScript** - Client-side interactivity
- **Font Awesome** - Icon library

## 🔧 Backend Technologies

- **PHP 7.4+** - Server-side logic
- **MySQLi** - Database interaction with prepared statements
- **Sessions** - User authentication

## 💳 Payment System

The system includes a **simulated payment system**:
- Payments are stored in database
- Status: Paid/Pending/Failed
- Admin can update payment status
- No real payment gateway integration (can be added later)

## 🐛 Troubleshooting

### Database Connection Error
- Ensure MySQL is running in XAMPP
- Check database credentials in `config/db.php`
- Verify `gym_management` database exists

### 404 Error on Login
- Ensure files are in `C:\xampp\htdocs\gym_management\`
- Check Apache is running
- Refresh browser

### Session Issues
- Clear browser cookies
- Ensure PHP session directory has write permissions
- Check `php.ini` session settings

## 📝 Notes

- All passwords start with default "password123"
- You can change credentials in `setup.sql`
- System uses MySQLi with prepared statements
- No external APIs or frameworks required
- Fully compatible with XAMPP

## ✅ Testing Checklist

- [ ] Database created from setup.sql
- [ ] Login works for all 3 roles
- [ ] Member can subscribe to package
- [ ] Instructor can create workout plans
- [ ] Admin can manage users
- [ ] Attendance marking works
- [ ] Notifications send successfully
- [ ] Payments tracking works
- [ ] Session logout clears data
- [ ] Responsive design on mobile

## 🎓 Learning Resources

This system demonstrates:
- PHP OOP and procedural programming
- Database design and normalization
- User authentication and sessions
- CRUD operations
- Prepared statements (SQL injection prevention)
- Responsive web design
- Bootstrap framework
- MVC-like architecture

## 📞 Support

For issues or questions:
1. Check the troubleshooting section
2. Verify database connection
3. Check browser console for JavaScript errors
4. Verify all files are in correct locations

## 📄 License

This project is for educational purposes.

---

**Happy Gym Managing! 🏋️💪**
