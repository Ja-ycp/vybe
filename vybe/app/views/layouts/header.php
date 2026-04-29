<?php
$pageTitle = isset($title) && $title !== '' ? app_e($title) . ' | Vybe' : 'Vybe | Mini Social Network';
$successFlash = app_get_flash('success');
$errorFlash = app_get_flash('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo app_url('styles.css'); ?>">
</head>
<body class="<?php echo app_e($bodyClass ?? ''); ?>">
    <nav class="navbar navbar-expand-lg site-nav">
        <div class="container app-container">
            <a class="navbar-brand brand-lockup" href="<?php echo app_is_authenticated() ? app_route('Feed') : app_route('Auth', 'login'); ?>">
                <img src="<?php echo app_url('vybe-mark.svg'); ?>" alt="Vybe icon" class="brand-mark">
                <span class="brand-copy">
                    <span class="brand-wordmark">vybe</span>
                    <small class="brand-tagline">Connect with anyone. Share genuine memories.</small>
                </span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#appNav" aria-controls="appNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="appNav">
                <div class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <?php if (app_is_authenticated()): ?>
                        <a class="nav-link" href="<?php echo app_route('Feed'); ?>">Feed</a>
                        <a class="nav-link" href="<?php echo app_route('Message', 'index'); ?>">Messages</a>
                        <a class="nav-link" href="<?php echo app_route('Profile', 'view', ['id' => (int) $_SESSION['user_id']]); ?>">Profile</a>
                        <a class="nav-link" href="<?php echo app_route('Profile', 'edit'); ?>">Edit Profile</a>
                        <a class="btn btn-accent ms-lg-2" href="<?php echo app_route('Post', 'create'); ?>">
                            <i class="fa-solid fa-plus me-2"></i>New Post
                        </a>
                        <form method="POST" action="<?php echo app_route('Auth', 'logout'); ?>" class="ms-lg-2">
                            <input type="hidden" name="_token" value="<?php echo app_csrf_token(); ?>">
                            <button type="submit" class="btn btn-outline-dark">Logout @<?php echo app_e($_SESSION['username']); ?></button>
                        </form>
                    <?php else: ?>
                        <a class="nav-link" href="<?php echo app_route('Auth', 'login'); ?>">Login</a>
                        <a class="btn btn-accent ms-lg-2" href="<?php echo app_route('Auth', 'register'); ?>">Create Account</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <main class="app-main">
        <div class="container app-container">
            <?php if ($successFlash !== null): ?>
                <div class="alert alert-success alert-app" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i><?php echo app_e($successFlash); ?>
                </div>
            <?php endif; ?>

            <?php if ($errorFlash !== null): ?>
                <div class="alert alert-danger alert-app" role="alert">
                    <i class="fa-solid fa-circle-exclamation me-2"></i><?php echo app_e($errorFlash); ?>
                </div>
            <?php endif; ?>

