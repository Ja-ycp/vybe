<?php
declare(strict_types=1);

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

if (!defined('PUBLIC_ROOT')) {
    define('PUBLIC_ROOT', APP_ROOT . '/public');
}

function app_base_url(): string
{
    static $baseUrl = null;

    if ($baseUrl !== null) {
        return $baseUrl;
    }

    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    if ($scriptDir === '/' || $scriptDir === '.') {
        $scriptDir = '';
    }

    $baseUrl = rtrim($scriptDir, '/');
    return $baseUrl;
}

function app_url(string $path = ''): string
{
    $baseUrl = app_base_url();
    $path = ltrim($path, '/');

    if ($path === '') {
        return $baseUrl === '' ? '/' : $baseUrl;
    }

    return ($baseUrl === '' ? '' : $baseUrl) . '/' . $path;
}

function app_absolute_url(string $path = ''): string
{
    if (preg_match('#^https?://#i', $path) === 1) {
        return $path;
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443');

    $scheme = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    return $scheme . '://' . $host . app_url($path);
}

function app_route(string $controller = 'Feed', string $action = 'index', array $params = []): string
{
    $query = ['controller' => $controller];

    if ($action !== '') {
        $query['action'] = $action;
    }

    $query = array_merge($query, $params);
    return app_url('index.php') . '?' . http_build_query($query);
}

function app_current_relative_url(): string
{
    $query = $_SERVER['QUERY_STRING'] ?? '';
    return 'index.php' . ($query !== '' ? '?' . $query : '');
}

function app_with_fragment(string $url, string $fragment): string
{
    $url = trim($url);
    $fragment = ltrim(trim($fragment), '#');

    if ($fragment === '') {
        return $url;
    }

    $url = preg_replace('/#.*$/', '', $url) ?? $url;
    return $url . '#' . $fragment;
}

function app_normalize_redirect_target(string $url): string
{
    $url = trim($url);

    if ($url === '') {
        return app_route('Feed');
    }

    if (str_starts_with($url, '?')) {
        return app_url('index.php') . $url;
    }

    if (preg_match('#^index\.php(\?[^#]*)?(#.*)?$#', $url) === 1) {
        return app_url($url);
    }

    $baseUrl = app_base_url();
    if ($baseUrl !== '' && str_starts_with($url, $baseUrl . '/')) {
        return $url;
    }

    return app_route('Feed');
}

function app_redirect(string $url): void
{
    header('Location: ' . app_normalize_redirect_target($url));
    exit;
}

function app_is_authenticated(): bool
{
    return isset($_SESSION['user_id']);
}

function app_require_auth(): void
{
    if (!app_is_authenticated()) {
        app_set_flash('error', 'Please log in to continue.');
        app_redirect(app_route('Auth', 'login'));
    }
}

function app_render(string $view, array $data = []): void
{
    $viewFile = APP_ROOT . '/app/views/' . $view . '.php';

    if (!file_exists($viewFile)) {
        http_response_code(500);
        echo 'View not found.';
        return;
    }

    extract($data, EXTR_SKIP);

    require APP_ROOT . '/app/views/layouts/header.php';
    require $viewFile;
    require APP_ROOT . '/app/views/layouts/footer.php';
}

function app_set_flash(string $key, string $message): void
{
    $_SESSION['flash'][$key] = $message;
}

function app_get_flash(string $key): ?string
{
    if (!isset($_SESSION['flash'][$key])) {
        return null;
    }

    $message = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);

    return $message;
}

function app_store_old(array $input): void
{
    $_SESSION['old'] = $input;
}

function app_old(string $key, string $default = ''): string
{
    return isset($_SESSION['old'][$key]) ? (string) $_SESSION['old'][$key] : $default;
}

function app_clear_old(): void
{
    unset($_SESSION['old']);
}

function app_e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function app_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function app_verify_csrf(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }

    $token = $_POST['_token'] ?? '';

    if ($token === '' || !hash_equals(app_csrf_token(), $token)) {
        http_response_code(419);
        exit('Invalid request token.');
    }
}

function app_upload_image(array $file, string $prefix = 'upload'): array
{
    $emptyUpload = [
        'uploaded' => false,
        'filename' => null,
        'error' => null,
    ];

    $errorCode = $file['error'] ?? UPLOAD_ERR_NO_FILE;
    if ($errorCode === UPLOAD_ERR_NO_FILE) {
        return $emptyUpload;
    }

    if ($errorCode !== UPLOAD_ERR_OK) {
        return [
            'uploaded' => false,
            'filename' => null,
            'error' => 'Image upload failed. Please try again.',
        ];
    }

    if (($file['size'] ?? 0) > 40 * 1024 * 1024) {
        return [
            'uploaded' => false,
            'filename' => null,
            'error' => 'Images must be 40MB or smaller.',
        ];
    }

    $tmpName = $file['tmp_name'] ?? '';
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        return [
            'uploaded' => false,
            'filename' => null,
            'error' => 'The uploaded image could not be verified.',
        ];
    }

    $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($tmpName) ?: '';

    if (!isset($allowedMimeTypes[$mimeType])) {
        return [
            'uploaded' => false,
            'filename' => null,
            'error' => 'Only JPG, PNG, GIF, and WEBP images are allowed.',
        ];
    }

    $uploadDir = PUBLIC_ROOT . '/uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $extension = $allowedMimeTypes[$mimeType];
    $filename = sprintf(
        '%s-%s.%s',
        preg_replace('/[^a-z0-9_-]/i', '', $prefix) ?: 'upload',
        bin2hex(random_bytes(10)),
        $extension
    );

    $destination = $uploadDir . '/' . $filename;
    if (!move_uploaded_file($tmpName, $destination)) {
        return [
            'uploaded' => false,
            'filename' => null,
            'error' => 'Unable to save the uploaded image.',
        ];
    }

    return [
        'uploaded' => true,
        'filename' => $filename,
        'error' => null,
    ];
}

function app_delete_upload(?string $filename): void
{
    if ($filename === null || $filename === '') {
        return;
    }

    $safeName = basename($filename);
    if ($safeName === '' || $safeName === '.gitkeep') {
        return;
    }

    $filePath = PUBLIC_ROOT . '/uploads/' . $safeName;
    if (is_file($filePath)) {
        unlink($filePath);
    }
}

function app_upload_url(?string $filename): ?string
{
    if ($filename === null || $filename === '') {
        return null;
    }

    return app_url('uploads/' . rawurlencode($filename));
}

function app_initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $initials = '';

    foreach ($parts as $part) {
        if ($part === '') {
            continue;
        }

        $initials .= strtoupper(substr($part, 0, 1));
        if (strlen($initials) >= 2) {
            break;
        }
    }

    return $initials !== '' ? $initials : 'V';
}

function app_format_datetime(string $timestamp): string
{
    return date('M j, Y g:i A', strtotime($timestamp));
}
?>
