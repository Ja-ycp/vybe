<?php
declare(strict_types=1);

class CommentReactionModel
{
    private PDO $conn;
    private string $table = 'comment_reactions';

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    public function toggle(int $commentId, int $userId): bool
    {
        if ($this->isReacted($commentId, $userId)) {
            $query = "DELETE FROM {$this->table} WHERE comment_id = :comment_id AND user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':comment_id' => $commentId,
                ':user_id' => $userId,
            ]);

            return false;
        }

        $query = "INSERT INTO {$this->table} (comment_id, user_id) VALUES (:comment_id, :user_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':comment_id' => $commentId,
            ':user_id' => $userId,
        ]);

        return true;
    }

    public function isReacted(int $commentId, int $userId): bool
    {
        $query = "SELECT 1 FROM {$this->table} WHERE comment_id = :comment_id AND user_id = :user_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':comment_id' => $commentId,
            ':user_id' => $userId,
        ]);

        return $stmt->fetch() !== false;
    }
}
?>
