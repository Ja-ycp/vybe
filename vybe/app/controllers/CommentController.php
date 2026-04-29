<?php
declare(strict_types=1);

class CommentController
{
    private PDO $conn;
    private CommentModel $commentModel;
    private CommentReactionModel $commentReactionModel;
    private PostModel $postModel;

    public function __construct(PDO $db)
    {
        $this->conn = $db;
        $this->commentModel = new CommentModel($db);
        $this->commentReactionModel = new CommentReactionModel($db);
        $this->postModel = new PostModel($db);
    }

    public function create(?int $id = null): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $id === null) {
            app_redirect(app_route('Feed'));
        }

        app_verify_csrf();

        $post = $this->postModel->getById($id, (int) $_SESSION['user_id']);
        $redirectTo = $_POST['redirect_to'] ?? app_route('Feed');

        if ($post === null) {
            app_set_flash('error', 'That post could not be found.');
            app_redirect($redirectTo);
        }

        $content = trim($_POST['content'] ?? '');
        $parentId = isset($_POST['parent_id']) && $_POST['parent_id'] !== ''
            ? (int) $_POST['parent_id']
            : null;
        $image = null;

        $upload = app_upload_image($_FILES['image'] ?? [], 'comment');
        if ($upload['error'] !== null) {
            app_set_flash('error', $upload['error']);
            app_redirect($redirectTo);
        }

        if ($upload['uploaded']) {
            $image = $upload['filename'];
        }

        if ($content === '' && $image === null) {
            app_set_flash('error', 'Comments cannot be empty unless you attach an image or GIF.');
            app_redirect($redirectTo);
        }

        if (mb_strlen($content) > 500) {
            app_set_flash('error', 'Comments must be 500 characters or fewer.');
            app_redirect($redirectTo);
        }

        if ($parentId !== null) {
            $replyTarget = $this->commentModel->getReplyTarget($parentId);
            if ($replyTarget === null || (int) $replyTarget['post_id'] !== $id) {
                app_set_flash('error', 'That comment can no longer be replied to.');
                app_redirect($redirectTo);
            }
        }

        $this->commentModel->create($id, (int) $_SESSION['user_id'], $content, $parentId, $image);
        app_set_flash('success', $parentId === null ? 'Comment added.' : 'Reply added.');
        app_redirect($redirectTo);
    }

    public function edit(?int $id = null): void
    {
        if ($id === null) {
            app_redirect(app_route('Feed'));
        }

        $comment = $this->commentModel->getById($id);
        if ($comment === null) {
            app_set_flash('error', 'That comment could not be found.');
            app_redirect(app_route('Feed'));
        }

        if ((int) $comment['user_id'] !== (int) $_SESSION['user_id']) {
            app_set_flash('error', 'You can only edit your own comments.');
            app_redirect(app_route('Feed'));
        }

        $error = null;
        $redirectTo = $_GET['redirect_to'] ?? app_route('Feed');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            app_verify_csrf();

            $content = trim($_POST['content'] ?? '');
            $redirectTo = $_POST['redirect_to'] ?? $redirectTo;

            if ($content === '' && empty($comment['image'])) {
                $error = 'Comment content cannot be empty unless the comment already has an image or GIF.';
            } elseif (mb_strlen($content) > 500) {
                $error = 'Comments must be 500 characters or fewer.';
            } else {
                $this->commentModel->update($id, $content, (int) $_SESSION['user_id']);
                app_set_flash('success', 'Your comment has been updated.');
                app_redirect($redirectTo);
            }

            $comment['content'] = $content;
        }

        $post = $this->postModel->getById((int) $comment['post_id'], (int) $_SESSION['user_id']);

        app_render('comments/edit', [
            'title' => 'Edit Comment',
            'comment' => $comment,
            'post' => $post,
            'error' => $error,
            'redirectTo' => $redirectTo,
            'bodyClass' => 'editor-page',
        ]);
    }

    public function delete(?int $id = null): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $id === null) {
            app_redirect(app_route('Feed'));
        }

        app_verify_csrf();

        $comment = $this->commentModel->getById($id);
        $redirectTo = $_POST['redirect_to'] ?? app_route('Feed');

        if ($comment === null) {
            app_set_flash('error', 'That comment could not be found.');
            app_redirect($redirectTo);
        }

        if ((int) $comment['user_id'] !== (int) $_SESSION['user_id']) {
            app_set_flash('error', 'You can only delete your own comments.');
            app_redirect($redirectTo);
        }

        $this->commentModel->delete($id, (int) $_SESSION['user_id']);
        if (!empty($comment['image'])) {
            app_delete_upload($comment['image']);
        }
        app_set_flash('success', 'Your comment has been deleted.');
        app_redirect($redirectTo);
    }

    public function react(?int $id = null): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $id === null) {
            app_redirect(app_route('Feed'));
        }

        app_verify_csrf();

        $redirectTo = $_POST['redirect_to'] ?? app_route('Feed');
        $comment = $this->commentModel->getReplyTarget($id);

        if ($comment === null) {
            app_set_flash('error', 'That comment could not be found.');
            app_redirect($redirectTo);
        }

        $this->commentReactionModel->toggle($id, (int) $_SESSION['user_id']);
        app_redirect($redirectTo);
    }
}
?>

