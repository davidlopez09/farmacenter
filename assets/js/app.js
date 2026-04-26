// assets/js/app.js
// ── Core utilities shared across the whole system ──────────────────────────

/* ── API Fetch wrapper ───────────────────────────────────────────────── */
const API = {
    async request(url, options = {}) {
        try {
            const res = await fetch(url, {
                headers: { 'Content-Type': 'application/json', ...options.headers },
                ...options,
            });
            const data = await res.json();
            return data
        } catch (e) {
            console.error('API Error:', e);
            return { success: false, message: 'Error de conexión con el servidor.' };
        }
    },
    get: (url) => API.request(url),
    post: (url, body) => API.request(url, { method: 'POST', body: JSON.stringify(body) }),
    put: (url, body) => API.request(url, { method: 'PUT', body: JSON.stringify(body) }),
    delete: (url) => API.request(url, { method: 'DELETE' }),
};

/* ── Toast notifications ─────────────────────────────────────────────── */
const Toast = {
    container: null,
    init() {
        this.container = document.getElementById('toast-container');
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.id = 'toast-container';
            document.body.appendChild(this.container);
        }
    },
    show(message, type = 'info', duration = 3500) {
        if (!this.container) this.init();
        const icons = { success: '✓', error: '✕', info: 'ℹ' };
        const el = document.createElement('div');
        el.className = `toast ${type}`;
        el.innerHTML = `<span>${icons[type] || '●'}</span><span>${message}</span>`;
        this.container.appendChild(el);
        setTimeout(() => { el.style.opacity = '0'; el.style.transition = 'opacity .3s'; setTimeout(() => el.remove(), 300); }, duration);
    },
    success: (msg, d) => Toast.show(msg, 'success', d),
    error: (msg, d) => Toast.show(msg, 'error', d),
    info: (msg, d) => Toast.show(msg, 'info', d),
};

/* ── Modal manager ───────────────────────────────────────────────────── */
const Modal = {
    open(id) { const m = document.getElementById(id); if (m) m.classList.add('open'); },
    close(id) { const m = document.getElementById(id); if (m) m.classList.remove('open'); },
    closeAll() { document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open')); },
};
// Close modal when clicking overlay
document.addEventListener('click', e => {
    if (e.target.classList.contains('modal-overlay')) Modal.closeAll();
});

/* ── Format helpers ──────────────────────────────────────────────────── */
const Fmt = {
    money: (n, sym = '$') => `${sym} ${Number(n || 0).toLocaleString('es-CO', { minimumFractionDigits: 0 })}`,
    date: (s) => s ? new Date(s).toLocaleDateString('es-CO', { day: '2-digit', month: 'short', year: 'numeric' }) : '-',
    datetime: (s) => s ? new Date(s).toLocaleString('es-CO', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-',
};

/* ── Confirm dialog ──────────────────────────────────────────────────── */
function confirmAction(message, onConfirm) {
    if (window.confirm(message)) onConfirm();
}

/* ── Sidebar navigation (SPA-style) ─────────────────────────────────── */
function navigateTo(view) {
    document.querySelectorAll('.nav-item').forEach(n => {
        n.classList.toggle('active', n.dataset.view === view);
    });
    document.querySelectorAll('.view-section').forEach(s => {
        s.style.display = s.id === `view-${view}` ? '' : 'none';
    });
    if (window.viewHandlers && window.viewHandlers[view]) {
        window.viewHandlers[view]();
    }
}

/* ── Pagination renderer ─────────────────────────────────────────────── */
function renderPagination(containerId, current, total, onChange) {
    const el = document.getElementById(containerId);
    if (!el) return;
    el.innerHTML = '';
    for (let i = 1; i <= total; i++) {
        const btn = document.createElement('button');
        btn.className = `page-btn ${i === current ? 'active' : ''}`;
        btn.textContent = i;
        btn.onclick = () => onChange(i);
        el.appendChild(btn);
    }
}

/* ── Init ────────────────────────────────────────────────────────────── */
Toast.init();