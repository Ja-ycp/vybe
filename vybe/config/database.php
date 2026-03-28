<?php
declare(strict_types=1);

class Database
{
    private string $host = '127.0.0.1';
    private string $port = '3306';
    private string $dbName = 'vybe_social';
    private string $username = 'root';
    private string $password = '';

    public function __construct()
    {
        $this->host = getenv('DB_HOST') ?: $this->host;
        $this->port = getenv('DB_PORT') ?: $this->port;
        $this->dbName = getenv('DB_NAME') ?: $this->dbName;
        $this->username = getenv('DB_USER') ?: $this->username;

        $envPassword = getenv('DB_PASSWORD');
        if ($envPassword !== false) {
            $this->password = $envPassword;
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
                ]
            );
        } catch (PDOException $exception) {
            return null;
        }
    }
}
?>

