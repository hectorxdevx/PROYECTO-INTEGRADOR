/**
 * Aceternity UI Interactive Effects & Universal Theme Manager (Light Default: White/Blue, Dark Mode Toggle)
 */
(function () {
    const STORAGE_KEY = 'theme';

    function getPreferredTheme() {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved === 'dark' || saved === 'light') {
            return saved;
        }
        return 'light'; // Por defecto Blanco y Azul (Modo Claro)
    }

    function applyTheme(theme) {
        document.documentElement.setAttribute('data-bs-theme', theme);
        if (document.body) {
            document.body.classList.toggle('theme-dark', theme === 'dark');
            document.body.classList.toggle('theme-light', theme === 'light');
        }

        // Sincronizar switch de Mazer si existe
        const mazerToggle = document.getElementById('toggle-dark');
        if (mazerToggle) {
            mazerToggle.checked = (theme === 'dark');
        }

        // Actualizar todos los botones .ac-theme-btn
        document.querySelectorAll('.ac-theme-btn').forEach(btn => {
            if (theme === 'dark') {
                btn.innerHTML = '<i class="bi bi-sun-fill"></i> <span>Modo Claro</span>';
            } else {
                btn.innerHTML = '<i class="bi bi-moon-stars-fill"></i> <span>Modo Oscuro</span>';
            }
        });
    }

    // Aplicar inmediatamente antes del renderizado completo
    const initialTheme = getPreferredTheme();
    document.documentElement.setAttribute('data-bs-theme', initialTheme);

    window.toggleSysMonitorTheme = function () {
        const current = document.documentElement.getAttribute('data-bs-theme') || 'light';
        const next = current === 'dark' ? 'light' : 'dark';
        localStorage.setItem(STORAGE_KEY, next);
        applyTheme(next);
    };

    document.addEventListener('DOMContentLoaded', () => {
        applyTheme(getPreferredTheme());

        // Escuchar clics en botones de tema Aceternity
        document.querySelectorAll('.ac-theme-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                window.toggleSysMonitorTheme();
            });
        });

        // Escuchar cambios en el switch #toggle-dark de los dashboards
        const mazerToggle = document.getElementById('toggle-dark');
        if (mazerToggle) {
            mazerToggle.addEventListener('change', (e) => {
                const next = e.target.checked ? 'dark' : 'light';
                localStorage.setItem(STORAGE_KEY, next);
                applyTheme(next);
            });
        }

        // Efecto Spotlight interactivo estilo Aceternity UI en todas las tarjetas .ac-bento-card
        document.querySelectorAll('.ac-bento-card').forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                card.style.setProperty('--mouse-x', `${x}px`);
                card.style.setProperty('--mouse-y', `${y}px`);
            });
        });
    });
})();
