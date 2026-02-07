/* Lightweight JS helpers for accessibility:
   - Skip link focus management
   - Live region announcements
   - Keyboard navigation for menus/tables
   - Modal focus trap
*/

(function () {
  const live = document.getElementById('live-region');
  function announce(msg) {
    if (!live) return;
    live.textContent = '';
    setTimeout(() => { live.textContent = msg; }, 50);
  }

  // Skip link: ensure focus lands on main content
  const skip = document.querySelector('.skip-link');
  const main = document.getElementById('main');
  if (skip && main) {
    skip.addEventListener('click', () => {
      main.setAttribute('tabindex', '-1');
      main.focus();
      announce('Skipped to main content');
    });
  }

  // Keyboard navigation for menus (arrow keys)
  document.querySelectorAll('[role="menubar"]').forEach(bar => {
    const items = bar.querySelectorAll('[role="menuitem"]');
    bar.addEventListener('keydown', (e) => {
      const idx = Array.from(items).indexOf(document.activeElement);
      if (e.key === 'ArrowRight') {
        e.preventDefault();
        (items[idx + 1] || items[0]).focus();
      } else if (e.key === 'ArrowLeft') {
        e.preventDefault();
        (items[idx - 1] || items[items.length - 1]).focus();
      } else if (e.key === 'Home') {
        e.preventDefault();
        items[0].focus();
      } else if (e.key === 'End') {
        e.preventDefault();
        items[items.length - 1].focus();
      }
    });
  });

  // Focus trap for modals/dialogs
  function trapFocus(container) {
    const focusables = container.querySelectorAll('a, button, input, select, textarea, [tabindex]:not([tabindex="-1"])');
    const first = focusables[0];
    const last = focusables[focusables.length - 1];
    container.addEventListener('keydown', (e) => {
      if (e.key !== 'Tab') return;
      if (e.shiftKey && document.activeElement === first) {
        e.preventDefault(); last.focus();
      } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault(); first.focus();
      }
    });
  }
  document.querySelectorAll('[role="dialog"]').forEach(trapFocus);

  // Table keyboard navigation (cells focusable)
  document.querySelectorAll('table').forEach(table => {
    table.querySelectorAll('td, th').forEach(cell => cell.setAttribute('tabindex', '0'));
    table.addEventListener('keydown', (e) => {
      const cells = Array.from(table.querySelectorAll('td, th'));
      const idx = cells.indexOf(document.activeElement);
      if (idx < 0) return;
      const cols = table.rows[0]?.cells.length || 1;
      if (e.key === 'ArrowRight') cells[idx + 1]?.focus();
      if (e.key === 'ArrowLeft') cells[idx - 1]?.focus();
      if (e.key === 'ArrowDown') cells[idx + cols]?.focus();
      if (e.key === 'ArrowUp') cells[idx - cols]?.focus();
    });
  });

  // Toggle to show tabular data alternative for charts
  document.querySelectorAll('.chart-toggle button').forEach(btn => {
    btn.addEventListener('click', () => {
      const target = document.getElementById(btn.getAttribute('data-target'));
      if (!target) return;
      const hidden = target.getAttribute('hidden') !== null;
      if (hidden) {
        target.removeAttribute('hidden');
        announce('Data table shown');
        btn.textContent = 'Hide table view';
      } else {
        target.setAttribute('hidden', '');
        announce('Data table hidden');
        btn.textContent = 'Show table view';
      }
    });
  });
})();
