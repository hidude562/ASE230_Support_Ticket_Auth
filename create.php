<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$user = currentUser();

$values = [
    'name' => '',
    'email' => '',
    'category' => '',
    'description' => ''
];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['name'] = trim($_POST['name'] ?? '');
    $values['email'] = trim($_POST['email'] ?? '');
    $values['category'] = trim($_POST['category'] ?? '');
    $values['description'] = trim($_POST['description'] ?? '');

    if ($values['name'] === '') {
        $errors[] = 'Customer name is required.';
    }

    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }

    if (!in_array($values['category'], allowedCategories(), true)) {
        $errors[] = 'Choose a valid category.';
    }

    if (strlen($values['description']) < 10) {
        $errors[] = 'Description must contain at least 10 characters.';
    }

    $attachment = processOptionalImageUpload($_FILES['attachment'] ?? [], $errors);

    if (empty($errors)) {
        $tickets = loadTickets();

        $newTicket = [
            'id' => nextTicketId($tickets),
            'name' => $values['name'],
            'email' => $values['email'],
            'category' => $values['category'],
            'description' => $values['description'],
            'status' => 'Open',
            'attachment' => $attachment,

            /*
             * NOTICE:
             * created_by comes from the authenticated SESSION.
             * It does NOT come from a hidden form input.
             *
             * A-HA:
             * If identity matters, do not trust the browser to tell you
             * who the authenticated user is.
             */
            'created_by' => $user['id']
        ];

        $tickets[] = $newTicket;

        if (saveTickets($tickets)) {
            setFlash('Ticket #' . $newTicket['id'] . ' was created.');
            header('Location: index.php');
            exit;
        }

        $errors[] = 'The ticket could not be saved.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Support Ticket</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<main class="container">
    <p><a href="index.php">&larr; Back to tickets</a></p>

    <section class="panel">
        <h1>Create Support Ticket</h1>

        <p>
            Signed in as <strong><?= e($user['name']) ?></strong>.
            Your user ID will be associated with the ticket by the server.
        </p>

        <?php if (!empty($errors)): ?>
            <div class="error-box">
                <strong>Please correct the following:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="create.php" enctype="multipart/form-data">
            <label for="name">Customer Name</label>
            <input type="text" id="name" name="name" value="<?= e($values['name']) ?>">

            <label for="email">Customer Email</label>
            <input type="email" id="email" name="email" value="<?= e($values['email']) ?>">

            <label for="category">Category</label>
            <select id="category" name="category">
                <option value="">Choose a category</option>
                <?php foreach (allowedCategories() as $category): ?>
                    <option
                        value="<?= e($category) ?>"
                        <?= $values['category'] === $category ? 'selected' : '' ?>
                    >
                        <?= e(ucfirst($category)) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="description">Description</label>
            <textarea id="description" name="description" rows="7"><?= e($values['description']) ?></textarea>

            <label for="attachment">Optional Image</label>
            <input
                type="file"
                id="attachment"
                name="attachment"
                accept="image/jpeg,image/png,image/webp"
            >

            <button type="submit">Submit Ticket</button>
        </form>
    </section>
</main>
</body>
</html>
