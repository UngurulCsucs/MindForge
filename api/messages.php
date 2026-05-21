<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

$sess   = requireLogin();
$userId = (int)$sess['user_id'];
$db     = getDB();

$sessionId = isset($_GET['session_id']) ? (int)$_GET['session_id'] : 0;
if (!$sessionId) {
    jsonResponse(['error' => 'session_id lipsă']);
    exit;
}

// Verifică că sesiunea aparține userului autentificat
$check = $db->prepare("SELECT id FROM sessions WHERE id = ? AND user_id = ?");
$check->execute([$sessionId, $userId]);
if (!$check->fetch()) {
    http_response_code(403);
    jsonResponse(['error' => 'Acces interzis']);
    exit;
}

$stmt = $db->prepare("
    SELECT role, content, image_path, created_at
    FROM messages
    WHERE session_id = ?
    ORDER BY created_at ASC
");
$stmt->execute([$sessionId]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

jsonResponse(['messages' => $messages]);