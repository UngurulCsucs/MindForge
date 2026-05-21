// ============================================
// MindForge — Chat Frontend Logic
// ============================================

const MindForge = (() => {

  // ── State ──────────────────────────────────
  let sessionId     = null;
  let isLoading     = false;
  let imageFile     = null;
  let messageCount  = 0;

  // ── DOM refs ───────────────────────────────
  const messagesEl   = () => document.getElementById('mf-messages');
  const inputEl      = () => document.getElementById('mf-input');
  const sendBtn      = () => document.getElementById('mf-send');
  const imageBtn     = () => document.getElementById('mf-image-btn');
  const imageInput   = () => document.getElementById('mf-image-input');
  const imagePreview = () => document.getElementById('mf-image-preview');
  const typingEl     = () => document.getElementById('mf-typing');
  const sessionList  = () => document.getElementById('mf-sessions');

  // ── Init ───────────────────────────────────
  function init() {
    bindEvents();
    focusInput();
    loadSessions();
    autoResizeTextarea();
  }

  function bindEvents() {
    sendBtn()?.addEventListener('click', sendMessage);

    inputEl()?.addEventListener('keydown', e => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
      }
    });

    inputEl()?.addEventListener('input', autoResizeTextarea);

    imageBtn()?.addEventListener('click', () => imageInput()?.click());

    imageInput()?.addEventListener('change', e => {
      const file = e.target.files[0];
      if (!file) return;
      if (file.size > 5 * 1024 * 1024) {
        showToast('Imaginea trebuie să fie sub 5MB', 'error');
        return;
      }
      imageFile = file;
      showImagePreview(file);
    });

    document.getElementById('mf-new-session')?.addEventListener('click', () => {
      sessionId = null;
      clearMessages();
      showWelcome();
      loadSessions();
    });

    document.getElementById('mf-remove-image')?.addEventListener('click', removeImage);
  }

  // ── Trimitere mesaj ────────────────────────
  async function sendMessage() {
    if (isLoading) return;

    const text = inputEl()?.value.trim() ?? '';
    if (!text && !imageFile) return;

    setLoading(true);
    hideWelcome();

    // Afiseaza mesajul userului
    appendMessage('user', text, imageFile ? URL.createObjectURL(imageFile) : null);

    // Reseteaza input
    if (inputEl()) inputEl().value = '';
    autoResizeTextarea();
    removeImage();

    showTyping();
    messageCount++;

    try {
      let response;

      if (imageFile) {
        // Multipart form pentru imagini
        const formData = new FormData();
        formData.append('message', text);
        formData.append('image', imageFile);
        if (sessionId) formData.append('session_id', sessionId);
        if (!sessionId) formData.append('new_session', '1');

        response = await fetch('/api/chat.php', {
          method: 'POST',
          body: formData,
        });
      } else {
        // JSON simplu pentru text
        response = await fetch('/api/chat.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            message:     text,
            session_id:  sessionId || 0,
            new_session: !sessionId,
          }),
        });
      }

      const data = await response.json();

      hideTyping();

      if (!response.ok || data.error) {
        showToast(data.error || 'Eroare server', 'error');
        setLoading(false);
        return;
      }

      // Actualizeaza session ID
      if (data.session_id) sessionId = data.session_id;

      // Afiseaza raspunsul AI cu efect de typing
      appendMessage('assistant', data.message);

      // Actualizeaza profilul in sidebar
      if (data.profile) updateProfileUI(data.profile);

      // Dupa 8 mesaje, propune reflectia
      if (messageCount > 0 && messageCount % 8 === 0) {
        setTimeout(() => promptReflection(), 800);
      }

      // Refresh lista sesiuni
      loadSessions();

    } catch (err) {
      hideTyping();
      showToast('Conexiune eșuată. Verifică internetul.', 'error');
      console.error('MindForge error:', err);
    }

    setLoading(false);
    focusInput();
  }

  // ── Afiseaza un mesaj ─────────────────────
  function appendMessage(role, text, imageUrl = null) {
    const wrap = messagesEl();
    if (!wrap) return;

    const div = document.createElement('div');
    div.className = `mf-msg mf-msg--${role}`;
    div.setAttribute('data-role', role);

    const avatar = document.createElement('div');
    avatar.className = 'mf-msg__avatar';
    avatar.textContent = role === 'assistant' ? 'MF' : (window.MF_USER_INITIALS || 'TU');

    const bubble = document.createElement('div');
    bubble.className = 'mf-msg__bubble';

    if (imageUrl) {
      const img = document.createElement('img');
      img.src = imageUrl;
      img.className = 'mf-msg__image';
      img.alt = 'Imagine atașată';
      bubble.appendChild(img);
    }

    const p = document.createElement('p');
    p.className = 'mf-msg__text';

    if (role === 'assistant') {
      // Typing effect pentru AI
      typeText(p, text, 18);
    } else {
      p.textContent = text;
    }

    bubble.appendChild(p);

    const time = document.createElement('span');
    time.className = 'mf-msg__time';
    time.textContent = new Date().toLocaleTimeString('ro-RO', { hour: '2-digit', minute: '2-digit' });
    bubble.appendChild(time);

    div.appendChild(avatar);
    div.appendChild(bubble);
    wrap.appendChild(div);

    scrollToBottom();
  }

  // ── Typing effect ─────────────────────────
  function typeText(el, text, speed = 20) {
    let i = 0;
    el.textContent = '';
    const interval = setInterval(() => {
      el.textContent += text[i];
      i++;
      if (i >= text.length) {
        clearInterval(interval);
        scrollToBottom();
      }
      // Scroll periodic in timp ce scrie
      if (i % 30 === 0) scrollToBottom();
    }, speed);
  }

  // ── Typing indicator ──────────────────────
  function showTyping() {
    const el = typingEl();
    if (el) el.style.display = 'flex';
    scrollToBottom();
  }

  function hideTyping() {
    const el = typingEl();
    if (el) el.style.display = 'none';
  }

  // ── Scroll ────────────────────────────────
  function scrollToBottom() {
    const wrap = messagesEl();
    if (wrap) wrap.scrollTop = wrap.scrollHeight;
  }

  // ── Image preview ─────────────────────────
  function showImagePreview(file) {
    const prev = imagePreview();
    if (!prev) return;
    const reader = new FileReader();
    reader.onload = e => {
      prev.style.display = 'flex';
      const img = prev.querySelector('img');
      if (img) img.src = e.target.result;
    };
    reader.readAsDataURL(file);
  }

  function removeImage() {
    imageFile = null;
    const prev = imagePreview();
    if (prev) prev.style.display = 'none';
    const inp = imageInput();
    if (inp) inp.value = '';
  }

  // ── Stare loading ─────────────────────────
  function setLoading(state) {
    isLoading = state;
    const btn = sendBtn();
    const inp = inputEl();
    if (btn) btn.disabled = state;
    if (inp) inp.disabled = state;
    if (btn) btn.classList.toggle('mf-send--loading', state);
  }

  // ── Auto-resize textarea ──────────────────
  function autoResizeTextarea() {
    const el = inputEl();
    if (!el) return;
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 160) + 'px';
  }

  // ── Welcome screen ────────────────────────
 // În chat.js, înlocuiește hideWelcome() cu:
    function hideWelcome() {
    const w = document.getElementById('mf-welcome');
    if (w) w.style.display = 'none';
    const m = document.getElementById('mf-messages');
    if (m) m.style.display = 'flex';   // ← lipsea asta
    }

  function showWelcome() {
    const w = document.getElementById('mf-welcome');
    if (w) w.style.display = 'flex';
    messageCount = 0;
  }

  function clearMessages() {
    const wrap = messagesEl();
    if (wrap) wrap.innerHTML = '';
  }

  // ── Reflectie ─────────────────────────────
  function promptReflection() {
    const div = document.createElement('div');
    div.className = 'mf-reflection';
    div.innerHTML = `
      <div class="mf-reflection__inner">
        <span class="mf-reflection__icon">🔍</span>
        <p><strong>Moment de reflecție</strong></p>
        <p>Ai parcurs câteva răspunsuri importante. Ce ai descoperit? Cum ai aplica asta în viața ta reală?</p>
        <div class="mf-reflection__actions">
          <button class="mf-btn mf-btn--ghost" onclick="this.closest('.mf-reflection').remove()">Mai târziu</button>
          <button class="mf-btn mf-btn--primary" onclick="MindForge.startReflection()">Reflectez acum</button>
        </div>
      </div>
    `;
    messagesEl()?.appendChild(div);
    scrollToBottom();
  }

  function startReflection() {
    document.querySelector('.mf-reflection')?.remove();
    if (inputEl()) inputEl().value = 'Vreau să reflectez asupra sesiunii noastre.';
    sendMessage();
  }

  // ── Sesiuni ───────────────────────────────
  async function loadSessions() {
    const list = sessionList();
    if (!list) return;

    try {
      const res  = await fetch('/api/sessions.php');
      const data = await res.json();
      if (!data.sessions) return;

      list.innerHTML = '';
      data.sessions.forEach(s => {
        const li = document.createElement('div');
        li.className = 'mf-session-item' + (s.id === sessionId ? ' mf-session-item--active' : '');
        li.innerHTML = `
          <span class="mf-session-item__icon">💬</span>
          <span class="mf-session-item__title">${escHtml(s.titlu)}</span>
        `;
        li.addEventListener('click', () => loadSession(s.id, s.titlu));
        list.appendChild(li);
      });
    } catch (e) {
      // Sesiunile nu sunt critice
    }
  }

  async function loadSession(id, titlu) {
    sessionId = id;
    clearMessages();
    hideWelcome();
    messageCount = 0;

    try {
      const res  = await fetch(`/api/messages.php?session_id=${id}`);
      const data = await res.json();
      if (data.messages) {
        data.messages.forEach(m => {
          appendMessage(m.role, m.content, m.image_path || null);
        });
      }
    } catch (e) {
      showToast('Nu s-a putut încărca sesiunea', 'error');
    }

    loadSessions();
  }

  // ── Update profil UI ──────────────────────
  function updateProfileUI(profile) {
    const setBar = (id, val) => {
      const el = document.getElementById(id);
      if (el) el.style.width = val + '%';
    };
    const setText = (id, val) => {
      const el = document.getElementById(id);
      if (el) el.textContent = val;
    };

    setBar('mf-bar-gc', profile.gandire_critica);
    setBar('mf-bar-ce', profile.claritate_emotionala);
    setBar('mf-bar-com', profile.comunicare);
    setBar('mf-bar-rez', profile.rezistenta);

    setText('mf-stat-sesiuni', profile.sesiuni_totale);
    setText('mf-stat-serie', profile.serie_activa + ' zile');
    setText('mf-nivel', profile.nivel);
  }

  // ── Toast notificari ─────────────────────
  function showToast(msg, type = 'info') {
    const t = document.createElement('div');
    t.className = `mf-toast mf-toast--${type}`;
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.classList.add('mf-toast--visible'), 10);
    setTimeout(() => {
      t.classList.remove('mf-toast--visible');
      setTimeout(() => t.remove(), 400);
    }, 3500);
  }

  // ── Utils ─────────────────────────────────
  function focusInput() {
    inputEl()?.focus();
  }

  function escHtml(str) {
    return str.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  }

  // ── Public API ────────────────────────────
  return { init, startReflection, showToast };

})();

// Porneste dupa DOM ready
document.addEventListener('DOMContentLoaded', MindForge.init);