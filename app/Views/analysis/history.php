<?php
/** @var array $analyses */
?>
<div class="container">
    <div class="hero">
        <h2><i class="fas fa-history"></i> Your Analysis History</h2>
        <p>Every genomic analysis run on your account</p>
    </div>

    <p style="margin-bottom:1.5rem;">
        <a href="<?= url('/analysis') ?>" class="btn btn-secondary">+ New Analysis</a>
    </p>

    <?php if (!$analyses): ?>
        <div class="card card--flat">
            <p class="empty-state">
                No analyses yet.
                <a href="<?= url('/analysis') ?>" class="link"><strong>Start your first analysis</strong></a>
            </p>
        </div>
    <?php else: ?>
        <div class="card card--flat">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Allele Freq</th>
                            <th>CADD</th>
                            <th>Consequence</th>
                            <th>Rarity</th>
                            <th>Risk Level</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($analyses as $analysis): ?>
                            <tr>
                                <td><small><?= e(date('M d, Y H:i', strtotime($analysis['created_at']))) ?></small></td>
                                <td><?= number_format((float) $analysis['allele_frequency'], 4) ?></td>
                                <td><?= number_format((float) $analysis['cadd_score'], 2) ?></td>
                                <td><?= e($analysis['consequence_type']) ?></td>
                                <td><?= e($analysis['rarity']) ?></td>
                                <td>
                                    <span class="badge" style="background:<?= risk_color($analysis['risk_level']) ?>;">
                                        <?= e($analysis['risk_level']) ?>
                                    </span>
                                </td>
                                <td style="text-align:right;">
                                    <a href="<?= url('/report/' . (int) $analysis['id']) ?>" class="btn btn-primary btn-sm">View Report</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <p style="margin-top:1.5rem; padding-top:1.5rem; border-top:1px solid var(--border); text-align:center; color:var(--muted);">
                <strong>Total analyses:</strong> <?= count($analyses) ?>
            </p>
        </div>
    <?php endif; ?>
</div>
