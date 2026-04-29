<section class="login-scene">
    <div class="login-glow login-glow-rose"></div>
    <div class="login-glow login-glow-violet"></div>
    <div class="login-accent-dot"></div>

    <section class="login-card">
        <div class="login-brand">
            <img src="<?php echo app_url('vybe-mark.svg'); ?>" alt="Vybe icon" class="login-brand-mark">
            <div>
                <span class="login-brand-word">vybe</span>
                <p class="login-brand-copy">Connect with anyone. Share genuine memories.</p>
            </div>
        </div>

        <div class="login-copy">
            <span class="eyebrow">Welcome back</span>
            <h1>Log in to rejoin your people.</h1>
            <p>Pick up your conversations, messages, and feed updates right where you left them.</p>
        </div>

        <?php if ($error !== null): ?>
            <div class="alert alert-danger mb-3"><?php echo app_e($error); ?></div>
        <?php endif; ?>

        <form method="POST" class="login-form">
            <input type="hidden" name="_token" value="<?php echo app_csrf_token(); ?>">

            <div>
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control form-control-lg" value="<?php echo app_e(app_old('username')); ?>" required>
            </div>

            <div>
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control form-control-lg" required>
            </div>

            <button type="submit" class="btn btn-accent w-100">Login</button>
        </form>

        <p class="auth-switch mb-0">
            Need an account?
            <a href="<?php echo app_route('Auth', 'register'); ?>">Create one here</a>
        </p>
    </section>
</section>

