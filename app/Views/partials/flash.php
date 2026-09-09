<?php
/** @var array $flash  type => list of messages */

$icons = [
    'error'   => 'fa-exclamation-circle',
    'success' => 'fa-check-circle',
    'info'    => 'fa-info-circle',
];

foreach ($flash as $type => $messages):
    $icon = $icons[$type] ?? 'fa-info-circle';
    foreach ((array) $messages as $message): ?>
        <div class="alert alert-<?= e($type) ?>">
            <i class="fas <?= e($icon) ?>"></i>
            <div><?= e($message) ?></div>
        </div>
    <?php endforeach;
endforeach;
