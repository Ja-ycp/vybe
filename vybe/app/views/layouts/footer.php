        </div>
    </main>
    <div class="modal fade" id="appConfirmModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content confirm-modal-card">
                <div class="modal-header border-0">
                    <div class="confirm-modal-header-copy">
                        <div class="confirm-modal-icon">
                            <i class="fa-solid fa-shield-heart"></i>
                        </div>
                        <div>
                            <h2 class="confirm-modal-title mb-1">Confirm Action</h2>
                            <p class="confirm-modal-copy mb-0">Please confirm before continuing.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="confirm-modal-message" data-confirm-modal-message></div>
                    <div class="confirm-modal-actions">
                        <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-outline-danger" data-confirm-modal-submit>Continue</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let pendingConfirmForm = null;
        let activeFeedRequest = null;

        const showConfirmModalForForm = function (form) {
            const modalElement = document.getElementById('appConfirmModal');
            if (!modalElement || typeof bootstrap === 'undefined') {
                return;
            }

            const title = modalElement.querySelector('.confirm-modal-title');
            const copy = modalElement.querySelector('.confirm-modal-copy');
            const message = modalElement.querySelector('[data-confirm-modal-message]');
            const submitButton = modalElement.querySelector('[data-confirm-modal-submit]');
            const icon = modalElement.querySelector('.confirm-modal-icon');
            const variant = form.getAttribute('data-confirm-variant') || 'danger';

            if (title) {
                title.textContent = form.getAttribute('data-confirm-title') || 'Confirm Action';
            }

            if (copy) {
                copy.textContent = variant === 'danger'
                    ? 'Please review this action carefully before continuing.'
                    : 'Please confirm before continuing.';
            }

            if (message) {
                message.textContent = form.getAttribute('data-confirm-message') || 'Are you sure you want to continue?';
            }

            if (submitButton) {
                submitButton.textContent = form.getAttribute('data-confirm-button') || 'Continue';
                submitButton.classList.remove('btn-outline-danger', 'btn-accent');
                submitButton.classList.add(variant === 'danger' ? 'btn-outline-danger' : 'btn-accent');
            }

            if (icon) {
                icon.classList.toggle('is-danger', variant === 'danger');
                icon.classList.toggle('is-accent', variant !== 'danger');
            }

            pendingConfirmForm = form;
            bootstrap.Modal.getOrCreateInstance(modalElement).show();
        };

        const openCommentPanelById = function (panelId) {
            if (!panelId) {
                return null;
            }

            document.querySelectorAll('[data-comment-panel]').forEach(function (panel) {
                if (panel.id !== panelId) {
                    panel.classList.add('is-hidden');
                    panel.querySelectorAll('.comment-reply-form, [data-emoji-picker]').forEach(function (node) {
                        node.classList.add('is-hidden');
                    });
                }
            });

            const panel = document.getElementById(panelId);
            if (!panel) {
                return null;
            }

            panel.classList.remove('is-hidden');
            return panel;
        };

        const getCommentAnchorForPanel = function (panel) {
            const postCard = panel && panel.closest ? panel.closest('[data-comment-panel-id]') : null;
            if (!postCard) {
                return null;
            }

            return postCard.querySelector('[data-comment-anchor]') || postCard;
        };

        const closeCommentPanels = function (options) {
            const settings = Object.assign({ keepAnchorVisible: false }, options || {});
            let firstOpenAnchor = null;

            document.querySelectorAll('[data-comment-panel]').forEach(function (panel) {
                if (!panel.classList.contains('is-hidden') && !firstOpenAnchor) {
                    firstOpenAnchor = getCommentAnchorForPanel(panel);
                }

                panel.classList.add('is-hidden');
                panel.querySelectorAll('.comment-reply-form, [data-emoji-picker]').forEach(function (node) {
                    node.classList.add('is-hidden');
                });
            });

            window.sessionStorage.removeItem('vybe-open-comment-panel');

            if (settings.keepAnchorVisible && firstOpenAnchor) {
                window.requestAnimationFrame(function () {
                    const rect = firstOpenAnchor.getBoundingClientRect();
                    const padding = 20;
                    const isOutsideViewport = rect.top < padding || rect.bottom > (window.innerHeight - padding);

                    if (isOutsideViewport) {
                        firstOpenAnchor.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'nearest' });
                    }
                });
            }
        };

        const closeReplyForms = function (exceptId) {
            document.querySelectorAll('.comment-reply-form').forEach(function (form) {
                if (exceptId && form.id === exceptId) {
                    return;
                }

                form.classList.add('is-hidden');
                form.querySelectorAll('[data-emoji-picker]').forEach(function (node) {
                    node.classList.add('is-hidden');
                });
            });
        };

        const findCommentPanelId = function (node) {
            const postCard = node && node.closest ? node.closest('[data-comment-panel-id]') : null;
            if (!postCard) {
                return null;
            }

            return postCard.getAttribute('data-comment-panel-id');
        };

        const rememberCommentPanel = function (node) {
            const panelId = findCommentPanelId(node);
            if (!panelId) {
                return;
            }

            window.sessionStorage.setItem('vybe-open-comment-panel', panelId);
        };

        const persistStayTarget = function (node) {
            const target = node && node.closest ? node.closest('[data-stay-id]') : null;
            if (!target) {
                return;
            }

            const targetId = target.getAttribute('data-stay-id');
            if (targetId) {
                window.sessionStorage.setItem('vybe-stay-target', targetId);
            }
        };

        const parsePostIdFromRedirect = function (value) {
            const match = (value || '').match(/#post-(\d+)/);
            return match ? parseInt(match[1], 10) : null;
        };

        const getFormPostId = function (form) {
            const postCard = form.closest('[id^="post-"]');
            if (postCard && postCard.id) {
                const match = postCard.id.match(/^post-(\d+)$/);
                if (match) {
                    return parseInt(match[1], 10);
                }
            }

            const redirectInput = form.querySelector('input[name="redirect_to"]');
            if (redirectInput) {
                return parsePostIdFromRedirect(redirectInput.value);
            }

            return null;
        };

        const restoreCommentPanelState = function (postId, wasOpen) {
            if (!wasOpen || !postId) {
                return;
            }

            const panel = document.getElementById('comment-panel-' + postId);
            if (panel) {
                panel.classList.remove('is-hidden');
            }
        };

        const replacePostCardFromResponse = function (postId, html, wasPanelOpen) {
            if (!postId) {
                return false;
            }

            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const replacement = doc.getElementById('post-' + postId);
            const current = document.getElementById('post-' + postId);

            if (!replacement || !current) {
                return false;
            }

            current.replaceWith(replacement);
            restoreCommentPanelState(postId, wasPanelOpen);
            return true;
        };

        const setFormButtonsDisabled = function (form, disabled) {
            form.querySelectorAll('button[type="submit"], .comment-send-btn').forEach(function (button) {
                button.disabled = disabled;
            });
        };

        const submitFeedFormAsync = async function (form) {
            const postId = getFormPostId(form);
            const commentPanel = postId ? document.getElementById('comment-panel-' + postId) : null;
            const wasPanelOpen = commentPanel ? !commentPanel.classList.contains('is-hidden') : false;
            const redirectTo = form.querySelector('input[name="redirect_to"]');

            if (redirectTo && postId) {
                redirectTo.value = appRouteWithHashFallback(redirectTo.value, postId);
            }

            setFormButtonsDisabled(form, true);

            const requestToken = Symbol('feed-request');
            activeFeedRequest = requestToken;

            try {
                const response = await fetch(form.action, {
                    method: (form.method || 'POST').toUpperCase(),
                    body: new FormData(form),
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const responseText = await response.text();
                if (activeFeedRequest !== requestToken) {
                    return;
                }

                const replaced = replacePostCardFromResponse(postId, responseText, wasPanelOpen);
                if (!replaced) {
                    window.location.assign(form.action);
                }
            } catch (error) {
                window.location.assign(form.action);
            } finally {
                setFormButtonsDisabled(form, false);
            }
        };

        const appRouteWithHashFallback = function (value, postId) {
            if (!value || !postId) {
                return value;
            }

            if (value.indexOf('#post-') !== -1) {
                return value;
            }

            return value + '#post-' + postId;
        };

        document.addEventListener('submit', function (event) {
            persistStayTarget(event.target);

            if (event.target.matches('[data-comment-persist-panel]')) {
                rememberCommentPanel(event.target);
            }

            if (event.target.matches('[data-confirm-modal]')) {
                if (event.target.dataset.confirmApproved === '1') {
                    delete event.target.dataset.confirmApproved;
                    return;
                }

                event.preventDefault();
                const sourceModalElement = event.target.closest('.modal.show');
                if (sourceModalElement && sourceModalElement.id !== 'appConfirmModal' && typeof bootstrap !== 'undefined') {
                    sourceModalElement.addEventListener('hidden.bs.modal', function handleSourceHidden() {
                        showConfirmModalForForm(event.target);
                    }, { once: true });
                    bootstrap.Modal.getOrCreateInstance(sourceModalElement).hide();
                    return;
                }

                showConfirmModalForForm(event.target);
                return;
            }

            if (event.target.matches('[data-feed-async]')) {
                event.preventDefault();
                submitFeedFormAsync(event.target);
            }
        });

        document.addEventListener('click', function (event) {
            const stayTrigger = event.target.closest('a[href]');
            if (stayTrigger) {
                persistStayTarget(stayTrigger);
            }

            const directNavigateTrigger = event.target.closest('[data-direct-navigate]');
            if (directNavigateTrigger) {
                const targetUrl = directNavigateTrigger.getAttribute('data-direct-navigate') || '';
                if (targetUrl !== '') {
                    window.location.assign(targetUrl);
                }
                return;
            }

            const confirmSubmit = event.target.closest('[data-confirm-modal-submit]');
            if (confirmSubmit) {
                const modalElement = document.getElementById('appConfirmModal');
                if (!pendingConfirmForm || !modalElement || typeof bootstrap === 'undefined') {
                    return;
                }

                pendingConfirmForm.dataset.confirmApproved = '1';
                bootstrap.Modal.getOrCreateInstance(modalElement).hide();
                if (typeof pendingConfirmForm.requestSubmit === 'function') {
                    pendingConfirmForm.requestSubmit();
                } else {
                    pendingConfirmForm.submit();
                }
                pendingConfirmForm = null;
                return;
            }

            const clickCommentTrigger = event.target.closest('[data-comment-target]');
            if (clickCommentTrigger) {
                const targetId = clickCommentTrigger.getAttribute('data-comment-target');
                const panelId = clickCommentTrigger.getAttribute('data-comment-panel');
                openCommentPanelById(panelId);

                const field = document.getElementById(targetId);
                if (field) {
                    field.focus();
                    field.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return;
            }

            const openCommentPanel = document.querySelector('[data-comment-panel]:not(.is-hidden)');
            const openCommentPostCard = openCommentPanel ? openCommentPanel.closest('[data-comment-panel-id]') : null;
            if (openCommentPostCard && !openCommentPostCard.contains(event.target)) {
                closeCommentPanels({ keepAnchorVisible: true });
            }

            const clickInsideReplyForm = event.target.closest('.comment-reply-form');
            const clickReplyTrigger = event.target.closest('[data-reply-target]');
            if (!clickInsideReplyForm && !clickReplyTrigger) {
                closeReplyForms();
            }

            const emojiToggle = event.target.closest('[data-emoji-toggle]');
            if (emojiToggle) {
                const composer = emojiToggle.closest('[data-comment-composer]');
                const picker = composer ? composer.querySelector('[data-emoji-picker]') : null;
                if (picker) {
                    picker.classList.toggle('is-hidden');
                }
                return;
            }

            const messageEmojiToggle = event.target.closest('[data-message-emoji-toggle]');
            if (messageEmojiToggle) {
                const composer = messageEmojiToggle.closest('[data-message-compose]');
                const picker = composer ? composer.querySelector('[data-message-emoji-picker]') : null;
                if (picker) {
                    picker.classList.toggle('is-hidden');
                }
                return;
            }

            const emojiButton = event.target.closest('[data-emoji-value]');
            if (emojiButton) {
                const composer = emojiButton.closest('[data-comment-composer]');
                const field = composer ? composer.querySelector('textarea[name=\"content\"]') : null;
                const picker = composer ? composer.querySelector('[data-emoji-picker]') : null;
                const emoji = emojiButton.getAttribute('data-emoji-value') || '';

                if (field && emoji !== '') {
                    const start = field.selectionStart || field.value.length;
                    const end = field.selectionEnd || field.value.length;
                    const prefix = field.value.slice(0, start);
                    const suffix = field.value.slice(end);
                    field.value = prefix + emoji + suffix;
                    field.focus();
                    const cursor = start + emoji.length;
                    field.setSelectionRange(cursor, cursor);
                }

                if (picker) {
                    picker.classList.add('is-hidden');
                }
                return;
            }

            const messageEmojiButton = event.target.closest('[data-message-emoji-value]');
            if (messageEmojiButton) {
                const composer = messageEmojiButton.closest('[data-message-compose]');
                const field = composer ? composer.querySelector('textarea[name="content"]') : null;
                const picker = composer ? composer.querySelector('[data-message-emoji-picker]') : null;
                const emoji = messageEmojiButton.getAttribute('data-message-emoji-value') || '';

                if (field && emoji !== '') {
                    const start = field.selectionStart || field.value.length;
                    const end = field.selectionEnd || field.value.length;
                    const prefix = field.value.slice(0, start);
                    const suffix = field.value.slice(end);
                    field.value = prefix + emoji + suffix;
                    field.focus();
                    const cursor = start + emoji.length;
                    field.setSelectionRange(cursor, cursor);
                }

                if (picker) {
                    picker.classList.add('is-hidden');
                }
                return;
            }

            const fileTrigger = event.target.closest('[data-comment-file-trigger]');
            if (fileTrigger) {
                const composer = fileTrigger.closest('[data-comment-composer]');
                const fileInput = composer ? composer.querySelector('[data-comment-file-input]') : null;
                if (fileInput) {
                    fileInput.accept = fileTrigger.getAttribute('data-comment-file-trigger') === 'gif'
                        ? 'image/gif'
                        : 'image/jpeg,image/png,image/gif,image/webp';
                    fileInput.click();
                }
                return;
            }

            const removePreview = event.target.closest('[data-comment-preview-remove]');
            if (removePreview) {
                const composer = removePreview.closest('[data-comment-composer]');
                const preview = composer ? composer.querySelector('[data-comment-preview]') : null;
                const previewImage = composer ? composer.querySelector('[data-comment-preview-image]') : null;
                const previewName = composer ? composer.querySelector('[data-comment-preview-name]') : null;
                const fileInput = composer ? composer.querySelector('[data-comment-file-input]') : null;

                if (fileInput) {
                    fileInput.value = '';
                }

                if (previewImage) {
                    previewImage.src = '';
                }

                if (previewName) {
                    previewName.textContent = 'No file selected';
                }

                if (preview) {
                    preview.classList.add('is-hidden');
                }
                return;
            }

            const replyTrigger = event.target.closest('[data-reply-target]');
            if (replyTrigger) {
                const targetId = replyTrigger.getAttribute('data-reply-target');
                const form = document.getElementById(targetId);
                if (form) {
                    closeReplyForms(targetId);
                    form.classList.toggle('is-hidden');

                    if (!form.classList.contains('is-hidden')) {
                        const field = form.querySelector('textarea');
                        if (field) {
                            field.focus();
                        }
                    }
                }
                return;
            }

            const messageManageTrigger = event.target.closest('[data-message-manage]');
            if (messageManageTrigger) {
                const modalElement = document.getElementById('messageManageModal');
                if (!modalElement || typeof bootstrap === 'undefined') {
                    return;
                }

                const title = modalElement.querySelector('.message-manage-title');
                const copy = modalElement.querySelector('.message-manage-copy');
                const preview = modalElement.querySelector('[data-message-manage-preview]');
                const unsendForm = modalElement.querySelector('[data-message-unsend-form]');
                const unsendRedirect = modalElement.querySelector('[data-message-unsend-redirect]');
                const removeForm = modalElement.querySelector('[data-message-remove-form]');
                const removeRedirect = modalElement.querySelector('[data-message-remove-redirect]');
                const canUnsend = messageManageTrigger.getAttribute('data-message-can-unsend') === '1';

                if (title) {
                    title.textContent = canUnsend ? 'Unsend Message' : 'Hide Message';
                }

                if (copy) {
                    copy.textContent = canUnsend
                        ? 'Choose whether to unsend this message for everyone or only for yourself.'
                        : 'Choose whether to hide this message from your own chat history.';
                }

                if (preview) {
                    preview.textContent = messageManageTrigger.getAttribute('data-message-preview') || 'Choose how you want to remove this message.';
                }

                if (unsendForm) {
                    unsendForm.action = messageManageTrigger.getAttribute('data-message-unsend-action') || '';
                    unsendForm.classList.toggle('is-hidden', !canUnsend);
                }

                if (unsendRedirect) {
                    unsendRedirect.value = messageManageTrigger.getAttribute('data-message-unsend-redirect') || '';
                }

                if (removeForm) {
                    removeForm.action = messageManageTrigger.getAttribute('data-message-remove-action') || '';
                }

                if (removeRedirect) {
                    removeRedirect.value = messageManageTrigger.getAttribute('data-message-remove-redirect') || '';
                }

                bootstrap.Modal.getOrCreateInstance(modalElement).show();
                return;
            }

            const messageReplyTrigger = event.target.closest('[data-message-reply]');
            if (messageReplyTrigger) {
                const composeForm = document.querySelector('[data-message-compose]');
                const replyPreview = composeForm ? composeForm.querySelector('[data-message-reply-preview]') : null;
                const replyInput = composeForm ? composeForm.querySelector('[data-message-reply-input]') : null;
                const replySender = composeForm ? composeForm.querySelector('[data-message-reply-sender]') : null;
                const replyContent = composeForm ? composeForm.querySelector('[data-message-reply-content]') : null;
                const composeField = composeForm ? composeForm.querySelector('textarea[name="content"]') : null;

                if (composeForm && replyPreview && replyInput && replySender && replyContent) {
                    replyInput.value = messageReplyTrigger.getAttribute('data-message-id') || '';
                    replySender.textContent = messageReplyTrigger.getAttribute('data-message-sender') || 'Replying to';
                    replyContent.textContent = messageReplyTrigger.getAttribute('data-message-content') || '';
                    replyPreview.classList.remove('is-hidden');
                }

                if (composeField) {
                    composeField.focus();
                    composeField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return;
            }

            const messageReplyClear = event.target.closest('[data-message-reply-clear]');
            if (messageReplyClear) {
                const composeForm = document.querySelector('[data-message-compose]');
                const replyPreview = composeForm ? composeForm.querySelector('[data-message-reply-preview]') : null;
                const replyInput = composeForm ? composeForm.querySelector('[data-message-reply-input]') : null;
                const replySender = composeForm ? composeForm.querySelector('[data-message-reply-sender]') : null;
                const replyContent = composeForm ? composeForm.querySelector('[data-message-reply-content]') : null;

                if (replyInput) {
                    replyInput.value = '';
                }

                if (replySender) {
                    replySender.textContent = 'Replying to';
                }

                if (replyContent) {
                    replyContent.textContent = '';
                }

                if (replyPreview) {
                    replyPreview.classList.add('is-hidden');
                }
                return;
            }

            const clickInsideMessageEmojiPicker = event.target.closest('[data-message-emoji-picker]');
            if (!clickInsideMessageEmojiPicker && !event.target.closest('[data-message-emoji-toggle]')) {
                document.querySelectorAll('[data-message-emoji-picker]').forEach(function (picker) {
                    picker.classList.add('is-hidden');
                });
            }

        });

        document.addEventListener('change', function (event) {
            const fileInput = event.target.closest('[data-comment-file-input]');
            if (!fileInput) {
                return;
            }

            const composer = fileInput.closest('[data-comment-composer]');
            const preview = composer ? composer.querySelector('[data-comment-preview]') : null;
            const previewImage = composer ? composer.querySelector('[data-comment-preview-image]') : null;
            const previewName = composer ? composer.querySelector('[data-comment-preview-name]') : null;
            const file = fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;

            if (!file || !preview || !previewImage || !previewName) {
                return;
            }

            previewImage.src = URL.createObjectURL(file);
            previewName.textContent = file.name;
            preview.classList.remove('is-hidden');
        });

        document.addEventListener('DOMContentLoaded', function () {
            const confirmModalElement = document.getElementById('appConfirmModal');
            if (confirmModalElement) {
                confirmModalElement.addEventListener('hidden.bs.modal', function () {
                    if (pendingConfirmForm !== null) {
                        delete pendingConfirmForm.dataset.confirmApproved;
                    }

                    pendingConfirmForm = null;
                });
            }

            const openCommentPanelId = window.sessionStorage.getItem('vybe-open-comment-panel');
            if (openCommentPanelId) {
                openCommentPanelById(openCommentPanelId);
                window.sessionStorage.removeItem('vybe-open-comment-panel');
            }

            const targetId = window.location.hash
                ? window.location.hash.replace('#', '')
                : window.sessionStorage.getItem('vybe-stay-target');

            if (!targetId) {
                return;
            }

            const target = document.getElementById(targetId);
            if (!target) {
                return;
            }

            window.setTimeout(function () {
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                window.sessionStorage.removeItem('vybe-stay-target');
            }, 80);
        });
    </script>
</body>
</html>
