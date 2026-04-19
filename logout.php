<?php
/**
 * logout.php — Destroy session and redirect to login
 */
require_once __DIR__ . '/includes/config.php';

// Clear all session data
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}
session_destroy();

// Expire the persistent cookie
setcookie('vb_user', '', time() - 3600, '/');

header('Location: ' . BASE_URL . '/login.php');
exit;
