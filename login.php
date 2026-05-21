<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: /MindForge/dashboard.php');
    exit;
}
$error = $_SESSION['auth_error'] ?? null;
unset($_SESSION['auth_error']);
?>
<!DOCTYPE html>
<html lang="ro" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — MindForge</title>
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
  --teal:#2dd4a0; --coral:#f76a6a;
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
  --teal:#0f9e6e; --coral:#d94040;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html,body{height:100%;font-family:var(--font-b);background:var(--bg);color:var(--text);}
body{display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:100vh;padding:1.5rem;position:relative;overflow:hidden;}

/* Ambient bg */
.bg-orb{position:fixed;border-radius:50%;filter:blur(80px);pointer-events:none;z-index:0;}
.bg-orb-1{width:500px;height:500px;background:rgba(124,106,247,0.12);top:-100px;right:-100px;}
.bg-orb-2{width:400px;height:400px;background:rgba(45,212,160,0.07);bottom:-80px;left:-80px;}

.mf-header{position:fixed;top:0;left:0;right:0;height:58px;display:flex;align-items:center;justify-content:space-between;padding:0 1.5rem;background:rgba(10,10,15,0.8);backdrop-filter:blur(12px);border-bottom:1px solid var(--border);z-index:10;}
.mf-logo{font-family:var(--font-h);font-size:1.2rem;font-weight:800;letter-spacing:-0.03em;color:var(--text);text-decoration:none;display:flex;align-items:center;gap:8px;}
.mf-logo-mark{width:28px;height:28px;border-radius:8px;background:var(--accent);display:flex;align-items:center;justify-content:center;transition:transform var(--tr),box-shadow var(--tr);}
.mf-logo:hover .mf-logo-mark{transform:rotate(-8deg);box-shadow:0 0 16px rgba(124,106,247,0.5);}
.mf-logo-mark svg{width:15px;height:15px;fill:white;}

.mf-card{position:relative;z-index:1;width:100%;max-width:420px;background:var(--surface);border:1px solid var(--border2);border-radius:18px;padding:2.5rem;box-shadow:0 24px 64px rgba(0,0,0,0.4);}

.mf-card-header{text-align:center;margin-bottom:2rem;}
.mf-card-icon{width:56px;height:56px;border-radius:14px;background:rgba(124,106,247,0.15);display:flex;align-items:center;justify-content:center;font-size:1.6rem;margin:0 auto 1.25rem;}
.mf-card-title{font-family:var(--font-h);font-size:1.55rem;font-weight:800;letter-spacing:-0.03em;color:var(--text);margin-bottom:0.4rem;}
.mf-card-sub{font-size:0.88rem;color:var(--text2);line-height:1.5;font-weight:300;}

.mf-field{margin-bottom:1rem;}
.mf-label{display:block;font-family:var(--font-m);font-size:0.7rem;color:var(--text3);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.5rem;}
.mf-input-wrap{position:relative;}
.mf-input-wrap svg{position:absolute;left:12px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:var(--text3);pointer-events:none;transition:color var(--tr);}
.mf-input{width:100%;background:var(--bg2);border:1px solid var(--border2);border-radius:var(--radius-sm);padding:0.7rem 0.9rem 0.7rem 2.4rem;font-family:var(--font-b);font-size:0.92rem;color:var(--text);outline:none;transition:all var(--tr);}
.mf-input::placeholder{color:var(--text3);}
.mf-input:focus{border-color:rgba(124,106,247,0.5);box-shadow:0 0 0 3px rgba(124,106,247,0.1);}
.mf-input:focus + svg, .mf-input-wrap:focus-within svg{color:var(--accent2);}
/* fix: icon inside wrap, input first */
.mf-input-wrap .mf-input{padding-left:2.5rem;}

.mf-pw-toggle{position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text3);padding:4px;display:flex;align-items:center;transition:color var(--tr);}
.mf-pw-toggle:hover{color:var(--text2);}
.mf-pw-toggle svg{width:15px;height:15px;}

.mf-forgot{display:block;text-align:right;font-size:0.78rem;color:var(--text3);text-decoration:none;margin-top:0.35rem;transition:color var(--tr);}
.mf-forgot:hover{color:var(--accent2);}

.mf-btn-primary{width:100%;padding:0.8rem;border-radius:var(--radius-sm);background:var(--accent);color:#fff;border:none;font-family:var(--font-h);font-size:0.95rem;font-weight:700;letter-spacing:0.01em;cursor:pointer;transition:all var(--tr);margin-top:1.25rem;display:flex;align-items:center;justify-content:center;gap:8px;}
.mf-btn-primary:hover{background:var(--accent2);box-shadow:0 8px 24px rgba(124,106,247,0.4);transform:translateY(-1px);}
.mf-btn-primary:active{transform:translateY(0);}
.mf-btn-primary:disabled{opacity:0.5;cursor:not-allowed;transform:none;box-shadow:none;}

.mf-divider{display:flex;align-items:center;gap:0.75rem;margin:1.5rem 0;color:var(--text3);font-size:0.78rem;}
.mf-divider::before,.mf-divider::after{content:'';flex:1;height:1px;background:var(--border);}

.mf-card-footer{text-align:center;margin-top:1.5rem;font-size:0.85rem;color:var(--text2);}
.mf-card-footer a{color:var(--accent2);text-decoration:none;font-weight:500;transition:color var(--tr);}
.mf-card-footer a:hover{color:var(--accent);}

.mf-error{background:rgba(247,106,106,0.1);border:1px solid rgba(247,106,106,0.3);border-radius:var(--radius-sm);padding:0.7rem 1rem;font-size:0.85rem;color:var(--coral);margin-bottom:1rem;display:flex;align-items:center;gap:8px;}
.mf-error svg{width:15px;height:15px;flex-shrink:0;}

.mf-theme-btn{width:32px;height:32px;border-radius:var(--radius-sm);border:1px solid var(--border2);background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--text2);transition:all var(--tr);}
.mf-theme-btn:hover{background:var(--surface);color:var(--text);}
.mf-theme-btn svg{width:14px;height:14px;}

.mf-strength{height:3px;border-radius:99px;background:var(--bg3);margin-top:0.4rem;overflow:hidden;display:none;}
.mf-strength-bar{height:100%;border-radius:99px;transition:width 0.4s,background 0.4s;}

@keyframes fadeUp{from{opacity:0;transform:translateY(16px);}to{opacity:1;transform:translateY(0);}}
.mf-card{animation:fadeUp 0.4s ease;}
</style>
</head>
<body>
<div class="bg-orb bg-orb-1"></div>
<div class="bg-orb bg-orb-2"></div>

<header class="mf-header">
  <a href="/MindForge/" class="mf-logo">
    <div class="mf-logo-mark">
      <svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
    </div>
    MindForge
  </a>
  <button class="mf-theme-btn" id="themeToggle" aria-label="Schimbă tema">
    <svg id="iconMoon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
    <svg id="iconSun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="display:none"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/></svg>
  </button>
</header>

<div class="mf-card">
  <div class="mf-card-header">
    <div class="mf-card-icon">🧠</div>
    <h1 class="mf-card-title">Bun venit înapoi</h1>
    <p class="mf-card-sub">Continuă-ți călătoria de creștere personală</p>
  </div>

  <?php if ($error): ?>
  <div class="mf-error">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <?= htmlspecialchars($error) ?>
  </div>
  <?php endif; ?>

  <form method="POST" action="/MindForge/api/auth.php" id="loginForm">
    <input type="hidden" name="action" value="login">

    <div class="mf-field">
      <label class="mf-label" for="email">Email</label>
      <div class="mf-input-wrap">
        <input class="mf-input" type="email" id="email" name="email" placeholder="tu@exemplu.com" required autocomplete="email">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
      </div>
    </div>

    <div class="mf-field">
      <label class="mf-label" for="password">Parolă</label>
      <div class="mf-input-wrap">
        <input class="mf-input" type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        <button type="button" class="mf-pw-toggle" id="pwToggle" aria-label="Arată parola">
          <!-- <svg id="eyeShow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          <svg id="eyeHide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg> -->
        </button>
      </div>
      <a href="#" class="mf-forgot">Ai uitat parola?</a>
    </div>

    <button type="submit" class="mf-btn-primary" id="submitBtn">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      Intră în cont
    </button>
  </form>

  <div class="mf-card-footer">
    Nu ai cont? <a href="/MindForge/register.php">Creează unul gratuit</a>
  </div>
</div>

<script>
// Theme toggle
let isDark = true;
document.getElementById('themeToggle').addEventListener('click', () => {
  isDark = !isDark;
  document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
  document.getElementById('iconMoon').style.display = isDark ? 'block' : 'none';
  document.getElementById('iconSun').style.display  = isDark ? 'none'  : 'block';
});

// Password toggle
document.getElementById('pwToggle').addEventListener('click', () => {
  const pw = document.getElementById('password');
  const isText = pw.type === 'text';
  pw.type = isText ? 'password' : 'text';
  document.getElementById('eyeShow').style.display = isText ? 'block' : 'none';
  document.getElementById('eyeHide').style.display = isText ? 'none'  : 'block';
});

// Loading state on submit
document.getElementById('loginForm').addEventListener('submit', () => {
  const btn = document.getElementById('submitBtn');
  btn.disabled = true;
  btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:spin 1s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Se verifică...';
});
</script>
<style>@keyframes spin{to{transform:rotate(360deg);}}</style>
</body>
</html>