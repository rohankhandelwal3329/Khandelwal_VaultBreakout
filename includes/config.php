<?php
/**
 * config.php — Global configuration for Vault Breakout
 * CSC 4370 | Group Project 2 | Rohan Khandelwal & Mohit Kokane
 */

// Start session on every page that includes this file
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Base URL (works in any XAMPP subdirectory) ───────────────────────────────
// Resolves to e.g. /Khandelwal_VaultBreakout  from any page or include
if (!defined('BASE_URL')) {
    // On shared servers like CODD, DOCUMENT_ROOT doesn't match the ~user directory path.
    // Since all our PHP pages are in the same main folder, we can just use relative paths everywhere!
    define('BASE_URL', '.');
}

// ── Game constants ──────────────────────────────────────────────────────────
define('BASE_SCORE',        1000);   // Starting score each run
define('WRONG_PENALTY',     50);     // Points deducted per wrong answer
define('HINT_PENALTY',      100);    // Points deducted per hint used
define('TOTAL_ROOMS',       3);      // Number of puzzle rooms
define('LOCKOUT_THRESHOLD', 3);      // Wrong attempts before brute-force lock
define('LOCKOUT_SECONDS',   30);     // Seconds the hint button is locked

// ── File paths ──────────────────────────────────────────────────────────────
define('USERS_FILE',      __DIR__ . '/../data/users.txt');
define('LEADERBOARD_FILE',__DIR__ . '/../data/leaderboard.txt');

// ── Puzzle definitions ──────────────────────────────────────────────────────
// Each room: question, answer (lowercase trimmed), type, hints (3 tiers), feedback type
$ROOMS = [
    1 => [
        'title'     => 'The Cipher Chamber',
        'type'      => 'math',
        'question'  => 'Decode the vault sequence: <strong>3 × 7 + 6 ÷ 2 = ?</strong>',
        'sub'       => 'Enter the numeric answer to unlock the first door.',
        'answer'    => '24',
        'hints'     => [
            'Follow standard order of operations (PEMDAS).',
            'Multiplication and division come before addition.',
            'Compute 3×7=21 and 6÷2=3 separately, then add.',
        ],
        'error_meta'=> 'numeric — check your order of operations',
        'icon'      => '🔢',
    ],
    2 => [
        'title'     => 'The Symbol Vault',
        'type'      => 'cipher',
        'question'  => 'A Caesar cipher (shift 3) was used to lock this code: <strong>RSHQ</strong><br>Decode the hidden word.',
        'sub'       => 'Shift each letter back by 3 positions in the alphabet.',
        'answer'    => 'open',
        'hints'     => [
            'A Caesar cipher shifts letters by a fixed number.',
            'To decode, shift each letter BACK by the cipher amount.',
            'R→O, S→P, H→E, Q→N',
        ],
        'error_meta'=> 'direction — make sure you are shifting backward, not forward',
        'icon'      => '🔤',
    ],
    3 => [
        'title'     => 'The Pattern Lock',
        'type'      => 'pattern',
        'question'  => 'Complete the number pattern: <strong>2, 4, 8, 16, ?</strong>',
        'sub'       => 'Find the next number in the sequence and enter it.',
        'answer'    => '32',
        'hints'     => [
            'Look at what operation connects each term.',
            'Each number is double the previous one.',
            '16 × 2 = ?',
        ],
        'error_meta'=> 'pattern type — this is a geometric sequence, not arithmetic',
        'icon'      => '🔷',
    ],
];

// ── Helper: require login ───────────────────────────────────────────────────
function requireLogin(): void {
    if (empty($_SESSION['user'])) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

// ── Helper: sanitize output ─────────────────────────────────────────────────
function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ── Helper: sanitize input ──────────────────────────────────────────────────
function clean(string $s): string {
    return trim(htmlspecialchars(strip_tags($s)));
}

// ── Helper: compute security clearance % ───────────────────────────────────
function clearancePercent(): int {
    $correct = $_SESSION['rooms_solved'] ?? 0;
    $hints   = array_sum($_SESSION['hints_used'] ?? []);
    $wrong   = array_sum($_SESSION['wrong_attempts'] ?? []);
    $pct = intval(($correct / TOTAL_ROOMS) * 100) - ($hints * 5) - ($wrong * 3);
    return max(0, min(100, $pct));
}
