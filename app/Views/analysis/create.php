<?php
/** @var array $consequences */
/** @var array $old */
?>
<div class="container">
    <div class="hero">
        <h2><i class="fas fa-microscope"></i> Najran Genomic Analysis Tool</h2>
        <p>Analyse genetic variants and predict pathogenic effects</p>
    </div>

    <div class="grid grid-2">
        <div class="card card--flat">
            <h3><i class="fas fa-sliders"></i> Analysis Parameters</h3>

            <form method="POST" action="<?= url('/analysis') ?>" style="margin-top:1.5rem;">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="allele_freq">Allele Frequency (0&ndash;1)</label>
                    <input type="number" id="allele_freq" name="allele_freq"
                           min="0" max="1" step="0.0001" required
                           value="<?= e(old($old, 'allele_freq')) ?>">
                    <p class="form-hint">How common the variant is in the population.</p>
                </div>

                <div class="form-group">
                    <label for="cadd_score">CADD Score (0&ndash;60)</label>
                    <input type="number" id="cadd_score" name="cadd_score"
                           min="0" max="60" step="0.1" required
                           value="<?= e(old($old, 'cadd_score')) ?>">
                    <p class="form-hint">Higher values predict a more deleterious variant.</p>
                </div>

                <div class="form-group">
                    <label for="consequence">Consequence Type</label>
                    <select id="consequence" name="consequence" required>
                        <option value="">Select&hellip;</option>
                        <?php foreach ($consequences as $option): ?>
                            <option value="<?= e($option) ?>"<?= old($old, 'consequence') === $option ? ' selected' : '' ?>>
                                <?= e($option) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="notes">Notes (optional)</label>
                    <textarea id="notes" name="notes" rows="3"
                              placeholder="Add any additional information&hellip;"><?= e(old($old, 'notes')) ?></textarea>
                </div>

                <button type="submit" class="btn btn-secondary btn-block">Run Analysis</button>
            </form>
        </div>

        <?= Core\View::partial('partials/interpretation') ?>
    </div>
</div>
