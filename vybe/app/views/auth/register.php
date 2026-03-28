<section class="login-scene register-scene">
    <div class="login-glow login-glow-rose"></div>
    <div class="login-glow login-glow-violet"></div>
    <div class="login-accent-dot"></div>
    <div class="login-accent-dot login-accent-dot-secondary"></div>

    <section class="login-card register-card">
        <div class="login-brand">
            <img src="<?php echo app_url('vybe-mark.svg'); ?>" alt="Vybe icon" class="login-brand-mark">
            <div>
                <span class="login-brand-word">vybe</span>
                <p class="login-brand-copy">Connect with anyone. Share genuine memories.</p>
            </div>
        </div>

        <div class="login-copy">
            <span class="eyebrow">Create account</span>
            <h1>Start your space on Vybe.</h1>
            <p>Set up your profile, pick a username, and jump into your feed, messages, and moments.</p>
        </div>

        <?php if ($error !== null): ?>
            <div class="alert alert-danger mb-3"><?php echo app_e($error); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="login-form register-form">
            <input type="hidden" name="_token" value="<?php echo app_csrf_token(); ?>">

            <div>
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control form-control-lg" value="<?php echo app_e(app_old('username')); ?>" required>
            </div>

            <div>
                <label class="form-label">Full Name</label>
                <input type="text" name="full_name" class="form-control form-control-lg" value="<?php echo app_e(app_old('full_name')); ?>" required>
            </div>

            <div>
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control form-control-lg" minlength="6" required>
            </div>

            <div>
                <label class="form-label">Bio</label>
                <textarea name="bio" class="form-control" rows="4" placeholder="Tell people a little about yourself"><?php echo app_e(app_old('bio')); ?></textarea>
            </div>

            <div>
                <label class="form-label">Profile Photo (Optional)</label>
                <input type="file" name="profile_image" class="form-control" accept="image/png,image/jpeg,image/gif,image/webp">
            </div>

            <button type="submit" class="btn btn-accent w-100">Create Account</button>
        </form>

        <p class="auth-switch mb-0">
            Already registered?
            <a href="<?php echo app_route('Auth', 'login'); ?>">Log in here</a>
        </p>
    </section>
</section>

