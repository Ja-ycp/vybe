<?php
declare(strict_types=1);

class PostModel
{
    private PDO $conn;
    private string $table = 'posts';

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    public function create(int $userId, string $content, ?string $image = null, ?int $sharedPostId = null): int
    {
        $query = "INSERT INTO {$this->table} (user_id, content, image, shared_post_id)
                  VALUES (:user_id, :content, :image, :shared_post_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':user_id' => $userId,
            ':content' => $content,
            ':image' => $image,
            ':shared_post_id' => $sharedPostId,
        ]);

        return (int) $this->conn->lastInsertId();
    }

    public function getFeed(?int $viewerId = null): array
    {
        $query = $this->baseSelect() . "
                  ORDER BY p.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([':viewer_id' => $viewerId ?? 0]);

        return $stmt->fetchAll();
    }

    public function getById(int $id, ?int $viewerId = null): ?array
    {
        $query = $this->baseSelect() . "
                  WHERE p.id = :id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':id' => $id,
            ':viewer_id' => $viewerId ?? 0,
        ]);

        $post = $stmt->fetch();
        return $post ?: null;
    }

    public function update(int $id, string $content, ?string $image, bool $removeImage = false): bool
    {
        $query = "UPDATE {$this->table}
                  SET content = :content,
                      image = :image
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':content' => $content,
            ':image' => $removeImage ? null : $image,
            ':id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':id' => $id]);
    }

    public function getByUser(int $userId, ?int $viewerId = null): array
    {
        $query = $this->baseSelect() . "
                  WHERE p.user_id = :user_id
                  ORDER BY p.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':user_id' => $userId,
            ':viewer_id' => $viewerId ?? 0,
        ]);

        return $stmt->fetchAll();
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

    private function baseSelect(): string
    {
        return "SELECT
                    p.*,
                    u.full_name,
                    u.username,
                    u.profile_image,
                    (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count,
                    (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count,
                    (SELECT COUNT(*) FROM {$this->table} shared_posts WHERE shared_posts.shared_post_id = p.id) AS share_count,
                    EXISTS(
                        SELECT 1
                        FROM likes viewer_like
                        WHERE viewer_like.post_id = p.id
                          AND viewer_like.user_id = :viewer_id
                    ) AS is_liked,
                    shared_p.user_id AS shared_post_user_id,
                    shared_p.content AS shared_post_content,
                    shared_p.image AS shared_post_image,
                    shared_p.created_at AS shared_post_created_at,
                    shared_u.full_name AS shared_post_full_name,
                    shared_u.username AS shared_post_username,
                    shared_u.profile_image AS shared_post_profile_image
                  FROM {$this->table} p
                  INNER JOIN users u ON u.id = p.user_id
                  LEFT JOIN {$this->table} shared_p ON shared_p.id = p.shared_post_id
                  LEFT JOIN users shared_u ON shared_u.id = shared_p.user_id";
    }
}

