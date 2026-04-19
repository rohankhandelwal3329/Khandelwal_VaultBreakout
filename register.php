<?php
/**
 * register.php — New user registration
 * Stores credentials in flat file (data/users.txt)
 * Uses PHP sessions + htmlspecialchars() + filter_input()
 */
require_once __DIR__ . '/includes/config.php';
$base = BASE_URL;

// Already logged in — send to home
if (!empty($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/');
    exit;
}

$errors   = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize & validate
    $username = clean(filter_input(INPUT_POST, 'username', FILTER_DEFAULT) ?? '');
    $password = filter_input(INPUT_POST, 'password', FILTER_DEFAULT) ?? '';
    $confirm  = filter_input(INPUT_POST, 'confirm',  FILTER_DEFAULT) ?? '';

    if (strlen($username) < 3 || strlen($username) > 20) {
        $errors[] = 'Username must be 3–20 characters.';
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username may only contain letters, numbers, and underscores.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    // Check for duplicate username
    if (empty($errors)) {
        $users = file_exists(USERS_FILE) ? file(USERS_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
        foreach ($users as $line) {
            [$u] = explode(':', $line, 2);
            if (strtolower($u) === strtolower($username)) {
                $errors[] = 'That username is already taken. Please choose another.';
                break;
            }
        }
    }

    // Save and redirect
    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        file_put_contents(USERS_FILE, $username . ':' . $hashed . PHP_EOL, FILE_APPEND | LOCK_EX);
        $_SESSION['flash'] = 'Account created! Please log in.';
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

$pageTitle = 'Register';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap animate-in">
    <div class="card card--glow">
        <h1 class="auth-title">🔐 Create Account</h1>
        <p class="auth-sub">Join Vault Breakout and start your escape.</p>

        <?php if ($errors): ?>
            <div class="alert alert--error" role="alert" id="reg-errors">
                <?php foreach ($errors as $e): ?>
                    <div>⚠ <?= h($e) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= $base ?>/register.php" novalidate id="register-form">
            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input
                    id="username" name="username" type="text"
                    class="form-input <?= $errors ? 'form-input--error' : '' ?>"
                    value="<?= h($username) ?>"
                    maxlength="20" autocomplete="username" required
                    placeholder="e.g. vault_breaker">
                <p class="form-input-val">Letters, numbers, underscores · 3–20 chars</p>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input
                    id="password" name="password" type="password"
                    class="form-input"
                    minlength="6" autocomplete="new-password" required
                    placeholder="Minimum 6 characters">
            </div>

            <div class="form-group">
                <label class="form-label" for="confirm">Confirm Password</label>
                <input
                    id="confirm" name="confirm" type="password"
                    class="form-input"
                    autocomplete="new-password" required
                    placeholder="Re-enter your password">
            </div>

            <button type="submit" class="btn btn--primary btn--full" id="btn-register-submit">
                🚀 Create Account
            </button>
        </form>

        <hr class="divider">
        <p class="text-center text-muted" style="font-size:0.88rem;">
            Already have an account? <a href="<?= $base ?>/login.php" id="link-to-login">Log in</a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
