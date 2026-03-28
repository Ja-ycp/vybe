<?php
declare(strict_types=1);

class MessageController
{
    private PDO $conn;
    private MessageModel $messageModel;
    private UserModel $userModel;

    public function __construct(PDO $db)
    {
        $this->conn = $db;
        $this->messageModel = new MessageModel($db);
        $this->userModel = new UserModel($db);
    }

    public function index(?int $id = null): void
    {
        $currentUserId = (int) $_SESSION['user_id'];
        $threads = $this->messageModel->getInbox($currentUserId);
        $recentUsers = $this->userModel->getRecentUsers($currentUserId, 8);
        $selectedUser = null;
        $conversation = [];

        if ($id !== null) {
            if ($id === $currentUserId) {
                app_set_flash('error', 'You cannot send a private message to yourself.');
                app_redirect(app_route('Message', 'index'));
            }

            $selectedUser = $this->userModel->getById($id);
            if ($selectedUser === null) {
                app_set_flash('error', 'That user could not be found.');
                app_redirect(app_route('Message', 'index'));
            }

            $this->messageModel->markConversationAsRead($currentUserId, $id);
            $conversation = $this->messageModel->getConversation($currentUserId, $id);
        }

        app_render('messages/index', [
            'title' => 'Messages',
            'threads' => $threads,
            'recentUsers' => $recentUsers,
            'selectedUser' => $selectedUser,
            'conversation' => $conversation,
        ]);
    }

    public function send(?int $id = null): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $id === null) {
            app_redirect(app_route('Message', 'index'));
        }

        app_verify_csrf();

        $currentUserId = (int) $_SESSION['user_id'];
        if ($id === $currentUserId) {
            app_set_flash('error', 'You cannot send a private message to yourself.');
            app_redirect(app_route('Message', 'index'));
        }

        $recipient = $this->userModel->getById($id);
        if ($recipient === null) {
            app_set_flash('error', 'That user could not be found.');
            app_redirect(app_route('Message', 'index'));
        }

        $content = trim($_POST['content'] ?? '');
        $replyToMessageId = isset($_POST['reply_to_message_id']) && $_POST['reply_to_message_id'] !== ''
            ? (int) $_POST['reply_to_message_id']
            : null;
        if ($content === '') {
            app_set_flash('error', 'Type a message before sending.');
            app_redirect(app_route('Message', 'index', ['id' => $id]));
        }

        if (mb_strlen($content) > 2000) {
            app_set_flash('error', 'Messages must be 2000 characters or fewer.');
            app_redirect(app_route('Message', 'index', ['id' => $id]));
        }

        if ($replyToMessageId !== null) {
            $replyMessage = $this->messageModel->getMessageForUser($replyToMessageId, $currentUserId);
            $isSameConversation = $replyMessage !== null
                && in_array((int) $replyMessage['sender_id'], [$currentUserId, $id], true)
                && in_array((int) $replyMessage['receiver_id'], [$currentUserId, $id], true);

            if (!$isSameConversation) {
                app_set_flash('error', 'You can only reply to messages from this conversation.');
                app_redirect(app_route('Message', 'index', ['id' => $id]));
            }
        }

        $messageId = $this->messageModel->sendMessage($currentUserId, $id, $content, $replyToMessageId);
        app_redirect(app_with_fragment(app_route('Message', 'index', ['id' => $id]), 'message-' . $messageId));
    }

    public function react(?int $id = null): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $id === null) {
            app_redirect(app_route('Message', 'index'));
        }

        app_verify_csrf();

        $currentUserId = (int) $_SESSION['user_id'];
        $redirectTo = $_POST['redirect_to'] ?? app_route('Message', 'index');
        $message = $this->messageModel->getMessageForUser($id, $currentUserId);

        if ($message === null) {
            app_set_flash('error', 'That message could not be found.');
            app_redirect(app_route('Message', 'index'));
        }

        if (!empty($message['unsent_at'])) {
            app_set_flash('error', 'You cannot react to a message that has been unsent.');
            app_redirect($redirectTo);
        }

        $this->messageModel->toggleReaction($id, $currentUserId);
        app_redirect($redirectTo);
    }

    public function deleteConversation(?int $id = null): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $id === null) {
            app_redirect(app_route('Message', 'index'));
        }

        app_verify_csrf();

        $currentUserId = (int) $_SESSION['user_id'];
        if ($id === $currentUserId) {
            app_set_flash('error', 'You cannot delete a conversation with yourself.');
            app_redirect(app_route('Message', 'index'));
        }

        $selectedUser = $this->userModel->getById($id);
        if ($selectedUser === null) {
            app_set_flash('error', 'That conversation could not be found.');
            app_redirect(app_route('Message', 'index'));
        }

        $this->messageModel->deleteConversationForUser($currentUserId, $id);
        app_set_flash('success', 'This conversation has been removed from your inbox.');
        app_redirect(app_route('Message', 'index'));
    }

    public function unsend(?int $id = null): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $id === null) {
            app_redirect(app_route('Message', 'index'));
        }

        app_verify_csrf();

        $currentUserId = (int) $_SESSION['user_id'];
        $redirectTo = $_POST['redirect_to'] ?? app_route('Message', 'index');
        $message = $this->messageModel->getMessageForUser($id, $currentUserId);

        if ($message === null) {
            app_set_flash('error', 'That message could not be found.');
            app_redirect(app_route('Message', 'index'));
        }

        if ((int) $message['sender_id'] !== $currentUserId) {
            app_set_flash('error', 'You can only unsend your own messages.');
            app_redirect($redirectTo);
        }

        if (!empty($message['unsent_at'])) {
            app_set_flash('error', 'That message has already been unsent.');
            app_redirect($redirectTo);
        }

        $this->messageModel->unsendMessage($id, $currentUserId);
        app_set_flash('success', 'Message unsent.');
        app_redirect($redirectTo);
    }

    public function remove(?int $id = null): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $id === null) {
            app_redirect(app_route('Message', 'index'));
        }

        app_verify_csrf();

        $currentUserId = (int) $_SESSION['user_id'];
        $redirectTo = $_POST['redirect_to'] ?? app_route('Message', 'index');
        $message = $this->messageModel->getMessageForUser($id, $currentUserId);

        if ($message === null) {
            app_set_flash('error', 'That message could not be found.');
            app_redirect(app_route('Message', 'index'));
        }

        $this->messageModel->removeMessageForUser($id, $currentUserId);
        app_set_flash('success', 'Message removed from this chat for you.');
        app_redirect($redirectTo);
    }
}
?>
