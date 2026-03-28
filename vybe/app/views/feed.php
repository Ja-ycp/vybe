<?php $redirectTo = app_current_relative_url(); ?>

<section class="hero-card surface-card">
    <div>
        <span class="eyebrow">Your shared timeline</span>
        <h1>See what everyone is posting right now.</h1>
        <p class="hero-copy">Create updates, drop photos, react to posts, and keep conversations going in one clean newsfeed.</p>
    </div>
    <div class="hero-actions">
        <a class="btn btn-accent" href="<?php echo app_route('Post', 'create'); ?>">
            <i class="fa-solid fa-pen-to-square me-2"></i>Write a Post
        </a>
        <a class="btn btn-outline-dark" href="<?php echo app_route('Profile', 'view', ['id' => (int) $_SESSION['user_id']]); ?>">
            <i class="fa-solid fa-user me-2"></i>Open My Profile
        </a>
    </div>
</section>

<div class="page-grid">
    <section class="content-stack">
        <?php if ($posts === []): ?>
            <div class="surface-card empty-state">
                <i class="fa-regular fa-message"></i>
                <h2>No posts yet</h2>
                <p>Start the conversation by sharing your first update with the class.</p>
                <a class="btn btn-accent" href="<?php echo app_route('Post', 'create'); ?>">Create the first post</a>
            </div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <?php include APP_ROOT . '/app/views/partials/post_card.php'; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <aside class="sidebar-stack">
        <section class="surface-card search-card">
            <div class="section-heading">
                <h2>Search Users</h2>
                <span>Find classmates by name or username</span>
            </div>
            <form method="GET" class="stack-form">
                <input type="hidden" name="controller" value="Feed">
                <input type="hidden" name="action" value="index">
                <div class="input-group input-group-lg">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" class="form-control" value="<?php echo app_e($search); ?>" placeholder="Search people">
                </div>
                <button type="submit" class="btn btn-dark w-100">Search</button>
            </form>

            <?php if ($search !== ''): ?>
                <div class="stack-list">
                    <?php if ($searchResults === []): ?>
                        <p class="small-muted mb-0">No users matched "<?php echo app_e($search); ?>".</p>
                    <?php else: ?>
                        <?php foreach ($searchResults as $user): ?>
                            <a class="person-row" href="<?php echo app_route('Profile', 'view', ['id' => (int) $user['id']]); ?>">
                                <div class="avatar avatar-sm">
                                    <?php if (!empty($user['profile_image'])): ?>
                                        <img class="avatar-image" src="<?php echo app_upload_url($user['profile_image']); ?>" alt="<?php echo app_e($user['full_name']); ?>">
                                    <?php else: ?>
                                        <span class="avatar-fallback"><?php echo app_e(app_initials($user['full_name'])); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <strong><?php echo app_e($user['full_name']); ?></strong>
                                    <div class="small-muted">@<?php echo app_e($user['username']); ?></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="surface-card people-card">
            <div class="section-heading">
                <h2>Discover People</h2>
                <span>Recently active community members</span>
            </div>
            <div class="stack-list">
                <?php foreach ($discoverUsers as $user): ?>
                    <a class="person-row" href="<?php echo app_route('Profile', 'view', ['id' => (int) $user['id']]); ?>">
                        <div class="avatar avatar-sm">
                            <?php if (!empty($user['profile_image'])): ?>
                                <img class="avatar-image" src="<?php echo app_upload_url($user['profile_image']); ?>" alt="<?php echo app_e($user['full_name']); ?>">
                            <?php else: ?>
                                <span class="avatar-fallback"><?php echo app_e(app_initials($user['full_name'])); ?></span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <strong><?php echo app_e($user['full_name']); ?></strong>
                            <div class="small-muted">@<?php echo app_e($user['username']); ?></div>
                            <?php if (!empty($user['bio'])): ?>
                                <div class="person-bio"><?php echo app_e($user['bio']); ?></div>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    </aside>
</div>

