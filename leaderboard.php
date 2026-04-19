<?php
/**
 * leaderboard.php — Top scores display
 * Reads from data/leaderboard.txt, sorts, shows top 10.
 * Accessible without re-login (uses $_SESSION cookie check for highlight).
 */
require_once __DIR__ . '/includes/config.php';
$base = BASE_URL;

$currentUser = $_SESSION['user'] ?? ($_COOKIE['vb_user'] ?? null);

// ── Load and parse leaderboard ─────────────────────────────────
$entries = [];
if (file_exists(LEADERBOARD_FILE)) {
    foreach (file(LEADERBOARD_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $parts = explode('|', $line);
        if (count($parts) >= 3) {
            $entries[] = [
                'user'  => h($parts[0]),
                'score' => (int)$parts[1],
                'time'  => (int)$parts[2],
                'date'  => h($parts[3] ?? ''),
            ];
        }
    }
}

// Sort by score DESC, then by time ASC (faster = better tie-break)
usort($entries, fn($a, $b) => $b['score'] <=> $a['score'] ?: $a['time'] <=> $b['time']);
$top = array_slice($entries, 0, 10);

// Medal emojis
$medals = ['🥇','🥈','🥉'];

$pageTitle = 'Leaderboard';
require_once __DIR__ . '/includes/header.php';
?>

<div class="leaderboard-wrap animate-in">
    <h1 class="lb-title"><img src="<?= $base ?>/assets/images/icon_trophy.png" class="ui-icon" style="width:1.2em;height:1.2em;" alt="Trophy"> Leaderboard</h1>
    <p class="lb-sub">Top vault breakers — ranked by score (ties broken by fastest completion time)</p>

    <div class="card card--gold">
        <?php if (empty($top)): ?>
            <p class="lb-empty">No scores yet — be the first to escape the vault!</p>
        <?php else: ?>
            <table class="lb-table" id="leaderboard-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Agent</th>
                        <th>Score</th>
                        <th>Time</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($top as $i => $e):
                        $rank    = $i + 1;
                        $rankCls = $rank <= 3 ? "rank-{$rank}" : '';
                        $isSelf  = ($currentUser && strtolower($e['user']) === strtolower($currentUser));
                        $mins = floor($e['time'] / 60);
                        $secs = $e['time'] % 60;
                    ?>
                    <tr class="<?= $rankCls ?>" <?= $isSelf ? 'style="background:rgba(0,212,255,0.07);"' : '' ?>>
                        <td>
                            <?php if ($rank <= 3): ?>
                                <span class="rank-medal"><?= $medals[$rank-1] ?></span>
                            <?php else: ?>
                                <span class="mono">#<?= $rank ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= $e['user'] ?> <?= $isSelf ? '<span style="font-size:0.7rem;color:var(--clr-glow);">(you)</span>' : '' ?></td>
                        <td class="lb-score"><?= number_format($e['score']) ?></td>
                        <td class="mono" style="font-size:0.85rem;"><?= $mins ?>m <?= $secs ?>s</td>
                        <td class="text-muted" style="font-size:0.8rem;"><?= $e['date'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="text-center mt-4">
        <?php if (!empty($_SESSION['user'])): ?>
            <a href="<?= $base ?>/start.php" class="btn btn--primary" id="btn-play-again-lb">🚀 Play Again</a>
        <?php else: ?>
            <a href="<?= $base ?>/login.php" class="btn btn--primary" id="btn-login-lb"><img src="<?= $base ?>/assets/images/icon_lock.png" class="ui-icon" alt="Login"> Login to Play</a>
        <?php endif; ?>
        <a href="<?= $base ?>/" class="btn btn--outline" style="margin-left:0.75rem" id="btn-home-lb"><img src="<?= $base ?>/assets/images/icon_door.png" class="ui-icon" alt="Home"> Home</a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
