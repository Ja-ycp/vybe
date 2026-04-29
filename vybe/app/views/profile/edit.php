<div class="editor-shell">
    <section class="surface-card editor-card">
        <div class="section-heading">
            <h2>Edit Profile</h2>
            <span>Update your public details and keep your account secure</span>
        </div>

        <?php if ($profileError !== null): ?>
            <div class="alert alert-danger mb-3"><?php echo app_e($profileError); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="stack-form">
            <input type="hidden" name="_token" value="<?php echo app_csrf_token(); ?>">
            <input type="hidden" name="form_action" value="profile">

            <div class="profile-preview">
                <div class="avatar avatar-xl">
                    <?php if (!empty($user['profile_image'])): ?>
                        <img class="avatar-image" src="<?php echo app_upload_url($user['profile_image']); ?>" alt="<?php echo app_e($user['full_name']); ?>">
                    <?php else: ?>
                        <span class="avatar-fallback"><?php echo app_e(app_initials($user['full_name'])); ?></span>
                    <?php endif; ?>
                </div>
                <div>
                    <h3 class="mb-1"><?php echo app_e($user['full_name']); ?></h3>
                    <p class="small-muted mb-0">@<?php echo app_e($user['username']); ?></p>
                </div>
            </div>

            <div>
                <label class="form-label">Full Name</label>
                <input type="text" name="full_name" class="form-control form-control-lg" value="<?php echo app_e($user['full_name']); ?>" required>
            </div>

            <div>
                <label class="form-label">Bio</label>
                <textarea name="bio" class="form-control" rows="5" placeholder="Say something about yourself"><?php echo app_e($user['bio']); ?></textarea>
            </div>

            <div>
                <label class="form-label">Profile Photo</label>
                <input type="file" name="profile_image" class="form-control" accept="image/png,image/jpeg,image/gif,image/webp">
            </div>

            <?php if (!empty($user['profile_image'])): ?>
                <div class="form-check danger-check">
                    <input class="form-check-input" type="checkbox" name="remove_image" id="removeProfileImage" value="1">
                    <label class="form-check-label" for="removeProfileImage">Remove current profile photo</label>
                </div>
            <?php endif; ?>

            <div class="editor-actions">
                <button type="submit" class="btn btn-accent">Save Changes</button>
                <a class="btn btn-outline-dark" href="<?php echo app_route('Profile', 'view', ['id' => (int) $_SESSION['user_id']]); ?>">Cancel</a>
            </div>
        </form>

        <div class="editor-divider"></div>

        <section class="security-panel" id="security-card">
            <div class="section-heading">
                <h2>Change Password</h2>
                <span>Confirm your current password before saving a new one</span>
            </div>

            <p class="security-copy">Choose a strong password with at least 8 characters, including letters and numbers, to better protect your account.</p>

            <?php if ($passwordError !== null): ?>
                <div class="alert alert-danger mb-3"><?php echo app_e($passwordError); ?></div>
            <?php endif; ?>

            <form method="POST" class="stack-form">
                <input type="hidden" name="_token" value="<?php echo app_csrf_token(); ?>">
                <input type="hidden" name="form_action" value="password">

                <div>
                    <label class="form-label" for="currentPassword">Current Password</label>
                    <input type="password" name="current_password" id="currentPassword" class="form-control form-control-lg" autocomplete="current-password" required>
                </div>

                <div>
                    <label class="form-label" for="newPassword">New Password</label>
                    <input type="password" name="new_password" id="newPassword" class="form-control form-control-lg" autocomplete="new-password" required>
                    <div class="form-hint">Use 8 to 72 characters with at least one letter and one number.</div>
                </div>

                <div>
                    <label class="form-label" for="confirmPassword">Confirm New Password</label>
                    <input type="password" name="confirm_password" id="confirmPassword" class="form-control form-control-lg" autocomplete="new-password" required>
                </div>

                <div class="security-note">
                    <i class="fa-solid fa-shield-heart"></i>
                    <span>Password updates take effect immediately after you save them.</span>
                </div>

                <div class="editor-actions">
                    <button type="submit" class="btn btn-accent">Update Password</button>
                </div>
            </form>
        </section>

        <div class="editor-divider"></div>

        <section class="danger-panel" id="delete-account-card">
            <div class="section-heading">
                <h2>Delete Account</h2>
                <span>Permanently remove your account and everything attached to it</span>
            </div>

            <p class="danger-copy">Deleting your account permanently removes your profile, posts, comments, likes, messages, and uploaded images. This cannot be undone.</p>

            <?php if ($deleteError !== null): ?>
                <div class="alert alert-danger mb-3"><?php echo app_e($deleteError); ?></div>
            <?php endif; ?>

            <form
                method="POST"
                class="stack-form"
                data-confirm-modal
                data-confirm-title="Delete Account"
                data-confirm-message="This permanently deletes your account, messages, posts, comments, and uploaded media. This action cannot be undone."
                data-confirm-button="Delete Account"
                data-confirm-variant="danger"
            >
                <input type="hidden" name="_token" value="<?php echo app_csrf_token(); ?>">
                <input type="hidden" name="form_action" value="delete_account">

                <div>
                    <label class="form-label" for="deletePassword">Current Password</label>
                    <input type="password" name="delete_password" id="deletePassword" class="form-control form-control-lg" autocomplete="current-password" required>
                </div>

                <div>
                    <label class="form-label" for="deleteConfirmation">Type CONFIRM DELETION to confirm</label>
                    <input
                        type="text"
                        name="delete_confirmation"
                        id="deleteConfirmation"
                        class="form-control form-control-lg danger-confirm-input"
                        placeholder="CONFIRM DELETION"
                        autocomplete="off"
                        autocapitalize="characters"
                        spellcheck="false"
                        required
                    >
                    <div class="form-hint">This exact phrase is required before your account can be deleted.</div>
                </div>

                <div class="danger-note">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>This action is permanent and immediately signs you out after deletion.</span>
                </div>

                <div class="editor-actions">
                    <button type="submit" class="btn btn-outline-danger">Delete Account Permanently</button>
                </div>
            </form>
        </section>
    </section>
</div>

