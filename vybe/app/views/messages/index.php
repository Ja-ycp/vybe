<section class="hero-card surface-card">
    <div>
        <span class="eyebrow">Private messages</span>
        <h1>Keep one-to-one conversations off the public feed.</h1>
        <p class="hero-copy">Open a profile and start a private chat, or continue an existing conversation from your inbox.</p>
    </div>
    <div class="hero-actions">
        <a class="btn btn-outline-dark" href="<?php echo app_route('Feed'); ?>">
            <i class="fa-solid fa-arrow-left me-2"></i>Back to Feed
        </a>
    </div>
</section>

<div class="messages-layout">
    <aside class="surface-card messages-sidebar">
        <div class="section-heading">
            <h2>Inbox</h2>
            <span>Your latest conversations</span>
        </div>

        <div class="stack-list">
            <?php if ($threads === []): ?>
                <p class="small-muted mb-0">No conversations yet.</p>
            <?php else: ?>
                <?php foreach ($threads as $thread): ?>
                    <a class="thread-card <?php echo $selectedUser !== null && (int) $selectedUser['id'] === (int) $thread['partner_id'] ? 'is-active' : ''; ?>" href="<?php echo app_route('Message', 'index', ['id' => (int) $thread['partner_id']]); ?>">
                        <div class="avatar avatar-sm">
                            <?php if (!empty($thread['profile_image'])): ?>
                                <img class="avatar-image" src="<?php echo app_upload_url($thread['profile_image']); ?>" alt="<?php echo app_e($thread['full_name']); ?>">
                            <?php else: ?>
                                <span class="avatar-fallback"><?php echo app_e(app_initials($thread['full_name'])); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="thread-copy">
                            <div class="thread-head">
                                <strong><?php echo app_e($thread['full_name']); ?></strong>
                                <?php if ((int) $thread['unread_count'] > 0): ?>
                                    <span class="unread-badge"><?php echo (int) $thread['unread_count']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="small-muted">@<?php echo app_e($thread['username']); ?> . <?php echo app_e(app_format_datetime($thread['last_message_at'])); ?></div>
                            <p class="thread-preview">
                                <?php
                                $threadPreview = !empty($thread['last_message_unsent_at'])
                                    ? ((int) $thread['last_message_sender_id'] === (int) $_SESSION['user_id']
                                        ? 'You unsent a message.'
                                        : 'A message was unsent.')
                                    : mb_strimwidth((string) $thread['last_message'], 0, 70, '...');
                                ?>
                                <?php echo app_e($threadPreview); ?>
                            </p>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="section-heading mt-4">
            <h2>Start New Chat</h2>
            <span>Choose someone from the community</span>
        </div>
        <div class="stack-list">
            <?php if ($recentUsers === []): ?>
                <p class="small-muted mb-0">No other users are available yet. Register another account to test private messaging.</p>
            <?php else: ?>
                <?php foreach ($recentUsers as $user): ?>
                    <a class="person-row" href="<?php echo app_route('Message', 'index', ['id' => (int) $user['id']]); ?>">
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
    </aside>

    <section class="surface-card messages-panel">
        <?php if ($selectedUser === null): ?>
            <div class="empty-state message-empty">
                <i class="fa-regular fa-paper-plane"></i>
                <h2>Select a conversation</h2>
                <p>Open an existing thread from your inbox or start a new private message from another user's profile.</p>
            </div>
        <?php else: ?>
            <header class="message-header">
                <div class="avatar avatar-md">
                    <?php if (!empty($selectedUser['profile_image'])): ?>
                        <img class="avatar-image" src="<?php echo app_upload_url($selectedUser['profile_image']); ?>" alt="<?php echo app_e($selectedUser['full_name']); ?>">
                    <?php else: ?>
                        <span class="avatar-fallback"><?php echo app_e(app_initials($selectedUser['full_name'])); ?></span>
                    <?php endif; ?>
                </div>
                <div>
                    <h2 class="mb-1"><?php echo app_e($selectedUser['full_name']); ?></h2>
                    <div class="small-muted">@<?php echo app_e($selectedUser['username']); ?></div>
                </div>
                <div class="message-header-actions ms-auto">
                    <a class="btn btn-outline-dark" href="<?php echo app_route('Profile', 'view', ['id' => (int) $selectedUser['id']]); ?>">
                        <i class="fa-solid fa-user me-2"></i>View Profile
                    </a>
                    <form
                        method="POST"
                        action="<?php echo app_route('Message', 'deleteConversation', ['id' => (int) $selectedUser['id']]); ?>"
                        data-confirm-modal
                        data-confirm-title="Delete Conversation"
                        data-confirm-message="This conversation will be removed from your inbox and hidden from your side."
                        data-confirm-button="Delete Conversation"
                        data-confirm-variant="danger"
                    >
                        <input type="hidden" name="_token" value="<?php echo app_csrf_token(); ?>">
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="fa-solid fa-trash-can me-2"></i>Delete Conversation
                        </button>
                    </form>
                </div>
            </header>

            <div class="message-thread">
                <?php if ($conversation === []): ?>
                    <div class="empty-thread">
                        <p class="mb-0">No messages yet. Send the first private message to start this conversation.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($conversation as $message): ?>
                        <?php $messageRedirectTo = app_with_fragment(app_route('Message', 'index', ['id' => (int) $selectedUser['id']]), 'message-' . (int) $message['id']); ?>
                        <?php $conversationRedirectTo = app_route('Message', 'index', ['id' => (int) $selectedUser['id']]); ?>
                        <?php $isOwnMessage = (int) $message['sender_id'] === (int) $_SESSION['user_id']; ?>
                        <?php $isUnsentMessage = !empty($message['unsent_at']); ?>
                        <?php
                        $messageManagePreview = $isUnsentMessage
                            ? ($isOwnMessage ? 'You unsent a message.' : $message['sender_full_name'] . ' unsent a message.')
                            : mb_strimwidth((string) $message['content'], 0, 160, '...');
                        ?>
                        <div class="message-bubble-row <?php echo (int) $message['sender_id'] === (int) $_SESSION['user_id'] ? 'is-self' : ''; ?>" id="message-<?php echo (int) $message['id']; ?>" data-stay-id="message-<?php echo (int) $message['id']; ?>">
                            <div class="message-bubble <?php echo $isUnsentMessage ? 'is-unsent' : ''; ?>">
                                <?php if (!empty($message['reply_message_id'])): ?>
                                    <div class="message-reply-preview">
                                        <strong><?php echo app_e($message['reply_sender_full_name'] ?: $message['reply_sender_username']); ?></strong>
                                        <p class="mb-0">
                                            <?php
                                            $replyPreview = !empty($message['reply_message_unsent_at'])
                                                ? 'This message was unsent.'
                                                : mb_strimwidth((string) $message['reply_message_content'], 0, 110, '...');
                                            ?>
                                            <?php echo app_e($replyPreview); ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                                <div class="message-meta">
                                    <strong><?php echo app_e($message['sender_full_name']); ?></strong>
                                    <span><?php echo app_e(app_format_datetime($message['created_at'])); ?></span>
                                </div>
                                <?php if ($isUnsentMessage): ?>
                                    <p class="mb-0 message-unsent-copy">
                                        <?php echo app_e($isOwnMessage ? 'You unsent a message.' : $message['sender_full_name'] . ' unsent a message.'); ?>
                                    </p>
                                <?php else: ?>
                                    <p class="mb-0"><?php echo nl2br(app_e($message['content'])); ?></p>
                                <?php endif; ?>
                                <div class="message-actions">
                                    <?php if (!$isUnsentMessage): ?>
                                        <button
                                            type="button"
                                            class="message-react-btn"
                                            data-message-reply
                                            data-message-id="<?php echo (int) $message['id']; ?>"
                                            data-message-sender="<?php echo app_e($message['sender_full_name']); ?>"
                                            data-message-content="<?php echo app_e(mb_strimwidth((string) $message['content'], 0, 140, '...')); ?>"
                                        >
                                            <i class="fa-solid fa-reply"></i>
                                            <span>Reply</span>
                                        </button>
                                        <form method="POST" action="<?php echo app_route('Message', 'react', ['id' => (int) $message['id']]); ?>">
                                            <input type="hidden" name="_token" value="<?php echo app_csrf_token(); ?>">
                                            <input type="hidden" name="redirect_to" value="<?php echo app_e($messageRedirectTo); ?>">
                                            <button type="submit" class="message-react-btn <?php echo (int) ($message['is_reacted'] ?? 0) === 1 ? 'is-active' : ''; ?>">
                                                <i class="fa-solid fa-heart"></i>
                                                <span><?php echo (int) ($message['is_reacted'] ?? 0) === 1 ? 'Reacted' : 'React'; ?></span>
                                            </button>
                                        </form>
                                        <?php if ((int) ($message['reaction_count'] ?? 0) > 0): ?>
                                            <span class="message-reaction-count">
                                                <i class="fa-solid fa-heart"></i>
                                                <?php echo (int) $message['reaction_count']; ?>
                                            </span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <button
                                        type="button"
                                        class="message-react-btn"
                                        data-message-manage
                                        data-message-preview="<?php echo app_e($messageManagePreview); ?>"
                                        data-message-unsend-action="<?php echo app_route('Message', 'unsend', ['id' => (int) $message['id']]); ?>"
                                        data-message-unsend-redirect="<?php echo app_e($messageRedirectTo); ?>"
                                        data-message-remove-action="<?php echo app_route('Message', 'remove', ['id' => (int) $message['id']]); ?>"
                                        data-message-remove-redirect="<?php echo app_e($conversationRedirectTo); ?>"
                                        data-message-can-unsend="<?php echo $isOwnMessage && !$isUnsentMessage ? '1' : '0'; ?>"
                                    >
                                        <i class="fa-solid fa-ellipsis"></i>
                                        <span>More</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <form method="POST" action="<?php echo app_route('Message', 'send', ['id' => (int) $selectedUser['id']]); ?>" class="message-compose" data-message-compose>
                <input type="hidden" name="_token" value="<?php echo app_csrf_token(); ?>">
                <input type="hidden" name="reply_to_message_id" value="" data-message-reply-input>
                <div class="message-compose-reply is-hidden" data-message-reply-preview>
                    <div class="message-compose-reply-copy">
                        <strong data-message-reply-sender>Replying to</strong>
                        <p class="mb-0" data-message-reply-content></p>
                    </div>
                    <button type="button" class="message-compose-reply-clear" data-message-reply-clear>
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="message-compose-shell">
                    <textarea name="content" class="form-control message-compose-input" rows="3" maxlength="2000" placeholder="Write a private message..." required></textarea>
                    <div class="message-compose-tools-row">
                        <button type="button" class="message-compose-tool-btn" data-message-emoji-toggle aria-label="Add emoji">
                            <i class="fa-regular fa-face-smile"></i>
                        </button>
                        <button type="submit" class="btn btn-accent">Send Message</button>
                    </div>
                </div>
                <div class="message-emoji-picker is-hidden" data-message-emoji-picker>
                    <?php foreach (['😀', '😍', '😂', '🔥', '🙏', '🥹', '✨', '💖'] as $emoji): ?>
                        <button type="button" class="message-emoji-btn" data-message-emoji-value="<?php echo app_e($emoji); ?>"><?php echo app_e($emoji); ?></button>
                    <?php endforeach; ?>
                </div>
            </form>

        <?php endif; ?>
    </section>
</div>

<?php if ($selectedUser !== null): ?>
    <div class="modal fade" id="messageManageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content message-manage-card">
                <div class="modal-header border-0">
                    <div>
                        <h2 class="message-manage-title mb-1">Manage Message</h2>
                        <p class="message-manage-copy mb-0">Choose how you want to remove this message.</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="message-manage-preview" data-message-manage-preview></div>

                    <div class="message-manage-actions">
                        <form
                            method="POST"
                            class="message-manage-form is-hidden"
                            data-message-unsend-form
                            data-confirm-modal
                            data-confirm-title="Unsend Message"
                            data-confirm-message="This will unsend the message for everyone in the conversation."
                            data-confirm-button="Unsend for Everyone"
                            data-confirm-variant="danger"
                        >
                            <input type="hidden" name="_token" value="<?php echo app_csrf_token(); ?>">
                            <input type="hidden" name="redirect_to" value="" data-message-unsend-redirect>
                            <button type="submit" class="message-manage-option">
                                <span class="message-manage-option-copy">
                                    <strong>Unsend for everyone</strong>
                                    <small>Replace the message for both people with an unsent notice.</small>
                                </span>
                                <i class="fa-solid fa-rotate-left"></i>
                            </button>
                        </form>

                        <form
                            method="POST"
                            class="message-manage-form"
                            data-message-remove-form
                            data-confirm-modal
                            data-confirm-title="Unsend for You"
                            data-confirm-message="This will hide the message only from your side of the conversation."
                            data-confirm-button="Unsend for You"
                            data-confirm-variant="danger"
                        >
                            <input type="hidden" name="_token" value="<?php echo app_csrf_token(); ?>">
                            <input type="hidden" name="redirect_to" value="" data-message-remove-redirect>
                            <button type="submit" class="message-manage-option">
                                <span class="message-manage-option-copy">
                                    <strong>Unsend for you</strong>
                                    <small>Hide this message only from your side of the conversation.</small>
                                </span>
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
