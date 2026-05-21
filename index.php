<?php
session_start();

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? 'Alexandru';
$userLevel = $_SESSION['user_level'] ?? 'Intermediar';
$userScore = $_SESSION['user_score'] ?? 72;
?>
<!DOCTYPE html>
<html lang="ro" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MindForge — Gândește. Crește. Devii.</title>
<script src="/MindForge/assets/js/theme.js"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root {
  --bg: #0a0a0f;
  --bg2: #111118;
  --bg3: #18181f;
  --surface: #1e1e28;
  --surface2: #252533;
  --border: rgba(255,255,255,0.07);
  --border2: rgba(255,255,255,0.13);
  --text: #f0eff8;
  --text2: #9896b0;
  --text3: #5e5d78;
  --accent: #7c6af7;
  --accent2: #a395fb;
  --accent-glow: rgba(124,106,247,0.18);
  --teal: #2dd4a0;
  --teal-dim: rgba(45,212,160,0.12);
  --amber: #f5a623;
  --amber-dim: rgba(245,166,35,0.12);
  --coral: #f76a6a;
  --coral-dim: rgba(247,106,106,0.12);
  --radius: 14px;
  --radius-sm: 8px;
  --radius-lg: 20px;
  --radius-xl: 28px;
  --font-head: 'Syne', sans-serif;
  --font-body: 'DM Sans', sans-serif;
  --font-mono: 'DM Mono', monospace;
  --transition: 0.22s cubic-bezier(0.4,0,0.2,1);
  --transition-slow: 0.45s cubic-bezier(0.4,0,0.2,1);
}
[data-theme="light"] {
  --bg: #f4f3fa;
  --bg2: #eceaf6;
  --bg3: #e4e2f0;
  --surface: #ffffff;
  --surface2: #f8f7fe;
  --border: rgba(0,0,0,0.07);
  --border2: rgba(0,0,0,0.13);
  --text: #14131f;
  --text2: #4e4c68;
  --text3: #9997b5;
  --accent: #5b49e8;
  --accent2: #7c6af7;
  --accent-glow: rgba(91,73,232,0.14);
  --teal: #0f9e6e;
  --teal-dim: rgba(15,158,110,0.1);
  --amber: #c87e10;
  --amber-dim: rgba(200,126,16,0.1);
  --coral: #d94040;
  --coral-dim: rgba(217,64,64,0.1);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

html { scroll-behavior: smooth; }

body {
  font-family: var(--font-body);
  background: var(--bg);
  color: var(--text);
  font-size: 16px;
  line-height: 1.6;
  min-height: 100vh;
  overflow-x: hidden;
}

/* ── NOISE TEXTURE OVERLAY ── */
body::before {
  content: '';
  position: fixed;
  inset: 0;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
  pointer-events: none;
  z-index: 0;
  opacity: 0.5;
}

/* ── SCROLLBAR ── */
::-webkit-scrollbar { width: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: var(--surface2); border-radius: 99px; }

/* ── NAV ── */
nav {
  position: fixed;
  top: 0; left: 0; right: 0;
  z-index: 100;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 2.5rem;
  height: 68px;
  background: rgba(10,10,15,0.75);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border-bottom: 1px solid var(--border);
  transition: background var(--transition);
}
[data-theme="light"] nav {
  background: rgba(244,243,250,0.8);
}
.nav-logo {
  font-family: var(--font-head);
  font-size: 1.4rem;
  font-weight: 800;
  letter-spacing: -0.03em;
  color: var(--text);
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 10px;
}
.nav-logo-mark {
  width: 32px; height: 32px;
  background: var(--accent);
  border-radius: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
  transition: transform var(--transition), box-shadow var(--transition);
}
.nav-logo:hover .nav-logo-mark {
  transform: rotate(-8deg) scale(1.08);
  box-shadow: 0 0 24px var(--accent-glow);
}
.nav-logo-mark svg { width: 18px; height: 18px; fill: white; }
.nav-links {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  list-style: none;
}
.nav-links a {
  font-size: 0.9rem;
  color: var(--text2);
  text-decoration: none;
  padding: 0.5rem 0.85rem;
  border-radius: var(--radius-sm);
  transition: color var(--transition), background var(--transition);
  font-weight: 400;
  letter-spacing: 0.01em;
}
.nav-links a:hover { color: var(--text); background: var(--surface); }
.nav-links a.active { color: var(--text); }
.nav-actions { display: flex; align-items: center; gap: 0.75rem; }

/* ── BUTTONS ── */
.btn {
  font-family: var(--font-body);
  font-size: 0.9rem;
  font-weight: 500;
  padding: 0.55rem 1.2rem;
  border-radius: var(--radius-sm);
  border: 1px solid var(--border2);
  background: transparent;
  color: var(--text2);
  cursor: pointer;
  transition: all var(--transition);
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  white-space: nowrap;
}
.btn:hover { color: var(--text); background: var(--surface); border-color: var(--border2); }
.btn-primary {
  background: var(--accent);
  color: #fff;
  border-color: transparent;
  position: relative;
  overflow: hidden;
}
.btn-primary::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg,rgba(255,255,255,0.12),transparent);
  opacity: 0;
  transition: opacity var(--transition);
}
.btn-primary:hover::after { opacity: 1; }
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 28px rgba(124,106,247,0.38); color: #fff; }
.btn-primary:active { transform: translateY(0); }
.btn-ghost { border-color: transparent; }
.btn-ghost:hover { background: var(--surface); border-color: var(--border); }
.btn-icon {
  width: 38px; height: 38px;
  padding: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: var(--radius-sm);
}
.btn-lg {
  padding: 0.8rem 2rem;
  font-size: 1rem;
  border-radius: var(--radius);
}

/* ── THEME TOGGLE ── */
.theme-toggle {
  width: 38px; height: 38px;
  border-radius: var(--radius-sm);
  border: 1px solid var(--border2);
  background: transparent;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all var(--transition);
  color: var(--text2);
}
.theme-toggle:hover { background: var(--surface); color: var(--text); }
.theme-toggle svg { width: 17px; height: 17px; transition: transform 0.4s ease; }
.theme-toggle:hover svg { transform: rotate(20deg); }

/* ── HERO ── */
.hero {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 8rem 2.5rem 5rem;
  position: relative;
  overflow: hidden;
  text-align: center;
}
.hero-orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.25;
  pointer-events: none;
}
.hero-orb-1 {
  width: 600px; height: 600px;
  background: var(--accent);
  top: -150px; left: 50%;
  transform: translateX(-50%);
  animation: float1 8s ease-in-out infinite;
}
.hero-orb-2 {
  width: 350px; height: 350px;
  background: var(--teal);
  bottom: 0; left: 10%;
  animation: float2 10s ease-in-out infinite;
  opacity: 0.15;
}
.hero-orb-3 {
  width: 280px; height: 280px;
  background: var(--amber);
  bottom: 100px; right: 8%;
  animation: float1 12s ease-in-out infinite reverse;
  opacity: 0.12;
}
@keyframes float1 {
  0%,100% { transform: translateX(-50%) translateY(0); }
  50% { transform: translateX(-50%) translateY(-30px); }
}
@keyframes float2 {
  0%,100% { transform: translateY(0); }
  50% { transform: translateY(-20px); }
}
.hero-inner {
  position: relative;
  z-index: 1;
  max-width: 760px;
}
.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 0.4rem 1rem;
  background: var(--surface);
  border: 1px solid var(--border2);
  border-radius: 99px;
  font-size: 0.82rem;
  color: var(--text2);
  font-family: var(--font-mono);
  margin-bottom: 2.5rem;
  animation: fadeUp 0.6s ease both;
}
.hero-badge-dot {
  width: 6px; height: 6px;
  background: var(--teal);
  border-radius: 50%;
  animation: pulse 2s ease infinite;
}
@keyframes pulse {
  0%,100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.5; transform: scale(0.8); }
}
h1.hero-title {
  font-family: var(--font-head);
  font-size: clamp(2.8rem, 7vw, 5rem);
  font-weight: 800;
  line-height: 1.05;
  letter-spacing: -0.04em;
  color: var(--text);
  margin-bottom: 1.75rem;
  animation: fadeUp 0.6s 0.1s ease both;
}
.hero-title span.grad {
  background: linear-gradient(135deg, var(--accent2) 0%, var(--teal) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
.hero-sub {
  font-size: 1.15rem;
  color: var(--text2);
  max-width: 540px;
  margin: 0 auto 3rem;
  line-height: 1.75;
  font-weight: 300;
  animation: fadeUp 0.6s 0.2s ease both;
}
.hero-cta {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
  animation: fadeUp 0.6s 0.3s ease both;
}
.hero-scroll {
  position: absolute;
  bottom: 2.5rem; left: 50%;
  transform: translateX(-50%);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  color: var(--text3);
  font-size: 0.78rem;
  font-family: var(--font-mono);
  animation: fadeUp 0.6s 0.6s ease both;
  text-decoration: none;
  transition: color var(--transition);
}
.hero-scroll:hover { color: var(--text2); }
.hero-scroll-line {
  width: 1px; height: 48px;
  background: linear-gradient(to bottom, transparent, var(--text3));
  animation: scrollLine 1.8s ease infinite;
}
@keyframes scrollLine {
  0% { transform: scaleY(0); transform-origin: top; }
  50% { transform: scaleY(1); transform-origin: top; }
  51% { transform-origin: bottom; }
  100% { transform: scaleY(0); transform-origin: bottom; }
}
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(24px); }
  to { opacity: 1; transform: translateY(0); }
}

/* ── SECTIONS ── */
section {
  position: relative;
  z-index: 1;
}
.container {
  max-width: 1100px;
  margin: 0 auto;
  padding: 0 2.5rem;
}
.section-label {
  font-family: var(--font-mono);
  font-size: 0.78rem;
  color: var(--accent);
  text-transform: uppercase;
  letter-spacing: 0.15em;
  margin-bottom: 1rem;
  display: block;
}
.section-title {
  font-family: var(--font-head);
  font-size: clamp(1.8rem, 4vw, 2.8rem);
  font-weight: 700;
  letter-spacing: -0.03em;
  line-height: 1.1;
  color: var(--text);
  margin-bottom: 1rem;
}
.section-sub {
  font-size: 1rem;
  color: var(--text2);
  max-width: 520px;
  line-height: 1.75;
  font-weight: 300;
}

/* ── HOW IT WORKS ── */
.how-section { padding: 7rem 0; }
.how-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 1.5px;
  margin-top: 4rem;
  border: 1.5px solid var(--border);
  border-radius: var(--radius-lg);
  overflow: hidden;
}
.how-card {
  padding: 2.5rem 2rem;
  background: var(--surface);
  transition: background var(--transition);
  position: relative;
  cursor: default;
}
.how-card::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, var(--accent-glow), transparent);
  opacity: 0;
  transition: opacity var(--transition-slow);
}
.how-card:hover { background: var(--surface2); }
.how-card:hover::before { opacity: 1; }
.how-num {
  font-family: var(--font-mono);
  font-size: 0.75rem;
  color: var(--accent);
  margin-bottom: 1.25rem;
  display: block;
}
.how-icon {
  width: 44px; height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.25rem;
  font-size: 1.3rem;
  transition: transform var(--transition);
}
.how-card:hover .how-icon { transform: scale(1.1) rotate(-5deg); }
.how-icon.purple { background: rgba(124,106,247,0.15); }
.how-icon.teal { background: var(--teal-dim); }
.how-icon.amber { background: var(--amber-dim); }
.how-icon.coral { background: var(--coral-dim); }
.how-card h3 {
  font-family: var(--font-head);
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 0.75rem;
  color: var(--text);
  letter-spacing: -0.02em;
}
.how-card p { font-size: 0.92rem; color: var(--text2); line-height: 1.7; font-weight: 300; }

/* ── FEATURES ── */
.features-section { padding: 6rem 0; }
.features-header { margin-bottom: 4rem; }
.features-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;
}
.feature-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 2rem;
  transition: all var(--transition);
  cursor: default;
  position: relative;
  overflow: hidden;
}
.feature-card::after {
  content: '';
  position: absolute;
  bottom: 0; left: 0; right: 0;
  height: 2px;
  background: linear-gradient(90deg, var(--accent), var(--teal));
  transform: scaleX(0);
  transition: transform var(--transition-slow);
  transform-origin: left;
}
.feature-card:hover { border-color: var(--border2); transform: translateY(-3px); box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
.feature-card:hover::after { transform: scaleX(1); }
.feature-tag {
  display: inline-block;
  padding: 0.3rem 0.75rem;
  border-radius: 99px;
  font-size: 0.75rem;
  font-family: var(--font-mono);
  margin-bottom: 1.25rem;
}
.tag-purple { background: rgba(124,106,247,0.15); color: var(--accent2); }
.tag-teal { background: var(--teal-dim); color: var(--teal); }
.tag-amber { background: var(--amber-dim); color: var(--amber); }
.tag-coral { background: var(--coral-dim); color: var(--coral); }
.feature-card h3 {
  font-family: var(--font-head);
  font-size: 1.15rem;
  font-weight: 700;
  margin-bottom: 0.75rem;
  color: var(--text);
  letter-spacing: -0.02em;
}
.feature-card p { font-size: 0.9rem; color: var(--text2); line-height: 1.7; font-weight: 300; }
.feature-card.wide { grid-column: 1 / -1; display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: center; }
.feature-card.wide .feature-visual {
  background: var(--bg3);
  border-radius: var(--radius);
  padding: 1.5rem;
  font-family: var(--font-mono);
  font-size: 0.8rem;
  color: var(--text2);
  border: 1px solid var(--border);
  line-height: 2;
}
.feature-card.wide .feature-visual .line-accent { color: var(--accent2); }
.feature-card.wide .feature-visual .line-teal { color: var(--teal); }
.feature-card.wide .feature-visual .line-amber { color: var(--amber); }
.feature-card.wide .feature-visual .line-muted { color: var(--text3); }
.feature-card.wide .feature-visual .cursor {
  display: inline-block;
  width: 2px; height: 1em;
  background: var(--accent);
  vertical-align: middle;
  margin-left: 2px;
  animation: blink 1s step-end infinite;
}
@keyframes blink { 50% { opacity: 0; } }

/* ── STATS ── */
.stats-section { padding: 5rem 0; }
.stats-inner {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1px;
  background: var(--border);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  overflow: hidden;
}
.stat-item {
  background: var(--surface);
  padding: 2.5rem 2rem;
  text-align: center;
  transition: background var(--transition);
  cursor: default;
}
.stat-item:hover { background: var(--surface2); }
.stat-num {
  font-family: var(--font-head);
  font-size: 2.5rem;
  font-weight: 800;
  color: var(--text);
  letter-spacing: -0.04em;
  display: block;
  margin-bottom: 0.5rem;
}
.stat-num span { color: var(--accent2); }
.stat-label { font-size: 0.85rem; color: var(--text3); font-weight: 300; }

/* ── DASHBOARD PREVIEW ── */
.dashboard-section { padding: 7rem 0; }
.dashboard-window {
  background: var(--bg2);
  border: 1px solid var(--border);
  border-radius: var(--radius-xl);
  overflow: hidden;
  margin-top: 4rem;
  box-shadow: 0 40px 120px rgba(0,0,0,0.5);
  transition: transform var(--transition-slow), box-shadow var(--transition-slow);
}
.dashboard-window:hover {
  transform: translateY(-6px);
  box-shadow: 0 60px 140px rgba(0,0,0,0.6), 0 0 0 1px rgba(124,106,247,0.2);
}
.window-bar {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 0.85rem 1.5rem;
  background: var(--bg3);
  border-bottom: 1px solid var(--border);
}
.window-dot {
  width: 12px; height: 12px;
  border-radius: 50%;
  cursor: pointer;
  transition: filter var(--transition);
}
.window-dot:hover { filter: brightness(1.3); }
.window-dot.red { background: #ff5f57; }
.window-dot.yellow { background: #febc2e; }
.window-dot.green { background: #28c840; }
.window-title {
  flex: 1;
  text-align: center;
  font-family: var(--font-mono);
  font-size: 0.78rem;
  color: var(--text3);
}
.dashboard-layout {
  display: grid;
  grid-template-columns: 240px 1fr;
  min-height: 500px;
}
.dash-sidebar {
  background: var(--bg3);
  border-right: 1px solid var(--border);
  padding: 1.5rem 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}
.dash-user {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 0.75rem;
  margin-bottom: 1rem;
  border-radius: var(--radius-sm);
  background: var(--surface);
}
.dash-avatar {
  width: 36px; height: 36px;
  border-radius: 10px;
  background: linear-gradient(135deg, var(--accent), var(--teal));
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.85rem;
  font-weight: 600;
  color: white;
  font-family: var(--font-head);
  flex-shrink: 0;
}
.dash-user-info {}
.dash-user-name { font-size: 0.88rem; font-weight: 500; color: var(--text); line-height: 1.2; }
.dash-user-level { font-size: 0.73rem; color: var(--text3); font-family: var(--font-mono); }
.dash-nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 0.6rem 0.75rem;
  border-radius: var(--radius-sm);
  font-size: 0.85rem;
  color: var(--text2);
  cursor: pointer;
  transition: all var(--transition);
}
.dash-nav-item:hover { background: var(--surface); color: var(--text); }
.dash-nav-item.active { background: rgba(124,106,247,0.15); color: var(--accent2); }
.dash-nav-icon { font-size: 1rem; width: 20px; text-align: center; }
.dash-main {
  padding: 2rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  overflow: hidden;
}
.dash-cards {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.75rem;
}
.dash-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 1.25rem;
  transition: all var(--transition);
  cursor: default;
}
.dash-card:hover { border-color: var(--border2); transform: translateY(-2px); }
.dash-card-label { font-size: 0.73rem; color: var(--text3); font-family: var(--font-mono); margin-bottom: 0.5rem; display: block; }
.dash-card-val { font-family: var(--font-head); font-size: 1.8rem; font-weight: 700; letter-spacing: -0.03em; }
.dash-card-val.purple { color: var(--accent2); }
.dash-card-val.teal { color: var(--teal); }
.dash-card-val.amber { color: var(--amber); }
.dash-card-sub { font-size: 0.75rem; color: var(--text3); margin-top: 0.25rem; }
.dash-chat {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 1.25rem;
  flex: 1;
}
.chat-messages { display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 1rem; }
.chat-msg {
  display: flex;
  gap: 10px;
  font-size: 0.83rem;
  color: var(--text2);
  line-height: 1.6;
  animation: fadeUp 0.5s ease both;
}
.chat-msg.user { flex-direction: row-reverse; }
.chat-msg.user .chat-bubble { background: rgba(124,106,247,0.2); color: var(--accent2); border-radius: 12px 12px 2px 12px; }
.chat-bubble {
  background: var(--bg3);
  padding: 0.6rem 0.9rem;
  border-radius: 12px 12px 12px 2px;
  max-width: 80%;
  line-height: 1.55;
}
.chat-avatar-sm {
  width: 28px; height: 28px;
  border-radius: 8px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
  font-weight: 600;
}
.chat-avatar-sm.ai { background: rgba(124,106,247,0.2); color: var(--accent2); }
.chat-avatar-sm.user { background: rgba(45,212,160,0.2); color: var(--teal); }
.chat-input-bar {
  display: flex;
  gap: 8px;
  align-items: center;
  background: var(--bg3);
  border: 1px solid var(--border2);
  border-radius: var(--radius-sm);
  padding: 0.5rem 0.75rem;
}
.chat-input-text { flex: 1; font-size: 0.82rem; color: var(--text3); font-family: var(--font-body); }
.chat-send {
  width: 28px; height: 28px;
  background: var(--accent);
  border-radius: 7px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all var(--transition);
}
.chat-send:hover { transform: scale(1.1); box-shadow: 0 4px 14px rgba(124,106,247,0.5); }
.chat-send svg { width: 14px; height: 14px; fill: white; }

/* ── PROGRESS BARS ── */
.progress-section-inner {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 1.25rem;
}
.progress-title { font-size: 0.8rem; color: var(--text3); font-family: var(--font-mono); margin-bottom: 1rem; }
.progress-item { margin-bottom: 0.85rem; }
.progress-item:last-child { margin-bottom: 0; }
.progress-label {
  display: flex;
  justify-content: space-between;
  font-size: 0.8rem;
  color: var(--text2);
  margin-bottom: 5px;
}
.progress-label span:last-child { color: var(--text3); font-family: var(--font-mono); }
.progress-bar {
  height: 5px;
  background: var(--bg3);
  border-radius: 99px;
  overflow: hidden;
}
.progress-fill {
  height: 100%;
  border-radius: 99px;
  transition: width 1.5s cubic-bezier(0.4,0,0.2,1);
}
.fill-purple { background: linear-gradient(90deg, var(--accent), var(--accent2)); }
.fill-teal { background: linear-gradient(90deg, var(--teal), #4ce8b8); }
.fill-amber { background: linear-gradient(90deg, var(--amber), #f7c564); }
.fill-coral { background: linear-gradient(90deg, var(--coral), #f7a0a0); }

/* ── TESTIMONIALS ── */
.testimonials-section { padding: 6rem 0; }
.testimonials-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.25rem;
  margin-top: 3.5rem;
}
.testimonial-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 1.75rem;
  transition: all var(--transition);
  cursor: default;
}
.testimonial-card:hover { border-color: var(--border2); transform: translateY(-4px); box-shadow: 0 20px 50px rgba(0,0,0,0.3); }
.testimonial-stars { color: var(--amber); margin-bottom: 1rem; font-size: 0.9rem; letter-spacing: 2px; }
.testimonial-text {
  font-size: 0.92rem;
  color: var(--text2);
  line-height: 1.75;
  font-weight: 300;
  margin-bottom: 1.5rem;
  font-style: italic;
}
.testimonial-author { display: flex; align-items: center; gap: 10px; }
.testimonial-avatar {
  width: 38px; height: 38px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.82rem;
  font-weight: 600;
  color: white;
  font-family: var(--font-head);
  flex-shrink: 0;
}
.testimonial-name { font-size: 0.88rem; font-weight: 500; color: var(--text); }
.testimonial-role { font-size: 0.75rem; color: var(--text3); font-family: var(--font-mono); }

/* ── CTA SECTION ── */
.cta-section { padding: 7rem 0; }
.cta-inner {
  text-align: center;
  max-width: 600px;
  margin: 0 auto;
}
.cta-inner .section-title { margin-bottom: 1.5rem; }
.cta-inner .section-sub { margin: 0 auto 3rem; }
.cta-inner .btn-lg { margin: 0 auto; }

/* ── FOOTER ── */
footer {
  border-top: 1px solid var(--border);
  padding: 3rem 0;
}
.footer-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1.5rem;
}
.footer-copy { font-size: 0.83rem; color: var(--text3); font-weight: 300; }
.footer-links { display: flex; gap: 2rem; }
.footer-links a {
  font-size: 0.83rem;
  color: var(--text3);
  text-decoration: none;
  transition: color var(--transition);
}
.footer-links a:hover { color: var(--text2); }

/* ── INTERSECT ANIMATIONS ── */
.reveal {
  opacity: 0;
  transform: translateY(30px);
  transition: opacity 0.7s ease, transform 0.7s ease;
}
.reveal.visible {
  opacity: 1;
  transform: translateY(0);
}

/* ── MOBILE ── */
@media (max-width: 768px) {
  nav { padding: 0 1.25rem; }
  .nav-links { display: none; }
  .hero { padding: 7rem 1.25rem 4rem; }
  .container { padding: 0 1.25rem; }
  .features-grid { grid-template-columns: 1fr; }
  .feature-card.wide { grid-column: auto; display: block; }
  .feature-card.wide .feature-visual { margin-top: 1.5rem; }
  .stats-inner { grid-template-columns: 1fr 1fr; }
  .dashboard-layout { grid-template-columns: 1fr; }
  .dash-sidebar { display: none; }
  .testimonials-grid { grid-template-columns: 1fr; }
  .how-grid { grid-template-columns: 1fr; }
  .dash-cards { grid-template-columns: 1fr; }
}

/* ── CUSTOM CURSOR ── */
.cursor-dot {
  width: 8px; height: 8px;
  background: var(--accent);
  border-radius: 50%;
  position: fixed;
  pointer-events: none;
  z-index: 9999;
  transform: translate(-50%, -50%);
  transition: width 0.2s, height 0.2s, background 0.2s;
  mix-blend-mode: screen;
}
.cursor-ring {
  width: 32px; height: 32px;
  border: 1.5px solid rgba(124,106,247,0.4);
  border-radius: 50%;
  position: fixed;
  pointer-events: none;
  z-index: 9998;
  transform: translate(-50%, -50%);
  transition: width 0.15s, height 0.15s, border-color 0.15s;
}
body:has(a:hover, button:hover, .btn:hover, [class*="card"]:hover) .cursor-ring {
  width: 48px; height: 48px;
  border-color: rgba(124,106,247,0.6);
}
@media (hover: none) { .cursor-dot, .cursor-ring { display: none; } }

/* ── FLOATING PARTICLES ── */
.particles { position: fixed; inset: 0; pointer-events: none; z-index: 0; overflow: hidden; }
.particle {
  position: absolute;
  width: 2px; height: 2px;
  background: var(--accent);
  border-radius: 50%;
  opacity: 0;
  animation: particleFloat linear infinite;
}
@keyframes particleFloat {
  0% { opacity: 0; transform: translateY(100vh) translateX(0); }
  10% { opacity: 0.6; }
  90% { opacity: 0.3; }
  100% { opacity: 0; transform: translateY(-10vh) translateX(var(--dx)); }
}
</style>
</head>
<body>

<div class="cursor-dot" id="cursorDot"></div>
<div class="cursor-ring" id="cursorRing"></div>

<div class="particles" id="particles"></div>

<!-- NAV -->
<nav>
  <a href="#" class="nav-logo">
    <div class="nav-logo-mark">
      <svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
    </div>
    MindForge
  </a>

  <ul class="nav-links">
    <li><a href="#cum-functioneaza" class="active">Cum funcționează</a></li>
    <li><a href="#functionalitati">Funcționalități</a></li>
    <li><a href="#dashboard">Dashboard</a></li>
    <li><a href="#testimoniale">Testimoniale</a></li>
  </ul>

  <div class="nav-actions">
    <button class="theme-toggle" id="themeToggle" aria-label="Schimbă tema">
      <svg id="iconMoon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
      </svg>
      <svg id="iconSun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none">
        <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
      </svg>
    </button>
    <?php if($isLoggedIn): ?>
      <a href="dashboard.php" class="btn btn-primary">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        Dashboard
      </a>
    <?php else: ?>
      <a href="login.php" class="btn">Conectare</a>
      <a href="register.php" class="btn btn-primary">Începe gratuit</a>
    <?php endif; ?>
  </div>
</nav>

<!-- HERO -->
<section class="hero" id="hero">
  <div class="hero-orb hero-orb-1"></div>
  <div class="hero-orb hero-orb-2"></div>
  <div class="hero-orb hero-orb-3"></div>

  <div class="hero-inner">
    <div class="hero-badge">
      <span class="hero-badge-dot"></span>
      Nu ești consumator de informație — ești creatorul ei
    </div>

    <h1 class="hero-title">
      Gândești mai profund.<br>
      Devii <span class="grad">mai puternic.</span>
    </h1>

    <p class="hero-sub">
      Un mentor AI care nu îți dă răspunsurile mură-n gură.
      Te ghidează să le descoperi singur — pe plan personal,
      social și educațional.
    </p>

    <div class="hero-cta">
      <a href="register.php" class="btn btn-primary btn-lg">
        Începe călătoria
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </a>
      <a href="#cum-functioneaza" class="btn btn-ghost btn-lg">Descoperă cum funcționează</a>
    </div>
  </div>

  <!-- <a href="#cum-functioneaza" class="hero-scroll">
    <span>scroll</span>
    <div class="hero-scroll-line"></div>
  </a> -->
</section>

<!-- CUM FUNCȚIONEAZĂ -->
<section class="how-section" id="cum-functioneaza">
  <div class="container">
    <div class="reveal">
      <span class="section-label">Metoda</span>
      <h2 class="section-title">Socratic AI — nu un motor de căutare</h2>
      <p class="section-sub">Orice dialog are o logică: te întrebăm, tu răspunzi, ambii creștem.</p>
    </div>

    <div class="how-grid reveal">
      <div class="how-card">
        <span class="how-num">01</span>
        <div class="how-icon purple">🧠</div>
        <h3>Îți studiezi nivelul real</h3>
        <p>Din primele interacțiuni, MindForge îți înțelege stilul de gândire, punctele forte și zonele de crescut — fără teste plictisitoare.</p>
      </div>
      <div class="how-card">
        <span class="how-num">02</span>
        <div class="how-icon teal">💬</div>
        <h3>Dialoghezi, nu consumi</h3>
        <p>Pui o întrebare. AI-ul îți pune una înapoi. Prin această metodă socratică, tu construiești cunoașterea — nu o primești gata.</p>
      </div>
      <div class="how-card">
        <span class="how-num">03</span>
        <div class="how-icon amber">🎯</div>
        <h3>Crești pe toate planurile</h3>
        <p>Educație, dezvoltare personală, pregătire socială — toate în același spațiu, adaptat la tine, nu la o medie statistică.</p>
      </div>
      <div class="how-card">
        <span class="how-num">04</span>
        <div class="how-icon coral">🔍</div>
        <h3>Reflectezi și integrezi</h3>
        <p>La finalul fiecărei sesiuni, ești provocat să articulezi ce ai descoperit. Abia atunci cunoașterea devine a ta cu adevărat.</p>
      </div>
    </div>
  </div>
</section>

<!-- STATS -->
<section class="stats-section">
  <div class="container">
    <div class="stats-inner reveal">
      <div class="stat-item">
        <span class="stat-num">3<span>x</span></span>
        <span class="stat-label">retenție mai bună față de lectură pasivă</span>
      </div>
      <div class="stat-item">
        <span class="stat-num">87<span>%</span></span>
        <span class="stat-label">dintre utilizatori raportează progres personal vizibil</span>
      </div>
      <div class="stat-item">
        <span class="stat-num">0</span>
        <span class="stat-label">răspunsuri date fără ca tu să gândești mai întâi</span>
      </div>
      <div class="stat-item">
        <span class="stat-num">∞</span>
        <span class="stat-label">subiecte disponibile — de la matematică la identitate</span>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="features-section" id="functionalitati">
  <div class="container">
    <div class="features-header reveal">
      <span class="section-label">Funcționalități</span>
      <h2 class="section-title">Construit pentru omul care<br>vrea mai mult</h2>
    </div>

    <div class="features-grid reveal">
      <div class="feature-card">
        <span class="feature-tag tag-purple">Profil dinamic</span>
        <h3>AI-ul te cunoaște cu adevărat</h3>
        <p>Fiecare conversație actualizează profilul tău cognitiv și emoțional. Nu repeți lucruri deja știute, nu ești tratat ca un necunoscut.</p>
      </div>

      <div class="feature-card">
        <span class="feature-tag tag-teal">Gândire critică</span>
        <h3>Nu îți dăm pești. Te învățăm să pescuiești</h3>
        <p>Mentorul nu răspunde direct. Pune întrebări care te duc spre răspuns — tocmai de aceea îl vei ține minte.</p>
      </div>

      <div class="feature-card wide">
        <div>
          <span class="feature-tag tag-amber">Dialog real</span>
          <h3>Conversație ca o sesiune cu un mentor bun</h3>
          <p>Nu copy-paste de pe Wikipedia. Nu o comandă adresată unui robot. Un schimb autentic, adaptat ritmului și nivelului tău real — în fiecare sesiune.</p>
          <br>
          <p style="color:var(--text3);font-size:0.85rem;font-weight:300">Funcționează pentru matematică, filosofie, comunicare, emoții, carieră, relații — orice subiect uman contează.</p>
        </div>
        <div class="feature-visual">
          <div class="line-muted"># sesiune de dialog</div>
          <div class="line-accent">Tu: „Nu înțeleg anxietatea socială"</div>
          <div class="line-muted">→ analizez nivelul...</div>
          <div class="line-teal">AI: „Când ai simțit-o ultima dată?"</div>
          <div class="line-accent">Tu: „Ieri, la o prezentare"</div>
          <div class="line-teal">AI: „Ce gând ai avut exact înainte?"</div>
          <div class="line-amber">→ profil actualizat: nivel mediu, stil reflectiv</div>
          <div class="line-teal">AI: „Știi de unde vine acea voce?"<span class="cursor"></span></div>
        </div>
      </div>

      <div class="feature-card">
        <span class="feature-tag tag-coral">Reflecție</span>
        <h3>Sesiunea nu se termină când crezi tu</h3>
        <p>La final, ești întrebat ce ai integrat, ce rămâne neclar și cum vei aplica. Abia aceasta e învățarea reală.</p>
      </div>

      <div class="feature-card">
        <span class="feature-tag tag-purple">Progres vizibil</span>
        <h3>Dashboard care arată cine devii</h3>
        <p>Nu note. Nu puncte. Skill-uri reale: gândire critică, claritate emoțională, comunicare, curaj. Urмărești o persoană care crește.</p>
      </div>
    </div>
  </div>
</section>

<!-- DASHBOARD PREVIEW -->
<section class="dashboard-section" id="dashboard">
  <div class="container">
    <div class="reveal" style="text-align:center;max-width:560px;margin:0 auto;">
      <span class="section-label">Interfața</span>
      <h2 class="section-title">Dashboard-ul tău personal</h2>
      <p class="section-sub">Un spațiu curat, fără distrageri, unde evoluția ta este vizibilă.</p>
    </div>

    <div class="dashboard-window reveal">
      <div class="window-bar">
        <div class="window-dot red"></div>
        <div class="window-dot yellow"></div>
        <div class="window-dot green"></div>
        <span class="window-title">mindforge.ro — dashboard personal</span>
      </div>

      <div class="dashboard-layout">
        <!-- SIDEBAR -->
        <div class="dash-sidebar">
          <div class="dash-user">
            <div class="dash-avatar"><?= strtoupper(substr($userName,0,2)) ?></div>
            <div class="dash-user-info">
              <div class="dash-user-name"><?= htmlspecialchars($userName) ?></div>
              <div class="dash-user-level"><?= htmlspecialchars($userLevel) ?></div>
            </div>
          </div>
          <div class="dash-nav-item active">
            <span class="dash-nav-icon">⬡</span> Dashboard
          </div>
          <div class="dash-nav-item">
            <span class="dash-nav-icon">💬</span> Sesiune nouă
          </div>
          <div class="dash-nav-item">
            <span class="dash-nav-icon">📊</span> Progresul meu
          </div>
          <div class="dash-nav-item">
            <span class="dash-nav-icon">📝</span> Reflecții
          </div>
          <div class="dash-nav-item">
            <span class="dash-nav-icon">⚙</span> Setări
          </div>
        </div>

        <!-- MAIN -->
        <div class="dash-main">
          <div class="dash-cards">
            <div class="dash-card">
              <span class="dash-card-label">sesiuni totale</span>
              <div class="dash-card-val purple">24</div>
              <div class="dash-card-sub">+3 săptămâna aceasta</div>
            </div>
            <div class="dash-card">
              <span class="dash-card-label">gândire critică</span>
              <div class="dash-card-val teal"><?= $userScore ?>%</div>
              <div class="dash-card-sub">+8% față de luna trecută</div>
            </div>
            <div class="dash-card">
              <span class="dash-card-label">consistență</span>
              <div class="dash-card-val amber">6 zile</div>
              <div class="dash-card-sub">serie activă curentă</div>
            </div>
          </div>

          <div class="dash-chat">
            <div class="chat-messages" id="chatMessages">
              <div class="chat-msg ai">
                <div class="chat-avatar-sm ai">AI</div>
                <div class="chat-bubble">Bună, <?= htmlspecialchars($userName) ?>! Ce vrei să explorezi azi?</div>
              </div>
              <div class="chat-msg user">
                <div class="chat-avatar-sm user"><?= strtoupper(substr($userName,0,1)) ?></div>
                <div class="chat-bubble">Vreau să înțeleg de ce mă blochez când vorbesc în public.</div>
              </div>
              <div class="chat-msg ai">
                <div class="chat-avatar-sm ai">AI</div>
                <div class="chat-bubble">Interesant. Care este primul gând care îți trece prin minte chiar înainte să începi să vorbești?</div>
              </div>
            </div>
            <div class="chat-input-bar">
              <span class="chat-input-text">Scrie răspunsul tău...</span>
              <div class="chat-send">
                <svg viewBox="0 0 24 24"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
              </div>
            </div>
          </div>

          <div class="progress-section-inner">
            <div class="progress-title">// skill-uri în dezvoltare</div>
            <div class="progress-item">
              <div class="progress-label"><span>Gândire critică</span><span>72%</span></div>
              <div class="progress-bar"><div class="progress-fill fill-purple" id="p1" style="width:0%"></div></div>
            </div>
            <div class="progress-item">
              <div class="progress-label"><span>Claritate emoțională</span><span>58%</span></div>
              <div class="progress-bar"><div class="progress-fill fill-teal" id="p2" style="width:0%"></div></div>
            </div>
            <div class="progress-item">
              <div class="progress-label"><span>Comunicare</span><span>81%</span></div>
              <div class="progress-bar"><div class="progress-fill fill-amber" id="p3" style="width:0%"></div></div>
            </div>
            <div class="progress-item">
              <div class="progress-label"><span>Rezistență la presiune</span><span>45%</span></div>
              <div class="progress-bar"><div class="progress-fill fill-coral" id="p4" style="width:0%"></div></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- TESTIMONIALE
<section class="testimonials-section" id="testimoniale">
  <div class="container">
    <div class="reveal" style="text-align:center;max-width:480px;margin:0 auto;">
      <span class="section-label">Comunitate</span>
      <h2 class="section-title">Ce spun cei care gândesc cu noi</h2>
    </div>

    <div class="testimonials-grid reveal">
      <div class="testimonial-card">
        <div class="testimonial-stars">★★★★★</div>
        <p class="testimonial-text">„Prima platformă care mă tratează ca pe un adult. Nu îmi dă răspunsuri — mă face să gândesc. Și asta schimbă totul."</p>
        <div class="testimonial-author">
          <div class="testimonial-avatar" style="background:linear-gradient(135deg,#7c6af7,#a395fb)">MA</div>
          <div>
            <div class="testimonial-name">Mihai Andrei</div>
            <div class="testimonial-role">student, 22 ani</div>
          </div>
        </div>
      </div>
      <div class="testimonial-card">
        <div class="testimonial-stars">★★★★★</div>
        <p class="testimonial-text">„Am folosit zeci de aplicații de learning. MindForge e singura care m-a pus față în față cu mine însumi. Incomod, dar necesar."</p>
        <div class="testimonial-author">
          <div class="testimonial-avatar" style="background:linear-gradient(135deg,#2dd4a0,#4ce8b8)">EI</div>
          <div>
            <div class="testimonial-name">Elena Ionescu</div>
            <div class="testimonial-role">psiholog, 34 ani</div>
          </div>
        </div>
      </div>
      <div class="testimonial-card">
        <div class="testimonial-stars">★★★★★</div>
        <p class="testimonial-text">„Matematica, comunicarea, anxietatea socială — totul într-un singur loc. Și fiecare sesiune mă lasă cu ceva real, nu cu un rezumat."</p>
        <div class="testimonial-author">
          <div class="testimonial-avatar" style="background:linear-gradient(135deg,#f5a623,#f7c564)">RC</div>
          <div>
            <div class="testimonial-name">Radu Constantin</div>
            <div class="testimonial-role">antreprenor, 28 ani</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section> -->

<!-- CTA -->
<section class="cta-section">
  <div class="container">
    <div class="cta-inner reveal">
      <span class="section-label" style="display:block;text-align:center">Gata să începi?</span>
      <h2 class="section-title">Umanitatea ta nu e de sacrificat pe altarul comodității</h2>
      <p class="section-sub">MindForge îți oferă puterea AI fără să-ți ia independența gândirii. Tu rămâi autorul propriei evoluții.</p>
      <a href="register.php" class="btn btn-primary btn-lg" style="display:inline-flex">
        Creează cont gratuit
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </a>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="container">
    <div class="footer-inner">
      <span class="footer-copy">© 2026 MindForge. Construit pentru oameni care gândesc.</span>
      <nav class="footer-links">
        <a href="#">Acasă</a>
        <a href="/MindForge/chat.php">Sesiune</a>
        <a href="/MindForge/dashboard.php">Profil</a>
        <a href="/MindForge/register.php">înregistrează-te</a>
      </nav>
    </div>
  </div>
</footer>

<script>
/* ── CUSTOM CURSOR ── */
const dot = document.getElementById('cursorDot');
const ring = document.getElementById('cursorRing');
let mx = 0, my = 0, rx = 0, ry = 0;
document.addEventListener('mousemove', e => { mx = e.clientX; my = e.clientY; });
function animateCursor() {
  rx += (mx - rx) * 0.12;
  ry += (my - ry) * 0.12;
  dot.style.left = mx + 'px';
  dot.style.top = my + 'px';
  ring.style.left = rx + 'px';
  ring.style.top = ry + 'px';
  requestAnimationFrame(animateCursor);
}
animateCursor();

/* ── THEME TOGGLE ── */
const html = document.documentElement;
const themeBtn = document.getElementById('themeToggle');
const iconMoon = document.getElementById('iconMoon');
const iconSun = document.getElementById('iconSun');
let isDark = true;
themeBtn.addEventListener('click', () => {
  isDark = !isDark;
  html.setAttribute('data-theme', isDark ? 'dark' : 'light');
  iconMoon.style.display = isDark ? 'block' : 'none';
  iconSun.style.display = isDark ? 'none' : 'block';
});

/* ── SCROLL REVEAL ── */
const observer = new IntersectionObserver(entries => {
  entries.forEach((e, i) => {
    if (e.isIntersecting) {
      setTimeout(() => e.target.classList.add('visible'), i * 60);
    }
  });
}, { threshold: 0.1, rootMargin: '0px 0px -60px 0px' });
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

/* ── PROGRESS BARS ── */
const progressObserver = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      setTimeout(() => {
        document.getElementById('p1').style.width = '72%';
        document.getElementById('p2').style.width = '58%';
        document.getElementById('p3').style.width = '81%';
        document.getElementById('p4').style.width = '45%';
      }, 300);
      progressObserver.disconnect();
    }
  });
}, { threshold: 0.3 });
const progSection = document.querySelector('.progress-section-inner');
if (progSection) progressObserver.observe(progSection);

/* ── FLOATING PARTICLES ── */
const particlesContainer = document.getElementById('particles');
function createParticle() {
  const p = document.createElement('div');
  p.className = 'particle';
  const x = Math.random() * 100;
  const dx = (Math.random() - 0.5) * 100 + 'px';
  const dur = 8 + Math.random() * 12;
  const delay = Math.random() * 8;
  const size = Math.random() > 0.7 ? 3 : 2;
  const colors = ['#7c6af7','#2dd4a0','#f5a623','#a395fb'];
  const color = colors[Math.floor(Math.random() * colors.length)];
  p.style.cssText = `left:${x}%;--dx:${dx};animation-duration:${dur}s;animation-delay:${delay}s;width:${size}px;height:${size}px;background:${color};`;
  particlesContainer.appendChild(p);
  setTimeout(() => p.remove(), (dur + delay) * 1000);
}
for (let i = 0; i < 20; i++) createParticle();
setInterval(createParticle, 1200);

/* ── NAV ACTIVE ON SCROLL ── */
const sections = document.querySelectorAll('section[id]');
const navLinks = document.querySelectorAll('.nav-links a');
window.addEventListener('scroll', () => {
  const scrollY = window.scrollY;
  sections.forEach(section => {
    if (scrollY >= section.offsetTop - 120) {
      navLinks.forEach(l => l.classList.remove('active'));
      const active = document.querySelector(`.nav-links a[href="#${section.id}"]`);
      if (active) active.classList.add('active');
    }
  });
}, { passive: true });

/* ── TYPING EFFECT IN FEATURE VISUAL ── */
/* already static — cursor blink handled by CSS */

/* ── PARALLAX ORBS ── */
document.addEventListener('mousemove', e => {
  const orb1 = document.querySelector('.hero-orb-1');
  const orb2 = document.querySelector('.hero-orb-2');
  const orb3 = document.querySelector('.hero-orb-3');
  const rx = (e.clientX / window.innerWidth - 0.5) * 30;
  const ry = (e.clientY / window.innerHeight - 0.5) * 20;
  if (orb1) orb1.style.transform = `translateX(calc(-50% + ${rx}px)) translateY(${ry}px)`;
  if (orb2) orb2.style.transform = `translateX(${rx * 0.5}px) translateY(${ry * 0.5}px)`;
  if (orb3) orb3.style.transform = `translateX(${-rx * 0.7}px) translateY(${ry * 0.7}px)`;
}, { passive: true });

/* ── COUNTER ANIMATION FOR STATS ── */
function animateCounter(el, target, duration = 1800) {
  let start = null;
  const isPercent = target.toString().includes('%');
  const isX = target.toString().includes('x');
  const num = parseFloat(target);
  const step = ts => {
    if (!start) start = ts;
    const progress = Math.min((ts - start) / duration, 1);
    const eased = 1 - Math.pow(1 - progress, 3);
    const current = Math.round(eased * num);
    el.innerHTML = current + (isPercent ? '<span>%</span>' : isX ? '<span>x</span>' : '');
    if (progress < 1) requestAnimationFrame(step);
  };
  requestAnimationFrame(step);
}
const statObserver = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      const nums = entry.target.querySelectorAll('.stat-num');
      nums.forEach(n => {
        const text = n.textContent.trim();
        if (text === '0') { n.innerHTML = '0'; return; }
        if (text === '∞') return;
        if (text.endsWith('x')) animateCounter(n, text, 1400);
        else if (text.endsWith('%')) animateCounter(n, text, 1600);
      });
      statObserver.disconnect();
    }
  });
}, { threshold: 0.4 });
const statsEl = document.querySelector('.stats-inner');
if (statsEl) statObserver.observe(statsEl);

/* ── SMOOTH NAV HIGHLIGHT ── */
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const id = a.getAttribute('href').slice(1);
    const target = document.getElementById(id);
    if (target) {
      e.preventDefault();
      target.scrollIntoView({ behavior: 'smooth' });
    }
  });
});
</script>
</body>
</html>