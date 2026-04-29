<?php
$commentDepth = $commentDepth ?? 0;
$commentId = (int) $threadComment['id'];
$replyFieldId = 'reply-input-' . $commentId;
$replyFormId = 'reply-form-' . $commentId;
$reactionCount = (int) ($threadComment['reaction_count'] ?? 0);
$isReacted = (int) ($threadComment['is_reacted'] ?? 0) === 1;
$threadReplies = $threadComment['replies'] ?? [];
$currentComment = $threadComment;
$currentDepth = $commentDepth;
?>
<div class="comment-thread<?php echo $commentDepth > 0 ? ' comment-thread-reply' : ''; ?>" style="--comment-depth: <?php echo (int) min($commentDepth, 4); ?>;">
    <div class="comment-item">
        <div class="avatar avatar-xs">
            <?php if (!empty($threadComment['profile_image'])): ?>
                <img class="avatar-image" src="<?php echo app_upload_url($threadComment['profile_image']); ?>" alt="<?php echo app_e($threadComment['full_name']); ?>">
            <?php else: ?>
                <span class="avatar-fallback"><?php echo app_e(app_initials($threadComment['full_name'])); ?></span>
            <?php endif; ?>
        </div>

        <div class="comment-main">
            <div class="comment-bubble">
                <div class="comment-meta">
                    <strong><?php echo app_e($threadComment['full_name']); ?></strong>
                    <span>@<?php echo app_e($threadComment['username']); ?> . <?php echo app_e(app_format_datetime($threadComment['created_at'])); ?></span>
                </div>
                <?php if (trim((string) $threadComment['content']) !== ''): ?>
                    <p class="mb-0"><?php echo nl2br(app_e($threadComment['content'])); ?></p>
                <?php endif; ?>
                <?php if (!empty($threadComment['image'])): ?>
                    <div class="comment-media-preview">
                        <img src="<?php echo app_upload_url($threadComment['image']); ?>" alt="Comment attachment">
                    </div>
                <?php endif; ?>
            </div>

            <div class="comment-thread-actions">
                <form method="POST" action="<?php echo app_route('Comment', 'react', ['id' => $commentId]); ?>" data-comment-persist-panel data-feed-async>
                    <input type="hidden" name="_token" value="<?php echo app_csrf_token(); ?>">
                    <input type="hidden" name="redirect_to" value="<?php echo app_e($postRedirectTo); ?>">
                    <button type="submit" class="comment-thread-btn <?php echo $isReacted ? 'is-active' : ''; ?>">
                        <i class="fa-solid fa-heart"></i>
                        <span><?php echo $isReacted ? 'Reacted' : 'React'; ?></span>
                    </button>
                </form>

                <button type="button" class="comment-thread-btn" data-reply-target="<?php echo app_e($replyFormId); ?>">
                    <i class="fa-solid fa-reply"></i>
                    <span>Reply</span>
                </button>

                <?php if ($reactionCount > 0): ?>
                    <span class="comment-reaction-count">
                        <i class="fa-solid fa-heart"></i>
                        <?php echo $reactionCount; ?> <?php echo $reactionCount === 1 ? 'reaction' : 'reactions'; ?>
                    </span>
                <?php endif; ?>

                <?php if ((int) $threadComment['user_id'] === (int) $_SESSION['user_id']): ?>
                    <a class="comment-thread-btn" href="<?php echo app_route('Comment', 'edit', ['id' => $commentId, 'redirect_to' => $postRedirectTo]); ?>">
                        <i class="fa-solid fa-pen-to-square"></i>
                        <span>Edit</span>
                    </a>

                    <form
                        method="POST"
                        action="<?php echo app_route('Comment', 'delete', ['id' => $commentId]); ?>"
                        data-comment-persist-panel
                        data-confirm-modal
                        data-confirm-title="Delete Comment"
                        data-confirm-message="This comment and its replies will be permanently removed from the conversation."
                        data-confirm-button="Delete Comment"
                        data-confirm-variant="danger"
                    >
                        <input type="hidden" name="_token" value="<?php echo app_csrf_token(); ?>">
                        <input type="hidden" name="redirect_to" value="<?php echo app_e($postRedirectTo); ?>">
                        <button type="submit" class="comment-thread-btn danger-link">
                            <i class="fa-solid fa-trash"></i>
                            <span>Delete</span>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <form id="<?php echo app_e($replyFormId); ?>" method="POST" action="<?php echo app_route('Comment', 'create', ['id' => (int) $post['id']]); ?>" class="comment-form comment-composer comment-reply-form is-hidden" enctype="multipart/form-data" data-comment-composer data-comment-persist-panel data-feed-async>
        <input type="hidden" name="_token" value="<?php echo app_csrf_token(); ?>">
        <input type="hidden" name="redirect_to" value="<?php echo app_e($postRedirectTo); ?>">
        <input type="hidden" name="parent_id" value="<?php echo $commentId; ?>">
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
                id="<?php echo app_e($replyFieldId); ?>"
                name="content"
                class="form-control comment-composer-input"
                rows="1"
                maxlength="500"
                placeholder="Reply as <?php echo app_e($viewerDisplayName); ?>"
            ></textarea>
            <div class="comment-composer-tools">
                <button type="button" class="comment-tool-btn" aria-label="Add reaction" data-emoji-toggle>
                    <i class="fa-regular fa-face-smile"></i>
                </button>
                <button type="button" class="comment-tool-btn" aria-label="Add photo" data-comment-file-trigger="image">
                    <i class="fa-regular fa-image"></i>
                </button>
                <button type="submit" class="comment-send-btn" aria-label="Send reply">
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
                <span class="small-muted">This will be sent with your reply.</span>
            </div>
            <button type="button" class="comment-preview-remove" data-comment-preview-remove>
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </form>

    <?php foreach ($threadReplies as $replyComment): ?>
        <?php
        $threadComment = $replyComment;
        $commentDepth = $currentDepth + 1;
        include APP_ROOT . '/app/views/partials/comment_item.php';
        ?>
    <?php endforeach; ?>

    <?php
    $threadComment = $currentComment;
    $commentDepth = $currentDepth;
    ?>
</div>
