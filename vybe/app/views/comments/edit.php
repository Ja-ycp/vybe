<div class="editor-shell">
    <section class="surface-card editor-card">
        <div class="section-heading">
            <h2>Edit Comment</h2>
            <span>Make changes before sending it back to the thread</span>
        </div>

        <?php if ($error !== null): ?>
            <div class="alert alert-danger mb-3"><?php echo app_e($error); ?></div>
        <?php endif; ?>

        <?php if ($post !== null): ?>
            <div class="comment-context">
                <strong>Replying on post by @<?php echo app_e($post['username']); ?></strong>
                <p class="mb-0"><?php echo nl2br(app_e($post['content'])); ?></p>
                <?php if (!empty($comment['image'])): ?>
                    <div class="comment-media-preview mt-3">
                        <img src="<?php echo app_upload_url($comment['image']); ?>" alt="Attached comment image">
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="stack-form">
            <input type="hidden" name="_token" value="<?php echo app_csrf_token(); ?>">
            <input type="hidden" name="redirect_to" value="<?php echo app_e($redirectTo); ?>">

            <div>
                <label class="form-label">Comment</label>
                <textarea name="content" class="form-control" rows="5" maxlength="500" required><?php echo app_e($comment['content']); ?></textarea>
            </div>

            <div class="editor-actions">
                <button type="submit" class="btn btn-accent">Save Comment</button>
                <a class="btn btn-outline-dark" href="<?php echo app_e(app_normalize_redirect_target($redirectTo)); ?>">Cancel</a>
            </div>
        </form>
    </section>
</div>
