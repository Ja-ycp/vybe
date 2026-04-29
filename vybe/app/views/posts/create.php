<div class="editor-shell">
    <section class="surface-card editor-card">
        <div class="section-heading">
            <h2>Create Post</h2>
            <span>Share a thought, announcement, or photo update</span>
        </div>

        <?php if ($error !== null): ?>
            <div class="alert alert-danger mb-3"><?php echo app_e($error); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="stack-form">
            <input type="hidden" name="_token" value="<?php echo app_csrf_token(); ?>">

            <div>
                <label class="form-label">What's on your mind?</label>
                <textarea name="content" class="form-control composer-textarea" rows="7" maxlength="2000" placeholder="Write a post for the community..." required><?php echo app_e($post['content'] ?? ''); ?></textarea>
            </div>

            <div>
                <label class="form-label">Add Image (Optional)</label>
                <input type="file" name="image" class="form-control" accept="image/png,image/jpeg,image/gif,image/webp">
            </div>

            <div class="editor-actions">
                <button type="submit" class="btn btn-accent">Publish Post</button>
                <a class="btn btn-outline-dark" href="<?php echo app_route('Feed'); ?>">Cancel</a>
            </div>
        </form>
    </section>
</div>

