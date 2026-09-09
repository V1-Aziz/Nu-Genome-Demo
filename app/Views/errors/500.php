<div class="container form-card">
    <div class="card card--flat" style="text-align:center; max-width:640px;">
        <h1 style="font-size:4rem; color:var(--warning); margin-bottom:.5rem;">500</h1>
        <h2 style="justify-content:center;">Something Went Wrong</h2>
        <p style="margin:1rem 0; color:var(--muted);">
            The request could not be completed. The details have been written to the application log.
        </p>

        <?php if (!empty($message)): ?>
            <div class="alert alert-error" style="text-align:left; margin-top:1.5rem;">
                <i class="fas fa-bug"></i>
                <div><code><?= e($message) ?></code></div>
            </div>
            <p class="form-hint">Shown because <code>app.debug</code> is enabled in <code>config/config.php</code>.</p>
        <?php endif; ?>

        <p style="margin-top:2rem;">
            <a href="<?= url('/') ?>" class="btn btn-primary">Back to Home</a>
        </p>
    </div>
</div>
