# Vybe PHP MVC Migration TODO

## Progress Tracker for Mini Social Networking App Migration

### 1. [x] Backup existing data/assets
   - Copy data/db.json and public/uploads to backups/ (done)

### 2. [x] MySQL Setup
   - Install/enable XAMPP/MariaDB (localhost:3306)
   - Create DB `vybe_social`
   - Execute sql/social_app.sql

### 3. [x] Create core structure
   - app/controllers/* (pending)
   - app/models/* (User/Post/Comment/Like done)
   - app/views/* (pending)
   - config/database.php (done)
   - public/index.php (pending)
   - sql/social_app.sql (done)
   - docs/ (pending)
   - app/controllers/* (Auth/Profile/Post/Comment/Like)
   - app/models/* (User/Post/Comment/Like)
   - app/views/* (layouts, auth, profile, feed, etc.)
   - config/database.php
   - public/index.php (router)
   - sql/social_app.sql
   - docs/ (ERD, README)

### 4. [x] Implement Models (PDO CRUD)
   - UserModel: CRUD users, hash, photo (done)
   - PostModel: CRUD posts (done)
   - CommentModel: CRUD (done)
   - LikeModel: toggle/count (done)
   - UserModel: CRUD users, hash, photo
   - PostModel: CRUD posts
   - etc.

### 5. [ ] Implement Controllers
   - AuthController: register/login/logout
   - PostController: create/read/update/delete/list
   - etc. w/ session checks

### 6. [ ] Views/Templates
   - Adapt index.html to PHP includes
   - header/footer/components

### 7. [ ] Frontend Adapt (app.js)
   - Update API calls to PHP routes

### 8. [ ] Security/Validation
   - PDO prepared, htmlspecialchars, auth guards

### 9. [ ] Test Modules
   - Auth flow, post CRUD, comments, likes, profile photo, search, responsive

### 10. [x] Docs/Deliverables
    - ERD diagram (docs/ERD.txt done)
    - README_PROJECT.md (done)
    - ERD diagram
    - README_PROJECT.md (outcomes, screenshots guide)

### 11. [x] Cleanup
    - Remove Node files (done)
    - Final test/demo (pending XAMPP/DB)

**Next: Step 1 backup.** Update after each step completion.
