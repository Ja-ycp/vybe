<?php
declare(strict_types=1);

class CommentModel
{
    private PDO $conn;
    private string $table = 'comments';

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    public function create(int $postId, int $userId, string $content, ?int $parentId = null, ?string $image = null): int
    {
        $query = "INSERT INTO {$this->table} (post_id, user_id, parent_id, content, image)
                  VALUES (:post_id, :user_id, :parent_id, :content, :image)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':post_id' => $postId,
            ':user_id' => $userId,
            ':parent_id' => $parentId,
            ':content' => $content,
            ':image' => $image,
        ]);

        return (int) $this->conn->lastInsertId();
    }

    public function getByPost(int $postId, ?int $viewerId = null): array
    {
        $grouped = $this->getByPostIds([$postId], $viewerId);
        return $grouped[$postId] ?? [];
    }

    public function getByPostIds(array $postIds, ?int $viewerId = null): array
    {
        if ($postIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($postIds), '?'));
        $query = "SELECT
                    c.*,
                    u.full_name,
                    u.username,
                    u.profile_image,
                    (SELECT COUNT(*) FROM comment_reactions cr WHERE cr.comment_id = c.id) AS reaction_count,
                    EXISTS(
                        SELECT 1
                        FROM comment_reactions viewer_reaction
                        WHERE viewer_reaction.comment_id = c.id
                          AND viewer_reaction.user_id = ?
                    ) AS is_reacted
                  FROM {$this->table} c
                  INNER JOIN users u ON u.id = c.user_id
                  WHERE c.post_id IN ({$placeholders})
                  ORDER BY COALESCE(c.parent_id, c.id) ASC, c.parent_id IS NOT NULL ASC, c.created_at ASC, c.id ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute(array_merge([(int) ($viewerId ?? 0)], array_values($postIds)));

        $rows = $stmt->fetchAll();
        $commentsById = [];
        foreach ($rows as $comment) {
            $comment['replies'] = [];
            $commentsById[(int) $comment['id']] = $comment;
        }

        $groupedComments = [];
        foreach ($commentsById as $commentId => &$comment) {
            $parentId = $comment['parent_id'] !== null ? (int) $comment['parent_id'] : null;
            if ($parentId !== null && isset($commentsById[$parentId])) {
                $commentsById[$parentId]['replies'][] = &$comment;
                continue;
            }

            $groupedComments[(int) $comment['post_id']][] = &$comment;
        }
        unset($comment);

        return $groupedComments;
    }

    public function getById(int $id): ?array
    {
        $query = "SELECT c.*, u.full_name, u.username, u.profile_image
                  FROM {$this->table} c
                  INNER JOIN users u ON u.id = c.user_id
                  WHERE c.id = :id
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id]);

        $comment = $stmt->fetch();
        return $comment ?: null;
    }

    public function getReplyTarget(int $id): ?array
    {
        $query = "SELECT id, post_id, user_id, parent_id, content
                  FROM {$this->table}
                  WHERE id = :id
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id]);

        $comment = $stmt->fetch();
        return $comment ?: null;
    }

    public function update(int $id, string $content, int $userId): bool
    {
        $query = "UPDATE {$this->table}
                  SET content = :content
                  WHERE id = :id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':content' => $content,
            ':id' => $id,
            ':user_id' => $userId,
        ]);
    }

    public function delete(int $id, int $userId): bool
    {
        $query = "DELETE FROM {$this->table}
                  WHERE id = :id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId,
        ]);
    }

    public function getImageUploadsByUser(int $userId): array
    {
        $query = "SELECT image
                  FROM {$this->table}
                  WHERE user_id = :user_id
                    AND image IS NOT NULL
                    AND image != ''";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':user_id' => $userId]);

        return array_values(array_filter(
            array_map(static fn(array $row): string => (string) $row['image'], $stmt->fetchAll()),
            static fn(string $image): bool => $image !== ''
        ));
    }
}
?>

