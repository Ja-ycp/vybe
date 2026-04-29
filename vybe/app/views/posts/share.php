<?php
$sharedPost = [
    'id' => (int) $sourcePost['id'],
    'user_id' => (int) $sourcePost['user_id'],
    'full_name' => $sourcePost['full_name'],
    'username' => $sourcePost['username'],
    'profile_image' => $sourcePost['profile_image'],
    'content' => $sourcePost['content'],
    'image' => $sourcePost['image'],
    'created_at' => $sourcePost['created_at'],
];
$cancelUrl = app_normalize_redirect_target($redirectTo);
?>
<div class="editor-shell">
    <section class="surface-card editor-card">
        <div class="section-heading">
            <h2>Share Post</h2>
            <span>Add an optional caption above the original post</span>
        </div>

        <?php if ($error !== null): ?>
            <div class="alert alert-danger mb-3"><?php echo app_e($error); ?></div>
        <?php endif; ?>

        <form method="POST" class="stack-form">
            <input type="hidden" name="_token" value="<?php echo app_csrf_token(); ?>">
            <input type="hidden" name="redirect_to" value="<?php echo app_e($redirectTo); ?>">

            <div>
                <label class="form-label">Say something about this post (Optional)</label>
                <textarea name="content" class="form-control composer-textarea" rows="5" maxlength="2000" placeholder="Add your own thoughts before sharing..."><?php echo app_e($post['content'] ?? ''); ?></textarea>
            </div>

            <?php include APP_ROOT . '/app/views/partials/shared_post_embed.php'; ?>

            <div class="editor-actions">
                <button type="submit" class="btn btn-accent">Share to Feed</button>
                <button
                    type="button"
                    class="btn btn-outline-dark"
                    data-direct-navigate="<?php echo app_e($cancelUrl); ?>"
                >
                    Cancel
                </button>
            </div>
        </form>
    </section>
</div>
