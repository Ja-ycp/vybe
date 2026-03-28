<?php
$commentFieldId = 'comment-input-' . (int) $post['id'];
$commentPanelId = 'comment-panel-' . (int) $post['id'];
$shareTargetId = !empty($post['shared_post_id']) ? (int) $post['shared_post_id'] : (int) $post['id'];
$viewerDisplayName = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'you';
$viewerAvatar = $_SESSION['profile_image'] ?? null;
$postRedirectTo = app_with_fragment($redirectTo, 'post-' . (int) $post['id']);
?>
<article class="surface-card post-card" id="post-<?php echo (int) $post['id']; ?>" data-stay-id="post-<?php echo (int) $post['id']; ?>" data-comment-panel-id="<?php echo app_e($commentPanelId); ?>">
    <header class="post-head">
        <a class="post-author" href="<?php echo app_route('Profile', 'view', ['id' => (int) $post['user_id']]); ?>">
            <div class="avatar avatar-md">
                <?php if (!empty($post['profile_image'])): ?>
                    <img class="avatar-image" src="<?php echo app_upload_url($post['profile_image']); ?>" alt="<?php echo app_e($post['full_name']); ?>">
                <?php else: ?>
                    <span class="avatar-fallback"><?php echo app_e(app_initials($post['full_name'])); ?></span>
                <?php endif; ?>
            </div>
            <div>
                <strong><?php echo app_e($post['full_name']); ?></strong>
                <div class="small-muted">
                    @<?php echo app_e($post['username']); ?> . <?php echo app_e(app_format_datetime($post['created_at'])); ?>
                    <?php if (!empty($post['shared_post_id'])): ?>
                        . shared a post
                    <?php endif; ?>
                </div>
            </div>
        </a>

        <?php if ((int) $post['user_id'] === (int) $_SESSION['user_id']): ?>
            <div class="post-owner-actions">
                <a class="btn btn-sm btn-outline-dark" href="<?php echo app_route('Post', 'edit', ['id' => (int) $post['id'], 'redirect_to' => $postRedirectTo]); ?>">
                    <i class="fa-solid fa-pen-to-square me-1"></i>Edit
                </a>
                <form
                    method="POST"
                    action="<?php echo app_route('Post', 'delete', ['id' => (int) $post['id']]); ?>"
                    data-confirm-modal
                    data-confirm-title="Delete Post"
                    data-confirm-message="This post will be permanently removed from your profile and the feed."
                    data-confirm-button="Delete Post"
                    data-confirm-variant="danger"
                >
                    <input type="hidden" name="_token" value="<?php echo app_csrf_token(); ?>">
                    <input type="hidden" name="redirect_to" value="<?php echo app_e($postRedirectTo); ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="fa-solid fa-trash me-1"></i>Delete
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </header>

    <div class="post-body">
        <?php if (trim((string) $post['content']) !== ''): ?>
            <p class="post-copy"><?php echo nl2br(app_e($post['content'])); ?></p>
        <?php endif; ?>

        <?php if (!empty($post['image'])): ?>
            <div class="media-preview">
                <img src="<?php echo app_upload_url($post['image']); ?>" alt="Post image">
            </div>
        <?php endif; ?>

        <?php if (!empty($post['shared_post_id']) && !empty($post['shared_post_user_id'])): ?>
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
        <?php endif; ?>
    </div>

    <div class="post-stats">
        <span class="stat-pill"><i class="fa-solid fa-heart"></i><?php echo (int) $post['like_count']; ?> likes</span>
        <span class="stat-pill"><i class="fa-solid fa-comment"></i><?php echo (int) $post['comment_count']; ?> comments</span>
        <?php if (empty($post['shared_post_id'])): ?>
            <span class="stat-pill"><i class="fa-solid fa-share"></i><?php echo (int) ($post['share_count'] ?? 0); ?> shares</span>
        <?php endif; ?>
    </div>

    <div class="post-actions">
        <form method="POST" action="<?php echo app_route('Like', 'toggle', ['id' => (int) $post['id']]); ?>">
            <input type="hidden" name="_token" value="<?php echo app_csrf_token(); ?>">
            <input type="hidden" name="redirect_to" value="<?php echo app_e($postRedirectTo); ?>">
            <button type="submit" class="post-action-btn <?php echo (int) $post['is_liked'] === 1 ? 'is-active' : ''; ?>">
                <i class="fa-solid fa-thumbs-up"></i>
                <span><?php echo (int) $post['is_liked'] === 1 ? 'Liked' : 'Like'; ?></span>
            </button>
        </form>
        <button type="button" class="post-action-btn" data-comment-target="<?php echo app_e($commentFieldId); ?>" data-comment-panel="<?php echo app_e($commentPanelId); ?>" data-comment-anchor>
            <i class="fa-regular fa-comment"></i>
            <span>Comment</span>
        </button>
        <a class="post-action-btn" href="<?php echo app_route('Post', 'share', ['id' => $shareTargetId, 'redirect_to' => $postRedirectTo]); ?>">
            <i class="fa-solid fa-share"></i>
            <span>Share</span>
        </a>
    </div>

    <div class="comment-section is-hidden" id="<?php echo app_e($commentPanelId); ?>" data-comment-panel>
        <div class="comment-list">
            <?php if (($post['comments'] ?? []) === []): ?>
                <p class="small-muted mb-0">No comments yet. Be the first to respond.</p>
            <?php else: ?>
                <?php foreach ($post['comments'] as $threadComment): ?>
                    <?php $commentDepth = 0; ?>
                    <?php include APP_ROOT . '/app/views/partials/comment_item.php'; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <form method="POST" action="<?php echo app_route('Comment', 'create', ['id' => (int) $post['id']]); ?>" class="comment-form comment-composer" enctype="multipart/form-data" data-comment-composer data-comment-persist-panel>
            <input type="hidden" name="_token" value="<?php echo app_csrf_token(); ?>">
            <input type="hidden" name="redirect_to" value="<?php echo app_e($postRedirectTo); ?>">
            <input type="file" name="image" class="d-none" accept="image/*" data-comment-file-input>
            <div class="avatar avatar-xs">
                <?php if (!empty($viewerAvatar)): ?>
                    <img class="avatar-image" src="<?php echo app_upload_url($viewerAvatar); ?>" alt="<?php echo app_e($viewerDisplayName); ?>">
                <?php else: ?>
                    <span class="avatar-fallback"><?php echo app_e(app_initials($viewerDisplayName)); ?></span>
                <?php endif; ?>
            </div>
            <div class="comment-composer-shell">
                <textarea
                    id="<?php echo app_e($commentFieldId); ?>"
                    name="content"
                    class="form-control comment-composer-input"
                    rows="1"
                    maxlength="500"
                    placeholder="Comment as <?php echo app_e($viewerDisplayName); ?>"
                ></textarea>
                <div class="comment-composer-tools">
                    <button type="button" class="comment-tool-btn" aria-label="Add reaction" data-emoji-toggle>
                        <i class="fa-regular fa-face-smile"></i>
                    </button>
                    <button type="button" class="comment-tool-btn" aria-label="Add photo" data-comment-file-trigger="image">
                        <i class="fa-regular fa-image"></i>
                    </button>
                    <button type="submit" class="comment-send-btn" aria-label="Send comment">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>
            </div>
            <div class="comment-emoji-picker is-hidden" data-emoji-picker>
                <?php foreach (['😀', '😍', '🔥', '👏', '😂', '💯', '🥹', '✨'] as $emoji): ?>
                    <button type="button" class="comment-emoji-btn" data-emoji-value="<?php echo app_e($emoji); ?>"><?php echo app_e($emoji); ?></button>
                <?php endforeach; ?>
            </div>
            <div class="comment-attachment-preview is-hidden" data-comment-preview>
                <img src="" alt="Selected attachment" data-comment-preview-image>
                <div class="comment-attachment-meta">
                    <strong data-comment-preview-name>No file selected</strong>
                    <span class="small-muted">This will be sent with your comment.</span>
                </div>
                <button type="button" class="comment-preview-remove" data-comment-preview-remove>
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </form>
    </div>
</article>
