<?php
// ============================================
// MindForge — Auth API
// Salvează ca: /api/auth.php
// ============================================
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once __DIR__ . '/../config/db.php';
$pdo = getDB();

// ── Suport JSON body (pentru fetch din chat.php) ──
$jsonInput = [];
$rawBody   = file_get_contents('php://input');
if ($rawBody) $jsonInput = json_decode($rawBody, true) ?? [];

$action = $_GET['action'] ?? $_POST['action'] ?? $jsonInput['action'] ?? '';

match ($action) {
    'login'             => handleLogin(),
    'register'          => handleRegister(),
    'logout'            => handleLogout(),
    'update_profile'    => handleUpdateProfile(),
    'change_password'   => handleChangePassword(),
    'delete_account'    => handleDeleteAccount(),
    'accept_ai_sugest'  => handleAcceptAiSugest($jsonInput),
    'ignore_ai_sugest'  => handleIgnoreAiSugest(),
    default             => redirect('/MindForge/login.php'),
};

// ============================================
// LOGIN
// ============================================
function handleLogin(): void
{
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$email || !$password) {
        flashError('Completează toate câmpurile.');
        redirect('/MindForge/login.php');
    }

    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([strtolower($email)]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        flashError('Email sau parolă incorectă.');
        redirect('/MindForge/login.php');
    }

    $pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?")->execute([$user['id']]);

    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];

    redirect('/MindForge/dashboard.php');
}

// ============================================
// REGISTER
// ============================================
function handleRegister(): void
{
    $firstName = trim($_POST['first_name']       ?? '');
    $lastName  = trim($_POST['last_name']        ?? '');
    $email     = strtolower(trim($_POST['email'] ?? ''));
    $password  = $_POST['password']              ?? '';
    $confirm   = $_POST['password_confirm']      ?? '';

    if (!$firstName || !$lastName || !$email || !$password) {
        flashError('Completează toate câmpurile obligatorii.');
        redirect('/MindForge/register.php');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flashError('Adresa de email nu este validă.');
        redirect('/MindForge/register.php');
    }

    if (strlen($password) < 8) {
        flashError('Parola trebuie să aibă cel puțin 8 caractere.');
        redirect('/MindForge/register.php');
    }

    if ($password !== $confirm) {
        flashError('Parolele nu se potrivesc.');
        redirect('/MindForge/register.php');
    }

    global $pdo;

    $check = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $check->execute([$email]);
    if ($check->fetch()) {
        flashError('Există deja un cont cu această adresă de email.');
        redirect('/MindForge/register.php');
    }

    $name = trim("$firstName $lastName");
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    $pdo->beginTransaction();
    try {
        $ins = $pdo->prepare("INSERT INTO users (name, email, password_hash, created_at) VALUES (?, ?, ?, NOW())");
        $ins->execute([$name, $email, $hash]);
        $userId = (int)$pdo->lastInsertId();

        $pdo->prepare("INSERT INTO user_profiles (user_id, nivel, stil, sesiuni_totale, serie_activa, gandire_critica, claritate_emotionala, comunicare, rezistenta) VALUES (?, 'incepator', 'analitic', 0, 0, 0, 0, 0, 0)")
            ->execute([$userId]);

        $pdo->commit();
    } catch (\Exception $e) {
        $pdo->rollBack();
        flashError('Eroare internă. Încearcă din nou.');
        redirect('/MindForge/register.php');
    }

    $_SESSION['user_id']    = $userId;
    $_SESSION['user_name']  = $name;
    $_SESSION['user_email'] = $email;

    redirect('/MindForge/dashboard.php');
}

// ============================================
// LOGOUT
// ============================================
function handleLogout(): void
{
    session_unset();
    session_destroy();
    redirect('/MindForge/login.php');
}

// ============================================
// UPDATE PROFILE
// ============================================
function handleUpdateProfile(): void
{
    requireAuth();
    global $pdo;

    $userId = $_SESSION['user_id'];
    $name   = trim($_POST['name']  ?? '');
    $bio    = trim($_POST['bio']   ?? '');
    $nivel  = in_array($_POST['nivel'] ?? '', ['incepator','intermediar','avansat']) ? $_POST['nivel'] : 'incepator';
    $stil   = in_array($_POST['stil']  ?? '', ['analitic','consistent','grabit','superficial']) ? $_POST['stil'] : 'analitic';

    if (!$name) {
        flashError('Numele nu poate fi gol.');
        redirect('/MindForge/dashboard.php');
    }

    $pdo->prepare("UPDATE users SET name = ? WHERE id = ?")->execute([$name, $userId]);

    // Când userul salvează manual, ștergem și sugestiile AI (le-a depășit)
    $pdo->prepare("UPDATE user_profiles SET nivel = ?, stil = ?, bio = ?, nivel_ai_sugest = NULL, stil_ai_sugest = NULL WHERE user_id = ?")
        ->execute([$nivel, $stil, $bio, $userId]);

    $_SESSION['user_name'] = $name;

    flashSuccess('Profilul a fost actualizat cu succes!');
    redirect('/MindForge/dashboard.php');
}

// ============================================
// CHANGE PASSWORD
// ============================================
function handleChangePassword(): void
{
    requireAuth();
    global $pdo;

    $userId  = $_SESSION['user_id'];
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password']     ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$current || !$new || !$confirm) {
        flashError('Completează toate câmpurile parolei.');
        redirect('/MindForge/dashboard.php');
    }

    if (strlen($new) < 8) {
        flashError('Parola nouă trebuie să aibă cel puțin 8 caractere.');
        redirect('/MindForge/dashboard.php');
    }

    if ($new !== $confirm) {
        flashError('Parolele noi nu se potrivesc.');
        redirect('/MindForge/dashboard.php');
    }

    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($current, $user['password_hash'])) {
        flashError('Parola curentă este incorectă.');
        redirect('/MindForge/dashboard.php');
    }

    $hash = password_hash($new, PASSWORD_BCRYPT, ['cost' => 12]);
    $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$hash, $userId]);

    flashSuccess('Parola a fost schimbată cu succes!');
    redirect('/MindForge/dashboard.php');
}

// ============================================
// DELETE ACCOUNT
// ============================================
function handleDeleteAccount(): void
{
    requireAuth();
    global $pdo;

    $userId = $_SESSION['user_id'];
    $pdo->prepare("DELETE FROM user_profiles WHERE user_id = ?")->execute([$userId]);
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);

    session_unset();
    session_destroy();
    redirect('/MindForge/login.php');
}

// ============================================
// ACCEPT AI SUGEST — aplică sugestia AI în profil
// ============================================
function handleAcceptAiSugest(array $jsonInput): void
{
    header('Content-Type: application/json');
    requireAuthJson();
    global $pdo;

    $uid   = (int)$_SESSION['user_id'];
    $nivel = $jsonInput['nivel'] ?? '';
    $stil  = $jsonInput['stil']  ?? '';

    $validNivel = ['incepator', 'intermediar', 'avansat'];
    $validStil  = ['analitic', 'consistent', 'grabit', 'superficial'];

    $sets   = [];
    $params = [];

    if ($nivel && in_array($nivel, $validNivel)) { $sets[] = 'nivel = ?'; $params[] = $nivel; }
    if ($stil  && in_array($stil,  $validStil))  { $sets[] = 'stil = ?';  $params[] = $stil;  }

    // Șterge sugestiile indiferent
    $sets[]   = 'nivel_ai_sugest = NULL';
    $sets[]   = 'stil_ai_sugest = NULL';
    $sets[]   = 'updated_at = NOW()';
    $params[] = $uid;

    $pdo->prepare("UPDATE user_profiles SET " . implode(', ', $sets) . " WHERE user_id = ?")
        ->execute($params);

    echo json_encode(['success' => true]);
    exit;
}

// ============================================
// IGNORE AI SUGEST — șterge sugestia fără să o aplice
// ============================================
function handleIgnoreAiSugest(): void
{
    header('Content-Type: application/json');
    requireAuthJson();
    global $pdo;

    $uid = (int)$_SESSION['user_id'];
    $pdo->prepare("UPDATE user_profiles SET nivel_ai_sugest = NULL, stil_ai_sugest = NULL, updated_at = NOW() WHERE user_id = ?")
        ->execute([$uid]);

    echo json_encode(['success' => true]);
    exit;
}

// ============================================
// HELPERS
// ============================================
function requireAuth(): void
{
    if (!isset($_SESSION['user_id'])) redirect('/MindForge/login.php');
}

function requireAuthJson(): void
{
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Neautentificat']);
        exit;
    }
}

function flashError(string $msg): void
{
    $_SESSION['auth_error'] = $msg;
}

function flashSuccess(string $msg): void
{
    $_SESSION['auth_success'] = $msg;
    $_SESSION['dash_success'] = $msg;
}

function redirect(string $url): never
{
    header("Location: $url");
    exit;
}