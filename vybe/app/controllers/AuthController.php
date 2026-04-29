<?php
declare(strict_types=1);

class AuthController
{
    private const GOOGLE_AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const GOOGLE_TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const GOOGLE_USERINFO_URL = 'https://openidconnect.googleapis.com/v1/userinfo';

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

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            app_verify_csrf();

            $username = strtolower(trim((string) ($_POST['username'] ?? '')));
            $password = (string) ($_POST['password'] ?? '');
            app_store_old(['username' => $username]);

            if ($username === '' || $password === '') {
                $error = 'Enter both your username and password.';
            } else {
                $user = $this->userModel->findByUsername($username);
                if ($user !== null && $this->userModel->verify($user, $password)) {
                    $this->completeLogin($user, 'Welcome back, ' . $user['full_name'] . '.');
                }

                if (
                    $user !== null
                    && !empty($user['google_id'])
                    && strtolower((string) ($user['auth_provider'] ?? '')) === 'google'
                ) {
                    $error = 'This account uses Google sign-in. Tap "Continue with Google".';
                } else {
                    $error = 'Invalid credentials. Please try again.';
                }
            }
        }

        app_render('auth/login', [
            'title' => 'Login',
            'error' => $error,
            'bodyClass' => 'auth-page login-page',
            'googleAuthEnabled' => $this->isGoogleAuthEnabled(),
            'googleAuthUrl' => app_route('Auth', 'google'),
        ]);
    }

    public function register(?int $id = null): void
    {
        if (app_is_authenticated()) {
            app_redirect(app_route('Feed'));
        }

        $error = null;

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            app_verify_csrf();

            $username = strtolower(trim((string) ($_POST['username'] ?? '')));
            $fullName = trim((string) ($_POST['full_name'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            $bio = trim((string) ($_POST['bio'] ?? ''));

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

                    $newUser = $this->userModel->getAuthById($userId);
                    if ($newUser !== null) {
                        $this->completeLogin($newUser, 'Your account is ready. Start sharing your first post.');
                    }

                    $error = 'Your account was created, but we could not start a session. Please log in.';
                }
            }
        }

        app_render('auth/register', [
            'title' => 'Register',
            'error' => $error,
            'bodyClass' => 'auth-page login-page register-page',
            'googleAuthEnabled' => $this->isGoogleAuthEnabled(),
            'googleAuthUrl' => app_route('Auth', 'google'),
        ]);
    }

    public function google(?int $id = null): void
    {
        if (app_is_authenticated()) {
            app_redirect(app_route('Feed'));
        }

        if (!$this->isGoogleAuthEnabled()) {
            app_set_flash('error', 'Google sign-in is not configured yet.');
            app_redirect(app_route('Auth', 'login'));
        }

        $state = bin2hex(random_bytes(24));
        $_SESSION['google_oauth_state'] = $state;
        $_SESSION['google_oauth_started_at'] = time();

        $query = http_build_query([
            'client_id' => $this->getGoogleClientId(),
            'redirect_uri' => $this->getGoogleRedirectUri(),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account',
            'include_granted_scopes' => 'true',
        ]);

        app_redirect(self::GOOGLE_AUTH_URL . '?' . $query, true);
    }

    public function googleCallback(?int $id = null): void
    {
        if (app_is_authenticated()) {
            app_redirect(app_route('Feed'));
        }

        if (!$this->isGoogleAuthEnabled()) {
            app_set_flash('error', 'Google sign-in is not configured yet.');
            app_redirect(app_route('Auth', 'login'));
        }

        $state = (string) ($_GET['state'] ?? '');
        $expectedState = (string) ($_SESSION['google_oauth_state'] ?? '');
        $startedAt = (int) ($_SESSION['google_oauth_started_at'] ?? 0);
        unset($_SESSION['google_oauth_state'], $_SESSION['google_oauth_started_at']);

        if ($state === '' || $expectedState === '' || !hash_equals($expectedState, $state)) {
            app_set_flash('error', 'Google sign-in could not be verified. Please try again.');
            app_redirect(app_route('Auth', 'login'));
        }

        if ($startedAt > 0 && (time() - $startedAt) > 900) {
            app_set_flash('error', 'Google sign-in session expired. Please try again.');
            app_redirect(app_route('Auth', 'login'));
        }

        if (isset($_GET['error'])) {
            $oauthError = trim((string) ($_GET['error'] ?? ''));
            $description = trim((string) ($_GET['error_description'] ?? ''));
            $message = $description !== '' ? $description : $oauthError;
            if ($message === '') {
                $message = 'Google sign-in was cancelled.';
            }

            app_set_flash('error', 'Google sign-in failed: ' . $message);
            app_redirect(app_route('Auth', 'login'));
        }

        $code = trim((string) ($_GET['code'] ?? ''));
        if ($code === '') {
            app_set_flash('error', 'Google sign-in returned no authorization code.');
            app_redirect(app_route('Auth', 'login'));
        }

        $tokenResponse = $this->postFormJson(self::GOOGLE_TOKEN_URL, [
            'code' => $code,
            'client_id' => $this->getGoogleClientId(),
            'client_secret' => $this->getGoogleClientSecret(),
            'redirect_uri' => $this->getGoogleRedirectUri(),
            'grant_type' => 'authorization_code',
        ]);

        if ($tokenResponse['status'] < 200 || $tokenResponse['status'] >= 300 || !isset($tokenResponse['body']['access_token'])) {
            error_log('Google token exchange failed status=' . $tokenResponse['status'] . ' body=' . $tokenResponse['raw']);
            app_set_flash('error', 'Could not complete Google sign-in. Please try again.');
            app_redirect(app_route('Auth', 'login'));
        }

        $accessToken = trim((string) $tokenResponse['body']['access_token']);
        if ($accessToken === '') {
            app_set_flash('error', 'Google sign-in returned an empty access token.');
            app_redirect(app_route('Auth', 'login'));
        }

        $userInfoResponse = $this->getJson(self::GOOGLE_USERINFO_URL, [
            'Authorization: Bearer ' . $accessToken,
        ]);

        if ($userInfoResponse['status'] < 200 || $userInfoResponse['status'] >= 300 || !is_array($userInfoResponse['body'])) {
            error_log('Google userinfo request failed status=' . $userInfoResponse['status'] . ' body=' . $userInfoResponse['raw']);
            app_set_flash('error', 'Could not fetch your Google profile. Please try again.');
            app_redirect(app_route('Auth', 'login'));
        }

        $googleProfile = $userInfoResponse['body'];
        $googleId = trim((string) ($googleProfile['sub'] ?? ''));
        $email = strtolower(trim((string) ($googleProfile['email'] ?? '')));
        $emailVerified = (bool) ($googleProfile['email_verified'] ?? false);

        if ($googleId === '' || $email === '' || !$emailVerified) {
            app_set_flash('error', 'Google did not return a verified email address for this account.');
            app_redirect(app_route('Auth', 'login'));
        }

        $fullName = trim((string) ($googleProfile['name'] ?? ''));
        if ($fullName === '') {
            $fullName = strstr($email, '@', true) ?: 'Google User';
        }

        $profileImage = null;

        try {
            $user = $this->userModel->findByGoogleId($googleId);
            if ($user === null) {
                $existingEmailUser = $this->userModel->findByEmail($email);
                if ($existingEmailUser !== null) {
                    $this->userModel->linkGoogleAccount((int) $existingEmailUser['id'], $googleId, $email, $profileImage);
                    $user = $this->userModel->getAuthById((int) $existingEmailUser['id']);
                }
            }

            if ($user === null) {
                $newUserId = $this->userModel->createFromGoogle($googleId, $email, $fullName, $profileImage);
                $user = $this->userModel->getAuthById($newUserId);
            }
        } catch (Throwable $exception) {
            error_log('Google sign-in user sync failed: ' . $exception->getMessage());
            app_set_flash('error', 'Google sign-in could not sync your account. Please try again.');
            app_redirect(app_route('Auth', 'login'));
        }

        if ($user === null) {
            app_set_flash('error', 'Google sign-in could not create your account. Please try again.');
            app_redirect(app_route('Auth', 'login'));
        }

        $welcomeName = trim((string) ($user['full_name'] ?? ''));
        if ($welcomeName === '') {
            $welcomeName = 'there';
        }

        $this->completeLogin($user, 'Welcome to Vybe, ' . $welcomeName . '.');
    }

    public function logout(?int $id = null): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
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

    private function completeLogin(array $user, string $flashMessage): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['username'] = (string) ($user['username'] ?? '');
        $_SESSION['full_name'] = (string) ($user['full_name'] ?? '');
        $_SESSION['profile_image'] = $user['profile_image'] ?? null;
        app_clear_old();
        app_set_flash('success', $flashMessage);
        app_redirect(app_route('Feed'));
    }

    private function isGoogleAuthEnabled(): bool
    {
        return $this->getGoogleClientId() !== '' && $this->getGoogleClientSecret() !== '';
    }

    private function getGoogleClientId(): string
    {
        return trim((string) (getenv('GOOGLE_CLIENT_ID') ?: ''));
    }

    private function getGoogleClientSecret(): string
    {
        return trim((string) (getenv('GOOGLE_CLIENT_SECRET') ?: ''));
    }

    private function getGoogleRedirectUri(): string
    {
        $configured = trim((string) (getenv('GOOGLE_REDIRECT_URI') ?: ''));
        if ($configured !== '') {
            return $configured;
        }

        return app_absolute_url(app_route('Auth', 'googleCallback'));
    }

    private function postFormJson(string $url, array $data): array
    {
        $requestBody = http_build_query($data, '', '&', PHP_QUERY_RFC3986);

        return $this->requestJson(
            $url,
            'POST',
            [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json',
            ],
            $requestBody
        );
    }

    private function getJson(string $url, array $headers = []): array
    {
        $headers[] = 'Accept: application/json';
        return $this->requestJson($url, 'GET', $headers, null);
    }

    private function requestJson(string $url, string $method, array $headers, ?string $body): array
    {
        $contextOptions = [
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $headers),
                'ignore_errors' => true,
                'timeout' => 20,
            ],
        ];

        if ($body !== null) {
            $contextOptions['http']['content'] = $body;
        }

        $context = stream_context_create($contextOptions);
        $raw = @file_get_contents($url, false, $context);
        $responseHeaders = $http_response_header ?? [];
        $status = $this->extractStatusCode($responseHeaders);

        if (($raw === false || $status === 0) && function_exists('curl_init')) {
            $curl = curl_init($url);
            if ($curl !== false) {
                $curlHeaders = $headers;
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $curlHeaders);
                curl_setopt($curl, CURLOPT_TIMEOUT, 20);

                if ($body !== null) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
                }

                $curlRaw = curl_exec($curl);
                if (is_string($curlRaw)) {
                    $raw = $curlRaw;
                }

                $curlStatus = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
                if (is_int($curlStatus) && $curlStatus > 0) {
                    $status = $curlStatus;
                }

                curl_close($curl);
            }
        }

        $decoded = null;
        if ($raw !== false) {
            $parsed = json_decode($raw, true);
            if (is_array($parsed)) {
                $decoded = $parsed;
            }
        }

        return [
            'status' => $status,
            'body' => $decoded,
            'raw' => $raw === false ? '' : $raw,
        ];
    }

    private function extractStatusCode(array $responseHeaders): int
    {
        if ($responseHeaders === []) {
            return 0;
        }

        $statusLine = (string) $responseHeaders[0];
        if (preg_match('/\s(\d{3})\s?/', $statusLine, $matches) === 1) {
            return (int) $matches[1];
        }

        return 0;
    }
}
