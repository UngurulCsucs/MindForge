<?php
// ============================================
// MindForge — API Sesiuni
// GET /api/sessions.php — lista sesiunilor userului
// ============================================

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

$sess = requireLogin();
$userId = (int)$sess['user_id'];

$db   = getDB();
$stmt = $db->prepare("
    SELECT s.id, s.titlu, s.status, s.created_at,
           COUNT(m.id) as nr_mesaje
    FROM sessions s
    LEFT JOIN messages m ON m.session_id = s.id
    WHERE s.user_id = ? 
    GROUP BY s.id
    ORDER BY s.created_at DESC
    LIMIT 20
");
$stmt->execute([$userId]);
$sessions = $stmt->fetchAll();

jsonResponse(['sessions' => $sessions]);