<?php
// ============================================
// MindForge — Functii ajutatoare
// ============================================

require_once __DIR__ . '/../config/db.php';

// ── Obtine profilul userului (sau il creaza) ──
function getUserProfile(int $userId): array {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
    $stmt->execute([$userId]);
    $profile = $stmt->fetch();

    if (!$profile) {
        $db->prepare("INSERT INTO user_profiles (user_id) VALUES (?)")->execute([$userId]);
        $stmt->execute([$userId]);
        $profile = $stmt->fetch();
    }
    return $profile;
}

// ── Actualizeaza profilul dupa un mesaj al userului ──
function updateProfileFromMessage(int $userId, string $userMessage, string $aiAnalysis): void {
    $db  = getDB();
    $prf = getUserProfile($userId);

    $len   = mb_strlen(trim($userMessage));
    $words = str_word_count($userMessage);

    // Scor profunzime bazat pe lungimea si complexitatea raspunsului
    $depthScore = 0;
    if ($words > 5)  $depthScore++;
    if ($words > 15) $depthScore++;
    if ($words > 30) $depthScore++;
    if (preg_match('/pentru că|deoarece|din cauza|astfel|prin urmare|consider|cred că/iu', $userMessage)) $depthScore += 2;
    if (preg_match('/\?/', $userMessage)) $depthScore++;  // pune intrebari = bun

    // Stil detectat
    $stil = 'analitic';
    if ($words < 4)  $stil = 'superficial';
    if ($words < 3)  $stil = 'grabit';
    if ($depthScore >= 4) $stil = 'consistent';

    // Nivel bazat pe acumulare
    $sesiuni = $prf['sesiuni_totale'];
    $nivel = 'incepator';
    if ($sesiuni > 5)  $nivel = 'intermediar';
    if ($sesiuni > 20) $nivel = 'avansat';

    // Update incrementat al skill-urilor (crestem mic dar constant)
    $gc  = min(100, $prf['gandire_critica']     + ($depthScore >= 3 ? 1 : 0));
    $ce  = min(100, $prf['claritate_emotionala'] + ($depthScore >= 4 ? 1 : 0));
    $com = min(100, $prf['comunicare']           + ($words > 20 ? 1 : 0));
    $rez = min(100, $prf['rezistenta']           + 0);  // creste doar la consistenta sesiunilor

    // Serie activa
    $azi         = date('Y-m-d');
    $ultimaSes   = $prf['ultima_sesiune'];
    $serie       = $prf['serie_activa'];
    if ($ultimaSes !== $azi) {
        $serie = ($ultimaSes === date('Y-m-d', strtotime('-1 day'))) ? $serie + 1 : 1;
    }
    $rez = min(100, $prf['rezistenta'] + ($serie >= 3 ? 1 : 0));

    $db->prepare("
        UPDATE user_profiles SET
            nivel = ?, stil = ?,
            gandire_critica = ?, claritate_emotionala = ?,
            comunicare = ?, rezistenta = ?,
            serie_activa = ?, ultima_sesiune = ?
        WHERE user_id = ?
    ")->execute([$nivel, $stil, $gc, $ce, $com, $rez, $serie, $azi, $userId]);
}

// ── Incrementeaza numarul de sesiuni ──
function incrementSessionCount(int $userId): void {
    getDB()->prepare("
        UPDATE user_profiles SET sesiuni_totale = sesiuni_totale + 1 WHERE user_id = ?
    ")->execute([$userId]);
}

// ── Obtine istoricul conversatiei pentru Gemini ──
function getConversationHistory(int $sessionId, int $limit = 10): array {
    $db   = getDB();
    $stmt = $db->prepare("
        SELECT role, content, image_path
        FROM messages
        WHERE session_id = ?
        ORDER BY created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$sessionId, $limit]);
    $rows = array_reverse($stmt->fetchAll());

    $history = [];
    foreach ($rows as $row) {
        $parts = [['text' => $row['content']]];
        if ($row['image_path']) {
            $imgData = base64_encode(file_get_contents(UPLOAD_DIR . basename($row['image_path'])));
            $mime    = mime_content_type(UPLOAD_DIR . basename($row['image_path']));
            array_unshift($parts, ['inline_data' => ['mime_type' => $mime, 'data' => $imgData]]);
        }
        $history[] = [
            'role'  => $row['role'] === 'assistant' ? 'model' : 'user',
            'parts' => $parts
        ];
    }
    return $history;
}

// ── Salveaza mesaj in DB ──
function saveMessage(int $sessionId, int $userId, string $role, string $content, ?string $imagePath = null): int {
    $db = getDB();
    $db->prepare("
        INSERT INTO messages (session_id, user_id, role, content, image_path)
        VALUES (?, ?, ?, ?, ?)
    ")->execute([$sessionId, $userId, $role, $content, $imagePath]);
    return (int)$db->lastInsertId();
}

// ── Creaza sesiune noua ──
function createSession(int $userId, string $titlu = 'Sesiune nouă'): int {
    $db = getDB();
    $db->prepare("INSERT INTO sessions (user_id, titlu) VALUES (?, ?)")->execute([$userId, $titlu]);
    incrementSessionCount($userId);
    return (int)$db->lastInsertId();
}

// ── Raspuns JSON standardizat ──
function jsonResponse(array $data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Verifica sesiunea PHP ──
function requireLogin() {
    return [
        'user_id' => 1,
        'user_name' => 'TestUser'
    ];
}