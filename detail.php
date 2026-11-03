<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$user = currentUser();
$flash = getFlash();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$tickets = loadTickets();
$ticket = $id ? findTicketById($tickets, $id) : null;

if (!$id || !$ticket) {
    http_response_code(404);
    $message = 'Ticket not found.';
} else {
    $message = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Detail</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<main class="container">
    <p><a href="index.php">&larr; Back to tickets</a></p>

    <?php if ($flash): ?>
        <div class="success-box"><?= e($flash) ?></div>
    <?php endif; ?>

    <?php if ($message): ?>
        <section class="panel">
            <h1><?= e($message) ?></h1>
            <p>Check the ticket ID and try again.</p>
        </section>
    <?php else: ?>
        <section class="panel">
            <h1>Ticket #<?= e((string)$ticket['id']) ?></h1>

            <dl class="ticket-detail">
                <dt>Name</dt>
                <dd><?= e($ticket['name']) ?></dd>

                <dt>Email</dt>
                <dd><?= e($ticket['email']) ?></dd>

                <dt>Category</dt>
                <dd><?= e(ucfirst($ticket['category'])) ?></dd>

                <dt>Status</dt>
                <dd><?= e($ticket['status']) ?></dd>

                <dt>Description</dt>
                <dd><?= nl2br(e($ticket['description'])) ?></dd>

                <dt>Attachment</dt>
                <dd><?= !empty($ticket['attachment']) ? e($ticket['attachment']) : 'None' ?></dd>
            </dl>
        </section>

        <section class="panel">
            <h2>Update Status</h2>

            <?php if (($user['role'] ?? '') === 'manager'): ?>
                <form method="POST" action="update-status.php">
                    <input type="hidden" name="id" value="<?= e((string)$ticket['id']) ?>">

                    <label for="status">New Status</label>
                    <select name="status" id="status">
                        <?php foreach (allowedStatuses() as $status): ?>
                            <option value="<?= e($status) ?>" <?= $ticket['status'] === $status ? 'selected' : '' ?>>
                                <?= e($status) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit">Update Ticket</button>
                </form>
            <?php else: ?>
                <p>
                    Your account can view this ticket, but only a manager
                    may change its status.
                </p>

                <!--
                    A-HA:
                    This UI check improves usability, but it is NOT enough for security.
                    A user can still manually POST to update-status.php.
                    The server-side handler must enforce the same authorization rule.
                -->
            <?php endif; ?>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
