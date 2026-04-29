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

    public function findByEmail(string $email): ?array
    {
        $query = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':email' => strtolower(trim($email))]);

        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findByGoogleId(string $googleId): ?array
    {
        $query = "SELECT * FROM {$this->table} WHERE google_id = :google_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':google_id' => $googleId]);

        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function linkGoogleAccount(
        int $userId,
        string $googleId,
        string $email,
        ?string $profileImage = null
    ): bool {
        $query = "UPDATE {$this->table}
                  SET google_id = :google_id,
                      email = :email,
                      auth_provider = CASE
                          WHEN auth_provider = '' OR auth_provider IS NULL THEN 'google'
                          WHEN auth_provider = 'local' THEN 'local_google'
                          ELSE auth_provider
                      END,
                      profile_image = CASE
                          WHEN (profile_image IS NULL OR profile_image = '') AND :profile_image IS NOT NULL THEN :profile_image
                          ELSE profile_image
                      END
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ':google_id' => $googleId,
            ':email' => strtolower(trim($email)),
            ':profile_image' => $profileImage,
            ':id' => $userId,
        ]);
    }

    public function createFromGoogle(
        string $googleId,
        string $email,
        string $fullName,
        ?string $profileImage = null
    ): int {
        $username = $this->generateUniqueUsername($email, $fullName);
        $password = bin2hex(random_bytes(24));
        $userId = $this->create($username, $password, $fullName, '', $profileImage);
        $this->linkGoogleAccount($userId, $googleId, $email, $profileImage);
        $providerStmt = $this->conn->prepare("UPDATE {$this->table} SET auth_provider = 'google' WHERE id = :id");
        $providerStmt->execute([':id' => $userId]);

        return $userId;
    }

    public function verify(array $user, string $password): bool
    {
        $hash = isset($user['password']) ? (string) $user['password'] : '';
        if ($hash === '') {
            return false;
        }

        return password_verify($password, $hash);
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

    private function generateUniqueUsername(string $email, string $fullName): string
    {
        $emailPart = strtolower(trim(strtok($email, '@') ?: ''));
        $namePart = strtolower(trim($fullName));
        $base = $this->normalizeUsernameCandidate($emailPart !== '' ? $emailPart : $namePart);

        $candidate = $base;
        for ($attempt = 0; $attempt < 50; $attempt++) {
            if ($this->findByUsername($candidate) === null) {
                return $candidate;
            }

            $suffix = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $candidate = substr($base, 0, 15) . '_' . $suffix;
        }

        return 'user_' . substr(bin2hex(random_bytes(6)), 0, 12);
    }

    private function normalizeUsernameCandidate(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9_]+/', '_', $value) ?? '';
        $value = trim($value, '_');

        if ($value === '') {
            $value = 'user';
        }

        if (strlen($value) < 3) {
            $value = str_pad($value, 3, 'x');
        }

        return substr($value, 0, 20);
    }
}

