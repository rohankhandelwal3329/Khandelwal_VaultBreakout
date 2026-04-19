<?php
/**
 * escape.php — Final escape / results screen
 * Shows score, player profile, and saves to leaderboard.
 */
require_once __DIR__ . '/includes/config.php';
requireLogin();
$base = BASE_URL;

// Guard: must have completed all rooms
if (empty($_SESSION['game_active']) || ($_SESSION['rooms_solved'] ?? 0) < TOTAL_ROOMS) {
    header('Location: ' . BASE_URL . '/');
    exit;
}

$user       = $_SESSION['user'];
$score      = (int)($_SESSION['score'] ?? 0);
$startTime  = (int)($_SESSION['start_time'] ?? time());
$elapsed    = time() - $startTime;
$minutes    = floor($elapsed / 60);
$seconds    = $elapsed % 60;
$totalWrong = array_sum($_SESSION['wrong_attempts'] ?? []);
$totalHints = array_sum($_SESSION['hints_used'] ?? []);
$clearPct   = clearancePercent();

// ── Player profile classification ─────────────────────────────
if ($totalHints === 0 && $elapsed < 120) {
    $profile = ['label' => 'Speed Runner', 'icon' => '<img src="' . $base . '/assets/images/icon_energy.png" class="ui-icon" alt="Elite">', 'desc' => 'Blazing fast with zero hints — true elite.'];
} elseif ($totalHints === 0) {
    $profile = ['label' => 'Methodical Solver', 'icon' => '🧠', 'desc' => 'Systematic and hint-free. Pure logic.'];
} else {
    $profile = ['label' => 'Hint-Reliant', 'icon' => '💡', 'desc' => 'Used hints to guide the escape path.'];
}

// ── Save to leaderboard (flat file) ───────────────────────────
// Format: username|score|time_seconds|date
$entry = implode('|', [
    $user,
    $score,
    $elapsed,
    date('Y-m-d H:i'),
]) . PHP_EOL;
file_put_contents(LEADERBOARD_FILE, $entry, FILE_APPEND | LOCK_EX);

// Mark game as done
$_SESSION['game_active'] = false;

$pageTitle = 'Escaped!';
require_once __DIR__ . '/includes/header.php';
?>

<div class="escape-wrap animate-in">
    <img src="<?= $base ?>/assets/images/escape_success.png" alt="Vault Escaped" class="escape-banner">
    <h1 class="escape-title">VAULT ESCAPED!</h1>
    <p class="escape-sub">
        Agent <strong><?= h($user) ?></strong>, you have successfully broken out of all
        <?= TOTAL_ROOMS ?> rooms. Your performance has been recorded.
    </p>

    <div class="escape-score animate-pop"><?= number_format($score) ?></div>
    <p class="text-muted" style="font-size:0.85rem;margin-bottom:2rem;">FINAL SCORE</p>

    <!-- Post-game profile card -->
    <div class="profile-card">
        <h3>🎯 Player Profile — "<?= h($profile['label']) ?>" <?= $profile['icon'] ?></h3>
        <p class="text-muted" style="font-size:0.88rem;margin-bottom:1rem;"><?= h($profile['desc']) ?></p>

        <div class="profile-stat">
            <span>⏱ Total Time</span>
            <span><?= $minutes ?>m <?= $seconds ?>s</span>
        </div>
        <div class="profile-stat">
            <span>✗ Wrong Attempts</span>
            <span><?= $totalWrong ?></span>
        </div>
        <div class="profile-stat">
            <span>💡 Hints Used</span>
            <span><?= $totalHints ?></span>
        </div>
        <div class="profile-stat">
            <span>⚡ Score Penalties</span>
            <span>−<?= ($totalWrong * WRONG_PENALTY) + ($totalHints * HINT_PENALTY) ?> pts</span>
        </div>
        <div class="profile-stat">
            <span>🛡 Security Clearance</span>
            <span><?= $clearPct ?>%</span>
        </div>

        <?php
        // Performance summary
        $summary = "Agent {$user} completed the vault in {$minutes}m {$seconds}s "
            . "with {$totalWrong} wrong attempt(s) and {$totalHints} hint(s) used. "
            . "Profile: {$profile['label']}. "
            . ($totalHints === 0 ? "Impressive — no hints needed!" : "Consider revisiting cipher logic before the next run.");
        ?>
        <p style="margin-top:1rem;font-size:0.82rem;color:var(--clr-muted);border-top:1px solid rgba(255,255,255,0.07);padding-top:1rem;">
            📊 <em><?= h($summary) ?></em>
        </p>
    </div>

    <div style="display:flex;gap:1rem;flex-wrap:wrap;justify-content:center;margin-top:2rem;">
        <a href="<?= $base ?>/leaderboard.php" class="btn btn--gold" id="btn-view-leaderboard"><img src="<?= $base ?>/assets/images/icon_trophy.png" class="ui-icon" alt="Leaderboard"> View Leaderboard</a>
        <a href="<?= $base ?>/start.php"       class="btn btn--outline" id="btn-play-again">🔄 Play Again</a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
