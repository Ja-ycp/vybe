-- social_app.sql
-- Mini Social Networking App Database Schema
-- MySQL 8.0+ / MariaDB
-- Run in phpMyAdmin or mysql CLI: CREATE DATABASE vybe_social; USE vybe_social; SOURCE social_app.sql;

DROP DATABASE IF EXISTS vybe_social;
CREATE DATABASE vybe_social CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vybe_social;

-- Users Table
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(50) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(190) DEFAULT NULL,
  google_id VARCHAR(191) DEFAULT NULL,
  auth_provider VARCHAR(24) NOT NULL DEFAULT 'local',
  full_name VARCHAR(100) NOT NULL,
  bio TEXT,
  profile_image VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_google_id (google_id)
);

-- Posts Table
CREATE TABLE posts (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  shared_post_id INT DEFAULT NULL,
  content TEXT NOT NULL,
  image VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (shared_post_id) REFERENCES posts(id) ON DELETE CASCADE
);

-- Comments Table
CREATE TABLE comments (
  id INT PRIMARY KEY AUTO_INCREMENT,
  post_id INT NOT NULL,
  user_id INT NOT NULL,
  parent_id INT DEFAULT NULL,
  content TEXT NOT NULL,
  image VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
);

-- Likes Table
CREATE TABLE likes (
  id INT PRIMARY KEY AUTO_INCREMENT,
  post_id INT NOT NULL,
  user_id INT NOT NULL,
  UNIQUE KEY unique_like (post_id, user_id),
  FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Comment Reactions Table
CREATE TABLE comment_reactions (
  id INT PRIMARY KEY AUTO_INCREMENT,
  comment_id INT NOT NULL,
  user_id INT NOT NULL,
  UNIQUE KEY unique_comment_reaction (comment_id, user_id),
  FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Messages Table
CREATE TABLE messages (
  id INT PRIMARY KEY AUTO_INCREMENT,
  sender_id INT NOT NULL,
  receiver_id INT NOT NULL,
  reply_to_message_id INT DEFAULT NULL,
  content TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  unsent_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (reply_to_message_id) REFERENCES messages(id) ON DELETE SET NULL
);

-- Message Reactions Table
CREATE TABLE message_reactions (
  id INT PRIMARY KEY AUTO_INCREMENT,
  message_id INT NOT NULL,
  user_id INT NOT NULL,
  UNIQUE KEY unique_message_reaction (message_id, user_id),
  FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Message Visibility Table
CREATE TABLE message_user_deletions (
  id INT PRIMARY KEY AUTO_INCREMENT,
  message_id INT NOT NULL,
  user_id INT NOT NULL,
  deleted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_message_user_delete (message_id, user_id),
  FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Indexes for performance
CREATE INDEX idx_posts_user ON posts(user_id);
CREATE INDEX idx_posts_created ON posts(created_at DESC);
CREATE INDEX idx_posts_shared ON posts(shared_post_id);
CREATE INDEX idx_comments_post ON comments(post_id);
CREATE INDEX idx_comments_parent ON comments(parent_id);
CREATE INDEX idx_likes_post ON likes(post_id);
CREATE INDEX idx_comment_reactions_comment ON comment_reactions(comment_id);
CREATE INDEX idx_messages_sender_receiver ON messages(sender_id, receiver_id);
CREATE INDEX idx_messages_receiver_read ON messages(receiver_id, is_read);
CREATE INDEX idx_messages_reply_to ON messages(reply_to_message_id);
CREATE INDEX idx_message_reactions_message ON message_reactions(message_id);
CREATE INDEX idx_message_user_deletions_user ON message_user_deletions(user_id, message_id);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_auth_provider ON users(auth_provider);

-- Sample Data (from backup db.json)
INSERT INTO users (username, password, full_name, bio) VALUES
('johnp', '$2y$10$f2KEHve17CgL5hmylfdkiuJVKSV4C8lMOc5LAirIYfxOzTbLj2HoK', 'John Clifford', 'Multimedia student sharing project updates and campus photos.');

INSERT INTO posts (user_id, content, image) VALUES
(1, 'Finished the first draft of our mini social app UI today. The feed is finally coming together.', NULL);

-- ERD Summary (text):
/*
users (1) --- (*) posts (1) --- (*) comments
          |              |
          (*) likes <--- (*)
          |
          (*) comment_reactions (*)
          |
          (*) messages (*)
          |
          (*) message_reactions (*)
          |
          (*) message_user_deletions (*)
*/

-- Usage: Import via phpMyAdmin or CLI: mysql -u root -p vybe_social < social_app.sql
