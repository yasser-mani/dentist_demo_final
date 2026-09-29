const App = (() => {
  const api = {
    async fetch(url, options = {}) {
      const config = { ...options, headers: { ...(options.headers || {}) } };
      if (config.body instanceof FormData) {
        // Let the browser set multipart boundaries and Content-Type.
      } else if (config.body !== undefined && config.body !== null && !config.headers['Content-Type']) {
        config.headers['Content-Type'] = 'application/json';
      }
      config.headers['Accept'] = 'application/json';

      const response = await fetch(url, config);
      const text = await response.text();
      let data = {};
      try { data = text ? JSON.parse(text) : {}; } catch { data = { success: false, error: text || 'Réponse serveur invalide.' }; }
      if (!response.ok || data.success === false) {
        throw new Error(data.error || `Erreur HTTP ${response.status}`);
      }
      return data;
    },

    toast(message, type = 'info') {
      const container = document.getElementById('toastContainer');
      if (!container) return;
      const toast = document.createElement('div');
      toast.className = `toast toast-${type}`;
      const symbol = type === 'success' ? '✓' : type === 'error' ? '!' : 'i';
      toast.innerHTML = `<div class="toast-icon">${symbol}</div><div class="toast-message">${api.escapeHtml(message)}</div>`;
      container.appendChild(toast);
      setTimeout(() => {
        toast.style.opacity = '0'; toast.style.transform = 'translateY(8px)';
        setTimeout(() => toast.remove(), 220);
      }, 3500);
    },

    openModal(title, bodyHTML, footerHTML = '') {
      const container = document.getElementById('modalContainer');
      if (!container) return;
      container.innerHTML = `<div class="modal-backdrop" data-close-modal></div><div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle"><div class="modal-header"><h3 class="modal-title" id="modalTitle">${api.escapeHtml(title)}</h3><button class="modal-close" type="button" aria-label="Fermer" data-close-modal>×</button></div><div class="modal-body">${bodyHTML}</div>${footerHTML ? `<div class="modal-footer">${footerHTML}</div>` : ''}</div>`;
      container.classList.add('active');
      container.querySelectorAll('[data-close-modal]').forEach(el => el.addEventListener('click', api.closeModal));
      const focusable = container.querySelector('input,textarea,select,button');
      if (focusable) setTimeout(() => focusable.focus(), 0);
    },

    closeModal() {
      const container = document.getElementById('modalContainer');
      if (!container) return;
      container.classList.remove('active');
      container.innerHTML = '';
    },

    escapeHtml(value) {
      const div = document.createElement('div'); div.textContent = value ?? ''; return div.innerHTML;
    },

    escapeAttr(value) { return api.escapeHtml(value).replace(/`/g, '&#96;'); },

    formatDate(value, includeTime = false) {
      if (!value) return '';
      const d = new Date(String(value).replace(' ', 'T'));
      if (Number.isNaN(d.getTime())) return '';
      return new Intl.DateTimeFormat('fr-FR', includeTime ? { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit' } : { day:'2-digit', month:'2-digit', year:'numeric' }).format(d);
    },

    localDateTimeValue(date = new Date()) {
      const pad = n => String(n).padStart(2, '0');
      return `${date.getFullYear()}-${pad(date.getMonth()+1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
    },

    renderBadge(label, color = 'gray') {
      const map = { blue:'blue', amber:'amber', green:'green', red:'red', gray:'gray' };
      return `<span class="badge badge-${map[color] || 'gray'}">${api.escapeHtml(label)}</span>`;
    },

    debounce(fn, wait = 250) {
      let timer; return (...args) => { clearTimeout(timer); timer = setTimeout(() => fn(...args), wait); };
    },

    formatFileSize(bytes) {
      const n = Number(bytes) || 0;
      if (n < 1024) return `${n} o`;
      if (n < 1024 * 1024) return `${(n / 1024).toFixed(1).replace('.', ',')} Ko`;
      return `${(n / 1024 / 1024).toFixed(1).replace('.', ',')} Mo`;
    },
  };
  return api;
})();
window.App = App;

(() => {
  const toggle = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');
  const backdrop = document.getElementById('sidebarBackdrop');
  if (!toggle || !sidebar || !backdrop) return;
  const close = () => { sidebar.classList.remove('open'); backdrop.classList.remove('active'); toggle.setAttribute('aria-expanded', 'false'); };
  toggle.addEventListener('click', () => {
    const open = !sidebar.classList.contains('open');
    sidebar.classList.toggle('open', open); backdrop.classList.toggle('active', open); toggle.setAttribute('aria-expanded', String(open));
  });
  backdrop.addEventListener('click', close);
  sidebar.querySelectorAll('.nav-link').forEach(link => link.addEventListener('click', close));
  window.addEventListener('keydown', event => { if (event.key === 'Escape') { close(); App.closeModal(); } });
})();
