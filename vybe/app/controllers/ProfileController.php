<?php
declare(strict_types=1);

class ProfileController
{
    private PDO $conn;
    private UserModel $userModel;
    private PostModel $postModel;
    private CommentModel $commentModel;

    public function __construct(PDO $db)
    {
        $this->conn = $db;
        $this->userModel = new UserModel($db);
        $this->postModel = new PostModel($db);
        $this->commentModel = new CommentModel($db);
    }

    public function view(?int $id = null): void
    {
        $profileId = $id ?? (int) $_SESSION['user_id'];
        $user = $this->userModel->getById($profileId);

        if ($user === null) {
            app_set_flash('error', 'That profile could not be found.');
            app_redirect(app_route('Feed'));
        }

        $posts = $this->attachComments($this->postModel->getByUser($profileId, (int) $_SESSION['user_id']));

        app_render('profile/view', [
            'title' => $user['full_name'] . '\'s Profile',
            'user' => $user,
            'posts' => $posts,
            'isOwnProfile' => $profileId === (int) $_SESSION['user_id'],
        ]);
    }

    public function edit(?int $id = null): void
    {
        $user = $this->userModel->getById((int) $_SESSION['user_id']);
        if ($user === null) {
            app_set_flash('error', 'Your profile could not be loaded.');
            app_redirect(app_route('Feed'));
        }

        $profileError = null;
        $passwordError = null;
        $deleteError = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            app_verify_csrf();
            $formAction = $_POST['form_action'] ?? 'profile';

            if ($formAction === 'password') {
                $authUser = $this->userModel->getAuthById((int) $_SESSION['user_id']);
                $currentPassword = (string) ($_POST['current_password'] ?? '');
                $newPassword = (string) ($_POST['new_password'] ?? '');
                $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

                if ($authUser === null) {
                    $passwordError = 'Your account could not be verified right now.';
                } elseif ($currentPassword === '') {
                    $passwordError = 'Enter your current password to continue.';
                } elseif ($newPassword === '') {
                    $passwordError = 'Enter a new password.';
                } elseif (strlen($newPassword) < 8) {
                    $passwordError = 'Your new password must be at least 8 characters long.';
                } elseif (strlen($newPassword) > 72) {
                    $passwordError = 'Your new password must be 72 characters or fewer.';
                } elseif (!preg_match('/[A-Za-z]/', $newPassword) || !preg_match('/\d/', $newPassword)) {
                    $passwordError = 'Use a stronger password with at least one letter and one number.';
                } elseif ($confirmPassword === '') {
                    $passwordError = 'Confirm your new password.';
                } elseif ($newPassword !== $confirmPassword) {
                    $passwordError = 'Your new password and confirmation do not match.';
                } elseif (!$this->userModel->verify($authUser, $currentPassword)) {
                    $passwordError = 'Your current password is incorrect.';
                } elseif (hash_equals($currentPassword, $newPassword)) {
                    $passwordError = 'Choose a new password that is different from your current one.';
                } else {
                    $this->userModel->updatePassword((int) $_SESSION['user_id'], $newPassword);
                    app_set_flash('success', 'Your password has been changed.');
                    app_redirect(app_with_fragment(app_route('Profile', 'edit'), 'security-card'));
                }
            } elseif ($formAction === 'delete_account') {
                $authUser = $this->userModel->getAuthById((int) $_SESSION['user_id']);
                $deletePassword = (string) ($_POST['delete_password'] ?? '');
                $deleteConfirmation = trim((string) ($_POST['delete_confirmation'] ?? ''));

                if ($authUser === null) {
                    $deleteError = 'Your account could not be verified right now.';
                } elseif ($deletePassword === '') {
                    $deleteError = 'Enter your current password before deleting your account.';
                } elseif (!$this->userModel->verify($authUser, $deletePassword)) {
                    $deleteError = 'Your current password is incorrect.';
                } elseif ($deleteConfirmation !== 'CONFIRM DELETION') {
                    $deleteError = 'Type CONFIRM DELETION exactly to confirm this permanent action.';
                } else {
                    $uploads = $this->collectUploadsForDeletion((int) $_SESSION['user_id'], $authUser);

                    try {
                        $this->conn->beginTransaction();
                        $deleted = $this->userModel->delete((int) $_SESSION['user_id']);

                        if (!$deleted) {
                            throw new RuntimeException('User deletion failed.');
                        }

                        $this->conn->commit();
                    } catch (Throwable $exception) {
                        if ($this->conn->inTransaction()) {
                            $this->conn->rollBack();
                        }

                        $deleteError = 'We could not delete your account right now. Please try again.';
                    }

                    if ($deleteError === null) {
                        foreach ($uploads as $filename) {
                            app_delete_upload($filename);
                        }

                        $this->resetSession();
                        app_set_flash('success', 'Your account has been permanently deleted.');
                        app_redirect(app_route('Auth', 'login'));
                    }
                }
            } else {
                $fullName = trim($_POST['full_name'] ?? '');
                $bio = trim($_POST['bio'] ?? '');
                $removeImage = isset($_POST['remove_image']) && $user['profile_image'];
                $profileImage = $user['profile_image'];

                if (mb_strlen($fullName) < 2) {
                    $profileError = 'Please enter your full name.';
                } elseif (mb_strlen($bio) > 255) {
                    $profileError = 'Your bio must be 255 characters or fewer.';
                } else {
                    $upload = app_upload_image($_FILES['profile_image'] ?? [], 'profile');
                    if ($upload['error'] !== null) {
                        $profileError = $upload['error'];
                    } else {
                        if ($upload['uploaded']) {
                            app_delete_upload($profileImage);
                            $profileImage = $upload['filename'];
                            $removeImage = false;
                        } elseif ($removeImage) {
                            app_delete_upload($profileImage);
                            $profileImage = null;
                        }

                        $this->userModel->update(
                            (int) $_SESSION['user_id'],
                            $fullName,
                            $bio,
                            $profileImage,
                            $removeImage
                        );

                        $_SESSION['full_name'] = $fullName;
                        $_SESSION['profile_image'] = $profileImage;
                        app_set_flash('success', 'Your profile has been updated.');
                        app_redirect(app_route('Profile', 'view', ['id' => (int) $_SESSION['user_id']]));
                    }
                }

                $user['full_name'] = $fullName;
                $user['bio'] = $bio;
            }
        }

        app_render('profile/edit', [
            'title' => 'Edit Profile',
            'user' => $user,
            'profileError' => $profileError,
            'passwordError' => $passwordError,
            'deleteError' => $deleteError,
            'bodyClass' => 'editor-page',
        ]);
    }

    private function collectUploadsForDeletion(int $userId, array $authUser): array
    {
        $uploads = array_merge(
            [$authUser['profile_image'] ?? null],
            $this->postModel->getImageUploadsByUser($userId),
            $this->commentModel->getImageUploadsByUser($userId)
        );

        return array_values(array_unique(array_filter(
            array_map(static fn($value): string => (string) $value, $uploads),
            static fn(string $value): bool => $value !== ''
        )));
    }

    private function resetSession(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
        session_start();
        session_regenerate_id(true);
    }

    private function attachComments(array $posts): array
    {
        $postIds = array_map(static fn(array $post): int => (int) $post['id'], $posts);
        $commentsByPost = $this->commentModel->getByPostIds($postIds, (int) $_SESSION['user_id']);

        foreach ($posts as &$post) {
            $post['comments'] = $commentsByPost[(int) $post['id']] ?? [];
        }
        unset($post);

        return $posts;
    }
}
?>

