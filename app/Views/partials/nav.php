<?php
/** @var array|null $auth_user */

$current = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

$links = [
    '/'             => 'Home',
    '/how-it-works' => 'How It Works',
];

if ($auth_user) {
    $links += [
        '/dashboard' => 'Dashboard',
        '/analysis'  => 'Analysis',
        '/results'   => 'Results',
    ];
}
?>
<header>
    <div class="navbar">
        <a class="logo" href="<?= url('/') ?>">
            <i class="fas fa-dna"></i> GenomePlatform
        </a>

        <nav>
            <ul class="nav-links">
                <?php foreach ($links as $href => $label): ?>
                    <li>
                        <a href="<?= url($href) ?>"<?= $current === $href ? ' class="active"' : '' ?>>
                            <?= e($label) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <div class="user-menu">
            <?php if ($auth_user): ?>
                <span><i class="fas fa-user-circle"></i> <?= e($auth_user['username']) ?></span>
                <a href="<?= url('/logout') ?>" class="btn btn-ghost btn-sm">Logout</a>
            <?php else: ?>
                <a href="<?= url('/login') ?>" class="btn btn-ghost btn-sm">Login</a>
                <a href="<?= url('/register') ?>" class="btn btn-secondary btn-sm">Register</a>
            <?php endif; ?>
        </div>
    </div>
</header>
