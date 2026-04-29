-- SQLite Schema (fallback)
CREATE TABLE users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  username TEXT UNIQUE NOT NULL,
  password TEXT NOT NULL,
  full_name TEXT NOT NULL,
  bio TEXT,
  profile_image TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
-- Add other tables similar
INSERT INTO users (username, password, full_name, bio) VALUES
('johnp', '$2b$10$6iWUy7OsleTeI6e67X/msuJiC0qvSqREaIoQUuG0rM62f2hJiWY8i', 'john clifford', 'me lang'),
('kathyp', '$2b$10$jO03zELd/qdR5IzVP9v90O4ghTMlEyvN0KWWUT0zUq6X6HwImVwKa', 'kathy P', 'i love kathy');
