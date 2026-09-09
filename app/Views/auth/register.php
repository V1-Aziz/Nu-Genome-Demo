<?php /** @var array $old */ ?>
<div class="container form-card">
    <div class="card card--flat">
        <h2 style="justify-content:center; margin-bottom:2rem;">
            <i class="fas fa-user-plus"></i> Create Account
        </h2>

        <form method="POST" action="<?= url('/register') ?>">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus
                       value="<?= e(old($old, 'username')) ?>">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required autocomplete="email"
                       value="<?= e(old($old, 'email')) ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="new-password">
                <p class="form-hint">At least 8 characters.</p>
            </div>

            <div class="form-group">
                <label for="password_confirm">Confirm Password</label>
                <input type="password" id="password_confirm" name="password_confirm" required autocomplete="new-password">
            </div>

            <button type="submit" class="btn btn-secondary btn-block">Create Account</button>
        </form>

        <p style="text-align:center; margin-top:1.5rem;">
            Already have an account? <a href="<?= url('/login') ?>" class="link">Login here</a>
        </p>
    </div>
</div>
