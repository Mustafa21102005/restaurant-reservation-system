# Restaurant Reservation System (Laravel 11)

A full-featured restaurant reservation web application built using **Laravel 11**, with role-based access for **Admin** and **Customers**. The system allows customers to reserve tables, receive reservation reminders, manage their profiles, and view a dynamic menu. Admins can manage reservations, tables, users, and menu items.

---

## 🔗 Features

### 👤 Customer

- Register/Login
- Make a table reservation
- Receive a **unique reservation code** via email
- Show reservation code upon arrival
- Receive automatic **email reminders**:
    - 10 minutes before the reservation
    - Optional reminder: 30 minutes, 1 hour, or 2 hours before
- Cancel or edit reservations (edit allowed only once)
- View all previous reservations
- Manage profile (update name, email, password or delete account)
- View dynamic menu categorized by:
    - Starters
    - Mains
    - Desserts
    - Drinks
- See **signature dishes** on home & menu pages
- Show **discount codes** on return visits (if given)

### 🛠️ Admin

- Secure login
- Manage:
    - Tables (table number, number of chairs)
    - Reservations (approve, cancel with reason, finish with thank-you or discount email, full edit access)
    - Categories (e.g., Starters, Mains)
    - Dishes (name, image, price, description, category)
    - Users (track cancellations, timeout/ban abuse)
- Send **emails** post-visit (e.g., thank-you, discount codes)
- View **reservation activity dashboard**
- Get notified of no-shows (auto-cancelled 10 min after reservation time)
- All changes send real-time **email notifications** to customers

### 📅 Scheduler / Artisan Commands

- `php artisan schedule:work` automates:
    - Sending reminders before reservation
    - Auto-cancellation of no-shows

---

## ⚙️ Tech Stack

- **Framework**: Laravel 11
- **Language**: PHP 8.2+
- **Frontend**: Blade, Bootstrap 5
- **Database**: MySQL
- **Scheduler**: Laravel Task Scheduling
- **Email**: Laravel Mail (SMTP)
- **Authentication**: Laravel Breeze

---

## 📧 Author

**Mustafa Azmi Khalil**

Full-stack developer passionate about building beautiful and user-friendly interfaces.

📬 [Email Me](mailto:mustafa.azmi.khalil@gmail.com)

💬 [WhatsApp](https://wa.me/966545117570)
