<?php
/**
 * login.php — User authentication
 * Validates credentials from flat file, starts session, redirects.
 */
require_once __DIR__ . '/includes/config.php';
$base = BASE_URL;

if (!empty($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/');
    exit;
}

$errors   = [];
$username = '';

// Flash message from register redirect
$flash = '';
if (!empty($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean(filter_input(INPUT_POST, 'username', FILTER_DEFAULT) ?? '');
    $password = filter_input(INPUT_POST, 'password', FILTER_DEFAULT) ?? '';

    if ($username === '') { $errors[] = 'Username is required.'; }
    if ($password === '') { $errors[] = 'Password is required.'; }

    if (empty($errors)) {
        $found = false;
        if (file_exists(USERS_FILE)) {
            foreach (file(USERS_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                [$u, $h] = explode(':', $line, 2);
                if (strtolower($u) === strtolower($username) && password_verify($password, $h)) {
                    $found = true;
                    $_SESSION['user'] = $u; // use original case from file
                    break;
                }
            }
        }
        if (!$found) {
            $errors[] = 'Invalid username or password.';
        } else {
            header('Location: ' . BASE_URL . '/');
            exit;
        }
    }
}

$pageTitle = 'Login';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap animate-in">
    <div class="card card--glow">
        <h1 class="auth-title">🔑 Agent Login</h1>
        <p class="auth-sub">Enter your credentials to access the vault.</p>

        <?php if ($flash): ?>
            <div class="alert alert--success" id="flash-msg">✅ <?= h($flash) ?></div>
        <?php endif; ?>
        <?php if ($errors): ?>
            <div class="alert alert--error" role="alert" id="login-errors">
                <?php foreach ($errors as $e): ?>
                    <div>⚠ <?= h($e) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= $base ?>/login.php" novalidate id="login-form">
            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input
                    id="username" name="username" type="text"
                    class="form-input <?= $errors ? 'form-input--error' : '' ?>"
                    value="<?= h($username) ?>"
                    autocomplete="username" required
                    placeholder="Your username">
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input
                    id="password" name="password" type="password"
                    class="form-input"
                    autocomplete="current-password" required
                    placeholder="Your password">
            </div>
            <button type="submit" class="btn btn--primary btn--full" id="btn-login-submit">
                🔓 Enter the Vault
            </button>
        </form>

        <hr class="divider">
        <p class="text-center text-muted" style="font-size:0.88rem;">
            New agent? <a href="<?= $base ?>/register.php" id="link-to-register">Create an account</a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
