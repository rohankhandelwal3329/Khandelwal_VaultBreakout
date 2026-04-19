<?php
/**
 * index.php — Landing / Home page
 * Shows hero section and game room status for logged-in users.
 */
require_once __DIR__ . '/includes/config.php';

$pageTitle = 'Home';
$user = $_SESSION['user'] ?? null;
$solved = $_SESSION['rooms_solved'] ?? 0;
$base = BASE_URL;

require_once __DIR__ . '/includes/header.php';
?>

<?php if (!$user): ?>
<!-- ═══ HERO — not logged in ═══ -->
<section class="hero animate-in">
    <img src="<?= $base ?>/assets/images/vault_hero.png" alt="Vault Background" class="hero__bg" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; filter: brightness(0.6) saturate(1); z-index: 0;">
    <div class="hero__overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(180deg, rgba(6,8,15,0.1) 0%, rgba(6,8,15,0.4) 60%, rgba(6,8,15,0.9) 100%); z-index: 1;"></div>
    <p class="hero__kicker">🔐 PHP Escape Room · CSC 4370</p>
    <h1 class="hero__title">Vault Breakout</h1>
    <p class="hero__sub">
        Solve cryptic puzzles, decode ciphers, and crack the pattern lock
        before the vault seals forever. Your score depends on speed and skill.
    </p>
    <div class="hero__cta-group">
        <a href="<?= $base ?>/register.php" class="btn btn--primary" id="hero-register">🚀 Start Playing</a>
        <a href="<?= $base ?>/login.php"    class="btn btn--outline"  id="hero-login">🔑 Log In</a>
    </div>
</section>

<!-- Feature cards -->
<section style="max-width:900px;margin:0 auto 4rem;padding:0 1rem;">
    <div class="rooms-grid">
        <div class="room-card animate-in">
            <img src="<?= $base ?>/assets/images/room1_bg.png" alt="Room 1" class="room-card__thumb">
            <p class="room-card__name">Cipher Chamber</p>
            <p class="room-card__tag">Math Puzzle · Room 1</p>
        </div>
        <div class="room-card animate-in" style="animation-delay:0.1s">
            <img src="<?= $base ?>/assets/images/room2_bg.png" alt="Room 2" class="room-card__thumb">
            <p class="room-card__name">Symbol Vault</p>
            <p class="room-card__tag">Caesar Cipher · Room 2</p>
        </div>
        <div class="room-card animate-in" style="animation-delay:0.2s">
            <img src="<?= $base ?>/assets/images/room3_bg.png" alt="Room 3" class="room-card__thumb">
            <p class="room-card__name">Pattern Lock</p>
            <p class="room-card__tag">Sequence · Room 3</p>
        </div>
    </div>
</section>

<?php else: ?>
<!-- ═══ DASHBOARD — logged in ═══ -->
<div style="max-width:780px;margin:2rem auto;padding:0 1rem;">
    <div class="animate-in">
        <p class="hero__kicker">Welcome back, <?= h($user) ?> <img src="<?= $base ?>/assets/images/icon_user.png" class="ui-icon" alt="User"></p>
        <h1 class="room-title" style="font-size:2rem;margin-bottom:0.5rem;">Mission Control</h1>
        <p class="text-muted" style="margin-bottom:1.5rem;">
            <?php if ($_SESSION['game_active'] ?? false): ?>
                Game in progress — <?= TOTAL_ROOMS - $solved ?> room(s) remaining.
            <?php else: ?>
                Ready to break the vault? Start a new run below.
            <?php endif; ?>
        </p>
    </div>

    <!-- Score HUD -->
    <div class="score-hud animate-in">
        <span class="hud-chip hud-chip--score"><img src="<?= $base ?>/assets/images/icon_energy.png" class="ui-icon" alt="Score"> Score: <?= h((string)($_SESSION['score'] ?? BASE_SCORE)) ?></span>
        <span class="hud-chip hud-chip--room"><img src="<?= $base ?>/assets/images/icon_door.png" class="ui-icon" alt="Rooms"> Rooms Solved: <?= $solved ?>/<?= TOTAL_ROOMS ?></span>
    </div>

    <!-- Progress -->
    <?php $pct = ($solved / TOTAL_ROOMS) * 100; ?>
    <div class="progress-track mb-2">
        <div class="progress-fill" style="width:<?= $pct ?>%"></div>
    </div>

    <!-- Room status grid -->
    <div class="rooms-grid animate-in">
        <?php
        global $ROOMS;
        foreach ($ROOMS as $num => $room):
            $isSolved = $num <= $solved;
            $isNext   = $num === ($solved + 1);
            $isLocked = $num > ($solved + 1);
            $cls = $isSolved ? 'room-card--solved' : ($isLocked ? 'room-card--locked' : '');
            $tag = $isSolved ? '<img src="' . $base . '/assets/images/icon_door.png" class="ui-icon" alt="Solved"> Solved' : ($isNext ? '<img src="' . $base . '/assets/images/icon_energy.png" class="ui-icon" alt="Next"> Up Next' : '<img src="' . $base . '/assets/images/icon_lock.png" class="ui-icon" alt="Locked"> Locked');
        ?>
        <div class="room-card <?= $cls ?>" style="animation-delay:<?= ($num-1)*0.08 ?>s">
            <img src="<?= $base ?>/assets/images/room<?= $num ?>_bg.png" alt="Room <?= $num ?>" class="room-card__thumb">
            <p class="room-card__name">Room <?= $num ?> · <?= h($room['title']) ?></p>
            <p class="room-card__tag"><?= $tag ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- CTA -->
    <div class="text-center mt-4">
        <?php if (($solved >= TOTAL_ROOMS) && ($_SESSION['game_active'] ?? false)): ?>
            <a href="<?= $base ?>/escape.php" class="btn btn--gold"><img src="<?= $base ?>/assets/images/icon_trophy.png" class="ui-icon" alt="Results"> View Results</a>
        <?php elseif ($_SESSION['game_active'] ?? false): ?>
            <a href="<?= $base ?>/room.php" class="btn btn--primary">▶ Continue Game</a>
        <?php else: ?>
            <a href="<?= $base ?>/start.php" class="btn btn--primary" id="btn-start-game">🚀 Start New Game</a>
        <?php endif; ?>
        <a href="<?= $base ?>/leaderboard.php" class="btn btn--outline mt-2" style="margin-left:0.75rem"><img src="<?= $base ?>/assets/images/icon_trophy.png" class="ui-icon" alt="Leaderboard"> Leaderboard</a>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
