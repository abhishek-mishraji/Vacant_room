<?php
require_once __DIR__ . '/../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter username and password.';
    } else {
        $stmt = $conn->prepare('SELECT admin_id, username, password FROM admins WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin) {
            $stored = $admin['password'];

            // Support bcrypt stored via password_hash OR legacy SHA256 hex (from earlier script)
            $ok = false;
            if (strpos($stored, '$2y$') === 0 || strpos($stored, '$2a$') === 0) {
                $ok = password_verify($password, $stored);
            } elseif (preg_match('/^[0-9a-f]{64}$/i', $stored)) {
                // MySQL SHA2(...,256) returns 64 hex chars
                $ok = hash('sha256', $password) === strtolower($stored);
            } else {
                // Fallback - direct compare (not recommended)
                $ok = ($password === $stored);
            }

            if ($ok) {
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_username'] = $admin['username'];
                header('Location: admin_dashboard.php');
                exit;
            }
        }
        $error = 'Invalid username or password.';
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="assets/css/login.css">

<div class="login-bg">
    <div class="login-bg-pattern"></div>
</div>

<div class="login-container">
    <div class="login-header">
        <h2 class="login-title">Admin Login</h2>
        <p class="login-subtitle">Enter your credentials to access the admin dashboard</p>
    </div>

    <?php if ($error): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" class="login-form">
        <div class="form-group">
            <label for="username">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                Username
            </label>
            <input type="text" id="username" name="username" required autocomplete="username">
        </div>

        <div class="form-group">
            <label for="password">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                Password
            </label>
            <input type="password" id="password" name="password" required autocomplete="current-password">
        </div>

        <button type="submit" class="login-btn">Sign In</button>
    </form>

    <div class="login-footer">
        <p>Return to <a href="index.php">Homepage</a></p>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>