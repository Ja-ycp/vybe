# Vybe Mini Social Networking Web App

## 1. Introduction
Vybe is a mini social networking web application built as a full-stack PHP MVC project with MySQL. It allows users to register, log in, manage profiles, publish posts, comment on posts, react with likes, and discover other users through search.

## 2. Core Features
- User authentication with register, login, logout, hashed passwords, and session handling
- User profiles with avatar upload, full name, username, bio, and post history
- Post CRUD with optional image uploads
- Comment CRUD with ownership checks
- Like toggle with one-like-per-user-per-post behavior
- Private one-to-one messaging inbox
- Newsfeed showing the latest posts from all users
- User discovery via name or username search
- Responsive Bootstrap-based interface with custom styling

## 3. MVC Architecture
- `app/models/` contains PDO-based database logic only
- `app/controllers/` handles validation, authorization, redirects, and business rules
- `app/views/` contains reusable presentation templates and layouts
- `public/index.php` acts as the front controller and router
- `config/database.php` provides the PDO connection

## 4. Security Practices
- Prepared statements for all database operations
- `password_hash()` and `password_verify()` for passwords
- Output escaping through `htmlspecialchars()`
- Session-based authentication
- Authorization checks so users can edit or delete only their own posts and comments
- Basic CSRF token protection for POST actions
- Image upload validation for type and size

## 5. Database
Required tables included in `sql/social_app.sql`:
- `users`
- `posts`
- `comments`
- `likes`
- `messages`

The SQL file also includes sample users, posts, comments, and likes.

## 6. Sample Accounts
- `johnp / password123`

## 7. Setup Guide
1. Start Apache and MySQL in XAMPP.
2. Import `sql/social_app.sql` into MySQL or phpMyAdmin.
3. Make sure `config/database.php` matches your local MySQL credentials if needed.
4. Open [public/index.php](/C:/xampp/htdocs/vybe/public/index.php) through `http://localhost/vybe/public/`.

## 8. Suggested Documentation Attachments
- Cover page
- Introduction and objectives
- System features
- Use case diagram
- ERD
- Database schema description
- Screenshots of login, register, feed, profile, create post, edit profile, and comment flows
- User guide

## 9. ERD Reference
See [ERD.txt](/C:/xampp/htdocs/vybe/docs/ERD.txt).
