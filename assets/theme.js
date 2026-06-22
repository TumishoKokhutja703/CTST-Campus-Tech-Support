/**
 * theme.js — CTST Theme Switcher
 * Handles dark mode, light mode, and high contrast.
 * Include this in every page just before </body>
 */

const THEMES = {
    light: {
        label: 'Light',
        icon: '☀️',
        vars: {
            '--navy':           '#0F2D52',
            '--navy-mid':       '#1A4A7A',
            '--navy-light':     '#2563A8',
            '--navy-pale':      '#EEF4FB',
            '--navy-glow':      'rgba(37,99,168,0.15)',
            '--white':          '#FFFFFF',
            '--bg':             '#F0F4F8',
            '--bg-2':           '#E8EDF3',
            '--card':           '#FFFFFF',
            '--border':         '#DDE3EC',
            '--border-strong':  '#BCC8D8',
            '--text-primary':   '#0D1B2E',
            '--text-secondary': '#4A5D73',
            '--text-muted':     '#8899AA',
        }
    },
    dark: {
        label: 'Dark',
        icon: '🌙',
        vars: {
            '--navy':           '#60A5FA',
            '--navy-mid':       '#93C5FD',
            '--navy-light':     '#BFDBFE',
            '--navy-pale':      '#1E3A5F',
            '--navy-glow':      'rgba(96,165,250,0.2)',
            '--white':          '#0F172A',
            '--bg':             '#0A0F1E',
            '--bg-2':           '#111827',
            '--card':           '#0F172A',
            '--border':         '#1E2D45',
            '--border-strong':  '#2D3F5C',
            '--text-primary':   '#F1F5F9',
            '--text-secondary': '#94A3B8',
            '--text-muted':     '#64748B',
        }
    },
    midnight: {
        label: 'Midnight',
        icon: '🔵',
        vars: {
            '--navy':           '#818CF8',
            '--navy-mid':       '#A5B4FC',
            '--navy-light':     '#C7D2FE',
            '--navy-pale':      '#1E1B4B',
            '--navy-glow':      'rgba(129,140,248,0.2)',
            '--white':          '#0F0F23',
            '--bg':             '#080818',
            '--bg-2':           '#0D0D1F',
            '--card':           '#0F0F23',
            '--border':         '#1E1B4B',
            '--border-strong':  '#2D2B6B',
            '--text-primary':   '#EEF2FF',
            '--text-secondary': '#A5B4FC',
            '--text-muted':     '#6366F1',
        }
    },
    contrast: {
        label: 'High Contrast',
        icon: '⚡',
        vars: {
            '--navy':           '#000000',
            '--navy-mid':       '#000000',
            '--navy-light':     '#0000CC',
            '--navy-pale':      '#E0E8FF',
            '--navy-glow':      'rgba(0,0,204,0.2)',
            '--white':          '#FFFFFF',
            '--bg':             '#F5F5F5',
            '--bg-2':           '#E5E5E5',
            '--card':           '#FFFFFF',
            '--border':         '#000000',
            '--border-strong':  '#000000',
            '--text-primary':   '#000000',
            '--text-secondary': '#1A1A1A',
            '--text-muted':     '#333333',
        }
    }
};

// ── Apply theme ──────────────────────────────────────────────────────────────
function applyTheme(name) {
    const theme = THEMES[name];
    if (!theme) return;

    const root = document.documentElement;
    Object.entries(theme.vars).forEach(([prop, val]) => {
        root.style.setProperty(prop, val);
    });

    // Toggle body dark class for extra dark-mode adjustments
    document.body.classList.remove('theme-light','theme-dark','theme-midnight','theme-contrast');
    document.body.classList.add(`theme-${name}`);

    // Update active state in picker
    document.querySelectorAll('.theme-option').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.theme === name);
    });

    // Persist choice
    localStorage.setItem('ctst_theme', name);
}

// ── Build the picker UI ──────────────────────────────────────────────────────
function buildThemePicker() {
    const picker = document.createElement('div');
    picker.id = 'theme-picker';
    picker.innerHTML = `
        <button class="theme-toggle-btn" id="themeToggleBtn" title="Change theme">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                <circle cx="12" cy="12" r="5"/>
                <line x1="12" y1="1" x2="12" y2="3"/>
                <line x1="12" y1="21" x2="12" y2="23"/>
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                <line x1="1" y1="12" x2="3" y2="12"/>
                <line x1="21" y1="12" x2="23" y2="12"/>
                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
            </svg>
        </button>
        <div class="theme-dropdown" id="themeDropdown">
            <div class="theme-dropdown-title">Choose theme</div>
            ${Object.entries(THEMES).map(([key, t]) => `
                <button class="theme-option" data-theme="${key}">
                    <span class="theme-option-icon">${t.icon}</span>
                    <span class="theme-option-label">${t.label}</span>
                    <span class="theme-option-check">✓</span>
                </button>
            `).join('')}
        </div>
    `;

    // Inject into topnav-right if it exists, otherwise body
    const navRight = document.querySelector('.topnav-right');
    if (navRight) {
        const bell = navRight.querySelector('.topnav-bell');
        navRight.insertBefore(picker, bell);
    } else {
        picker.style.position = 'fixed';
        picker.style.top = '12px';
        picker.style.right = '12px';
        picker.style.zIndex = '999';
        document.body.appendChild(picker);
    }

    // Toggle dropdown
    document.getElementById('themeToggleBtn').addEventListener('click', (e) => {
        e.stopPropagation();
        document.getElementById('themeDropdown').classList.toggle('open');
    });

    // Close on outside click
    document.addEventListener('click', () => {
        document.getElementById('themeDropdown')?.classList.remove('open');
    });

    // Option click
    document.querySelectorAll('.theme-option').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            applyTheme(btn.dataset.theme);
            document.getElementById('themeDropdown').classList.remove('open');
        });
    });
}

// ── CSS for the picker ───────────────────────────────────────────────────────
function injectThemeStyles() {
    const style = document.createElement('style');
    style.textContent = `
        #theme-picker {
            position: relative;
            display: flex;
            align-items: center;
        }

        .theme-toggle-btn {
            width: 34px; height: 34px;
            border-radius: 8px;
            border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,0.12);
            color: rgba(255,255,255,0.8);
            transition: background .15s, color .15s;
        }
        .theme-toggle-btn:hover {
            background: rgba(255,255,255,0.2);
            color: #fff;
        }

        .theme-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            background: var(--card, #fff);
            border: 1px solid var(--border, #dde3ec);
            border-radius: 14px;
            padding: 8px;
            min-width: 180px;
            box-shadow: 0 16px 48px rgba(0,0,0,0.18), 0 4px 16px rgba(0,0,0,0.1);
            z-index: 500;
            animation: dropIn .2s ease;
        }
        .theme-dropdown.open { display: block; }

        @keyframes dropIn {
            from { opacity: 0; transform: translateY(-8px) scale(.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .theme-dropdown-title {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--text-muted, #94a3b8);
            padding: 4px 10px 8px;
            border-bottom: 1px solid var(--border, #dde3ec);
            margin-bottom: 6px;
        }

        .theme-option {
            width: 100%;
            display: flex; align-items: center; gap: 10px;
            padding: 9px 10px;
            border-radius: 8px;
            border: none; cursor: pointer;
            background: transparent;
            color: var(--text-primary, #0d1b2e);
            font-size: 13px; font-weight: 500;
            font-family: inherit;
            transition: background .12s;
            text-align: left;
        }
        .theme-option:hover {
            background: var(--bg, #f0f4f8);
        }
        .theme-option.active {
            background: var(--navy-pale, #eef4fb);
            color: var(--navy-mid, #1a4a7a);
            font-weight: 600;
        }

        .theme-option-icon { font-size: 15px; flex-shrink: 0; }
        .theme-option-label { flex: 1; }
        .theme-option-check {
            font-size: 12px;
            color: var(--navy-mid, #1a4a7a);
            opacity: 0;
            transition: opacity .12s;
        }
        .theme-option.active .theme-option-check { opacity: 1; }

        /* ── Dark mode extra overrides ── */
        .theme-dark .login-body,
        .theme-midnight .login-body {
            background: var(--bg) !important;
        }
        .theme-dark .topnav,
        .theme-midnight .topnav {
            background: #0D1B2E !important;
            border-bottom-color: var(--border) !important;
        }
        .theme-dark .sidebar,
        .theme-midnight .sidebar {
            background: var(--white) !important;
            border-right-color: var(--border) !important;
        }
        .theme-dark .metric-card,
        .theme-midnight .metric-card,
        .theme-dark .table-card,
        .theme-midnight .table-card,
        .theme-dark .chart-card,
        .theme-midnight .chart-card,
        .theme-dark .detail-card,
        .theme-midnight .detail-card,
        .theme-dark .kpi-card,
        .theme-midnight .kpi-card {
            background: var(--white) !important;
            border-color: var(--border) !important;
        }
        .theme-dark thead th,
        .theme-midnight thead th {
            background: var(--bg-2) !important;
        }
        .theme-dark tbody tr:hover,
        .theme-midnight tbody tr:hover {
            background: var(--bg-2) !important;
        }
        .theme-dark .form-input,
        .theme-midnight .form-input {
            background: var(--bg-2) !important;
            border-color: var(--border) !important;
            color: var(--text-primary) !important;
        }
        .theme-dark .login-card,
        .theme-midnight .login-card {
            background: var(--white) !important;
            border-color: var(--border) !important;
        }
        .theme-dark .select-input,
        .theme-midnight .select-input {
            background: var(--bg-2) !important;
            color: var(--text-primary) !important;
            border-color: var(--border) !important;
        }
        .theme-dark .comment-bubble,
        .theme-midnight .comment-bubble {
            background: var(--bg-2) !important;
            border-color: var(--border) !important;
        }
        .theme-dark .description-block,
        .theme-midnight .description-block {
            background: var(--bg-2) !important;
            border-color: var(--border) !important;
        }

        /* High contrast */
        .theme-contrast .btn-primary,
        .theme-contrast .btn-sm.primary,
        .theme-contrast .btn-export {
            background: #000 !important;
            color: #fff !important;
        }
        .theme-contrast .badge-open { background: #CCE0FF; color: #000; }
        .theme-contrast .badge-progress { background: #FFE0A0; color: #000; }
        .theme-contrast .badge-resolved { background: #C0F0D0; color: #000; }
        .theme-contrast .badge-critical { background: #FFD0D0; color: #000; }
    `;
    document.head.appendChild(style);
}

// ── Init ─────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    injectThemeStyles();
    buildThemePicker();

    // Restore saved theme — default to light
    const saved = localStorage.getItem('ctst_theme') || 'light';
    applyTheme(saved);
});
