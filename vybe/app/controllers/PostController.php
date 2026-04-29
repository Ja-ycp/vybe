<?php
declare(strict_types=1);

class PostController
{
    private PDO $conn;
    private PostModel $postModel;

    public function __construct(PDO $db)
    {
        $this->conn = $db;
        $this->postModel = new PostModel($db);
    }

    public function create(?int $id = null): void
    {
        $error = null;
        $post = [
            'content' => '',
            'image' => null,
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            app_verify_csrf();

            $content = trim($_POST['content'] ?? '');
            $post['content'] = $content;

            if ($content === '') {
                $error = 'Write something before posting.';
            } elseif (mb_strlen($content) > 2000) {
                $error = 'Posts must be 2000 characters or fewer.';
            } else {
                $upload = app_upload_image($_FILES['image'] ?? [], 'post');
                if ($upload['error'] !== null) {
                    $error = $upload['error'];
                } else {
                    $this->postModel->create((int) $_SESSION['user_id'], $content, $upload['filename']);
                    app_set_flash('success', 'Your post is live.');
                    app_redirect(app_route('Feed'));
                }
            }
        }

        app_render('posts/create', [
            'title' => 'Create Post',
            'post' => $post,
            'error' => $error,
            'bodyClass' => 'editor-page',
        ]);
    }

    public function share(?int $id = null): void
    {
        if ($id === null) {
            app_redirect(app_route('Feed'));
        }

        $viewerId = (int) $_SESSION['user_id'];
        $sourcePost = $this->resolveShareTarget($id, $viewerId);
        if ($sourcePost === null) {
            app_set_flash('error', 'That post could not be shared.');
            app_redirect(app_route('Feed'));
        }

        $error = null;
        $sharePost = ['content' => ''];
        $redirectTo = $_GET['redirect_to'] ?? app_route('Feed');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            app_verify_csrf();

            $content = trim($_POST['content'] ?? '');
            $redirectTo = $_POST['redirect_to'] ?? $redirectTo;
            $sharePost['content'] = $content;

            if (mb_strlen($content) > 2000) {
                $error = 'Share captions must be 2000 characters or fewer.';
            } else {
                $this->postModel->create($viewerId, $content, null, (int) $sourcePost['id']);
                app_set_flash('success', 'Post shared to your feed.');
                app_redirect($redirectTo);
            }
        }

        app_render('posts/share', [
            'title' => 'Share Post',
            'post' => $sharePost,
            'sourcePost' => $sourcePost,
            'error' => $error,
            'redirectTo' => $redirectTo,
            'bodyClass' => 'editor-page',
        ]);
    }

    public function edit(?int $id = null): void
    {
        if ($id === null) {
            app_redirect(app_route('Feed'));
        }

        $post = $this->postModel->getById($id, (int) $_SESSION['user_id']);
        if ($post === null) {
            app_set_flash('error', 'That post could not be found.');
            app_redirect(app_route('Feed'));
        }

        if ((int) $post['user_id'] !== (int) $_SESSION['user_id']) {
            app_set_flash('error', 'You can only edit your own posts.');
            app_redirect(app_route('Feed'));
        }

        $error = null;
        $redirectTo = $_GET['redirect_to'] ?? app_route('Feed');
        $isSharedPost = !empty($post['shared_post_id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            app_verify_csrf();

            $content = trim($_POST['content'] ?? '');
            $removeImage = !$isSharedPost && isset($_POST['remove_image']) && $post['image'];
            $image = $post['image'];
            $redirectTo = $_POST['redirect_to'] ?? $redirectTo;

            if (!$isSharedPost && $content === '') {
                $error = 'Post content cannot be empty.';
            } elseif (mb_strlen($content) > 2000) {
                $error = 'Posts must be 2000 characters or fewer.';
            } else {
                if ($isSharedPost) {
                    $image = null;
                    $removeImage = false;
                } else {
                    $upload = app_upload_image($_FILES['image'] ?? [], 'post');
                    if ($upload['error'] !== null) {
                        $error = $upload['error'];
                    } else {
                        if ($upload['uploaded']) {
                            app_delete_upload($image);
                            $image = $upload['filename'];
                            $removeImage = false;
                        } elseif ($removeImage) {
                            app_delete_upload($image);
                            $image = null;
                        }
                    }
                }

                if ($error === null) {
                    $this->postModel->update($id, $content, $image, $removeImage);
                    app_set_flash('success', 'Your post has been updated.');
                    app_redirect($redirectTo);
                }
            }

            $post['content'] = $content;
            $post['image'] = $image;
        }

        app_render('posts/edit', [
            'title' => 'Edit Post',
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

        $post = $this->postModel->getById($id, (int) $_SESSION['user_id']);
        if ($post === null) {
            app_set_flash('error', 'That post could not be found.');
            app_redirect(app_route('Feed'));
        }

        if ((int) $post['user_id'] !== (int) $_SESSION['user_id']) {
            app_set_flash('error', 'You can only delete your own posts.');
            app_redirect(app_route('Feed'));
        }

        $redirectTo = $_POST['redirect_to'] ?? app_route('Feed');
        app_delete_upload($post['image']);
        $this->postModel->delete($id);
        app_set_flash('success', 'Your post has been deleted.');
        app_redirect($redirectTo);
    }

    private function resolveShareTarget(int $postId, int $viewerId): ?array
    {
        $post = $this->postModel->getById($postId, $viewerId);
        if ($post === null) {
            return null;
        }

        $visited = [];
        while (!empty($post['shared_post_id'])) {
            $nextId = (int) $post['shared_post_id'];
            if (isset($visited[$nextId])) {
                break;
            }

            $visited[$nextId] = true;
            $nextPost = $this->postModel->getById($nextId, $viewerId);
            if ($nextPost === null) {
                break;
            }

            $post = $nextPost;
        }

        return $post;
    }
}

