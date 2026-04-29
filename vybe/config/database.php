<?php
declare(strict_types=1);

class Database
{
    private string $host = '127.0.0.1';
    private string $port = '3306';
    private string $dbName = 'vybe_social';
    private string $username = 'root';
    private string $password = '';
    private ?string $lastError = null;

    public function __construct()
    {
        $this->applyDatabaseUrl(
            getenv('DATABASE_URL') ?: getenv('MYSQL_URL') ?: getenv('CLEARDB_DATABASE_URL') ?: null
        );

        $this->host = $this->readEnv(['DB_HOST', 'MYSQLHOST']) ?? $this->host;
        $this->port = $this->readEnv(['DB_PORT', 'MYSQLPORT']) ?? $this->port;
        $this->dbName = $this->readEnv(['DB_NAME', 'MYSQLDATABASE']) ?? $this->dbName;
        $this->username = $this->readEnv(['DB_USER', 'DB_USERNAME', 'MYSQLUSER']) ?? $this->username;
        $this->password = $this->readEnv(['DB_PASSWORD', 'MYSQLPASSWORD']) ?? $this->password;
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
            return null;
        }
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }
}
?>

