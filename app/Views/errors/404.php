<div class="container form-card">
    <div class="card card--flat" style="text-align:center; max-width:520px;">
        <h1 style="font-size:4rem; color:var(--primary); margin-bottom:.5rem;">404</h1>
        <h2 style="justify-content:center;">Page Not Found</h2>
        <p style="margin:1rem 0 2rem; color:var(--muted);">
            We couldn't find <code><?= e($path ?? '') ?></code>.
        </p>
        <a href="<?= url('/') ?>" class="btn btn-primary">Back to Home</a>
    </div>
</div>
