<?php
$sharedPostRouteId = (int) ($sharedPost['id'] ?? 0);
$sharedPostUserId = (int) ($sharedPost['user_id'] ?? 0);
?>
<div class="shared-post-preview">
    <div class="shared-post-label">
        <i class="fa-solid fa-share"></i>
        <span>Shared post</span>
    </div>

    <a class="shared-post-head" href="<?php echo app_route('Profile', 'view', ['id' => $sharedPostUserId]); ?>">
        <div class="avatar avatar-sm">
            <?php if (!empty($sharedPost['profile_image'])): ?>
                <img class="avatar-image" src="<?php echo app_upload_url($sharedPost['profile_image']); ?>" alt="<?php echo app_e($sharedPost['full_name'] ?? 'User'); ?>">
            <?php else: ?>
                <span class="avatar-fallback"><?php echo app_e(app_initials((string) ($sharedPost['full_name'] ?? 'Vybe User'))); ?></span>
            <?php endif; ?>
        </div>
        <div>
            <strong><?php echo app_e($sharedPost['full_name'] ?? 'Unknown User'); ?></strong>
            <div class="small-muted">
                @<?php echo app_e($sharedPost['username'] ?? 'unknown'); ?>
                <?php if (!empty($sharedPost['created_at'])): ?>
                    . <?php echo app_e(app_format_datetime($sharedPost['created_at'])); ?>
                <?php endif; ?>
            </div>
        </div>
    </a>

    <?php if (trim((string) ($sharedPost['content'] ?? '')) !== ''): ?>
        <p class="shared-post-copy"><?php echo nl2br(app_e($sharedPost['content'])); ?></p>
    <?php endif; ?>

    <?php if (!empty($sharedPost['image'])): ?>
        <div class="shared-post-media">
            <img src="<?php echo app_upload_url($sharedPost['image']); ?>" alt="Shared post image">
        </div>
    <?php endif; ?>
</div>
