<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /MindForge/login.php');
    exit;
}

require_once __DIR__ . '/config/db.php';
$pdo = getDB();

// Fetch user + profile from DB
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
    'bio'                 => '',
];

// Fetch sesiuni reale
$stmtS = $pdo->prepare("
    SELECT s.id, s.titlu, s.subiect, s.status, s.created_at, s.ended_at,
           COUNT(m.id) as nr_mesaje
    FROM sessions s
    LEFT JOIN messages m ON m.session_id = s.id
    WHERE s.user_id = ?
    GROUP BY s.id
    ORDER BY s.created_at DESC
    LIMIT 10
");
$stmtS->execute([$user['id']]);
$sessions = $stmtS->fetchAll(PDO::FETCH_ASSOC);

$firstName = explode(' ', $user['name'])[0] ?? $user['name'];
$nameParts = explode(' ', $user['name']);
$initials  = mb_strtoupper(mb_substr($nameParts[0], 0, 1)) . (isset($nameParts[1]) ? mb_strtoupper(mb_substr($nameParts[1], 0, 1)) : '');

$nivelLabel = ['incepator' => 'Începător', 'intermediar' => 'Intermediar', 'avansat' => 'Avansat'];
$stilLabel  = ['analitic' => 'Analitic', 'superficial' => 'Superficial', 'grabit' => 'Grăbit', 'consistent' => 'Consistent'];

$success = $_SESSION['dash_success'] ?? null;
$error   = $_SESSION['dash_error']   ?? null;
unset($_SESSION['dash_success'], $_SESSION['dash_error']);
?>
<!DOCTYPE html>
<html lang="ro" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MindForge — Profilul Meu</title>
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
html,body{min-height:100%;font-family:var(--font-b);background:var(--bg);color:var(--text);}
body{display:flex;flex-direction:column;}

.mf-header{height:58px;display:flex;align-items:center;justify-content:space-between;padding:0 1.5rem;background:var(--bg2);border-bottom:1px solid var(--border);position:sticky;top:0;z-index:10;}
.mf-logo{font-family:var(--font-h);font-size:1.2rem;font-weight:800;letter-spacing:-0.03em;color:var(--text);text-decoration:none;display:flex;align-items:center;gap:8px;}
.mf-logo-mark{width:28px;height:28px;border-radius:8px;background:var(--accent);display:flex;align-items:center;justify-content:center;transition:transform var(--tr),box-shadow var(--tr);}
.mf-logo:hover .mf-logo-mark{transform:rotate(-8deg);box-shadow:0 0 16px rgba(124,106,247,0.5);}
.mf-logo-mark svg{width:15px;height:15px;fill:white;}
.mf-header-nav{display:flex;align-items:center;gap:0.5rem;}
.mf-btn{font-family:var(--font-b);font-size:0.83rem;font-weight:500;padding:0.45rem 0.9rem;border-radius:var(--radius-sm);border:1px solid var(--border2);background:transparent;color:var(--text2);cursor:pointer;transition:all var(--tr);display:inline-flex;align-items:center;gap:5px;text-decoration:none;}
.mf-btn:hover{background:var(--surface2);color:var(--text);}
.mf-btn--primary{background:var(--accent);color:#fff;border-color:transparent;}
.mf-btn--primary:hover{background:var(--accent2);box-shadow:0 4px 16px rgba(124,106,247,0.4);color:#fff;}
.mf-btn--ghost{border-color:transparent;}
.mf-btn--ghost:hover{background:var(--surface);}
.mf-btn--danger{color:var(--coral);border-color:rgba(247,106,106,0.3);}
.mf-btn--danger:hover{background:rgba(247,106,106,0.1);}
.mf-btn svg{width:14px;height:14px;}
.mf-theme-btn{width:32px;height:32px;border-radius:var(--radius-sm);border:1px solid var(--border2);background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--text2);transition:all var(--tr);}
.mf-theme-btn:hover{background:var(--surface);}
.mf-theme-btn svg{width:14px;height:14px;}

.mf-main{flex:1;max-width:980px;margin:0 auto;padding:2.5rem 1.5rem;width:100%;}

.mf-hero{background:linear-gradient(135deg,rgba(124,106,247,0.15),rgba(45,212,160,0.08));border:1px solid rgba(124,106,247,0.2);border-radius:18px;padding:2rem;display:flex;align-items:center;gap:1.5rem;margin-bottom:2rem;position:relative;overflow:hidden;}
.mf-hero::before{content:'';position:absolute;top:-60px;right:-60px;width:200px;height:200px;border-radius:50%;background:rgba(124,106,247,0.1);pointer-events:none;}
.mf-avatar-lg{width:72px;height:72px;border-radius:18px;background:linear-gradient(135deg,var(--accent),var(--teal));display:flex;align-items:center;justify-content:center;font-family:var(--font-h);font-size:1.5rem;font-weight:800;color:white;flex-shrink:0;}
.mf-hero-info{flex:1;}
.mf-hero-name{font-family:var(--font-h);font-size:1.6rem;font-weight:800;letter-spacing:-0.03em;margin-bottom:0.2rem;}
.mf-hero-email{font-family:var(--font-m);font-size:0.78rem;color:var(--text3);margin-bottom:0.6rem;}
.mf-hero-badges{display:flex;gap:0.5rem;flex-wrap:wrap;}
.mf-badge{display:inline-flex;align-items:center;gap:5px;padding:0.3rem 0.7rem;border-radius:99px;font-family:var(--font-m);font-size:0.72rem;}
.mf-badge--nivel{background:rgba(124,106,247,0.15);color:var(--accent2);}
.mf-badge--stil{background:rgba(45,212,160,0.12);color:var(--teal);}
.mf-badge--joined{background:var(--surface2);color:var(--text3);}
.mf-badge-dot{width:5px;height:5px;border-radius:50%;background:currentColor;opacity:0.7;}

.mf-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;}
.mf-grid--3{grid-template-columns:1fr 1fr 1fr;}
.mf-col-full{grid-column:1/-1;}

.mf-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:1.5rem;}
.mf-card-title{font-family:var(--font-m);font-size:0.7rem;color:var(--text3);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:1.25rem;display:flex;align-items:center;gap:6px;}
.mf-card-title span{flex:1;}

.mf-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:0.75rem;margin-bottom:2rem;}
.mf-stat{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:1.25rem;text-align:center;}
.mf-stat-val{font-family:var(--font-h);font-size:2rem;font-weight:800;letter-spacing:-0.04em;line-height:1;}
.mf-stat-lbl{font-size:0.75rem;color:var(--text3);margin-top:0.4rem;}
.mf-stat-icon{font-size:1.1rem;margin-bottom:0.4rem;}

.mf-skill{margin-bottom:1.1rem;}
.mf-skill:last-child{margin-bottom:0;}
.mf-skill-header{display:flex;justify-content:space-between;align-items:center;font-size:0.82rem;color:var(--text2);margin-bottom:6px;}
.mf-skill-pct{font-family:var(--font-m);font-size:0.78rem;color:var(--text3);}
.mf-bar{height:6px;background:var(--bg3);border-radius:99px;overflow:hidden;}
.mf-bar-fill{height:100%;border-radius:99px;transition:width 1.2s cubic-bezier(0.4,0,0.2,1);}
.mf-bar-fill.purple{background:linear-gradient(90deg,var(--accent),var(--accent2));}
.mf-bar-fill.teal{background:linear-gradient(90deg,var(--teal),#4ce8b8);}
.mf-bar-fill.amber{background:linear-gradient(90deg,var(--amber),#f7c564);}
.mf-bar-fill.coral{background:linear-gradient(90deg,var(--coral),#f7a0a0);}

.mf-form-field{margin-bottom:1rem;}
.mf-form-field:last-child{margin-bottom:0;}
.mf-label{display:block;font-family:var(--font-m);font-size:0.68rem;color:var(--text3);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.45rem;}
.mf-input{width:100%;background:var(--bg2);border:1px solid var(--border2);border-radius:var(--radius-sm);padding:0.65rem 0.9rem;font-family:var(--font-b);font-size:0.9rem;color:var(--text);outline:none;transition:all var(--tr);}
.mf-input::placeholder{color:var(--text3);}
.mf-input:focus{border-color:rgba(124,106,247,0.5);box-shadow:0 0 0 3px rgba(124,106,247,0.1);}
.mf-textarea{resize:vertical;min-height:80px;line-height:1.5;}
.mf-select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%235e5d78' stroke-width='2' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 0.9rem center;padding-right:2.5rem;}

.mf-tabs{display:flex;gap:0.25rem;margin-bottom:2rem;background:var(--surface);border-radius:var(--radius);padding:0.3rem;border:1px solid var(--border);}
.mf-tab{flex:1;padding:0.5rem;border-radius:var(--radius-sm);background:transparent;border:none;cursor:pointer;font-family:var(--font-b);font-size:0.85rem;color:var(--text2);transition:all var(--tr);display:flex;align-items:center;justify-content:center;gap:6px;}
.mf-tab:hover{color:var(--text);}
.mf-tab.active{background:var(--surface2);color:var(--text);box-shadow:0 1px 3px rgba(0,0,0,0.3);}
.mf-tab svg{width:14px;height:14px;}
.mf-tab-panel{display:none;}
.mf-tab-panel.active{display:block;}

.mf-toast{position:fixed;bottom:2rem;left:50%;transform:translateX(-50%) translateY(20px);padding:0.65rem 1.25rem;border-radius:99px;font-size:0.85rem;z-index:9999;opacity:0;transition:all 0.3s ease;pointer-events:none;white-space:nowrap;}
.mf-toast--info{background:var(--surface2);color:var(--text);border:1px solid var(--border2);}
.mf-toast--error{background:rgba(247,106,106,0.15);color:var(--coral);border:1px solid rgba(247,106,106,0.3);}
.mf-toast--success{background:rgba(45,212,160,0.15);color:var(--teal);border:1px solid rgba(45,212,160,0.3);}
.mf-toast--visible{opacity:1;transform:translateX(-50%) translateY(0);}

.mf-divider{height:1px;background:var(--border);margin:1.25rem 0;}

.mf-danger-zone{border:1px solid rgba(247,106,106,0.2);border-radius:var(--radius);padding:1.25rem;background:rgba(247,106,106,0.04);}
.mf-danger-title{font-family:var(--font-m);font-size:0.7rem;color:var(--coral);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.75rem;}
.mf-danger-row{display:flex;align-items:center;justify-content:space-between;gap:1rem;}
.mf-danger-desc{font-size:0.83rem;color:var(--text2);}

/* Sesiuni / activitate */
.mf-activity-item{display:flex;align-items:center;gap:10px;padding:0.75rem 0;border-bottom:1px solid var(--border);}
.mf-activity-item:last-child{border-bottom:none;padding-bottom:0;}
.mf-activity-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;}
.mf-activity-content{flex:1;min-width:0;}
.mf-activity-text{font-size:0.85rem;color:var(--text2);line-height:1.4;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.mf-activity-text a{color:var(--text);text-decoration:none;font-weight:500;}
.mf-activity-text a:hover{color:var(--accent2);}
.mf-activity-time{font-family:var(--font-m);font-size:0.7rem;color:var(--text3);margin-top:2px;}
.mf-activity-meta{font-size:0.72rem;color:var(--text3);font-family:var(--font-m);margin-left:6px;}

/* Sessions list (tab dedicat) */
.mf-session-list{display:flex;flex-direction:column;gap:0.5rem;}
.mf-session-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:1rem 1.25rem;display:flex;align-items:center;gap:1rem;transition:border-color var(--tr);}
.mf-session-card:hover{border-color:var(--border2);}
.mf-session-icon{width:40px;height:40px;border-radius:10px;background:rgba(124,106,247,0.12);display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;}
.mf-session-info{flex:1;min-width:0;}
.mf-session-title{font-size:0.9rem;font-weight:500;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:3px;}
.mf-session-meta{font-family:var(--font-m);font-size:0.7rem;color:var(--text3);display:flex;gap:0.75rem;}
.mf-session-status{padding:0.2rem 0.55rem;border-radius:99px;font-family:var(--font-m);font-size:0.68rem;font-weight:500;}
.mf-session-status--activa{background:rgba(45,212,160,0.12);color:var(--teal);}
.mf-session-status--finalizata{background:rgba(124,106,247,0.12);color:var(--accent2);}
.mf-session-status--reflectie{background:rgba(245,166,35,0.12);color:var(--amber);}
.mf-empty{text-align:center;padding:3rem 1rem;color:var(--text3);}
.mf-empty-icon{font-size:2rem;margin-bottom:0.75rem;}
.mf-empty p{font-size:0.875rem;margin-bottom:1rem;}

@media(max-width:700px){.mf-grid,.mf-grid--3{grid-template-columns:1fr;}.mf-stats{grid-template-columns:1fr 1fr;}.mf-hero{flex-direction:column;text-align:center;}.mf-hero-badges{justify-content:center;}}
@keyframes fadeUp{from{opacity:0;transform:translateY(12px);}to{opacity:1;transform:translateY(0);}}
.mf-main>*{animation:fadeUp 0.4s ease both;}
.mf-hero{animation-delay:0.05s;}
.mf-stats{animation-delay:0.1s;}
.mf-tabs{animation-delay:0.15s;}
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
  <div class="mf-header-nav">
    <a href="/MindForge/chat.php" class="mf-btn">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      Chat
    </a>
    <button class="mf-theme-btn" id="themeToggle" aria-label="Schimbă tema">
      <svg id="iconMoon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
      <svg id="iconSun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="display:none"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/></svg>
    </button>
    <a href="/MindForge/api/auth.php?action=logout" class="mf-btn mf-btn--ghost">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Deconectare
    </a>
  </div>
</header>

<main class="mf-main">

  <!-- Hero -->
  <div class="mf-hero">
    <div class="mf-avatar-lg"><?= htmlspecialchars($initials) ?></div>
    <div class="mf-hero-info">
      <div class="mf-hero-name"><?= htmlspecialchars($user['name']) ?></div>
      <div class="mf-hero-email"><?= htmlspecialchars($user['email']) ?></div>
      <div class="mf-hero-badges">
        <span class="mf-badge mf-badge--nivel"><span class="mf-badge-dot"></span><?= $nivelLabel[$profile['nivel']] ?? 'Începător' ?></span>
        <span class="mf-badge mf-badge--stil"><span class="mf-badge-dot"></span><?= $stilLabel[$profile['stil']] ?? 'Analitic' ?></span>
        <span class="mf-badge mf-badge--joined">Membru din <?= date('M Y', strtotime($user['created_at'] ?? 'now')) ?></span>
      </div>
    </div>
    <a href="/MindForge/chat.php" class="mf-btn mf-btn--primary">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      Sesiune nouă
    </a>
  </div>

  <!-- Stats -->
  <div class="mf-stats">
    <div class="mf-stat">
      <div class="mf-stat-icon">🔥</div>
      <div class="mf-stat-val" style="color:var(--amber)"><?= $profile['serie_activa'] ?></div>
      <div class="mf-stat-lbl">zile serie</div>
    </div>
    <div class="mf-stat">
      <div class="mf-stat-icon">💬</div>
      <div class="mf-stat-val"><?= count($sessions) ?></div>
      <div class="mf-stat-lbl">sesiuni totale</div>
    </div>
    <div class="mf-stat">
      <div class="mf-stat-icon">🧠</div>
      <div class="mf-stat-val" style="color:var(--accent2)"><?= max($profile['gandire_critica'], $profile['claritate_emotionala'], $profile['comunicare'], $profile['rezistenta']) ?>%</div>
      <div class="mf-stat-lbl">skill maxim</div>
    </div>
    <div class="mf-stat">
      <div class="mf-stat-icon">⭐</div>
      <div class="mf-stat-val" style="color:var(--teal)"><?= round(($profile['gandire_critica'] + $profile['claritate_emotionala'] + $profile['comunicare'] + $profile['rezistenta']) / 4) ?>%</div>
      <div class="mf-stat-lbl">scor general</div>
    </div>
  </div>

  <!-- Tabs -->
  <div class="mf-tabs">
    <button class="mf-tab active" onclick="switchTab('skills')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
      Progres
    </button>
    <button class="mf-tab" onclick="switchTab('sessions')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      Sesiuni <?php if(count($sessions)>0): ?><span style="background:var(--accent);color:#fff;border-radius:99px;padding:0 6px;font-size:0.65rem;line-height:1.6;"><?= count($sessions) ?></span><?php endif; ?>
    </button>
    <button class="mf-tab" onclick="switchTab('profile')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      Profil
    </button>
    <button class="mf-tab" onclick="switchTab('security')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      Securitate
    </button>
  </div>

  <!-- Tab: Progres -->
  <div class="mf-tab-panel active" id="panel-skills">
    <div class="mf-grid">
      <div class="mf-card">
        <div class="mf-card-title"><span>// skill-uri</span></div>
        <div class="mf-skill">
          <div class="mf-skill-header"><span>Gândire critică</span><span class="mf-skill-pct"><?= $profile['gandire_critica'] ?>%</span></div>
          <div class="mf-bar"><div class="mf-bar-fill purple" style="width:0%" data-target="<?= $profile['gandire_critica'] ?>"></div></div>
        </div>
        <div class="mf-skill">
          <div class="mf-skill-header"><span>Claritate emoțională</span><span class="mf-skill-pct"><?= $profile['claritate_emotionala'] ?>%</span></div>
          <div class="mf-bar"><div class="mf-bar-fill teal" style="width:0%" data-target="<?= $profile['claritate_emotionala'] ?>"></div></div>
        </div>
        <div class="mf-skill">
          <div class="mf-skill-header"><span>Comunicare</span><span class="mf-skill-pct"><?= $profile['comunicare'] ?>%</span></div>
          <div class="mf-bar"><div class="mf-bar-fill amber" style="width:0%" data-target="<?= $profile['comunicare'] ?>"></div></div>
        </div>
        <div class="mf-skill">
          <div class="mf-skill-header"><span>Rezistență</span><span class="mf-skill-pct"><?= $profile['rezistenta'] ?>%</span></div>
          <div class="mf-bar"><div class="mf-bar-fill coral" style="width:0%" data-target="<?= $profile['rezistenta'] ?>"></div></div>
        </div>
      </div>

      <div class="mf-card">
        <div class="mf-card-title"><span>// activitate recentă</span></div>
        <?php if (empty($sessions)): ?>
          <div class="mf-activity-item">
            <div class="mf-activity-dot" style="background:var(--text3)"></div>
            <div class="mf-activity-content">
              <div class="mf-activity-text" style="color:var(--text3)">
                Nicio sesiune încă —
                <a href="/MindForge/chat.php" style="color:var(--accent2);text-decoration:none;">începe prima sesiune →</a>
              </div>
              <div class="mf-activity-time">—</div>
            </div>
          </div>
        <?php else: ?>
          <?php foreach (array_slice($sessions, 0, 5) as $s):
            $dotColor = match($s['status']) {
              'activa'     => 'var(--teal)',
              'finalizata' => 'var(--accent)',
              'reflectie'  => 'var(--amber)',
              default      => 'var(--text3)',
            };
            $titlu = $s['titlu'] ?: ($s['subiect'] ?: 'Sesiune fără titlu');
            $data  = date('d M, H:i', strtotime($s['created_at']));
          ?>
          <div class="mf-activity-item">
            <div class="mf-activity-dot" style="background:<?= $dotColor ?>"></div>
            <div class="mf-activity-content">
              <div class="mf-activity-text">
                <a href="/MindForge/chat.php?session=<?= $s['id'] ?>"><?= htmlspecialchars($titlu) ?></a>
                <span class="mf-activity-meta"><?= $s['nr_mesaje'] ?> msg</span>
              </div>
              <div class="mf-activity-time"><?= $data ?></div>
            </div>
            <a href="/MindForge/chat.php?session=<?= $s['id'] ?>" class="mf-btn mf-btn--ghost" style="font-size:0.75rem;padding:0.3rem 0.6rem;flex-shrink:0;">
              Continuă →
            </a>
          </div>
          <?php endforeach; ?>
          <?php if(count($sessions) > 5): ?>
          <div style="text-align:center;margin-top:0.75rem;">
            <button class="mf-btn mf-btn--ghost" style="font-size:0.78rem;" onclick="switchTab('sessions')">
              Vezi toate <?= count($sessions) ?> sesiunile →
            </button>
          </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Tab: Sesiuni -->
  <div class="mf-tab-panel" id="panel-sessions">
    <?php if (empty($sessions)): ?>
      <div class="mf-empty">
        <div class="mf-empty-icon">💬</div>
        <p>Nu ai nicio sesiune încă. Începe o conversație cu MindForge!</p>
        <a href="/MindForge/chat.php" class="mf-btn mf-btn--primary">Începe prima sesiune</a>
      </div>
    <?php else: ?>
      <div class="mf-session-list">
        <?php foreach ($sessions as $s):
          $statusClass = 'mf-session-status--' . $s['status'];
          $statusLabel = match($s['status']) {
            'activa'     => '● Activă',
            'finalizata' => '✓ Finalizată',
            'reflectie'  => '◎ Reflecție',
            default      => ucfirst($s['status']),
          };
          $icon = match($s['status']) {
            'activa'     => '🟢',
            'finalizata' => '✅',
            'reflectie'  => '🔍',
            default      => '💬',
          };
          $titlu = $s['titlu'] ?: ($s['subiect'] ?: 'Sesiune fără titlu');
          $data  = date('d M Y, H:i', strtotime($s['created_at']));
        ?>
        <div class="mf-session-card">
          <div class="mf-session-icon"><?= $icon ?></div>
          <div class="mf-session-info">
            <div class="mf-session-title"><?= htmlspecialchars($titlu) ?></div>
            <div class="mf-session-meta">
              <span><?= $data ?></span>
              <span>·</span>
              <span><?= $s['nr_mesaje'] ?> mesaje</span>
            </div>
          </div>
          <span class="mf-session-status <?= $statusClass ?>"><?= $statusLabel ?></span>
          <a href="/MindForge/chat.php?session=<?= $s['id'] ?>" class="mf-btn mf-btn--primary" style="flex-shrink:0;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" style="width:13px;height:13px"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            Deschide
          </a>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Tab: Profile -->
  <div class="mf-tab-panel" id="panel-profile">
    <form method="POST" action="/MindForge/api/auth.php">
      <input type="hidden" name="action" value="update_profile">
      <div class="mf-grid">
        <div class="mf-card">
          <div class="mf-card-title"><span>// informații personale</span></div>
          <div class="mf-form-field">
            <label class="mf-label" for="name">Nume complet</label>
            <input class="mf-input" type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
          </div>
          <div class="mf-form-field">
            <label class="mf-label" for="email_ro">Email</label>
            <input class="mf-input" type="email" id="email_ro" value="<?= htmlspecialchars($user['email']) ?>" disabled style="opacity:0.5;cursor:not-allowed;">
          </div>
          <div class="mf-form-field">
            <label class="mf-label" for="bio">Bio scurtă</label>
            <textarea class="mf-input mf-textarea" id="bio" name="bio" placeholder="Câteva cuvinte despre tine..."><?= htmlspecialchars($profile['bio'] ?? '') ?></textarea>
          </div>
          <div style="margin-top:1.25rem">
            <button type="submit" class="mf-btn mf-btn--primary">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
              Salvează modificările
            </button>
          </div>
        </div>
        <div class="mf-card">
          <div class="mf-card-title"><span>// preferințe</span></div>
          <div class="mf-form-field">
            <label class="mf-label" for="nivel">Nivel actual</label>
            <select class="mf-input mf-select" id="nivel" name="nivel">
              <option value="incepator"   <?= $profile['nivel']==='incepator'   ? 'selected':'' ?>>Începător</option>
              <option value="intermediar" <?= $profile['nivel']==='intermediar' ? 'selected':'' ?>>Intermediar</option>
              <option value="avansat"     <?= $profile['nivel']==='avansat'     ? 'selected':'' ?>>Avansat</option>
            </select>
          </div>
          <div class="mf-form-field">
            <label class="mf-label" for="stil">Stilul de învățare</label>
            <select class="mf-input mf-select" id="stil" name="stil">
              <option value="analitic"   <?= $profile['stil']==='analitic'   ? 'selected':'' ?>>Analitic</option>
              <option value="consistent" <?= $profile['stil']==='consistent' ? 'selected':'' ?>>Consistent</option>
              <option value="grabit"     <?= $profile['stil']==='grabit'     ? 'selected':'' ?>>Grăbit</option>
              <option value="superficial"<?= $profile['stil']==='superficial'? 'selected':'' ?>>Superficial</option>
            </select>
          </div>
        </div>
      </div>
    </form>
  </div>

  <!-- Tab: Security -->
  <div class="mf-tab-panel" id="panel-security">
    <div class="mf-grid">
      <div class="mf-card">
        <div class="mf-card-title"><span>// schimbă parola</span></div>
        <form method="POST" action="/MindForge/api/auth.php">
          <input type="hidden" name="action" value="change_password">
          <div class="mf-form-field">
            <label class="mf-label" for="current_pw">Parola curentă</label>
            <input class="mf-input" type="password" id="current_pw" name="current_password" placeholder="••••••••" required>
          </div>
          <div class="mf-form-field">
            <label class="mf-label" for="new_pw">Parolă nouă</label>
            <input class="mf-input" type="password" id="new_pw" name="new_password" placeholder="Minim 8 caractere" required minlength="8">
          </div>
          <div class="mf-form-field">
            <label class="mf-label" for="confirm_pw">Confirmă parola nouă</label>
            <input class="mf-input" type="password" id="confirm_pw" name="confirm_password" placeholder="Repetă parola nouă" required>
          </div>
          <div style="margin-top:1.25rem">
            <button type="submit" class="mf-btn mf-btn--primary">Actualizează parola</button>
          </div>
        </form>
      </div>
      <div class="mf-card">
        <div class="mf-card-title"><span>// sesiunea curentă</span></div>
        <div style="font-size:0.85rem;color:var(--text2);line-height:1.6;margin-bottom:1rem;">
          Ești autentificat ca <strong style="color:var(--text)"><?= htmlspecialchars($user['email']) ?></strong>.<br>
          Ultimul login: <span style="color:var(--text3);font-family:var(--font-m);font-size:0.8rem;">
            <?= $user['last_login_at'] ? date('d M Y, H:i', strtotime($user['last_login_at'])) : 'acum' ?>
          </span>
        </div>
        <a href="/MindForge/api/auth.php?action=logout" class="mf-btn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          Deconectare
        </a>
        <div class="mf-divider"></div>
        <div class="mf-danger-zone">
          <div class="mf-danger-title">⚠ Zonă periculoasă</div>
          <div class="mf-danger-row">
            <div class="mf-danger-desc">Ștergerea contului este permanentă și ireversibilă.</div>
            <button class="mf-btn mf-btn--danger" onclick="confirmDelete()">Șterge contul</button>
          </div>
        </div>
      </div>
    </div>
  </div>

</main>

<script>
let isDark = true;
document.getElementById('themeToggle').addEventListener('click', () => {
  isDark = !isDark;
  document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
  document.getElementById('iconMoon').style.display = isDark ? 'block' : 'none';
  document.getElementById('iconSun').style.display  = isDark ? 'none'  : 'block';
});

function switchTab(name) {
  const tabs   = ['skills','sessions','profile','security'];
  document.querySelectorAll('.mf-tab').forEach((t, i) => t.classList.toggle('active', tabs[i] === name));
  document.querySelectorAll('.mf-tab-panel').forEach(p => p.classList.toggle('active', p.id === 'panel-' + name));
  if (name === 'skills') animateBars();
}

function animateBars() {
  document.querySelectorAll('.mf-bar-fill[data-target]').forEach(el => {
    setTimeout(() => el.style.width = el.dataset.target + '%', 100);
  });
}
window.addEventListener('load', () => setTimeout(animateBars, 200));

function showToast(msg, type = 'info') {
  const t = document.createElement('div');
  t.className = `mf-toast mf-toast--${type}`;
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(() => t.classList.add('mf-toast--visible'), 10);
  setTimeout(() => { t.classList.remove('mf-toast--visible'); setTimeout(() => t.remove(), 400); }, 3500);
}

<?php if ($success): ?>showToast(<?= json_encode($success) ?>, 'success');<?php endif; ?>
<?php if ($error): ?>showToast(<?= json_encode($error) ?>, 'error');<?php endif; ?>

function confirmDelete() {
  if (confirm('Ești sigur? Această acțiune nu poate fi anulată. Toate datele tale vor fi șterse permanent.')) {
    window.location.href = '/MindForge/api/auth.php?action=delete_account';
  }
}
</script>
</body>
</html>