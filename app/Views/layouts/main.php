<?php
/**
 * @var string      $content     rendered view body
 * @var string|null $page_title
 * @var array|null  $auth_user
 * @var array|null  $flash
 * @var string|null $page_scripts
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(($page_title ?? 'GenomePlatform') . ' - GenomePlatform NU') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= url('/assets/css/style.css') ?>">
</head>
<body>
    <?= Core\View::partial('partials/nav', ['auth_user' => $auth_user ?? null]) ?>

    <main>
        <?php $flash_html = Core\View::partial('partials/flash', ['flash' => $flash ?? []]); ?>
        <?php if (trim($flash_html) !== ''): ?>
            <div class="container container--flash"><?= $flash_html ?></div>
        <?php endif; ?>
        <?= $content ?>
    </main>

    <?= Core\View::partial('partials/footer') ?>

    <?= $page_scripts ?? '' ?>
</body>
</html>
