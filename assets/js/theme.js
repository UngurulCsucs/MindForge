// Aplică tema instant — pus în <head> previne orice flash
(function() {
  var t = localStorage.getItem('mf-theme') || 'dark';
  document.documentElement.setAttribute('data-theme', t);
})();

// Toggle + sincronizare iconițe
document.addEventListener('DOMContentLoaded', function() {
  var btn      = document.getElementById('themeToggle');
  var iconMoon = document.getElementById('iconMoon');
  var iconSun  = document.getElementById('iconSun');
  if (!btn) return;

  function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('mf-theme', theme);
    if (iconMoon) iconMoon.style.display = theme === 'dark' ? 'block' : 'none';
    if (iconSun)  iconSun.style.display  = theme === 'dark' ? 'none'  : 'block';
  }

  // Sincronizează iconița la încărcare
  applyTheme(localStorage.getItem('mf-theme') || 'dark');

  btn.addEventListener('click', function() {
    var current = document.documentElement.getAttribute('data-theme');
    applyTheme(current === 'dark' ? 'light' : 'dark');
  });
});