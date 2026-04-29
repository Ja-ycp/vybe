# Vybe Run Guide

## 1. Start Services
1. Open XAMPP Control Panel.
2. Start `Apache`.
3. Start `MySQL`.

## 2. Import the Database
1. Open `http://localhost/phpmyadmin`.
2. Create or select the `vybe_social` database.
3. Import [social_app.sql](/C:/xampp/htdocs/vybe/sql/social_app.sql).

## 3. Check Database Credentials
Review [database.php](/C:/xampp/htdocs/vybe/config/database.php) and update the MySQL password if your local XAMPP setup is different.

## 4. Launch the App
Open `http://localhost/vybe/public/`

## 5. Enable Google Sign-In (Optional)
1. In Google Cloud Console, create OAuth credentials for a **Web application**.
2. Add your callback URL to Authorized redirect URIs:
   - Local: `http://localhost/vybe/public/index.php?controller=Auth&action=googleCallback`
   - Render: `https://<your-render-domain>/index.php?controller=Auth&action=googleCallback`
3. Set environment variables:
   - `GOOGLE_CLIENT_ID`
   - `GOOGLE_CLIENT_SECRET`
   - Optional: `GOOGLE_REDIRECT_URI` (use this if you want to force one callback URL)

## 6. Sample Login Accounts
- `johnp / password123`

## 7. Suggested Demo Flow
1. Register a new account or log in with a sample user.
2. Create a post with or without an image.
3. Like a post and add a comment.
4. Search for another user by username or full name.
5. Open that user's profile and send a private message.
6. Edit your comment and update your profile.
7. Edit and delete your own posts to demonstrate authorization.
