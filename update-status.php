<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

// Server-side authorization: only managers may change a status.
// (Hiding the form in detail.php is not enough.)
requireRole('manager');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$newStatus = trim($_POST['status'] ?? '');

if (!$id || !in_array($newStatus, allowedStatuses(), true)) {
    http_response_code(400);
    exit('Invalid ticket update.');
}

$tickets = loadTickets();

if (!updateTicketStatus($tickets, $id, $newStatus)) {
    http_response_code(404);
    exit('Ticket not found.');
}

if (!saveTickets($tickets)) {
    http_response_code(500);
    exit('Could not save ticket update.');
}

setFlash('Ticket #' . $id . ' status changed to ' . $newStatus . '.');

header('Location: detail.php?id=' . urlencode((string)$id));
exit;
