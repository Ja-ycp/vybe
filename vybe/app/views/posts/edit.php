<div class="editor-shell">
    <section class="surface-card editor-card">
        <div class="section-heading">
            <h2><?php echo !empty($post['shared_post_id']) ? 'Edit Shared Post' : 'Edit Post'; ?></h2>
            <span><?php echo !empty($post['shared_post_id']) ? 'Update the caption above the post you shared' : 'Update your caption or replace the image'; ?></span>
        </div>

        <?php if ($error !== null): ?>
            <div class="alert alert-danger mb-3"><?php echo app_e($error); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="stack-form">
            <input type="hidden" name="_token" value="<?php echo app_csrf_token(); ?>">
            <input type="hidden" name="redirect_to" value="<?php echo app_e($redirectTo); ?>">

            <div>
                <label class="form-label"><?php echo !empty($post['shared_post_id']) ? 'Share Caption' : 'Post Content'; ?></label>
                <textarea name="content" class="form-control composer-textarea" rows="7" maxlength="2000"<?php echo empty($post['shared_post_id']) ? ' required' : ''; ?>><?php echo app_e($post['content']); ?></textarea>
            </div>

            <?php if (!empty($post['shared_post_id'])): ?>
                <?php
                $sharedPost = [
                    'id' => (int) $post['shared_post_id'],
                    'user_id' => (int) $post['shared_post_user_id'],
                    'full_name' => $post['shared_post_full_name'],
                    'username' => $post['shared_post_username'],
                    'profile_image' => $post['shared_post_profile_image'],
                    'content' => $post['shared_post_content'],
                    'image' => $post['shared_post_image'],
                    'created_at' => $post['shared_post_created_at'],
                ];
                ?>
                <?php include APP_ROOT . '/app/views/partials/shared_post_embed.php'; ?>
            <?php else: ?>
                <?php if (!empty($post['image'])): ?>
                    <div class="media-preview">
                        <img src="<?php echo app_upload_url($post['image']); ?>" alt="Current post image">
                    </div>
                    <div class="form-check danger-check">
                        <input class="form-check-input" type="checkbox" name="remove_image" id="removePostImage" value="1">
                        <label class="form-check-label" for="removePostImage">Remove current post image</label>
                    </div>
                <?php endif; ?>

                <div>
                    <label class="form-label">Replace Image</label>
                    <input type="file" name="image" class="form-control" accept="image/png,image/jpeg,image/gif,image/webp">
                </div>
            <?php endif; ?>

            <div class="editor-actions">
                <button type="submit" class="btn btn-accent">Save Post</button>
                <a class="btn btn-outline-dark" href="<?php echo app_normalize_redirect_target($redirectTo); ?>">Cancel</a>
            </div>
        </form>
    </section>
</div>
