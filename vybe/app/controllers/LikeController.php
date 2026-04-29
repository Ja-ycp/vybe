<?php
declare(strict_types=1);

class LikeController
{
    private PDO $conn;
    private LikeModel $likeModel;
    private PostModel $postModel;

    public function __construct(PDO $db)
    {
        $this->conn = $db;
        $this->likeModel = new LikeModel($db);
        $this->postModel = new PostModel($db);
    }

    public function toggle(?int $id = null): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $id === null) {
            app_redirect(app_route('Feed'));
        }

        app_verify_csrf();

        $redirectTo = $_POST['redirect_to'] ?? app_route('Feed');
        $post = $this->postModel->getById($id, (int) $_SESSION['user_id']);

        if ($post === null) {
            app_set_flash('error', 'That post could not be found.');
            app_redirect($redirectTo);
        }

        $this->likeModel->toggle($id, (int) $_SESSION['user_id']);
        app_redirect($redirectTo);
    }
}
?>

