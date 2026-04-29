<?php
declare(strict_types=1);

class MessageModel
{
    private PDO $conn;
    private string $table = 'messages';

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    public function sendMessage(int $senderId, int $receiverId, string $content, ?int $replyToMessageId = null): int
    {
        $query = "INSERT INTO {$this->table} (sender_id, receiver_id, content, reply_to_message_id)
                  VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$senderId, $receiverId, $content, $replyToMessageId]);

        return (int) $this->conn->lastInsertId();
    }

    public function getConversation(int $userId, int $otherUserId): array
    {
        $query = "SELECT
                    m.*,
                    sender.username AS sender_username,
                    sender.full_name AS sender_full_name,
                    sender.profile_image AS sender_profile_image,
                    reply_message.id AS reply_message_id,
                    reply_message.content AS reply_message_content,
                    reply_message.unsent_at AS reply_message_unsent_at,
                    reply_sender.full_name AS reply_sender_full_name,
                    reply_sender.username AS reply_sender_username,
                    (SELECT COUNT(*) FROM message_reactions mr WHERE mr.message_id = m.id) AS reaction_count,
                    EXISTS(
                        SELECT 1
                        FROM message_reactions viewer_reaction
                        WHERE viewer_reaction.message_id = m.id
                          AND viewer_reaction.user_id = ?
                    ) AS is_reacted
                  FROM {$this->table} m
                  INNER JOIN users sender ON sender.id = m.sender_id
                  LEFT JOIN {$this->table} reply_message ON reply_message.id = m.reply_to_message_id
                  LEFT JOIN users reply_sender ON reply_sender.id = reply_message.sender_id
                  WHERE (
                        (m.sender_id = ? AND m.receiver_id = ?)
                     OR (m.sender_id = ? AND m.receiver_id = ?)
                  )
                    AND NOT EXISTS (
                        SELECT 1
                        FROM message_user_deletions hidden_message
                        WHERE hidden_message.message_id = m.id
                          AND hidden_message.user_id = ?
                    )
                  ORDER BY m.created_at ASC, m.id ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId, $userId, $otherUserId, $otherUserId, $userId, $userId]);

        return $stmt->fetchAll();
    }

    public function markConversationAsRead(int $userId, int $otherUserId): bool
    {
        $query = "UPDATE {$this->table}
                  SET is_read = 1
                  WHERE sender_id = ?
                    AND receiver_id = ?
                    AND is_read = 0
                    AND NOT EXISTS (
                        SELECT 1
                        FROM message_user_deletions hidden_message
                        WHERE hidden_message.message_id = {$this->table}.id
                          AND hidden_message.user_id = ?
                    )";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$otherUserId, $userId, $userId]);
    }

    public function getInbox(int $userId): array
    {
        $query = "SELECT
                    partner.id AS partner_id,
                    partner.username,
                    partner.full_name,
                    partner.profile_image,
                    latest.content AS last_message,
                    latest.sender_id AS last_message_sender_id,
                    latest.unsent_at AS last_message_unsent_at,
                    latest.created_at AS last_message_at,
                    (
                        SELECT COUNT(*)
                        FROM {$this->table} unread
                        WHERE unread.sender_id = partner.id
                          AND unread.receiver_id = ?
                          AND unread.is_read = 0
                          AND NOT EXISTS (
                              SELECT 1
                              FROM message_user_deletions hidden_unread
                              WHERE hidden_unread.message_id = unread.id
                                AND hidden_unread.user_id = ?
                          )
                    ) AS unread_count
                  FROM (
                    SELECT
                        CASE WHEN message.sender_id = ? THEN message.receiver_id ELSE message.sender_id END AS partner_id,
                        MAX(message.id) AS last_message_id
                    FROM {$this->table} message
                    WHERE (message.sender_id = ? OR message.receiver_id = ?)
                      AND NOT EXISTS (
                          SELECT 1
                          FROM message_user_deletions hidden_thread
                          WHERE hidden_thread.message_id = message.id
                            AND hidden_thread.user_id = ?
                      )
                    GROUP BY partner_id
                  ) thread
                  INNER JOIN {$this->table} latest ON latest.id = thread.last_message_id
                  INNER JOIN users partner ON partner.id = thread.partner_id
                  ORDER BY latest.created_at DESC, latest.id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId, $userId, $userId, $userId, $userId, $userId]);

        return $stmt->fetchAll();
    }

    public function getMessageForUser(int $messageId, int $userId): ?array
    {
        $query = "SELECT message.*
                  FROM {$this->table} message
                  WHERE message.id = ?
                    AND (message.sender_id = ? OR message.receiver_id = ?)
                    AND NOT EXISTS (
                        SELECT 1
                        FROM message_user_deletions hidden_message
                        WHERE hidden_message.message_id = message.id
                          AND hidden_message.user_id = ?
                    )
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$messageId, $userId, $userId, $userId]);

        $message = $stmt->fetch();
        return $message ?: null;
    }

    public function deleteConversationForUser(int $userId, int $otherUserId): int
    {
        $query = "INSERT INTO message_user_deletions (message_id, user_id)
                  SELECT message.id, ?
                  FROM {$this->table} message
                  LEFT JOIN message_user_deletions existing_hide
                    ON existing_hide.message_id = message.id
                   AND existing_hide.user_id = ?
                  WHERE (
                        (message.sender_id = ? AND message.receiver_id = ?)
                     OR (message.sender_id = ? AND message.receiver_id = ?)
                  )
                    AND existing_hide.id IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId, $userId, $userId, $otherUserId, $otherUserId, $userId]);

        return $stmt->rowCount();
    }

    public function removeMessageForUser(int $messageId, int $userId): bool
    {
        $query = "INSERT INTO message_user_deletions (message_id, user_id)
                  SELECT ?, ?
                  WHERE NOT EXISTS (
                      SELECT 1
                      FROM message_user_deletions
                      WHERE message_id = ?
                        AND user_id = ?
                  )";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$messageId, $userId, $messageId, $userId]);
    }

    public function unsendMessage(int $messageId, int $senderId): bool
    {
        try {
            $this->conn->beginTransaction();

            $updateQuery = "UPDATE {$this->table}
                            SET unsent_at = CURRENT_TIMESTAMP
                            WHERE id = ?
                              AND sender_id = ?
                              AND unsent_at IS NULL";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->execute([$messageId, $senderId]);

            if ($updateStmt->rowCount() < 1) {
                $this->conn->rollBack();
                return false;
            }

            $deleteReactionsQuery = "DELETE FROM message_reactions WHERE message_id = ?";
            $deleteReactionsStmt = $this->conn->prepare($deleteReactionsQuery);
            $deleteReactionsStmt->execute([$messageId]);

            $this->conn->commit();
            return true;
        } catch (Throwable $exception) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            throw $exception;
        }
    }

    public function toggleReaction(int $messageId, int $userId): bool
    {
        $checkQuery = "SELECT 1 FROM message_reactions WHERE message_id = ? AND user_id = ? LIMIT 1";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->execute([$messageId, $userId]);

        if ($checkStmt->fetch() !== false) {
            $deleteQuery = "DELETE FROM message_reactions WHERE message_id = ? AND user_id = ?";
            $deleteStmt = $this->conn->prepare($deleteQuery);
            $deleteStmt->execute([$messageId, $userId]);

            return false;
        }

        $insertQuery = "INSERT INTO message_reactions (message_id, user_id) VALUES (?, ?)";
        $insertStmt = $this->conn->prepare($insertQuery);
        $insertStmt->execute([$messageId, $userId]);

        return true;
    }
}
