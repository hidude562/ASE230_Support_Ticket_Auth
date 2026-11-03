<?php

const DATA_FILE = __DIR__ . '/../data/tickets.json';
const USER_FILE = __DIR__ . '/../data/users.json';
const UPLOAD_DIR = __DIR__ . '/../uploads/';

function allowedCategories(): array
{
    return ['technical', 'billing', 'account', 'other'];
}

function allowedStatuses(): array
{
    return ['Open', 'In Progress', 'Resolved'];
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function loadJsonFile(string $file): array
{
    if (!file_exists($file)) {
        return [];
    }

    $json = file_get_contents($file);
    $data = json_decode($json, true);

    return is_array($data) ? $data : [];
}

function loadTickets(): array
{
    return loadJsonFile(DATA_FILE);
}

function saveTickets(array $tickets): bool
{
    $json = json_encode($tickets, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    if ($json === false) {
        return false;
    }

    return file_put_contents(DATA_FILE, $json, LOCK_EX) !== false;
}

function loadUsers(): array
{
    return loadJsonFile(USER_FILE);
}

function findUserByEmail(array $users, string $email): ?array
{
    foreach ($users as $user) {
        if (strcasecmp($user['email'], $email) === 0) {
            return $user;
        }
    }

    return null;
}

function findTicketById(array $tickets, int $id): ?array
{
    foreach ($tickets as $ticket) {
        if ((int)$ticket['id'] === $id) {
            return $ticket;
        }
    }

    return null;
}

function nextTicketId(array $tickets): int
{
    $max = 0;

    foreach ($tickets as $ticket) {
        $max = max($max, (int)$ticket['id']);
    }

    return $max + 1;
}

function filterTickets(
    array $tickets,
    string $query = '',
    string $status = '',
    string $category = ''
): array {
    return array_values(array_filter($tickets, function ($ticket) use ($query, $status, $category) {
        if ($status !== '' && $ticket['status'] !== $status) {
            return false;
        }

        if ($category !== '' && $ticket['category'] !== $category) {
            return false;
        }

        if ($query !== '') {
            $haystack = strtolower(
                $ticket['name'] . ' ' .
                $ticket['email'] . ' ' .
                $ticket['description']
            );

            if (!str_contains($haystack, strtolower($query))) {
                return false;
            }
        }

        return true;
    }));
}

function updateTicketStatus(array &$tickets, int $id, string $newStatus): bool
{
    foreach ($tickets as &$ticket) {
        if ((int)$ticket['id'] === $id) {
            $ticket['status'] = $newStatus;
            return true;
        }
    }

    return false;
}

function processOptionalImageUpload(array $file, array &$errors): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        $errors[] = 'The image could not be uploaded.';
        return null;
    }

    if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
        $errors[] = 'The image must be 2 MB or smaller.';
        return null;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($allowedTypes[$mime])) {
        $errors[] = 'Only JPG, PNG, and WebP images are allowed.';
        return null;
    }

    $filename = bin2hex(random_bytes(8)) . '.' . $allowedTypes[$mime];
    $destination = UPLOAD_DIR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        $errors[] = 'The server could not save the uploaded image.';
        return null;
    }

    return $filename;
}
