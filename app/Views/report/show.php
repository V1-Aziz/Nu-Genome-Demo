<?php
/** @var array $analysis */

$risk      = (string) $analysis['risk_level'];
$riskHex   = risk_hex($risk);
$chartData = [
    'pathogenic' => round((float) $analysis['pathogenic_score'], 2),
    'cadd'       => round((float) $analysis['cadd_score'], 2),
    'maxCadd'    => 60,
    'riskColor'  => $riskHex,
];
?>
<div class="container">
    <p style="margin-bottom:1.5rem;">
        <a href="<?= url('/results') ?>" class="link"><i class="fas fa-arrow-left"></i> Back to results</a>
    </p>

    <div class="alert alert-banner" style="background:<?= e($riskHex) ?>;">
        <i class="fas fa-shield-alt"></i>
        <div>
            <h2>Risk Level: <?= e($risk) ?></h2>
            <p>
                This variant has a <?= e(strtolower($risk)) ?> risk of being pathogenic,
                based on its CADD score of <?= number_format((float) $analysis['cadd_score'], 2) ?>.
            </p>
        </div>
    </div>

    <div class="card card--flat">
        <h2><i class="fas fa-chart-pie"></i> Visual Summary</h2>

        <div class="grid grid-2" style="align-items:center; margin-top:1rem;">
            <div style="text-align:center;">
                <h4 style="margin-bottom:1rem;">Pathogenic Score</h4>
                <div class="gauge">
                    <canvas id="pathogenicChart" width="200" height="200"></canvas>
                    <div class="gauge__value" style="color:<?= e($riskHex) ?>;">
                        <?= round((float) $analysis['pathogenic_score']) ?>%
                    </div>
                </div>
            </div>

            <div>
                <h4 style="margin-bottom:1rem;">CADD Score Against Scale</h4>
                <div style="height:150px; width:100%;">
                    <canvas id="caddChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-2">
        <div class="card card--flat">
            <h3><i class="fas fa-file-alt"></i> Detailed Information</h3>

            <table class="table-plain" style="margin-top:1.5rem;">
                <tr><td>Report ID:</td><td>#<?= (int) $analysis['id'] ?></td></tr>
                <tr><td>Date:</td><td><?= e(date('M d, Y H:i', strtotime($analysis['created_at']))) ?></td></tr>
                <tr><td>Allele Frequency:</td><td><?= number_format((float) $analysis['allele_frequency'], 4) ?></td></tr>
                <tr><td>CADD Score:</td><td><?= number_format((float) $analysis['cadd_score'], 2) ?></td></tr>
                <tr><td>Consequence Type:</td><td><?= e($analysis['consequence_type']) ?></td></tr>
                <tr><td>Rarity:</td><td><?= e($analysis['rarity']) ?></td></tr>
                <tr>
                    <td>Pathogenic Score:</td>
                    <td><?= number_format((float) $analysis['pathogenic_score'], 1) ?>%</td>
                </tr>
                <tr>
                    <td>Risk Level:</td>
                    <td><span class="badge" style="background:<?= e($riskHex) ?>;"><?= e($risk) ?></span></td>
                </tr>
                <?php if (!empty($analysis['notes'])): ?>
                    <tr>
                        <td style="vertical-align:top;">Notes:</td>
                        <td style="white-space:pre-wrap;"><?= e($analysis['notes']) ?></td>
                    </tr>
                <?php endif; ?>
            </table>

            <div style="margin-top:2rem; display:flex; gap:1rem; flex-wrap:wrap;">
                <a href="<?= url('/analysis') ?>" class="btn btn-secondary">Run New Analysis</a>
                <a href="<?= url('/results') ?>" class="btn btn-primary">View All</a>
            </div>
        </div>

        <?= Core\View::partial('partials/interpretation') ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    var d = <?= json_encode($chartData, JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_AMP) ?>;

    new Chart(document.getElementById('pathogenicChart'), {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [d.pathogenic, 100 - d.pathogenic],
                backgroundColor: [d.riskColor, 'rgba(0,0,0,.06)'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            cutout: '80%',
            plugins: { legend: { display: false }, tooltip: { enabled: false } }
        }
    });

    var caddCtx = document.getElementById('caddChart').getContext('2d');
    var gradient = caddCtx.createLinearGradient(0, 0, 600, 0);
    gradient.addColorStop(0, '#047857');
    gradient.addColorStop(0.5, '#c2410c');
    gradient.addColorStop(1, '#b91c1c');

    new Chart(caddCtx, {
        type: 'bar',
        data: {
            labels: [''],
            datasets: [{ label: 'CADD Score', data: [d.cadd], backgroundColor: gradient, borderRadius: 5, barPercentage: 0.5 }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { beginAtZero: true, max: d.maxCadd, title: { display: true, text: 'CADD Score (0-' + d.maxCadd + ')' } },
                y: { grid: { display: false } }
            },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: function (c) { return ' CADD Score: ' + c.raw.toFixed(2); } } }
            }
        }
    });
})();
</script>
