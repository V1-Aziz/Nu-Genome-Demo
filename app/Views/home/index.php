<?php
/** @var array      $summary */
/** @var array|null $auth_user */
?>
<div class="container">
    <div class="hero">
        <h1><i class="fas fa-microscope"></i> Najran Genomic Analysis Platform</h1>
        <p>Advanced precision medicine for genomic research and analysis</p>

        <?php if ($auth_user): ?>
            <p style="margin-top:1.5rem;">
                <a href="<?= url('/analysis') ?>" class="btn btn-ghost">Run an Analysis</a>
            </p>
        <?php else: ?>
            <p style="margin-top:1.5rem;">
                <a href="<?= url('/register') ?>" class="btn btn-ghost">Get Started</a>
            </p>
        <?php endif; ?>
    </div>

    <div class="grid">
        <div class="card">
            <h2><i class="fas fa-dna"></i> Genomic Analysis</h2>
            <p>Analyse genetic variants against allele frequency and CADD deleteriousness scores to classify pathogenic risk.</p>
        </div>
        <div class="card">
            <h2><i class="fas fa-chart-line"></i> Clear Reporting</h2>
            <p>Every analysis produces a visual report with a pathogenic score, rarity classification and an interpretation guide.</p>
        </div>
        <div class="card">
            <h2><i class="fas fa-shield-alt"></i> Private by Default</h2>
            <p>Results are visible only to the account that created them. Nothing is shared across users.</p>
        </div>
    </div>

    <div class="card card--flat">
        <h2><i class="fas fa-chart-simple"></i> Platform Activity</h2>
        <div class="grid grid-stats grid-stats--narrow" style="margin-top:1rem;">
            <div class="stat">
                <h3 class="stat__value" style="color:var(--primary);"><?= number_format($summary['analyses']) ?></h3>
                <p class="stat__label">Analyses Run</p>
            </div>
            <div class="stat">
                <h3 class="stat__value" style="color:var(--primary-soft);"><?= number_format($summary['researchers']) ?></h3>
                <p class="stat__label">Contributing Accounts</p>
            </div>
        </div>
        <p class="form-hint" style="text-align:center; margin-top:1rem;">
            Aggregate totals only &mdash; individual results are never listed publicly.
        </p>
    </div>
</div>
