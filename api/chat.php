<?php
// ============================================
// MindForge — API Chat (Google Gemini)
// ============================================

session_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config/db.php';
$pdo = getDB();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Neautentificat']);
    exit;
}
$userId = (int)$_SESSION['user_id'];

$isMultipart = !empty($_FILES['image']['tmp_name']);
$input = [];

if ($isMultipart) {
    $userMsg   = trim($_POST['message']     ?? '');
    $sessionId = (int)($_POST['session_id'] ?? 0);
    $isNew     = (bool)($_POST['new_session'] ?? false);
} else {
    $raw       = file_get_contents('php://input');
    $input     = json_decode($raw, true) ?? [];
    $userMsg   = trim($input['message']     ?? '');
    $sessionId = (int)($input['session_id'] ?? 0);
    $isNew     = (bool)($input['new_session'] ?? false);
}

// ── load_session ──
if (($input['action'] ?? '') === 'load_session') {
    $sid = (int)($input['session_id'] ?? 0);
    if (!$sid) { echo json_encode(['error' => 'session_id lipsă']); exit; }

    $stmtCheck = $pdo->prepare("SELECT id FROM sessions WHERE id = ? AND user_id = ?");
    $stmtCheck->execute([$sid, $userId]);
    if (!$stmtCheck->fetch()) { echo json_encode(['error' => 'Sesiune negăsită']); exit; }

    $stmtMsgs = $pdo->prepare("
        SELECT role, content, image_path, created_at
        FROM messages
        WHERE session_id = ? AND user_id = ?
        ORDER BY created_at ASC, id ASC
    ");
    $stmtMsgs->execute([$sid, $userId]);
    $messages = $stmtMsgs->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['messages' => $messages]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Metoda invalida']);
    exit;
}

$imagePath = null;
if ($isMultipart) {
    $imagePath = handleImageUpload($_FILES['image']);
}

if (empty($userMsg) && !$imagePath) {
    http_response_code(400);
    echo json_encode(['error' => 'Mesajul este gol']);
    exit;
}

// ── Sesiune ──
if ($isNew || $sessionId === 0) {
    $stmtNewSess = $pdo->prepare("
        INSERT INTO sessions (user_id, titlu, status, created_at)
        VALUES (?, 'Sesiune nouă', 'activa', NOW())
    ");
    $stmtNewSess->execute([$userId]);
    $sessionId = (int)$pdo->lastInsertId();
    $titlu = mb_substr($userMsg, 0, 60) ?: 'Sesiune nouă';
    $pdo->prepare("UPDATE sessions SET titlu = ? WHERE id = ?")->execute([$titlu, $sessionId]);
    $_SESSION['conv_' . $sessionId] = [];
} else {
    $stmtCheck = $pdo->prepare("SELECT id FROM sessions WHERE id = ? AND user_id = ?");
    $stmtCheck->execute([$sessionId, $userId]);
    if (!$stmtCheck->fetch()) {
        $stmtNewSess = $pdo->prepare("
            INSERT INTO sessions (user_id, titlu, status, created_at)
            VALUES (?, 'Sesiune nouă', 'activa', NOW())
        ");
        $stmtNewSess->execute([$userId]);
        $sessionId = (int)$pdo->lastInsertId();
        $_SESSION['conv_' . $sessionId] = [];
    }
}

// ── Istoric ──
$stmtHistory = $pdo->prepare("
    SELECT role, content FROM messages
    WHERE session_id = ? AND user_id = ?
    ORDER BY created_at ASC, id ASC
");
$stmtHistory->execute([$sessionId, $userId]);
$dbMessages = $stmtHistory->fetchAll(PDO::FETCH_ASSOC);

$history = [];
foreach ($dbMessages as $m) {
    $geminiRole = $m['role'] === 'assistant' ? 'model' : 'user';
    $history[]  = ['role' => $geminiRole, 'parts' => [['text' => $m['content']]]];
}

// ── Citește profilul utilizatorului pentru prompt dinamic ──
$stmtProf = $pdo->prepare("SELECT nivel, stil FROM user_profiles WHERE user_id = ?");
$stmtProf->execute([$userId]);
$profUser = $stmtProf->fetch(PDO::FETCH_ASSOC);
$nivel = $profUser['nivel'] ?? 'incepator';
$stil  = $profUser['stil']  ?? 'analitic';

$nivelInstr = match($nivel) {
    'incepator' => <<<N
NIVELUL UTILIZATORULUI: ÎNCEPĂTOR
- Folosești cuvinte simple, fără jargon tehnic sau filozofic.
- Întrebările tale sunt concrete și legate de viața de zi cu zi.
- Validezi mai mult și provoci mai puțin — construiești încredere.
- Răspunsuri scurte: maxim 3-4 propoziții + o singură întrebare.
- Exemple din viața cotidiană, nu abstracții.
N,
    'intermediar' => <<<N
NIVELUL UTILIZATORULUI: INTERMEDIAR
- Poți introduce concepte noi dacă le explici pe scurt.
- Întrebările tale leagă 2-3 idei și cer conexiuni între ele.
- Alternezi între validare și provocare constructivă.
- Răspunsuri medii: 4-6 propoziții + o întrebare cu profunzime.
- Poți face referire la pattern-uri de gândire sau emoție.
N,
    'avansat' => <<<N
NIVELUL UTILIZATORULUI: AVANSAT
- Folosești limbaj filozofic, psihologic sau conceptual fără să explici termenii de bază.
- Întrebările tale sunt provocatoare, nuanțate, cer abstracție și meta-gândire.
- Provoci mai mult decât validezi — utilizatorul poate gestiona disconfortul cognitiv.
- Răspunsuri pot fi mai lungi: 5-8 propoziții + o întrebare care destabilizează confortabil.
- Faci referire la contradicții interne, pattern-uri profunde, asumpții implicite.
N,
    default => ''
};

$stilInstr = match($stil) {
    'analitic' => <<<S
STILUL DE ÎNVĂȚARE: ANALITIC
- Utilizatorul vrea să înțeleagă structura și logica din spatele lucrurilor.
- Pune întrebări de tip "De ce crezi că funcționează așa?" sau "Care e mecanismul?".
- Poți propune cadre de gândire (ex: "Există două forțe în tensiune aici...").
- Răspunsurile tale au o micro-structură clară: observație → întrebare.
S,
    'grabit' => <<<S
STILUL DE ÎNVĂȚARE: GRĂBIT
- Utilizatorul vrea să ajungă repede la esență — nu divaga, nu filozofa inutil.
- Fii direct: o observație scurtă + o întrebare concretă, acționabilă.
- Evită introducerile lungi. Taie tot ce nu e necesar.
- Dacă utilizatorul sare pași, semnalează scurt: "Înainte de asta — ce ai încercat deja?".
S,
    'superficial' => <<<S
STILUL DE ÎNVĂȚARE: SUPERFICIAL
- Utilizatorul tinde să răspundă cu minimum de efort. Nu-l judeca, dar nu-l lăsa.
- După fiecare răspuns scurt, reîntoarce mingea cu blândețe: "Poți dezvolta puțin?".
- Pune întrebări care cer un exemplu concret din viața lui, nu răspunsuri abstracte.
- Tonul rămâne cald, dar persistent — nu accepta "nu știu" fără o urmărire.
S,
    'consistent' => <<<S
STILUL DE ÎNVĂȚARE: CONSISTENT
- Utilizatorul progresează gradual. Respectă ritmul lui.
- La fiecare răspuns, construiești pe ce a spus înainte — nu sari la idei noi brusc.
- Recunoaște progresul explicit din când în când: "Observ că acum formulezi asta diferit față de început."
- Pași mici, consolidați. Fiecare sesiune are un fir narativ coerent.
S,
    default => ''
};

$systemPrompt = <<<PROMPT
Ești MindForge — un mentor personal, empatic și inteligent, dedicat creșterii umane autentice.

FILOZOFIA TA FUNDAMENTALĂ:
- Nu ești un motor de căutare și nu ești Wikipedia.
- Nu dai niciodată răspunsuri directe și complete la prima cerere.
- Folosești metoda socratică: pui întrebări care îl fac pe om să gândească singur.
- Crezi că adevărata învățare vine din interior, nu din afară.

REGULI STRICTE DE COMPORTAMENT:
1. Pune ÎNTOTDEAUNA o întrebare înainte sau în loc de a da un răspuns.
2. Dacă utilizatorul nu știe un lucru, nu îl explici direct — îl ghidezi spre descoperire.
3. Dacă răspunsul utilizatorului este superficial (1-3 cuvinte), ceri mai multă profunzime cu blândețe.
4. Dacă detectezi o greșeală de gândire, nu o corectezi direct — pune o întrebare care îl face să o descopere singur.
5. Ești cald, uman, nu robotic. Folosești empatie autentică.
6. Vorbești în română, natural, ca un prieten înțelept.
7. Nu folosești bullet points sau liste lungi. Vorbești în propoziții naturale.

--- INSTRUCȚIUNI ADAPTIVE (OBLIGATORII — au prioritate față de regulile generale) ---

{$nivelInstr}

{$stilInstr}

PROMPT;

// ── Lansează cele 2 cereri cURL în paralel ──
$mainCurl    = buildMainCurlHandle($systemPrompt, $history, $userMsg, $imagePath);
$profileCurl = buildProfileCurlHandle($pdo, $userId, $sessionId, $userMsg);

$mh = curl_multi_init();
curl_multi_add_handle($mh, $mainCurl);
curl_multi_add_handle($mh, $profileCurl);

do {
    $status = curl_multi_exec($mh, $active);
    if ($active) curl_multi_select($mh);
} while ($active && $status === CURLM_OK);

$mainRaw    = curl_multi_getcontent($mainCurl);
$profileRaw = curl_multi_getcontent($profileCurl);
$mainCode   = curl_getinfo($mainCurl, CURLINFO_HTTP_CODE);

curl_multi_remove_handle($mh, $mainCurl);
curl_multi_remove_handle($mh, $profileCurl);
curl_multi_close($mh);
curl_close($mainCurl);
curl_close($profileCurl);

// ── Procesează răspunsul principal ──
if (!$mainRaw || $mainCode !== 200) {
    $decoded = json_decode($mainRaw, true);
    $errMsg  = $decoded['error']['message'] ?? "Eroare HTTP $mainCode de la Gemini.";
    http_response_code(500);
    echo json_encode(['error' => $errMsg]);
    exit;
}

$data   = json_decode($mainRaw, true);
$aiText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

if (empty($aiText)) {
    http_response_code(500);
    echo json_encode(['error' => 'AI-ul nu a generat un răspuns. Încearcă din nou.']);
    exit;
}

// ── Procesează detecția nivel + stil + skills din răspunsul paralel ──
$detectedNivel = null;
$detectedStil  = null;
$skillsDelta   = [];

if ($profileRaw) {
    $pd  = json_decode($profileRaw, true);
    $pt  = trim(preg_replace('/```json|```/', '', $pd['candidates'][0]['content']['parts'][0]['text'] ?? ''));
    $dec = json_decode($pt, true);

    if (is_array($dec)) {
        $validNivel = ['incepator', 'intermediar', 'avansat'];
        $validStil  = ['analitic', 'consistent', 'grabit', 'superficial'];

        if (isset($dec['nivel']) && in_array($dec['nivel'], $validNivel)) {
            $detectedNivel = $dec['nivel'];
        }
        if (isset($dec['stil']) && in_array($dec['stil'], $validStil)) {
            $detectedStil = $dec['stil'];
        }

        // Extrage delta skills — clamp strict 0-2
        if (isset($dec['skills']) && is_array($dec['skills'])) {
            foreach (['gandire_critica', 'claritate_emotionala', 'comunicare', 'rezistenta'] as $sk) {
                $val = (int)($dec['skills'][$sk] ?? 0);
                $skillsDelta[$sk] = max(0, min(2, $val));
            }
        }
    }
}

// ── Salvează mesajele ──
$stmtInsertMsg = $pdo->prepare("
    INSERT INTO messages (session_id, user_id, role, content, image_path, tokens, created_at)
    VALUES (?, ?, ?, ?, ?, ?, NOW())
");
$stmtInsertMsg->execute([$sessionId, $userId, 'user',      $userMsg ?: '[imagine]', $imagePath, 0]);
$stmtInsertMsg->execute([$sessionId, $userId, 'assistant', $aiText,                null,        0]);

// ── Actualizează profilul ──
$profile = updateUserProfile($pdo, $userId, $sessionId, $detectedNivel, $detectedStil, $skillsDelta);

echo json_encode([
    'success'    => true,
    'message'    => $aiText,
    'session_id' => $sessionId,
    'profile'    => $profile,
]);
exit;

// ============================================
// FUNCȚII
// ============================================

function buildMainCurlHandle(string $systemPrompt, array $history, string $userMsg, ?string $imagePath): \CurlHandle
{
    $apiKey = GEMINI_API_KEY;
    $url    = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

    $currentParts = [];
    if ($imagePath && file_exists(UPLOAD_DIR . basename($imagePath))) {
        $fp = UPLOAD_DIR . basename($imagePath);
        $currentParts[] = ['inline_data' => [
            'mime_type' => mime_content_type($fp),
            'data'      => base64_encode(file_get_contents($fp)),
        ]];
    }
    if (!empty($userMsg)) $currentParts[] = ['text' => $userMsg];

    $contents   = $history;
    $contents[] = ['role' => 'user', 'parts' => $currentParts];

    $body = [
        'system_instruction' => ['parts' => [['text' => $systemPrompt]]],
        'contents'           => $contents,
        'generationConfig'   => ['temperature' => 0.85, 'maxOutputTokens' => 1200, 'topP' => 0.95],
        'safetySettings'     => [
            ['category' => 'HARM_CATEGORY_HARASSMENT',        'threshold' => 'BLOCK_NONE'],
            ['category' => 'HARM_CATEGORY_HATE_SPEECH',       'threshold' => 'BLOCK_NONE'],
            ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
            ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_NONE'],
        ],
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($body),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 30,
    ]);
    return $ch;
}

function buildProfileCurlHandle(PDO $pdo, int $userId, int $sessionId, string $userMsg): \CurlHandle
{
    $stmt = $pdo->prepare("SELECT nivel, stil FROM user_profiles WHERE user_id = ?");
    $stmt->execute([$userId]);
    $p           = $stmt->fetch(PDO::FETCH_ASSOC);
    $nivelCurent = $p['nivel'] ?? 'incepator';
    $stilCurent  = $p['stil']  ?? 'analitic';

    // Ultimele 8 mesaje user din sesiune + mesajul curent
    $stmtR = $pdo->prepare("
        SELECT content FROM messages
        WHERE session_id = ? AND role = 'user'
        ORDER BY created_at DESC, id DESC LIMIT 8
    ");
    $stmtR->execute([$sessionId]);
    $msgs = array_reverse($stmtR->fetchAll(PDO::FETCH_COLUMN));
    if (!empty($userMsg)) $msgs[] = $userMsg;

    $conversation = implode("\n---\n", $msgs);

    $prompt = <<<PROMPT
Ești un analist specializat în stiluri de învățare și niveluri de cunoaștere.
Analizează mesajele utilizatorului și returnează o evaluare pe 4 dimensiuni.

DIMENSIUNEA 1 — NIVELUL DE CUNOAȘTERE/MATURITATE COGNITIVĂ:
- "incepator": răspunsuri scurte (1-5 cuvinte), nu dezvoltă idei, vocabular simplu
- "intermediar": răspunsuri de lungime medie, oarecare profunzime, conectează 2-3 idei
- "avansat": răspunsuri elaborate, gândire critică vizibilă, întrebări nuanțate, abstracții

DIMENSIUNEA 2 — STILUL DE ÎNVĂȚARE:
- "analitic": descompune probleme, întreabă "de ce?", vrea logică și structură
- "consistent": răspunsuri regulate ca lungime și calitate, progres gradual
- "grabit": răspunsuri scurte, vrea rezultate rapide, sare pași
- "superficial": răspunde cu minim de efort, evită profunzimea

DIMENSIUNEA 3 — SKILLS demonstrate în mesajele de față (0, 1 sau 2 pentru fiecare):
- gandire_critica: 2=analizează cauze/face conexiuni între idei/pune la îndoială asumpții, 1=menționează o idee fără să o dezvolte, 0=răspuns superficial fără analiză
- claritate_emotionala: 2=numește o emoție ȘI explică de ce o simte, 1=numește emoția fără context, 0=evită complet emoțiile sau e vag
- comunicare: 2=se exprimă clar și structurat fără ambiguitate, 1=parțial clar cu unele ambiguități, 0=vag sau foarte scurt (sub 5 cuvinte)
- rezistenta: 2=continuă un subiect dificil sau revine după o întrebare grea fără să fugă, 1=continuă dar superficial, 0=evită sau schimbă subiectul

Nivelul curent: {$nivelCurent} | Stilul curent: {$stilCurent}

Mesajele utilizatorului:
---
{$conversation}
---

Răspunde EXCLUSIV cu JSON valid, fără markdown, fără explicații, fără text în afara JSON-ului:
{"nivel":"incepator|intermediar|avansat","stil":"analitic|consistent|grabit|superficial","skills":{"gandire_critica":0,"claritate_emotionala":0,"comunicare":0,"rezistenta":0}}
PROMPT;

    $apiKey = GEMINI_API_KEY;
    $url    = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

    $body = [
        'contents'         => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
        'generationConfig' => ['temperature' => 0.1, 'maxOutputTokens' => 80, 'topP' => 1.0],
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($body),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 15,
    ]);
    return $ch;
}

function updateUserProfile(PDO $pdo, int $userId, int $sessionId, ?string $detectedNivel, ?string $detectedStil, array $skillsDelta = []): array
{
    $stmt = $pdo->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
    $stmt->execute([$userId]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$profile) {
        $pdo->prepare("INSERT INTO user_profiles (user_id) VALUES (?)")->execute([$userId]);
        $stmt->execute([$userId]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ── Serie activă ──
    $today         = date('Y-m-d');
    $ultimaSesiune = $profile['ultima_sesiune'] ?? null;
    $serieActiva   = (int)($profile['serie_activa'] ?? 0);
    if ($ultimaSesiune === null) {
        $serieActiva = 1;
    } elseif ($ultimaSesiune === date('Y-m-d', strtotime('-1 day'))) {
        $serieActiva++;
    } elseif ($ultimaSesiune !== $today) {
        $serieActiva = 1;
    }

    // ── Sesiuni totale ──
    $stmtSesiuni = $pdo->prepare("SELECT COUNT(*) FROM sessions WHERE user_id = ?");
    $stmtSesiuni->execute([$userId]);
    $sesiuniTotale = (int)$stmtSesiuni->fetchColumn();

    // ── Skills — creștere reală bazată pe ce a demonstrat userul ──
    $gc  = (int)$profile['gandire_critica'];
    $ce  = (int)$profile['claritate_emotionala'];
    $com = (int)$profile['comunicare'];
    $rez = (int)$profile['rezistenta'];

    $gc  = min(100, $gc  + ($skillsDelta['gandire_critica']      ?? 0));
    $ce  = min(100, $ce  + ($skillsDelta['claritate_emotionala'] ?? 0));
    $com = min(100, $com + ($skillsDelta['comunicare']           ?? 0));
    $rez = min(100, $rez + ($skillsDelta['rezistenta']           ?? 0));

    // ── Nivel și Stil ──
    // Aplicăm automat detecția AI după minim 4 mesaje în sesiune,
    // ca să nu sară prea repede la primul mesaj
    $stmtMsgCount = $pdo->prepare("
        SELECT COUNT(*) FROM messages WHERE session_id = ? AND role = 'user'
    ");
    $stmtMsgCount->execute([$sessionId]);
    $msgCountInSess = (int)$stmtMsgCount->fetchColumn();

    $nivelCurent = $profile['nivel'] ?? 'incepator';
    $stilCurent  = $profile['stil']  ?? 'analitic';

    if ($detectedNivel && $msgCountInSess >= 4) {
        $nivelCurent = $detectedNivel;
    }
    if ($detectedStil && $msgCountInSess >= 4) {
        $stilCurent = $detectedStil;
    }

    // Păstrăm și sugestiile pentru transparență în UI
    $nivelAiSugest = ($detectedNivel && $detectedNivel !== ($profile['nivel'] ?? '')) ? $detectedNivel : null;
    $stilAiSugest  = ($detectedStil  && $detectedStil  !== ($profile['stil']  ?? '')) ? $detectedStil  : null;

    $pdo->prepare("
        UPDATE user_profiles SET
            gandire_critica      = ?,
            claritate_emotionala = ?,
            comunicare           = ?,
            rezistenta           = ?,
            sesiuni_totale       = ?,
            serie_activa         = ?,
            ultima_sesiune       = ?,
            nivel                = ?,
            nivel_ai_sugest      = ?,
            stil_ai_sugest       = ?,
            updated_at           = NOW()
        WHERE user_id = ?
    ")->execute([
        $gc, $ce, $com, $rez,
        $sesiuniTotale, $serieActiva, $today,
        $nivelCurent,
        $nivelAiSugest,
        $stilAiSugest,
        $userId,
    ]);

    return [
        'gandire_critica'      => $gc,
        'claritate_emotionala' => $ce,
        'comunicare'           => $com,
        'rezistenta'           => $rez,
        'sesiuni_totale'       => $sesiuniTotale,
        'serie_activa'         => $serieActiva,
        'nivel'                => $nivelCurent,
        'stil'                 => $stilCurent,
        'nivel_ai_sugest'      => $nivelAiSugest,
        'stil_ai_sugest'       => $stilAiSugest,
    ];
}

function handleImageUpload(array $file): ?string
{
    if ($file['error'] !== UPLOAD_ERR_OK) return null;

    $allowedMime = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $mime        = mime_content_type($file['tmp_name']);

    if ($file['size'] > MAX_UPLOAD_MB * 1024 * 1024) return null;
    if (!in_array($mime, $allowedMime, true)) return null;
    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);

    $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = uniqid('img_', true) . '.' . $ext;

    return move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $filename)
        ? UPLOAD_URL . $filename
        : null;
}