<?php
/** @var array $user */
/** @var array $stats */
/** @var array $recent */
?>
<div class="container">
    <div class="hero">
        <h2>Welcome, <?= e($user['username']) ?></h2>
        <p>Your genomic analysis dashboard</p>
    </div>

    <div class="grid grid-stats">
        <div class="card stat">
            <h3 class="stat__value" style="color:var(--primary);"><?= number_format($stats['total']) ?></h3>
            <p class="stat__label">Total Analyses</p>
        </div>

        <div class="card stat">
            <h3 class="stat__value" style="color:var(--primary-soft);">
                <?= $stats['avg_pathogenic'] === null ? '&mdash;' : number_format($stats['avg_pathogenic'], 1) . '%' ?>
            </h3>
            <p class="stat__label">Avg Pathogenic Score</p>
        </div>

        <div class="card stat">
            <h3 class="stat__value" style="color:<?= $stats['high_risk'] > 0 ? 'var(--danger)' : 'var(--muted)' ?>;"><?= number_format($stats['high_risk']) ?></h3>
            <p class="stat__label">High Risk Variants</p>
        </div>

        <div class="card stat">
            <h3 class="stat__value" style="color:var(--accent); text-transform:capitalize;"><?= e($user['role']) ?></h3>
            <p class="stat__label">Account Type</p>
        </div>
    </div>

    <div class="card card--flat">
        <h2><i class="fas fa-history"></i> Recent Analyses</h2>

        <?php if (!$recent): ?>
            <p class="empty-state">
                No analyses yet.
                <a href="<?= url('/analysis') ?>" class="link"><strong>Start one now</strong></a>
            </p>
        <?php else: ?>
            <div class="table-wrap" style="margin-top:1rem;">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Consequence</th>
                            <th>Risk Level</th>
                            <th style="text-align:right;">Report</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent as $analysis): ?>
                            <tr>
                                <td><small><?= e(date('M d, Y H:i', strtotime($analysis['created_at']))) ?></small></td>
                                <td><?= e($analysis['consequence_type']) ?></td>
                                <td>
                                    <span style="font-weight:700; color:<?= risk_color($analysis['risk_level']) ?>;">
                                        <?= e($analysis['risk_level']) ?>
                                    </span>
                                </td>
                                <td style="text-align:right;">
                                    <a href="<?= url('/report/' . (int) $analysis['id']) ?>" class="btn btn-primary btn-sm">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <p style="margin-top:1.5rem;">
                <a href="<?= url('/results') ?>" class="link">View full history &rarr;</a>
            </p>
        <?php endif; ?>
    </div>
</div>
