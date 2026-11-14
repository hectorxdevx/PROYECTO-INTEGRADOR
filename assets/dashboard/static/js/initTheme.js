const body = document.body;
const theme = localStorage.getItem('theme') || 'light';
localStorage.setItem('theme', theme);
document.documentElement.setAttribute('data-bs-theme', theme);
if (body) {
  body.classList.toggle('theme-dark', theme === 'dark');
  body.classList.toggle('theme-light', theme === 'light');
}

