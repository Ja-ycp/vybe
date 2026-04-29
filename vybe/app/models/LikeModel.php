<?php
declare(strict_types=1);

class LikeModel
{
    private PDO $conn;
    private string $table = 'likes';

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    public function toggle(int $postId, int $userId): bool
    {
        if ($this->isLiked($postId, $userId)) {
            $query = "DELETE FROM {$this->table} WHERE post_id = :post_id AND user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':post_id' => $postId,
                ':user_id' => $userId,
            ]);

            return false;
        }

        $query = "INSERT INTO {$this->table} (post_id, user_id) VALUES (:post_id, :user_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':post_id' => $postId,
            ':user_id' => $userId,
        ]);

        return true;
    }

    public function getCount(int $postId): int
    {
        $query = "SELECT COUNT(*) AS count FROM {$this->table} WHERE post_id = :post_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':post_id' => $postId]);

        return (int) ($stmt->fetch()['count'] ?? 0);
    }

    public function isLiked(int $postId, int $userId): bool
    {
        $query = "SELECT 1 FROM {$this->table} WHERE post_id = :post_id AND user_id = :user_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':post_id' => $postId,
            ':user_id' => $userId,
        ]);

        return $stmt->fetch() !== false;
    }
}
?>

