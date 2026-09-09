<div class="container form-card">
    <div class="card card--flat">
        <h2 style="justify-content:center; margin-bottom:2rem;">
            <i class="fas fa-sign-in-alt"></i> Login
        </h2>

        <form method="POST" action="<?= url('/login') ?>">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required autofocus autocomplete="email">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn btn-secondary btn-block">Login</button>
        </form>

        <p style="text-align:center; margin-top:1.5rem;">
            Don't have an account? <a href="<?= url('/register') ?>" class="link">Register here</a>
        </p>
    </div>
</div>
