<?php
/**
 * start.php — Initialize a fresh game session
 * Resets all game state and redirects to room.php
 */
require_once __DIR__ . '/includes/config.php';
requireLogin();

// Reset game state in session
$_SESSION['score']         = BASE_SCORE;
$_SESSION['rooms_solved']  = 0;
$_SESSION['current_room']  = 1;
$_SESSION['game_active']   = true;
$_SESSION['start_time']    = time();
$_SESSION['wrong_attempts'] = [];
$_SESSION['hints_used']     = [];
$_SESSION['hint_ptr']       = [];
$_SESSION['lockout_until']  = [];
$_SESSION['room_times']     = [];

// Cookie for cross-visit persistence (24 hours)
setcookie('vb_user', $_SESSION['user'], time() + 86400, '/', '', false, true);

header('Location: ' . BASE_URL . '/room.php');
exit;
