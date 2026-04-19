<?php
/**
 * header.php — Reusable page header + navigation
 * Expects $pageTitle to be set before including.
 */
$pageTitle = $pageTitle ?? 'Vault Breakout';
$user  = $_SESSION['user']  ?? null;
$score = $_SESSION['score'] ?? BASE_SCORE;
$base  = BASE_URL;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?> | Vault Breakout</title>
    <meta name="description" content="Vault Breakout — PHP Escape Room Game. Solve puzzles, unlock the vault, and climb the leaderboard!">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;800;900&family=Inter:wght@300;400;500;600&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
</head>
<body>

<nav class="navbar" role="navigation" aria-label="Main navigation">
    <div class="nav-brand">
        <img src="<?= $base ?>/assets/images/vault_logo.png" alt="Vault Logo" class="nav-logo">
        <a href="<?= $base ?>/" class="brand-name">Vault Breakout</a>
    </div>

    <div class="nav-links">
        <?php if ($user): ?>
            <span class="nav-user"><img src="<?= $base ?>/assets/images/icon_user.png" class="ui-icon" alt="User"> <?= h($user) ?></span>
            <?php if (!empty($_SESSION['game_active'])): ?>
                <span class="nav-score"><img src="<?= $base ?>/assets/images/icon_energy.png" class="ui-icon" alt="Score"> <?= h((string)$score) ?> pts</span>
            <?php endif; ?>
            <a href="<?= $base ?>/leaderboard.php" class="nav-link" id="nav-leaderboard"><img src="<?= $base ?>/assets/images/icon_trophy.png" class="ui-icon" alt="Leaderboard"> Leaderboard</a>
            <a href="<?= $base ?>/logout.php" class="nav-link nav-link--danger" id="nav-logout"><img src="<?= $base ?>/assets/images/icon_door.png" class="ui-icon" alt="Logout"> Logout</a>
        <?php else: ?>
            <a href="<?= $base ?>/login.php"    class="nav-link" id="nav-login">Login</a>
            <a href="<?= $base ?>/register.php" class="nav-link nav-link--cta" id="nav-register">Register</a>
        <?php endif; ?>
    </div>
</nav>

<main class="main-content">
