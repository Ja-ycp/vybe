<?php
declare(strict_types=1);

class AuthController
{
    private PDO $conn;
    private UserModel $userModel;

    public function __construct(PDO $db)
    {
        $this->conn = $db;
        $this->userModel = new UserModel($db);
    }

    public function login(?int $id = null): void
    {
        if (app_is_authenticated()) {
            app_redirect(app_route('Feed'));
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            app_verify_csrf();

            $username = strtolower(trim($_POST['username'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            app_store_old(['username' => $username]);

            if ($username === '' || $password === '') {
                $error = 'Enter both your username and password.';
            } else {
                $user = $this->userModel->findByUsername($username);
                if ($user !== null && $this->userModel->verify($user, $password)) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = (int) $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['profile_image'] = $user['profile_image'] ?? null;
                    app_clear_old();
                    app_set_flash('success', 'Welcome back, ' . $user['full_name'] . '.');
                    app_redirect(app_route('Feed'));
                }

                $error = 'Invalid credentials. Please try again.';
            }
        }

        app_render('auth/login', [
            'title' => 'Login',
            'error' => $error,
            'bodyClass' => 'auth-page login-page',
        ]);
    }

    public function register(?int $id = null): void
    {
        if (app_is_authenticated()) {
            app_redirect(app_route('Feed'));
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            app_verify_csrf();

            $username = strtolower(trim($_POST['username'] ?? ''));
            $fullName = trim($_POST['full_name'] ?? '');
            $password = (string) ($_POST['password'] ?? '');
            $bio = trim($_POST['bio'] ?? '');

            app_store_old([
                'username' => $username,
                'full_name' => $fullName,
                'bio' => $bio,
            ]);

            if (!preg_match('/^[a-z0-9_]{3,20}$/', $username)) {
                $error = 'Usernames must be 3 to 20 characters and use only letters, numbers, or underscores.';
            } elseif (mb_strlen($fullName) < 2) {
                $error = 'Please enter your full name.';
            } elseif (mb_strlen($password) < 8 || mb_strlen($password) > 72 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\\d/', $password)) {
                $error = 'Passwords need 8-72 characters with at least one letter and one number.';
            } elseif (mb_strlen($bio) > 255) {
                $error = 'Your bio must be 255 characters or fewer.';
            } elseif ($this->userModel->findByUsername($username) !== null) {
                $error = 'That username is already taken.';
            } else {
                $upload = app_upload_image($_FILES['profile_image'] ?? [], 'profile');
                if ($upload['error'] !== null) {
                    $error = $upload['error'];
                } else {
                    $userId = $this->userModel->create(
                        $username,
                        $password,
                        $fullName,
                        $bio,
                        $upload['filename']
                    );

                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['username'] = $username;
                    $_SESSION['full_name'] = $fullName;
                    $_SESSION['profile_image'] = $upload['filename'];
                    app_clear_old();
                    app_set_flash('success', 'Your account is ready. Start sharing your first post.');
                    app_redirect(app_route('Feed'));
                }
            }
        }

        app_render('auth/register', [
            'title' => 'Register',
            'error' => $error,
            'bodyClass' => 'auth-page login-page register-page',
        ]);
    }

    public function logout(?int $id = null): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            app_redirect(app_route('Feed'));
        }

        app_verify_csrf();

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
        session_start();
        session_regenerate_id(true);

        app_set_flash('success', 'You have been logged out.');
        app_redirect(app_route('Auth', 'login'));
    }
}

