<?php
/**
 * room.php — Core game puzzle room
 * Handles displaying the puzzle AND processing POST submissions.
 * All game logic is server-side PHP (no JavaScript).
 *
 * SESSION keys used:
 *  $_SESSION['current_room']         int  — which room (1-3)
 *  $_SESSION['score']                int  — current score
 *  $_SESSION['rooms_solved']         int  — rooms completed
 *  $_SESSION['wrong_attempts'][$rid] int  — wrong guesses per room
 *  $_SESSION['hints_used'][$rid]     int  — hints used per room
 *  $_SESSION['hint_ptr'][$rid]       int  — which hint index to show next
 *  $_SESSION['lockout_until'][$rid]  int  — Unix timestamp of hint lockout end
 *  $_SESSION['room_times'][$rid]     int  — Unix timestamp when room was entered
 */
require_once __DIR__ . '/includes/config.php';
requireLogin();
$base = BASE_URL;

// Guard: must have an active game
if (empty($_SESSION['game_active'])) {
    header('Location: ' . BASE_URL . '/');
    exit;
}

global $ROOMS;

$rid    = (int)($_SESSION['current_room'] ?? 1);
$room   = $ROOMS[$rid] ?? null;
if (!$room) {
    // All rooms done
    header('Location: /escape.php');
    exit;
}

// ── Init per-room session keys ──────────────────────────────────
if (!isset($_SESSION['wrong_attempts'][$rid])) $_SESSION['wrong_attempts'][$rid] = 0;
if (!isset($_SESSION['hints_used'][$rid]))     $_SESSION['hints_used'][$rid]     = 0;
if (!isset($_SESSION['hint_ptr'][$rid]))        $_SESSION['hint_ptr'][$rid]       = 0;
if (!isset($_SESSION['lockout_until'][$rid]))   $_SESSION['lockout_until'][$rid]  = 0;
if (!isset($_SESSION['room_times'][$rid]))      $_SESSION['room_times'][$rid]     = time();

// ── Handle POST ─────────────────────────────────────────────────
$feedback      = '';
$feedbackType  = '';
$showHint      = false;
$hintText      = '';
$unlocked      = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── Hint request ──────────────────────────────────────────
    if (isset($_POST['request_hint'])) {

        $now = time();
        // Brute-force lockout check
        if ($_SESSION['lockout_until'][$rid] > $now) {
            $wait = $_SESSION['lockout_until'][$rid] - $now;
            $feedback     = "🔒 Security lockout active — wait {$wait}s before requesting a hint.";
            $feedbackType = 'warning';
        } else {
            // Decrement score and give next hint
            $_SESSION['score'] -= HINT_PENALTY;
            $_SESSION['hints_used'][$rid]++;
            $ptr     = $_SESSION['hint_ptr'][$rid];
            $hints   = $room['hints'];
            $hintText = $hints[min($ptr, count($hints) - 1)];
            $_SESSION['hint_ptr'][$rid] = min($ptr + 1, count($hints) - 1);
            $showHint     = true;
            $feedback     = "💡 Hint used (−" . HINT_PENALTY . " points)";
            $feedbackType = 'info';
        }

    // ── Answer submission ─────────────────────────────────────
    } elseif (isset($_POST['answer'])) {
        $rawAnswer = filter_input(INPUT_POST, 'answer', FILTER_DEFAULT) ?? '';
        $answer    = strtolower(trim(htmlspecialchars($rawAnswer, ENT_QUOTES, 'UTF-8')));
        $correct   = strtolower(trim($room['answer']));

        if ($answer === '') {
            $feedback     = '⚠ Please enter an answer before submitting.';
            $feedbackType = 'warning';

        } elseif ($answer === $correct) {
            // ✅ Correct
            $_SESSION['rooms_solved']++;
            $_SESSION['current_room'] = $rid + 1;
            $unlocked = true;
            $feedback     = '✅ Correct! The door unlocks…';
            $feedbackType = 'success';
            // Brief pause then redirect
        } else {
            // ❌ Wrong
            $_SESSION['score']                -= WRONG_PENALTY;
            $_SESSION['wrong_attempts'][$rid]++;

            // Brute-force detection: lock hint if ≥ threshold wrong attempts
            if ($_SESSION['wrong_attempts'][$rid] >= LOCKOUT_THRESHOLD) {
                $_SESSION['lockout_until'][$rid] = time() + LOCKOUT_SECONDS;
            }

            $meta         = $room['error_meta'] ?? 'check your logic';
            $feedback     = "❌ Incorrect (−" . WRONG_PENALTY . " points). Directional hint: this is a {$meta} problem.";
            $feedbackType = 'error';
        }
    }
}

// ── Clamp score to 0 ────────────────────────────────────────────
$_SESSION['score'] = max(0, (int)$_SESSION['score']);

// ── Computed display values ──────────────────────────────────────
$wrong    = $_SESSION['wrong_attempts'][$rid];
$hintsU   = $_SESSION['hints_used'][$rid];
$score    = $_SESSION['score'];
$pct      = (($rid - 1) / TOTAL_ROOMS) * 100;
$clearPct = clearancePercent();

// Lockout state
$lockedOut    = $_SESSION['lockout_until'][$rid] > time();
$lockRemain   = max(0, $_SESSION['lockout_until'][$rid] - time());

$pageTitle = "Room {$rid} · {$room['title']}";
require_once __DIR__ . '/includes/header.php';
?>

<div class="room-wrap animate-in">
    <div class="room-scene room-scene--<?= $rid ?>"></div>

    <!-- Progress bar -->
    <div class="progress-track">
        <div class="progress-fill" style="width:<?= $pct ?>%"></div>
    </div>

    <!-- Room header -->
    <div class="room-header">
        <div>
            <span class="room-badge">Room <?= $rid ?> / <?= TOTAL_ROOMS ?></span>
            <h1 class="room-title mt-1"><?= $room['icon'] ?> <?= h($room['title']) ?></h1>
        </div>
        <div class="score-hud">
            <span class="hud-chip hud-chip--score"><img src="<?= $base ?>/assets/images/icon_energy.png" class="ui-icon" alt="Score"> <?= h((string)$score) ?> pts</span>
            <span class="hud-chip hud-chip--wrong">✗ <?= $wrong ?> wrong</span>
        </div>
    </div>

    <!-- Security clearance -->
    <div class="clearance-wrap">
        <p class="clearance-label">🛡 Security Clearance — <?= $clearPct ?>%</p>
        <div class="clearance-track">
            <div class="clearance-fill" style="width:<?= $clearPct ?>%"></div>
        </div>
    </div>

    <!-- Main card -->
    <div class="card card--glow <?= $unlocked ? 'door-unlock' : '' ?>">

        <?php if ($feedback): ?>
            <div class="alert alert--<?= h($feedbackType) ?>" role="alert" id="room-feedback">
                <?= h($feedback) ?>
            </div>
        <?php endif; ?>

        <?php if ($showHint && $hintText): ?>
            <div class="hint-box" id="hint-display">
                🔍 <?= h($hintText) ?>
            </div>
        <?php endif; ?>

        <?php if ($unlocked): ?>
            <!-- Door unlocked — auto-redirect via form button -->
            <div class="text-center" style="padding:2rem 0;">
                <img src="<?= $base ?>/assets/images/icon_door.png" style="width:4rem;height:4rem;display:block;margin:0 auto 1rem;animation:pop 0.5s ease;" alt="Unlocked">
                <p style="font-size:1.2rem;color:var(--clr-success);font-family:var(--font-hud);margin-bottom:1.5rem;">
                    ROOM <?= $rid ?> CLEARED
                </p>
                <?php if ($rid < TOTAL_ROOMS): ?>
                    <a href="<?= $base ?>/room.php" class="btn btn--primary" id="btn-next-room">
                        ▶ Enter Room <?= $rid + 1 ?>
                    </a>
                <?php else: ?>
                    <a href="<?= $base ?>/escape.php" class="btn btn--gold" id="btn-escape">
                        🏆 Complete Escape!
                    </a>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- Puzzle form -->
            <p class="room-question"><?= $room['question'] ?></p>
            <p class="room-sub"><?= h($room['sub']) ?></p>

            <form method="POST" action="<?= $base ?>/room.php" id="puzzle-form">
                <div class="form-group">
                    <label class="form-label" for="answer">Your Answer</label>
                    <input
                        id="answer" name="answer" type="text"
                        class="form-input <?= $feedbackType === 'error' ? 'form-input--error' : '' ?>"
                        value="<?= $feedbackType === 'error' ? h(filter_input(INPUT_POST, 'answer', FILTER_DEFAULT) ?? '') : '' ?>"
                        autocomplete="off" required
                        placeholder="Enter your answer…">
                </div>
                <div style="display:flex;gap:0.75rem;flex-wrap:wrap;">
                    <button type="submit" name="submit_answer" class="btn btn--primary" id="btn-submit-answer">
                        <img src="<?= $base ?>/assets/images/icon_door.png" class="ui-icon" alt="Submit"> Submit Answer
                    </button>
                    <?php if (!$lockedOut): ?>
                        <button type="submit" name="request_hint" value="1" class="btn btn--outline btn--sm" id="btn-hint">
                            💡 Use Hint (−<?= HINT_PENALTY ?>pts)
                        </button>
                    <?php else: ?>
                        <button type="button" class="btn btn--outline btn--sm" disabled id="btn-hint-locked">
                            <img src="<?= $base ?>/assets/images/icon_lock.png" class="ui-icon" alt="Locked"> Hint Locked (<?= $lockRemain ?>s)
                        </button>
                    <?php endif; ?>
                </div>
            </form>

            <?php if ($lockedOut): ?>
                <div class="alert alert--warning mt-2" id="lockout-warning">
                    ⚠ Suspicious Activity Detected — hint button locked for <?= $lockRemain ?> seconds.
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Hints used / scoring info -->
    <p class="text-muted text-center mt-2" style="font-size:0.8rem;">
        Hints used this room: <?= $hintsU ?> · Each wrong attempt costs <?= WRONG_PENALTY ?> pts · Each hint costs <?= HINT_PENALTY ?> pts
    </p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
