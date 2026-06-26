/* Inventory System — Core JS */

/* ── Sidebar mobile toggle ── */
document.addEventListener('DOMContentLoaded', () => {
  const sidebar   = document.querySelector('.sidebar');
  const toggleBtn = document.getElementById('sidebar-toggle');
  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', () => sidebar.classList.toggle('open'));
    document.addEventListener('click', e => {
      if (sidebar.classList.contains('open') &&
          !sidebar.contains(e.target) &&
          !toggleBtn.contains(e.target)) {
        sidebar.classList.remove('open');
      }
    });
  }

  /* ── Auto-dismiss flash alerts ── */
  document.querySelectorAll('.alert[data-auto-dismiss]').forEach(el => {
    setTimeout(() => el.remove(), 4000);
  });

  /* ── Confirm delete ── */
  document.querySelectorAll('[data-confirm]').forEach(btn => {
    btn.addEventListener('click', e => {
      if (!confirm(btn.dataset.confirm || 'Are you sure?')) e.preventDefault();
    });
  });

  /* ── Generic modal ── */
  document.querySelectorAll('[data-modal-open]').forEach(btn => {
    btn.addEventListener('click', () => {
      const modal = document.getElementById(btn.dataset.modalOpen);
      modal && modal.classList.add('open');
    });
  });

  document.querySelectorAll('[data-modal-close], .modal-overlay').forEach(el => {
    el.addEventListener('click', e => {
      if (e.target === el) {
        const overlay = el.closest('.modal-overlay') || el;
        overlay.classList.remove('open');
      }
    });
  });

  document.querySelectorAll('.modal').forEach(m => {
    m.addEventListener('click', e => e.stopPropagation());
  });

  /* ── Search with debounce ── */
  const searchInput = document.getElementById('table-search');
  if (searchInput) {
    let timer;
    searchInput.addEventListener('input', () => {
      clearTimeout(timer);
      timer = setTimeout(() => searchInput.closest('form').submit(), 400);
    });
  }
});

/* ── Confirm delete helper for inline use ── */
function confirmDelete(msg) {
  return confirm(msg || 'Delete this record? This cannot be undone.');
}

/* ── Format number with commas ── */
function fmtNumber(n) {
  return Number(n).toLocaleString();
}
