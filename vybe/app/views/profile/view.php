<?php $redirectTo = app_current_relative_url(); ?>

<section class="profile-hero surface-card">
    <div class="profile-header">
        <div class="avatar avatar-xl">
            <?php if (!empty($user['profile_image'])): ?>
                <img class="avatar-image" src="<?php echo app_upload_url($user['profile_image']); ?>" alt="<?php echo app_e($user['full_name']); ?>">
            <?php else: ?>
                <span class="avatar-fallback"><?php echo app_e(app_initials($user['full_name'])); ?></span>
            <?php endif; ?>
        </div>

        <div class="profile-copy">
            <span class="eyebrow">User profile</span>
            <h1><?php echo app_e($user['full_name']); ?></h1>
            <p class="handle">@<?php echo app_e($user['username']); ?></p>
            <p class="profile-bio"><?php echo $user['bio'] !== '' ? nl2br(app_e($user['bio'])) : 'This user has not added a bio yet.'; ?></p>
            <div class="info-chip-list">
                <span class="info-chip"><i class="fa-solid fa-file-lines"></i><?php echo count($posts); ?> posts</span>
                <span class="info-chip"><i class="fa-regular fa-calendar"></i>Joined <?php echo date('M Y', strtotime($user['created_at'])); ?></span>
            </div>
        </div>
    </div>

    <div class="hero-actions">
        <?php if ($isOwnProfile): ?>
            <a class="btn btn-accent" href="<?php echo app_route('Profile', 'edit'); ?>">
                <i class="fa-solid fa-pen me-2"></i>Edit Profile
            </a>
            <a class="btn btn-outline-dark" href="<?php echo app_route('Post', 'create'); ?>">
                <i class="fa-solid fa-plus me-2"></i>New Post
            </a>
        <?php else: ?>
            <a class="btn btn-accent" href="<?php echo app_route('Message', 'index', ['id' => (int) $user['id']]); ?>">
                <i class="fa-solid fa-paper-plane me-2"></i>Message
            </a>
            <a class="btn btn-outline-dark" href="<?php echo app_route('Feed'); ?>">
                <i class="fa-solid fa-arrow-left me-2"></i>Back to Feed
            </a>
        <?php endif; ?>
    </div>
</section>

<section class="content-stack profile-stream">
    <div class="section-heading mb-3">
        <h2><?php echo $isOwnProfile ? 'My Posts' : app_e($user['full_name']) . '\'s Posts'; ?></h2>
        <span>Latest posts appear first</span>
    </div>

    <?php if ($posts === []): ?>
        <div class="surface-card empty-state">
            <i class="fa-regular fa-note-sticky"></i>
            <h2>No posts yet</h2>
            <p><?php echo $isOwnProfile ? 'You have not posted anything yet.' : 'This user has not shared anything yet.'; ?></p>
            <?php if ($isOwnProfile): ?>
                <a class="btn btn-accent" href="<?php echo app_route('Post', 'create'); ?>">Create your first post</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <?php include APP_ROOT . '/app/views/partials/post_card.php'; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

