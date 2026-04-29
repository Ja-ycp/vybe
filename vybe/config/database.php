<?php
declare(strict_types=1);

class Database
{
    private string $driver = 'mysql';
    private string $host = '127.0.0.1';
    private string $port = '3306';
    private string $dbName = 'vybe_social';
    private string $username = 'root';
    private string $password = '';
    private string $sqlitePath = '';
    private bool $hasExplicitMysqlConfig = false;
    private ?string $lastError = null;

    public function __construct()
    {
        $this->sqlitePath = $this->readEnv(['SQLITE_PATH']) ?? $this->getDefaultSqlitePath();

        $requestedDriver = strtolower($this->readEnv(['DB_CONNECTION', 'DB_DRIVER']) ?? 'mysql');
        if ($requestedDriver === 'sqlite') {
            $this->driver = 'sqlite';
            return;
        }

        $databaseUrl = $this->readEnv(['DATABASE_URL', 'MYSQL_URL', 'CLEARDB_DATABASE_URL']);
        if ($databaseUrl !== null) {
            $this->hasExplicitMysqlConfig = true;
            $this->applyDatabaseUrl($databaseUrl);
        }

        $host = $this->readEnv(['DB_HOST', 'MYSQLHOST']);
        if ($host !== null) {
            $this->host = $host;
            $this->hasExplicitMysqlConfig = true;
        }

        $port = $this->readEnv(['DB_PORT', 'MYSQLPORT']);
        if ($port !== null) {
            $this->port = $port;
        }

        $dbName = $this->readEnv(['DB_NAME', 'MYSQLDATABASE']);
        if ($dbName !== null) {
            $this->dbName = $dbName;
        }

        $username = $this->readEnv(['DB_USER', 'DB_USERNAME', 'MYSQLUSER']);
        if ($username !== null) {
            $this->username = $username;
            $this->hasExplicitMysqlConfig = true;
        }

        $password = $this->readEnv(['DB_PASSWORD', 'MYSQLPASSWORD']);
        if ($password !== null) {
            $this->password = $password;
            $this->hasExplicitMysqlConfig = true;
        }
    }

    private function readEnv(array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = getenv($key);
            if ($value !== false && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function applyDatabaseUrl(?string $url): void
    {
        if (!$url) {
            return;
        }

        $parts = parse_url($url);
        if ($parts === false) {
            return;
        }

        if (isset($parts['host']) && $parts['host'] !== '') {
            $this->host = $parts['host'];
        }

        if (isset($parts['port']) && $parts['port'] > 0) {
            $this->port = (string) $parts['port'];
        }

        if (isset($parts['user']) && $parts['user'] !== '') {
            $this->username = $parts['user'];
        }

        if (isset($parts['pass'])) {
            $this->password = $parts['pass'];
        }

        if (isset($parts['path']) && $parts['path'] !== '') {
            $this->dbName = ltrim($parts['path'], '/');
        }
    }

    public function getConnection(): ?PDO
    {
        if ($this->driver === 'sqlite') {
            return $this->connectSqlite();
        }

        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $this->host,
                $this->port,
                $this->dbName
            );

            return new PDO(
                $dsn,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => 10,
                ]
            );
        } catch (PDOException $exception) {
            $this->lastError = $exception->getMessage();
            error_log(
                sprintf(
                    'DB connect failed host=%s port=%s db=%s user=%s error=%s',
                    $this->host,
                    $this->port,
                    $this->dbName,
                    $this->username,
                    $exception->getMessage()
                )
            );

            if ($this->shouldFallbackToSqlite()) {
                error_log('Falling back to SQLite database: ' . $this->sqlitePath);
                return $this->connectSqlite();
            }

            return null;
        }
    }

    private function shouldFallbackToSqlite(): bool
    {
        $flag = $this->readEnv(['DB_FALLBACK_SQLITE', 'ALLOW_SQLITE_FALLBACK']);
        if ($flag !== null) {
            return in_array(strtolower($flag), ['1', 'true', 'yes', 'on'], true);
        }

        $runningOnRender = getenv('RENDER') !== false;
        return $runningOnRender && !$this->hasExplicitMysqlConfig;
    }

    private function getDefaultSqlitePath(): string
    {
        if (defined('APP_ROOT')) {
            return APP_ROOT . '/data/vybe.sqlite';
        }

        return dirname(__DIR__) . '/data/vybe.sqlite';
    }

    private function connectSqlite(): ?PDO
    {
        try {
            $directory = dirname($this->sqlitePath);
            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }

            $isNewDatabase = !file_exists($this->sqlitePath);
            $pdo = new PDO('sqlite:' . $this->sqlitePath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->exec('PRAGMA foreign_keys = ON');

            $this->createSqliteSchema($pdo);
            if ($isNewDatabase) {
                $this->seedSqlite($pdo);
            }

            return $pdo;
        } catch (PDOException $exception) {
            $this->lastError = $exception->getMessage();
            error_log('SQLite connect failed path=' . $this->sqlitePath . ' error=' . $exception->getMessage());
            return null;
        }
    }

    private function createSqliteSchema(PDO $pdo): void
    {
        $statements = [
            "CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                full_name TEXT NOT NULL,
                bio TEXT,
                profile_image TEXT DEFAULT NULL,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS posts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                shared_post_id INTEGER DEFAULT NULL,
                content TEXT NOT NULL,
                image TEXT DEFAULT NULL,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (shared_post_id) REFERENCES posts(id) ON DELETE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS comments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                post_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                parent_id INTEGER DEFAULT NULL,
                content TEXT NOT NULL,
                image TEXT DEFAULT NULL,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS likes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                post_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                UNIQUE (post_id, user_id),
                FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS comment_reactions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                comment_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                UNIQUE (comment_id, user_id),
                FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS messages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                sender_id INTEGER NOT NULL,
                receiver_id INTEGER NOT NULL,
                reply_to_message_id INTEGER DEFAULT NULL,
                content TEXT NOT NULL,
                is_read INTEGER NOT NULL DEFAULT 0,
                unsent_at TEXT DEFAULT NULL,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (reply_to_message_id) REFERENCES messages(id) ON DELETE SET NULL
            )",
            "CREATE TABLE IF NOT EXISTS message_reactions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                message_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                UNIQUE (message_id, user_id),
                FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS message_user_deletions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                message_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                deleted_at TEXT DEFAULT CURRENT_TIMESTAMP,
                UNIQUE (message_id, user_id),
                FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )",
            'CREATE INDEX IF NOT EXISTS idx_posts_user ON posts(user_id)',
            'CREATE INDEX IF NOT EXISTS idx_posts_created ON posts(created_at DESC)',
            'CREATE INDEX IF NOT EXISTS idx_posts_shared ON posts(shared_post_id)',
            'CREATE INDEX IF NOT EXISTS idx_comments_post ON comments(post_id)',
            'CREATE INDEX IF NOT EXISTS idx_comments_parent ON comments(parent_id)',
            'CREATE INDEX IF NOT EXISTS idx_likes_post ON likes(post_id)',
            'CREATE INDEX IF NOT EXISTS idx_comment_reactions_comment ON comment_reactions(comment_id)',
            'CREATE INDEX IF NOT EXISTS idx_messages_sender_receiver ON messages(sender_id, receiver_id)',
            'CREATE INDEX IF NOT EXISTS idx_messages_receiver_read ON messages(receiver_id, is_read)',
            'CREATE INDEX IF NOT EXISTS idx_messages_reply_to ON messages(reply_to_message_id)',
            'CREATE INDEX IF NOT EXISTS idx_message_reactions_message ON message_reactions(message_id)',
            'CREATE INDEX IF NOT EXISTS idx_message_user_deletions_user ON message_user_deletions(user_id, message_id)',
        ];

        foreach ($statements as $statement) {
            $pdo->exec($statement);
        }
    }

    private function seedSqlite(PDO $pdo): void
    {
        $userCount = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        if ($userCount > 0) {
            return;
        }

        $insertUser = $pdo->prepare(
            'INSERT INTO users (username, password, full_name, bio) VALUES (?, ?, ?, ?)'
        );
        $insertUser->execute([
            'johnp',
            '$2y$10$f2KEHve17CgL5hmylfdkiuJVKSV4C8lMOc5LAirIYfxOzTbLj2HoK',
            'John Clifford',
            'Multimedia student sharing project updates and campus photos.',
        ]);

        $insertPost = $pdo->prepare(
            'INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)'
        );
        $insertPost->execute([
            1,
            'Finished the first draft of our mini social app UI today. The feed is finally coming together.',
            null,
        ]);
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }
}
?>

