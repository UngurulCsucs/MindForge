<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /MindForge/login.php');
    exit;
}

require_once __DIR__ . '/config/db.php';
$pdo = getDB();

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header('Location: /MindForge/login.php');
    exit;
}

$stmt2 = $pdo->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
$stmt2->execute([$user['id']]);
$profile = $stmt2->fetch(PDO::FETCH_ASSOC) ?: [
    'nivel'               => 'incepator',
    'stil'                => 'analitic',
    'sesiuni_totale'      => 0,
    'serie_activa'        => 0,
    'gandire_critica'     => 0,
    'claritate_emotionala'=> 0,
    'comunicare'          => 0,
    'rezistenta'          => 0,
];

// Fetch sesiuni pentru sidebar
$stmtSess = $pdo->prepare("
    SELECT s.id, s.titlu, s.subiect, s.status, s.created_at,
           COUNT(m.id) as nr_mesaje
    FROM sessions s
    LEFT JOIN messages m ON m.session_id = s.id
    WHERE s.user_id = ?
    GROUP BY s.id
    ORDER BY s.created_at DESC
    LIMIT 30
");
$stmtSess->execute([$user['id']]);
$sidebarSessions = $stmtSess->fetchAll(PDO::FETCH_ASSOC);

// Sesiune activă din URL
$activeSessionId = isset($_GET['session']) ? (int)$_GET['session'] : 0;
// Verifică că sesiunea aparține userului
if ($activeSessionId) {
    $stmtCheck = $pdo->prepare("SELECT id FROM sessions WHERE id = ? AND user_id = ?");
    $stmtCheck->execute([$activeSessionId, $user['id']]);
    if (!$stmtCheck->fetch()) $activeSessionId = 0;
}

$nameParts = explode(' ', $user['name']);
$initials  = mb_strtoupper(mb_substr($nameParts[0], 0, 1)) . (isset($nameParts[1]) ? mb_strtoupper(mb_substr($nameParts[1], 0, 1)) : '');
$firstName = $nameParts[0];

$nivelLabel = ['incepator' => 'Începător', 'intermediar' => 'Intermediar', 'avansat' => 'Avansat'];
$stilLabel  = ['analitic' => 'Analitic', 'superficial' => 'Superficial', 'grabit' => 'Grăbit', 'consistent' => 'Consistent'];
?>
<!DOCTYPE html>
<html lang="ro" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MindForge — Sesiune </title>
<link rel="icon" href="data:,">
<script src="/MindForge/assets/js/theme.js"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root {
  --bg:#0a0a0f; --bg2:#111118; --bg3:#16161e;
  --surface:#1e1e28; --surface2:#252533;
  --border:rgba(255,255,255,0.07); --border2:rgba(255,255,255,0.13);
  --text:#f0eff8; --text2:#9896b0; --text3:#5e5d78;
  --accent:#7c6af7; --accent2:#a395fb;
  --teal:#2dd4a0; --amber:#f5a623; --coral:#f76a6a;
  --radius:12px; --radius-sm:8px;
  --font-h:'Syne',sans-serif; --font-b:'DM Sans',sans-serif; --font-m:'DM Mono',monospace;
  --tr:0.2s cubic-bezier(0.4,0,0.2,1);
}
[data-theme="light"] {
  --bg:#f4f3fa; --bg2:#eceaf6; --bg3:#e4e2f0;
  --surface:#fff; --surface2:#f8f7fe;
  --border:rgba(0,0,0,0.07); --border2:rgba(0,0,0,0.13);
  --text:#14131f; --text2:#4e4c68; --text3:#9997b5;
  --accent:#5b49e8; --accent2:#7c6af7;
  --teal:#0f9e6e; --amber:#c87e10; --coral:#d94040;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html,body{height:100%;overflow:hidden;}
body{font-family:var(--font-b);background:var(--bg);color:var(--text);display:flex;flex-direction:column;}
.mf-header{height:58px;display:flex;align-items:center;justify-content:space-between;padding:0 1.25rem;background:var(--bg2);border-bottom:1px solid var(--border);flex-shrink:0;position:relative;z-index:10;}
.mf-logo{font-family:var(--font-h);font-size:1.2rem;font-weight:800;letter-spacing:-0.03em;color:var(--text);text-decoration:none;display:flex;align-items:center;gap:8px;}
.mf-logo-mark{width:28px;height:28px;border-radius:8px;background:var(--accent);display:flex;align-items:center;justify-content:center;transition:transform var(--tr),box-shadow var(--tr);}
.mf-logo:hover .mf-logo-mark{transform:rotate(-8deg);box-shadow:0 0 16px rgba(124,106,247,0.5);}
.mf-logo-mark svg{width:15px;height:15px;fill:white;}
.mf-header-actions{display:flex;align-items:center;gap:0.5rem;}
.mf-layout{flex:1;display:grid;grid-template-columns:260px 1fr 280px;overflow:hidden;}
/* Sidebar */
.mf-sidebar{background:var(--bg2);border-right:1px solid var(--border);display:flex;flex-direction:column;overflow:hidden;}
.mf-sidebar-head{padding:1rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;}
.mf-sidebar-title{font-family:var(--font-m);font-size:0.7rem;color:var(--text3);text-transform:uppercase;letter-spacing:0.1em;}
.mf-sessions{flex:1;overflow-y:auto;padding:0.5rem;}
.mf-sessions::-webkit-scrollbar{width:3px;}
.mf-sessions::-webkit-scrollbar-thumb{background:var(--surface2);border-radius:99px;}
.mf-session-item{display:flex;align-items:center;gap:8px;padding:0.6rem 0.75rem;border-radius:var(--radius-sm);font-size:0.83rem;color:var(--text2);cursor:pointer;transition:all var(--tr);text-decoration:none;}
.mf-session-item:hover{background:var(--surface);color:var(--text);}
.mf-session-item--active{background:rgba(124,106,247,0.15);color:var(--accent2);}
.mf-session-item__icon{font-size:0.9rem;flex-shrink:0;}
.mf-session-item__body{flex:1;min-width:0;}
.mf-session-item__title{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:0.83rem;}
.mf-session-item__meta{font-family:var(--font-m);font-size:0.65rem;color:var(--text3);margin-top:2px;}
.mf-session-empty{padding:1.5rem 0.75rem;text-align:center;color:var(--text3);font-size:0.8rem;line-height:1.6;}
/* Chat */
.mf-chat{display:flex;flex-direction:column;background:var(--bg);overflow:hidden;position:relative;}
.mf-messages{flex:1;overflow-y:auto;padding:2rem 1.5rem;display:flex;flex-direction:column;gap:1rem;scroll-behavior:smooth;}
.mf-messages::-webkit-scrollbar{width:4px;}
.mf-messages::-webkit-scrollbar-thumb{background:var(--surface2);border-radius:99px;}
.mf-welcome{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:3rem;gap:1rem;}
.mf-welcome-icon{width:64px;height:64px;border-radius:18px;background:rgba(124,106,247,0.15);display:flex;align-items:center;justify-content:center;font-size:1.8rem;margin-bottom:0.5rem;}
.mf-welcome h2{font-family:var(--font-h);font-size:1.4rem;font-weight:700;letter-spacing:-0.02em;}
.mf-welcome p{font-size:0.9rem;color:var(--text2);max-width:360px;line-height:1.7;font-weight:300;}
.mf-welcome-starters{display:flex;flex-wrap:wrap;gap:0.5rem;justify-content:center;margin-top:1rem;}
.mf-starter{padding:0.5rem 1rem;border-radius:99px;background:var(--surface);border:1px solid var(--border2);font-size:0.82rem;color:var(--text2);cursor:pointer;transition:all var(--tr);}
.mf-starter:hover{background:var(--surface2);color:var(--text);border-color:var(--accent);}
.mf-msg{display:flex;gap:0.75rem;align-items:flex-start;animation:msgIn 0.3s ease;}
.mf-msg--user{flex-direction:row-reverse;}
@keyframes msgIn{from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);}}
.mf-msg__avatar{width:32px;height:32px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:0.7rem;font-weight:600;flex-shrink:0;font-family:var(--font-h);}
.mf-msg--assistant .mf-msg__avatar{background:rgba(124,106,247,0.2);color:var(--accent2);}
.mf-msg--user .mf-msg__avatar{background:rgba(45,212,160,0.2);color:var(--teal);}
.mf-msg__bubble{max-width:72%;display:flex;flex-direction:column;gap:0.4rem;}
.mf-msg__text{padding:0.75rem 1rem;border-radius:12px 12px 12px 3px;font-size:0.9rem;line-height:1.65;color:var(--text);background:var(--surface);word-wrap:break-word;}
.mf-msg--user .mf-msg__text{border-radius:12px 12px 3px 12px;background:rgba(124,106,247,0.18);}
.mf-msg__image{max-width:260px;border-radius:10px;margin-bottom:0.25rem;border:1px solid var(--border2);}
.mf-msg__time{font-family:var(--font-m);font-size:0.68rem;color:var(--text3);padding:0 4px;}
.mf-msg--user .mf-msg__time{text-align:right;}
.mf-typing{display:none;align-items:center;gap:0.75rem;padding:0 1.5rem 0.5rem;}
.mf-typing__avatar{width:32px;height:32px;border-radius:9px;background:rgba(124,106,247,0.2);display:flex;align-items:center;justify-content:center;font-size:0.7rem;font-weight:600;color:var(--accent2);font-family:var(--font-h);flex-shrink:0;}
.mf-typing__dots{background:var(--surface);padding:0.75rem 1rem;border-radius:12px 12px 12px 3px;display:flex;gap:5px;align-items:center;}
.mf-typing__dot{width:6px;height:6px;border-radius:50%;background:var(--text3);animation:dot 1.4s ease infinite;}
.mf-typing__dot:nth-child(2){animation-delay:0.2s;}
.mf-typing__dot:nth-child(3){animation-delay:0.4s;}
@keyframes dot{0%,80%,100%{transform:scale(0.7);opacity:0.4;}40%{transform:scale(1);opacity:1;}}
.mf-reflection{background:linear-gradient(135deg,rgba(124,106,247,0.12),rgba(45,212,160,0.08));border:1px solid rgba(124,106,247,0.25);border-radius:var(--radius);padding:1.25rem;margin:0.5rem 0;}
.mf-reflection__inner{display:flex;flex-direction:column;gap:0.6rem;}
.mf-reflection__icon{font-size:1.5rem;}
.mf-reflection p{font-size:0.87rem;color:var(--text2);line-height:1.6;}
.mf-reflection strong{color:var(--text);font-weight:500;}
.mf-reflection__actions{display:flex;gap:0.5rem;margin-top:0.5rem;}
.mf-input-area{padding:1rem 1.5rem 1.25rem;background:var(--bg);border-top:1px solid var(--border);flex-shrink:0;}
.mf-image-preview{display:none;align-items:center;gap:0.75rem;padding:0.6rem 0.75rem;background:var(--surface);border-radius:var(--radius-sm);margin-bottom:0.6rem;border:1px solid var(--border2);}
.mf-image-preview img{width:40px;height:40px;object-fit:cover;border-radius:6px;}
.mf-image-preview span{font-size:0.8rem;color:var(--text2);flex:1;}
.mf-input-row{display:flex;gap:0.5rem;align-items:flex-end;background:var(--surface);border:1px solid var(--border2);border-radius:var(--radius);padding:0.6rem 0.75rem;transition:border-color var(--tr),box-shadow var(--tr);}
.mf-input-row:focus-within{border-color:rgba(124,106,247,0.5);box-shadow:0 0 0 3px rgba(124,106,247,0.1);}
.mf-input{flex:1;background:transparent;border:none;outline:none;font-family:var(--font-b);font-size:0.92rem;color:var(--text);resize:none;min-height:24px;max-height:160px;line-height:1.5;}
.mf-input::placeholder{color:var(--text3);}
.mf-input-actions{display:flex;gap:0.25rem;align-items:flex-end;flex-shrink:0;}
.mf-btn{font-family:var(--font-b);font-size:0.83rem;font-weight:500;padding:0.45rem 0.9rem;border-radius:var(--radius-sm);border:1px solid var(--border2);background:transparent;color:var(--text2);cursor:pointer;transition:all var(--tr);display:inline-flex;align-items:center;gap:5px;}
.mf-btn:hover{background:var(--surface2);color:var(--text);}
.mf-btn--icon{width:34px;height:34px;padding:0;justify-content:center;}
.mf-btn--icon svg{width:16px;height:16px;}
.mf-btn--primary{background:var(--accent);color:#fff;border-color:transparent;}
.mf-btn--primary:hover{background:var(--accent2);box-shadow:0 4px 16px rgba(124,106,247,0.4);color:#fff;}
.mf-btn--primary:disabled{opacity:0.5;cursor:not-allowed;box-shadow:none;}
.mf-btn--ghost{border-color:transparent;}
.mf-btn--ghost:hover{background:var(--surface);}
.mf-send--loading svg{animation:spin 1s linear infinite;}
@keyframes spin{to{transform:rotate(360deg);}}
.mf-input-hint{font-size:0.72rem;color:var(--text3);margin-top:0.5rem;font-family:var(--font-m);text-align:center;}
/* Profile panel */
.mf-profile{background:var(--bg2);border-left:1px solid var(--border);display:flex;flex-direction:column;overflow-y:auto;padding:1.25rem;gap:1.25rem;}
.mf-profile::-webkit-scrollbar{width:4px;}
.mf-profile::-webkit-scrollbar-thumb{background:var(--surface2);border-radius:99px;}
.mf-profile-user{display:flex;align-items:center;gap:10px;padding:1rem;background:var(--surface);border-radius:var(--radius);border:1px solid var(--border);}
.mf-avatar{width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,var(--accent),var(--teal));display:flex;align-items:center;justify-content:center;font-family:var(--font-h);font-size:0.95rem;font-weight:700;color:white;flex-shrink:0;}
.mf-profile-name{font-size:0.9rem;font-weight:500;color:var(--text);}
.mf-profile-nivel{font-family:var(--font-m);font-size:0.7rem;color:var(--accent2);margin-top:2px;}
.mf-prof-label{font-family:var(--font-m);font-size:0.68rem;color:var(--text3);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.75rem;display:block;}
.mf-skill{margin-bottom:0.85rem;}
.mf-skill:last-child{margin-bottom:0;}
.mf-skill-header{display:flex;justify-content:space-between;font-size:0.78rem;color:var(--text2);margin-bottom:5px;}
.mf-skill-pct{font-family:var(--font-m);color:var(--text3);}
.mf-bar{height:4px;background:var(--bg3);border-radius:99px;overflow:hidden;}
.mf-bar-fill{height:100%;border-radius:99px;transition:width 1s cubic-bezier(0.4,0,0.2,1);}
.mf-bar-fill.purple{background:linear-gradient(90deg,var(--accent),var(--accent2));}
.mf-bar-fill.teal{background:linear-gradient(90deg,var(--teal),#4ce8b8);}
.mf-bar-fill.amber{background:linear-gradient(90deg,var(--amber),#f7c564);}
.mf-bar-fill.coral{background:linear-gradient(90deg,var(--coral),#f7a0a0);}
.mf-stats-grid{display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;}
.mf-stat-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-sm);padding:0.75rem;}
.mf-stat-val{font-family:var(--font-h);font-size:1.4rem;font-weight:700;letter-spacing:-0.03em;color:var(--text);}
.mf-stat-lbl{font-size:0.7rem;color:var(--text3);margin-top:2px;line-height:1.3;}
.mf-stil-badge{display:inline-flex;align-items:center;gap:6px;padding:0.4rem 0.8rem;border-radius:99px;background:rgba(124,106,247,0.12);color:var(--accent2);font-size:0.78rem;font-family:var(--font-m);}
.mf-stil-dot{width:6px;height:6px;border-radius:50%;background:var(--accent);}
.mf-toast{position:fixed;bottom:2rem;left:50%;transform:translateX(-50%) translateY(20px);padding:0.65rem 1.25rem;border-radius:99px;font-size:0.85rem;z-index:9999;opacity:0;transition:all 0.3s ease;pointer-events:none;white-space:nowrap;}
.mf-toast--info{background:var(--surface2);color:var(--text);border:1px solid var(--border2);}
.mf-toast--error{background:rgba(247,106,106,0.15);color:var(--coral);border:1px solid rgba(247,106,106,0.3);}
.mf-toast--visible{opacity:1;transform:translateX(-50%) translateY(0);}
.mf-theme-btn{width:34px;height:34px;border-radius:var(--radius-sm);border:1px solid var(--border2);background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--text2);transition:all var(--tr);}
.mf-theme-btn:hover{background:var(--surface);color:var(--text);}
.mf-theme-btn svg{width:15px;height:15px;transition:transform 0.4s;}
.mf-theme-btn:hover svg{transform:rotate(20deg);}
.mf-divider{height:1px;background:var(--border);}
/* Loading overlay pentru restaurare sesiune */
.mf-loading-history{display:flex;align-items:center;justify-content:center;flex:1;gap:0.75rem;color:var(--text3);font-size:0.85rem;}
.mf-loading-history svg{width:20px;height:20px;animation:spin 1s linear infinite;color:var(--accent);}
@media(max-width:900px){.mf-layout{grid-template-columns:1fr;}.mf-sidebar,.mf-profile{display:none;}}
</style>
</head>
<body>

<header class="mf-header">
  <a href="/MindForge/" class="mf-logo">
    <div class="mf-logo-mark">
      <svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
    </div>
    MindForge
  </a>
  <div class="mf-header-actions">
    <a href="/MindForge/dashboard.php" class="mf-btn mf-btn--ghost" style="font-size:0.82rem;">← Profil</a>
    <button class="mf-theme-btn" id="themeToggle" aria-label="Schimbă tema">
      <svg id="iconMoon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
      <svg id="iconSun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="display:none"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/></svg>
    </button>
  </div>
</header>

<div class="mf-layout">

  <!-- Sidebar: lista sesiuni -->
  <aside class="mf-sidebar">
    <div class="mf-sidebar-head">
      <span class="mf-sidebar-title">Sesiuni</span>
      <button href=" "class="mf-btn mf-btn--icon" id="mf-new-session" title="Sesiune nouă">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      </button>
    </div>
    <div class="mf-sessions" id="mf-sessions">
      <?php if (empty($sidebarSessions)): ?>
        <div class="mf-session-empty">
          Nicio sesiune încă.<br>Începe o conversație!
        </div>
      <?php else: ?>
        <?php foreach ($sidebarSessions as $s):
          $titlu = $s['titlu'] ?: ($s['subiect'] ?: 'Sesiune fără titlu');
          $icon  = match($s['status']) {
            'activa'     => '🟢',
            'finalizata' => '✅',
            'reflectie'  => '🔍',
            default      => '💬',
          };
          $isActive = ($s['id'] == $activeSessionId);
          $data = date('d M', strtotime($s['created_at']));
        ?>
        <a href="/MindForge/chat.php?session=<?= $s['id'] ?>"
           class="mf-session-item<?= $isActive ? ' mf-session-item--active' : '' ?>"
           data-session-id="<?= $s['id'] ?>">
          <span class="mf-session-item__icon"><?= $icon ?></span>
          <div class="mf-session-item__body">
            <div class="mf-session-item__title"><?= htmlspecialchars($titlu) ?></div>
            <div class="mf-session-item__meta"><?= $data ?> · <?= $s['nr_mesaje'] ?> msg</div>
          </div>
        </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </aside>

  <!-- Chat principal -->
  <main class="mf-chat">
    <?php if ($activeSessionId): ?>
      <!-- Se va popula prin JS cu istoricul sesiunii -->
      <div class="mf-loading-history" id="mf-loading-history">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
        Se încarcă sesiunea...
      </div>
      <div class="mf-messages" id="mf-messages" style="display:none"></div>
    <?php else: ?>
      <div class="mf-welcome" id="mf-welcome">
        <div class="mf-welcome-icon">🧠</div>
        <h2>Bună, <?= htmlspecialchars($firstName) ?>!</h2>
        <p>Nu sunt aici să îți dau răspunsuri. Sunt aici să te ajut să le descoperi singur. Ce vrei să explorezi astăzi?</p>
        <div class="mf-welcome-starters">
          <button class="mf-starter" onclick="setStarter(this)">Nu înțeleg de ce mă simt blocat</button>
          <button class="mf-starter" onclick="setStarter(this)">Vreau să înțeleg mai bine matematica</button>
          <button class="mf-starter" onclick="setStarter(this)">Am dificultăți în relații sociale</button>
          <button class="mf-starter" onclick="setStarter(this)">Vreau să îmi construiesc un scop în viață</button>
          <button class="mf-starter" onclick="setStarter(this)">Nu știu cum să comunic mai bine</button>
          <button class="mf-starter" onclick="setStarter(this)">Mă confrunt cu anxietatea</button>
        </div>
      </div>
      <div class="mf-messages" id="mf-messages" style="display:none"></div>
    <?php endif; ?>

    <div class="mf-typing" id="mf-typing">
      <div class="mf-typing__avatar">MF</div>
      <div class="mf-typing__dots">
        <div class="mf-typing__dot"></div>
        <div class="mf-typing__dot"></div>
        <div class="mf-typing__dot"></div>
      </div>
    </div>

    <div class="mf-input-area">
      <div class="mf-image-preview" id="mf-image-preview">
        <img src="" alt="preview">
        <span>Imagine atașată</span>
        <button class="mf-btn mf-btn--icon mf-btn--ghost" id="mf-remove-image" title="Elimină imaginea">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
      <div class="mf-input-row">
        <textarea id="mf-input" class="mf-input" placeholder="Scrie un gând, o întrebare, o problemă reală..." rows="1" maxlength="4000"></textarea>
        <div class="mf-input-actions">
          <button class="mf-btn mf-btn--icon mf-btn--ghost" id="mf-image-btn" title="Atașează imagine">
            <!-- <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"> -->
              <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
              <polyline points="21 15 16 10 5 21"/>
            </svg>
          </button>
          <input type="file" id="mf-image-input" accept="image/*" style="display:none">
          <button class="mf-btn mf-btn--primary mf-btn--icon" id="mf-send" title="Trimite (Enter)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
              <line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/>
            </svg>
          </button>
        </div>
      </div>
      <p class="mf-input-hint">Enter pentru trimite · Shift+Enter pentru linie nouă </p>
    </div>
  </main>

  <!-- Profil panel dreapta -->
  <aside class="mf-profile">
    <div class="mf-profile-user">
      <div class="mf-avatar"><?= htmlspecialchars($initials) ?></div>
      <div>
        <div class="mf-profile-name"><?= htmlspecialchars($user['name']) ?></div>
        <div class="mf-profile-nivel" id="mf-nivel"><?= $nivelLabel[$profile['nivel']] ?? 'Începător' ?></div>
      </div>
    </div>
    <div class="mf-prof-section">
      <span class="mf-prof-label">// statistici</span>
      <div class="mf-stats-grid">
        <div class="mf-stat-card">
          <div class="mf-stat-val" id="mf-stat-sesiuni"><?= count($sidebarSessions) ?></div>
          <div class="mf-stat-lbl">sesiuni totale</div>
        </div>
        <div class="mf-stat-card">
          <div class="mf-stat-val" id="mf-stat-serie" style="color:var(--teal)"><?= $profile['serie_activa'] ?> <span style="font-size:0.9rem">zile</span></div>
          <div class="mf-stat-lbl">serie activă</div>
        </div>
      </div>
    </div>
    <div class="mf-divider"></div>
    <div class="mf-prof-section">
      <span class="mf-prof-label">// skill-uri</span>
      <div class="mf-skill">
        <div class="mf-skill-header"><span>Gândire critică</span><span class="mf-skill-pct"><?= $profile['gandire_critica'] ?>%</span></div>
        <div class="mf-bar"><div class="mf-bar-fill purple" id="mf-bar-gc" style="width:<?= $profile['gandire_critica'] ?>%"></div></div>
      </div>
      <div class="mf-skill">
        <div class="mf-skill-header"><span>Claritate emoțională</span><span class="mf-skill-pct"><?= $profile['claritate_emotionala'] ?>%</span></div>
        <div class="mf-bar"><div class="mf-bar-fill teal" id="mf-bar-ce" style="width:<?= $profile['claritate_emotionala'] ?>%"></div></div>
      </div>
      <div class="mf-skill">
        <div class="mf-skill-header"><span>Comunicare</span><span class="mf-skill-pct"><?= $profile['comunicare'] ?>%</span></div>
        <div class="mf-bar"><div class="mf-bar-fill amber" id="mf-bar-com" style="width:<?= $profile['comunicare'] ?>%"></div></div>
      </div>
      <div class="mf-skill">
        <div class="mf-skill-header"><span>Rezistență</span><span class="mf-skill-pct"><?= $profile['rezistenta'] ?>%</span></div>
        <div class="mf-bar"><div class="mf-bar-fill coral" id="mf-bar-rez" style="width:<?= $profile['rezistenta'] ?>%"></div></div>
      </div>
    </div>
    <div class="mf-divider"></div>
    <div class="mf-prof-section">
      <span class="mf-prof-label">// stil detectat</span>
      <div class="mf-stil-badge">
        <div class="mf-stil-dot"></div>
        <?= $stilLabel[$profile['stil']] ?? 'Analitic' ?>
      </div>
    </div>
  </aside>

</div>

<script>
window.MF_USER_INITIALS = '<?= htmlspecialchars($initials) ?>';
window.MF_ACTIVE_SESSION = <?= $activeSessionId ?: 'null' ?>;

const MindForge = (() => {
  let sessionId    = window.MF_ACTIVE_SESSION;
  let isLoading    = false;
  let imageFile    = null;
  let messageCount = 0;

  const $  = id => document.getElementById(id);
  const messagesEl   = () => $('mf-messages');
  const inputEl      = () => $('mf-input');
  const sendBtn      = () => $('mf-send');
  const imageBtn     = () => $('mf-image-btn');
  const imageInput   = () => $('mf-image-input');
  const imagePreview = () => $('mf-image-preview');
  const typingEl     = () => $('mf-typing');
  const welcomeEl    = () => $('mf-welcome');
  const loadingEl    = () => $('mf-loading-history');

  function init() {
    sendBtn()?.addEventListener('click', sendMessage);
    inputEl()?.addEventListener('keydown', e => {
      if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
    });
    inputEl()?.addEventListener('input', autoResize);
    imageBtn()?.addEventListener('click', () => imageInput()?.click());
    imageInput()?.addEventListener('change', e => {
      const file = e.target.files[0];
      if (!file) return;
      if (file.size > 5 * 1024 * 1024) { showToast('Imaginea trebuie să fie sub 5MB', 'error'); return; }
      imageFile = file;
      showImagePreview(file);
    });
    $('mf-new-session')?.addEventListener('click', () => {
      sessionId = null;
      messageCount = 0;
      clearMessages();
      showWelcome();
      // Elimină parametrul session din URL
      history.pushState({}, '', '/MindForge/chat.php');
      // Dezactivează evidențierea din sidebar
      document.querySelectorAll('.mf-session-item--active').forEach(el => el.classList.remove('mf-session-item--active'));
    });
    $('mf-remove-image')?.addEventListener('click', removeImage);
    inputEl()?.focus();
    autoResize();

    // Dacă avem o sesiune activă din URL, o încărcăm
    if (window.MF_ACTIVE_SESSION) {
      loadSessionHistory(window.MF_ACTIVE_SESSION);
    }
  }

  async function loadSessionHistory(sid) {
    try {
      const response = await fetch('/MindForge/api/chat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'load_session', session_id: sid }),
      });
      const data = await response.json();

      // Ascunde loading
      const loading = loadingEl();
      if (loading) loading.style.display = 'none';

      if (data.error || !data.messages) {
        showWelcome();
        showToast('Nu s-a putut încărca sesiunea.', 'error');
        return;
      }

      if (data.messages.length === 0) {
        // Sesiune goală — arată welcome dar cu sesiunea setată
        showWelcome();
      } else {
        hideWelcome();
        data.messages.forEach(m => appendMessage(m.role, m.content, null, false));
        messageCount = data.messages.filter(m => m.role === 'user').length;
        scrollToBottom();
      }
    } catch (err) {
      const loading = loadingEl();
      if (loading) loading.style.display = 'none';
      showWelcome();
      showToast('Eroare la încărcarea sesiunii.', 'error');
    }
  }

  async function sendMessage() {
    if (isLoading) return;
    const text = inputEl()?.value.trim() ?? '';
    if (!text && !imageFile) return;

    setLoading(true);
    hideWelcome();
    appendMessage('user', text, imageFile ? URL.createObjectURL(imageFile) : null);

    if (inputEl()) inputEl().value = '';
    autoResize();
    const imgToSend = imageFile;
    removeImage();
    showTyping();
    messageCount++;

    try {
      let response;
      if (imgToSend) {
        const fd = new FormData();
        fd.append('message', text);
        fd.append('image', imgToSend);
        fd.append('session_id', sessionId || 0);
        fd.append('new_session', sessionId ? '0' : '1');
        response = await fetch('/MindForge/api/chat.php', { method: 'POST', body: fd });
      } else {
        response = await fetch('/MindForge/api/chat.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ message: text, session_id: sessionId || 0, new_session: !sessionId }),
        });
      }

      const data = await response.json();
      hideTyping();

      if (!response.ok || data.error) {
        showToast(data.error || 'Eroare server', 'error');
        setLoading(false);
        return;
      }

      if (data.session_id) {
        sessionId = data.session_id;
        // Actualizează URL fără reload
        history.replaceState({}, '', `/MindForge/chat.php?session=${sessionId}`);
      }
      appendMessage('assistant', data.message);
      if (data.profile) updateProfileUI(data.profile);
      if (messageCount > 0 && messageCount % 8 === 0) setTimeout(promptReflection, 800);

    } catch (err) {
      hideTyping();
      showToast('Conexiune eșuată. Verifică internetul.', 'error');
      console.error(err);
    }

    setLoading(false);
    inputEl()?.focus();
  }

  function appendMessage(role, text, imageUrl = null, animate = true) {
    const wrap = messagesEl();
    if (!wrap) return;

    const div    = document.createElement('div');
    div.className = `mf-msg mf-msg--${role}`;
    if (!animate) div.style.animation = 'none';

    const avatar = document.createElement('div');
    avatar.className = 'mf-msg__avatar';
    avatar.textContent = role === 'assistant' ? 'MF' : (window.MF_USER_INITIALS || 'TU');

    const bubble = document.createElement('div');
    bubble.className = 'mf-msg__bubble';

    if (imageUrl) {
      const img = document.createElement('img');
      img.src = imageUrl; img.className = 'mf-msg__image'; img.alt = 'Imagine atașată';
      bubble.appendChild(img);
    }

    const p = document.createElement('p');
    p.className = 'mf-msg__text';
    // La mesajele vechi (din istoric) nu animăm textul
    if (role === 'assistant' && animate) {
      typeText(p, text);
    } else {
      p.innerHTML = formatText(text);
    }
    bubble.appendChild(p);

    const time = document.createElement('span');
    time.className = 'mf-msg__time';
    time.textContent = new Date().toLocaleTimeString('ro-RO', { hour: '2-digit', minute: '2-digit' });
    bubble.appendChild(time);

    div.appendChild(avatar);
    div.appendChild(bubble);
    wrap.appendChild(div);
    if (animate) scrollToBottom();
  }

  function formatText(s) {
    const esc = t => t.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    return esc(s)
      .replace(/\*\*(.*?)\*\*/g,'<strong>$1</strong>')
      .replace(/\*(.*?)\*/g,'<em>$1</em>')
      .replace(/\n/g,'<br>');
  }

  function typeText(el, text, speed = 16) {
    el.innerHTML = '';
    const chars = [...text];
    let i = 0, raw = '';
    const iv = setInterval(() => {
      raw += chars[i++];
      el.innerHTML = formatText(raw);
      if (i % 30 === 0) scrollToBottom();
      if (i >= chars.length) { clearInterval(iv); scrollToBottom(); }
    }, speed);
  }

  function showTyping() { const e = typingEl(); if (e) e.style.display='flex'; scrollToBottom(); }
  function hideTyping() { const e = typingEl(); if (e) e.style.display='none'; }
  function scrollToBottom() { const w = messagesEl(); if (w) w.scrollTop = w.scrollHeight; }

  function showImagePreview(file) {
    const prev = imagePreview(); if (!prev) return;
    const r = new FileReader();
    r.onload = e => { prev.style.display='flex'; const img=prev.querySelector('img'); if(img) img.src=e.target.result; };
    r.readAsDataURL(file);
  }

  function removeImage() {
    imageFile = null;
    const p = imagePreview(); if (p) p.style.display='none';
    const i = imageInput();   if (i) i.value='';
  }

  function setLoading(s) {
    isLoading = s;
    const b = sendBtn(), i = inputEl();
    if (b) b.disabled = s;
    if (i) i.disabled = s;
    b?.classList.toggle('mf-send--loading', s);
  }

  function autoResize() {
    const el = inputEl(); if (!el) return;
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 160) + 'px';
  }

  function hideWelcome() {
    const w = welcomeEl();  if (w) w.style.display = 'none';
    const m = messagesEl(); if (m) m.style.display = 'flex';
    const l = loadingEl();  if (l) l.style.display = 'none';
  }

  function showWelcome() {
    clearMessages();
    const w = welcomeEl(); if (w) w.style.display = 'flex';
  }

  function clearMessages() {
    const m = messagesEl();
    if (m) { m.innerHTML = ''; m.style.display = 'none'; }
  }

  function promptReflection() {
    const div = document.createElement('div');
    div.className = 'mf-reflection';
    div.innerHTML = `<div class="mf-reflection__inner">
      <span class="mf-reflection__icon">🔍</span>
      <p><strong>Moment de reflecție</strong></p>
      <p>Ai parcurs câteva răspunsuri importante. Ce ai descoperit? Cum ai aplica asta în viața ta reală?</p>
      <div class="mf-reflection__actions">
        <button class="mf-btn mf-btn--ghost" onclick="this.closest('.mf-reflection').remove()">Mai târziu</button>
        <button class="mf-btn mf-btn--primary" onclick="MindForge.startReflection()">Reflectez acum</button>
      </div></div>`;
    messagesEl()?.appendChild(div);
    scrollToBottom();
  }

  function startReflection() {
    document.querySelector('.mf-reflection')?.remove();
    if (inputEl()) inputEl().value = 'Vreau să reflectez asupra sesiunii noastre.';
    sendMessage();
  }

  function updateProfileUI(p) {
    const bar  = (id, v) => { const e=document.getElementById('mf-bar-'+id);  if(e) e.style.width=v+'%'; };
    const text = (id, v) => { const e=document.getElementById(id); if(e) e.textContent=v; };
    bar('gc',  p.gandire_critica);
    bar('ce',  p.claritate_emotionala);
    bar('com', p.comunicare);
    bar('rez', p.rezistenta);
    text('mf-stat-sesiuni', p.sesiuni_totale);
    text('mf-stat-serie',   p.serie_activa + ' zile');
    text('mf-nivel',        p.nivel);
  }

  function showToast(msg, type = 'info') {
    const t = document.createElement('div');
    t.className = `mf-toast mf-toast--${type}`;
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.classList.add('mf-toast--visible'), 10);
    setTimeout(() => { t.classList.remove('mf-toast--visible'); setTimeout(() => t.remove(), 400); }, 3500);
  }

  return { init, startReflection, showToast };
})();

function setStarter(el) {
  const input = document.getElementById('mf-input');
  if (input) { input.value = el.textContent; input.focus(); input.dispatchEvent(new Event('input')); }
}

// Theme toggle
let isDark = true;
document.getElementById('themeToggle')?.addEventListener('click', () => {
  isDark = !isDark;
  document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
  document.getElementById('iconMoon').style.display = isDark ? 'block' : 'none';
  document.getElementById('iconSun').style.display  = isDark ? 'none'  : 'block';
});

document.addEventListener('DOMContentLoaded', MindForge.init);
</script>
</body>
</html>