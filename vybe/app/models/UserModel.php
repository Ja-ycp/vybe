<?php
declare(strict_types=1);

class UserModel
{
    private PDO $conn;
    private string $table = 'users';

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    public function create(
        string $username,
        string $password,
        string $fullName,
        string $bio = '',
        ?string $profileImage = null
    ): int {
        $query = "INSERT INTO {$this->table} (username, password, full_name, bio, profile_image)
                  VALUES (:username, :password, :full_name, :bio, :profile_image)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':username' => $username,
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':full_name' => $fullName,
            ':bio' => $bio,
            ':profile_image' => $profileImage,
        ]);

        return (int) $this->conn->lastInsertId();
    }

    public function findByUsername(string $username): ?array
    {
        $query = "SELECT * FROM {$this->table} WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':username' => $username]);

        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function verify(array $user, string $password): bool
    {
        return password_verify($password, $user['password']);
    }

    public function getById(int $id): ?array
    {
        $query = "SELECT id, username, full_name, bio, profile_image, created_at
                  FROM {$this->table}
                  WHERE id = :id
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id]);

        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function getAuthById(int $id): ?array
    {
        $query = "SELECT id, username, full_name, bio, profile_image, password, created_at
                  FROM {$this->table}
                  WHERE id = :id
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id]);

        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function update(int $id, string $fullName, string $bio, ?string $profileImage, bool $removeImage = false): bool
    {
        $query = "UPDATE {$this->table}
                  SET full_name = :full_name,
                      bio = :bio,
                      profile_image = :profile_image
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':full_name' => $fullName,
            ':bio' => $bio,
            ':profile_image' => $removeImage ? null : $profileImage,
            ':id' => $id,
        ]);
    }

    public function updatePassword(int $id, string $password): bool
    {
        $query = "UPDATE {$this->table}
                  SET password = :password
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        return $stmt->execute([':id' => $id]);
    }

    public function search(string $term, ?int $excludeUserId = null, int $limit = 8): array
    {
        $conditions = ['(full_name LIKE ? OR username LIKE ?)'];
        $likeTerm = '%' . $term . '%';
        $params = [$likeTerm, $likeTerm];

        if ($excludeUserId !== null) {
            $conditions[] = 'id != ?';
            $params[] = $excludeUserId;
        }

        $limit = max(1, $limit);

        $query = "SELECT id, username, full_name, bio, profile_image, created_at
                  FROM {$this->table}
                  WHERE " . implode(' AND ', $conditions) . "
                  ORDER BY full_name ASC
                  LIMIT {$limit}";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function getRecentUsers(?int $excludeUserId = null, int $limit = 6): array
    {
        $limit = max(1, $limit);
        $query = "SELECT id, username, full_name, bio, profile_image, created_at
                  FROM {$this->table}";
        $params = [];

        if ($excludeUserId !== null) {
            $query .= " WHERE id != :exclude_id";
            $params[':exclude_id'] = $excludeUserId;
        }

        $query .= " ORDER BY created_at DESC LIMIT {$limit}";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }
}

