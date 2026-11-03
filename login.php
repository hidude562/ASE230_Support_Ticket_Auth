<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$email = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $users = loadUsers();
    $user = findUserByEmail($users, $email);

    // Always run password_verify(), even for an unknown email, so the
    // response time does not reveal which emails exist.
    $dummyHash = '$2y$10$gLeoXbiUqHqr.rNks09NkOFqXb9F0AtOTYWfBJJzBSEvHVldSgGUq';
    $hash = $user['password_hash'] ?? $dummyHash;
    $passwordOk = password_verify($password, $hash);

    if ($user !== null && $passwordOk) {
        loginUser($user);

        // Use the remembered page once, but only if it is a local path
        // (starts with a single "/"), so login cannot redirect off-site.
        $target = $_SESSION['intended_url'] ?? '';
        unset($_SESSION['intended_url']);

        $isLocal = is_string($target) && $target !== ''
            && $target[0] === '/' && !str_starts_with($target, '//');

        header('Location: ' . ($isLocal ? $target : 'index.php'));
        exit;
    }

    $error = 'Invalid email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<main class="container narrow">
    <section class="panel">
        <h1>Support Desk Sign In</h1>
        <p>Sign in to access the support ticket application.</p>

        <?php if ($error !== ''): ?>
            <div class="error-box"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <label for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="<?= e($email) ?>"
                autocomplete="username"
                required
            >

            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                autocomplete="current-password"
                required
            >

            <button type="submit">Sign In</button>
        </form>
    </section>
</main>
</body>
</html>
