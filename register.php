<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: /MindForge/dashboard.php');
    exit;
}
$error   = $_SESSION['auth_error']   ?? null;
$success = $_SESSION['auth_success'] ?? null;
unset($_SESSION['auth_error'], $_SESSION['auth_success']);
?>
<!DOCTYPE html>
<html lang="ro" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cont nou — MindForge</title>
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
  --teal:#2dd4a0; --coral:#f76a6a; --amber:#f5a623;
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
  --teal:#0f9e6e; --coral:#d94040; --amber:#c87e10;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html,body{height:100%;font-family:var(--font-b);background:var(--bg);color:var(--text);}
body {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;  
  min-height: 100vh;
  padding: 90px 1.5rem 3rem;   
  position: relative;
  overflow-x: hidden;
}
.bg-orb{position:fixed;border-radius:50%;filter:blur(80px);pointer-events:none;z-index:0;}
.bg-orb-1{width:500px;height:500px;background:rgba(124,106,247,0.12);top:-100px;right:-100px;}
.bg-orb-2{width:400px;height:400px;background:rgba(45,212,160,0.07);bottom:-80px;left:-80px;}
.mf-header{position:fixed;top:0;left:0;right:0;height:58px;display:flex;align-items:center;justify-content:space-between;padding:0 1.5rem;background:rgba(10,10,15,0.8);backdrop-filter:blur(12px);border-bottom:1px solid var(--border);z-index:10;}
.mf-logo{font-family:var(--font-h);font-size:1.2rem;font-weight:800;letter-spacing:-0.03em;color:var(--text);text-decoration:none;display:flex;align-items:center;gap:8px;}
.mf-logo-mark{width:28px;height:28px;border-radius:8px;background:var(--accent);display:flex;align-items:center;justify-content:center;transition:transform var(--tr),box-shadow var(--tr);}
.mf-logo:hover .mf-logo-mark{transform:rotate(-8deg);box-shadow:0 0 16px rgba(124,106,247,0.5);}
.mf-logo-mark svg{width:15px;height:15px;fill:white;}
.mf-theme-btn{width:32px;height:32px;border-radius:var(--radius-sm);border:1px solid var(--border2);background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--text2);transition:all var(--tr);}
.mf-theme-btn:hover{background:var(--surface);color:var(--text);}
.mf-theme-btn svg{width:14px;height:14px;}
.mf-card{position:relative;z-index:1;width:100%;max-width:460px;background:var(--surface);border:1px solid var(--border2);border-radius:18px;padding:2.5rem;box-shadow:0 24px 64px rgba(0,0,0,0.4);animation:fadeUp 0.4s ease;}
@keyframes fadeUp{from{opacity:0;transform:translateY(16px);}to{opacity:1;transform:translateY(0);}}
.mf-card-header{text-align:center;margin-bottom:2rem;}
.mf-card-icon{width:56px;height:56px;border-radius:14px;background:rgba(45,212,160,0.15);display:flex;align-items:center;justify-content:center;font-size:1.6rem;margin:0 auto 1.25rem;}
.mf-card-title{font-family:var(--font-h);font-size:1.55rem;font-weight:800;letter-spacing:-0.03em;color:var(--text);margin-bottom:0.4rem;}
.mf-card-sub{font-size:0.88rem;color:var(--text2);line-height:1.5;font-weight:300;}
.mf-row{display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;}
.mf-field{margin-bottom:1rem;}
.mf-label{display:block;font-family:var(--font-m);font-size:0.7rem;color:var(--text3);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.5rem;}
.mf-input-wrap{position:relative;}
.mf-input-wrap svg.field-icon{position:absolute;left:12px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:var(--text3);pointer-events:none;transition:color var(--tr);}
.mf-input-wrap:focus-within svg.field-icon{color:var(--accent2);}
.mf-input{width:100%;background:var(--bg2);border:1px solid var(--border2);border-radius:var(--radius-sm);padding:0.7rem 0.9rem 0.7rem 2.4rem;font-family:var(--font-b);font-size:0.92rem;color:var(--text);outline:none;transition:all var(--tr);}
.mf-input::placeholder{color:var(--text3);}
.mf-input:focus{border-color:rgba(124,106,247,0.5);box-shadow:0 0 0 3px rgba(124,106,247,0.1);}
.mf-input.valid{border-color:rgba(45,212,160,0.5);}
.mf-input.invalid{border-color:rgba(247,106,106,0.5);}
.mf-pw-toggle{position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text3);padding:4px;display:flex;align-items:center;transition:color var(--tr);}
.mf-pw-toggle:hover{color:var(--text2);}
.mf-pw-toggle svg{width:14px;height:14px;}
.mf-strength-wrap{margin-top:0.5rem;}
.mf-strength-bars{display:flex;gap:3px;margin-bottom:0.3rem;}
.mf-strength-bar{flex:1;height:3px;border-radius:99px;background:var(--bg3);transition:background 0.3s;}
.mf-strength-label{font-family:var(--font-m);font-size:0.68rem;color:var(--text3);}
.mf-hint{font-size:0.74rem;color:var(--text3);margin-top:0.35rem;line-height:1.4;}
.mf-btn-primary{width:100%;padding:0.8rem;border-radius:var(--radius-sm);background:var(--accent);color:#fff;border:none;font-family:var(--font-h);font-size:0.95rem;font-weight:700;letter-spacing:0.01em;cursor:pointer;transition:all var(--tr);margin-top:1.25rem;display:flex;align-items:center;justify-content:center;gap:8px;}
.mf-btn-primary:hover{background:var(--accent2);box-shadow:0 8px 24px rgba(124,106,247,0.4);transform:translateY(-1px);}
.mf-btn-primary:disabled{opacity:0.5;cursor:not-allowed;transform:none;box-shadow:none;}
.mf-terms{font-size:0.78rem;color:var(--text3);text-align:center;margin-top:1rem;line-height:1.5;}
.mf-terms a{color:var(--accent2);text-decoration:none;}
.mf-terms a:hover{text-decoration:underline;}
.mf-card-footer{text-align:center;margin-top:1.5rem;font-size:0.85rem;color:var(--text2);}
.mf-card-footer a{color:var(--accent2);text-decoration:none;font-weight:500;}
.mf-card-footer a:hover{color:var(--accent);}
.mf-error{background:rgba(247,106,106,0.1);border:1px solid rgba(247,106,106,0.3);border-radius:var(--radius-sm);padding:0.7rem 1rem;font-size:0.85rem;color:var(--coral);margin-bottom:1rem;display:flex;align-items:center;gap:8px;}
.mf-error svg{width:15px;height:15px;flex-shrink:0;}
.mf-success{background:rgba(45,212,160,0.1);border:1px solid rgba(45,212,160,0.3);border-radius:var(--radius-sm);padding:0.7rem 1rem;font-size:0.85rem;color:var(--teal);margin-bottom:1rem;display:flex;align-items:center;gap:8px;}
.mf-benefits{display:flex;flex-direction:column;gap:0.5rem;margin-bottom:1.5rem;padding:1rem;background:var(--bg2);border-radius:var(--radius-sm);border:1px solid var(--border);}
.mf-benefit{display:flex;align-items:center;gap:8px;font-size:0.82rem;color:var(--text2);}
.mf-benefit-dot{width:6px;height:6px;border-radius:50%;flex-shrink:0;}
@media (max-width: 500px) {
  body {
    padding: 78px 1rem 2rem;  
  }

  .mf-card {
    padding: 1.75rem 1.25rem; 
    border-radius: 14px;
  }

  .mf-row {
    grid-template-columns: 1fr; 
    gap: 0;
  }

  .mf-card-title {
    font-size: 1.3rem;
  }
}
@keyframes spin{to{transform:rotate(360deg);}}
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
    <div class="mf-card-icon">🚀</div>
    <h1 class="mf-card-title">Începe călătoria</h1>
    <p class="mf-card-sub">Creează-ți contul gratuit și descoperă cine poți deveni</p>
  </div>

  <div class="mf-benefits">
    <div class="mf-benefit"><div class="mf-benefit-dot" style="background:var(--accent)"></div>Mentor AI socratic disponibil 24/7</div>
    <div class="mf-benefit"><div class="mf-benefit-dot" style="background:var(--teal)"></div>Urmărire progres și skill-uri personale</div>
    <div class="mf-benefit"><div class="mf-benefit-dot" style="background:var(--amber)"></div>Istoric conversații și reflecții salvate</div>
  </div>

  <?php if ($error): ?>
  <div class="mf-error">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <?= htmlspecialchars($error) ?>
  </div>
  <?php endif; ?>

  <?php if ($success): ?>
  <div class="mf-success">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
    <?= htmlspecialchars($success) ?>
  </div>
  <?php endif; ?>

  <form method="POST" action="/MindForge/api/auth.php" id="registerForm">
    <input type="hidden" name="action" value="register">

    <div class="mf-row">
      <div class="mf-field">
        <label class="mf-label" for="first_name">Prenume</label>
        <div class="mf-input-wrap">
          <input class="mf-input" type="text" id="first_name" name="first_name" placeholder="Ion" required autocomplete="given-name">
          <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
      </div>
      <div class="mf-field">
        <label class="mf-label" for="last_name">Nume</label>
        <div class="mf-input-wrap">
          <input class="mf-input" type="text" id="last_name" name="last_name" placeholder="Popescu" required autocomplete="family-name">
          <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
      </div>
    </div>

    <div class="mf-field">
      <label class="mf-label" for="email">Email</label>
      <div class="mf-input-wrap">
        <input class="mf-input" type="email" id="email" name="email" placeholder="tu@exemplu.com" required autocomplete="email">
        <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
      </div>
    </div>

    <div class="mf-field">
      <label class="mf-label" for="password">Parolă</label>
      <div class="mf-input-wrap">
        <input class="mf-input" type="password" id="password" name="password" placeholder="Minim 8 caractere" required autocomplete="new-password">
        <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        <button type="button" class="mf-pw-toggle" id="pwToggle1">
          <svg id="eye1Show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          <svg id="eye1Hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
        </button>
      </div>
      <div class="mf-strength-wrap" id="strengthWrap" style="display:none">
        <div class="mf-strength-bars">
          <div class="mf-strength-bar" id="s1"></div>
          <div class="mf-strength-bar" id="s2"></div>
          <div class="mf-strength-bar" id="s3"></div>
          <div class="mf-strength-bar" id="s4"></div>
        </div>
        <span class="mf-strength-label" id="strengthLabel"></span>
      </div>
    </div>

    <div class="mf-field">
      <label class="mf-label" for="password_confirm">Confirmă parola</label>
      <div class="mf-input-wrap">
        <input class="mf-input" type="password" id="password_confirm" name="password_confirm" placeholder="Repetă parola" required autocomplete="new-password">
        <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        <button type="button" class="mf-pw-toggle" id="pwToggle2">
          <svg id="eye2Show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          <svg id="eye2Hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
        </button>
      </div>
      <p class="mf-hint" id="matchHint" style="display:none"></p>
    </div>

    <button type="submit" class="mf-btn-primary" id="submitBtn">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
      Creează cont gratuit
    </button>

    <p class="mf-terms">Continuând, ești de acord cu <a href="#">Termenii de utilizare</a> și <a href="#">Politica de confidențialitate</a></p>
  </form>

  <div class="mf-card-footer">
    Ai deja cont? <a href="/MindForge/login.php">Intră în cont</a>
  </div>
</div>

<script>
// Theme
let isDark = true;
document.getElementById('themeToggle').addEventListener('click', () => {
  isDark = !isDark;
  document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
  document.getElementById('iconMoon').style.display = isDark ? 'block' : 'none';
  document.getElementById('iconSun').style.display  = isDark ? 'none'  : 'block';
});

// PW toggles
function makePwToggle(btnId, inputId, showId, hideId) {
  document.getElementById(btnId).addEventListener('click', () => {
    const pw = document.getElementById(inputId);
    const isText = pw.type === 'text';
    pw.type = isText ? 'password' : 'text';
    document.getElementById(showId).style.display = isText ? 'block' : 'none';
    document.getElementById(hideId).style.display = isText ? 'none'  : 'block';
  });
}
makePwToggle('pwToggle1', 'password', 'eye1Show', 'eye1Hide');
makePwToggle('pwToggle2', 'password_confirm', 'eye2Show', 'eye2Hide');

// Password strength
const pw = document.getElementById('password');
const bars = [document.getElementById('s1'),document.getElementById('s2'),document.getElementById('s3'),document.getElementById('s4')];
const strengthWrap = document.getElementById('strengthWrap');
const strengthLabel = document.getElementById('strengthLabel');
const colors = ['#f76a6a','#f5a623','#7c6af7','#2dd4a0'];
const labels = ['Slabă','Moderată','Bună','Puternică'];

pw.addEventListener('input', () => {
  const v = pw.value;
  if (!v) { strengthWrap.style.display = 'none'; return; }
  strengthWrap.style.display = 'block';
  let score = 0;
  if (v.length >= 8)  score++;
  if (/[A-Z]/.test(v)) score++;
  if (/[0-9]/.test(v)) score++;
  if (/[^A-Za-z0-9]/.test(v)) score++;
  bars.forEach((b, i) => b.style.background = i < score ? colors[score-1] : 'var(--bg3)');
  strengthLabel.textContent = labels[score-1] || '';
  strengthLabel.style.color = colors[score-1] || 'var(--text3)';
  checkMatch();
});

// Confirm match
const confirm = document.getElementById('password_confirm');
const matchHint = document.getElementById('matchHint');
function checkMatch() {
  if (!confirm.value) { matchHint.style.display='none'; return; }
  matchHint.style.display = 'block';
  if (confirm.value === pw.value) {
    matchHint.textContent = '✓ Parolele se potrivesc';
    matchHint.style.color = 'var(--teal)';
    confirm.classList.add('valid'); confirm.classList.remove('invalid');
  } else {
    matchHint.textContent = '✗ Parolele nu se potrivesc';
    matchHint.style.color = 'var(--coral)';
    confirm.classList.add('invalid'); confirm.classList.remove('valid');
  }
}
confirm.addEventListener('input', checkMatch);

// Submit guard
document.getElementById('registerForm').addEventListener('submit', e => {
  if (pw.value !== confirm.value) {
    e.preventDefault();
    matchHint.style.display = 'block';
    matchHint.textContent = '✗ Parolele nu se potrivesc';
    matchHint.style.color = 'var(--coral)';
    return;
  }
  if (pw.value.length < 8) {
    e.preventDefault();
    return;
  }
  const btn = document.getElementById('submitBtn');
  btn.disabled = true;
  btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:spin 1s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Se creează contul...';
});
</script>
<style>@keyframes spin{to{transform:rotate(360deg);}}</style>
</body>
</html>