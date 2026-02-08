# Accessibility integration guide

1. Add a skip link and landmarks to each dashboard page:
   ```html
   <a class="skip-link" href="#main">Skip to main content</a>
   <header role="banner">...</header>
   <nav aria-label="Primary navigation">...</nav>
   <main id="main">...</main>
   <aside aria-label="Filters">...</aside>
   <footer role="contentinfo">...</footer>
   <div id="live-region" class="sr-only" aria-live="polite"></div>
   ```

2. Include the CSS and JS:
   ```html
   <link rel="stylesheet" href="/styles/accessibility-dashboard.css">
   <script src="/scripts/accessibility-dashboard.js" defer></script>
   ```

3. Ensure all interactive controls have:
   - Visible text labels (`<label>` or `aria-label`)
   - Minimum hit area 44×44 (already handled in CSS)
   - Keyboard access (`tabindex="0"` where needed, avoid `tabindex>0`)
   - Clear focus states (`:focus-visible` provided)

4. Charts:
   - Provide a textual summary under each chart:
     ```html
     <p class="chart-description">Sales increased 12% in Q4, with highest growth in Region A.</p>
     ```
   - Provide a toggle to a table view:
     ```html
     <div class="chart-toggle"><button data-target="sales-table">Show table view</button></div>
     <table id="sales-table" class="table" hidden>...</table>
     ```

5. High contrast toggle (optional):
   ```js
   // Example: allow users to toggle contrast
   document.querySelector('#contrastToggle')?.addEventListener('click', () => {
     document.documentElement.toggleAttribute('data-contrast');
   });
   ```

6. Reduced motion:
   - Avoid large animated transitions; CSS already respects `prefers-reduced-motion`.

7. Common issues to fix broken dashboards:
   - Check console/network for API failures, 404s, CORS, JSON shape changes.
   - Ensure chart libs initialize after data is ready; guard against `null/undefined`.
   - Validate dependencies/versions; mismatched major versions often break charts.
   - Keyboard test: Tab through the page, ensure logical order and visible focus.
   - Color contrast: Use the provided variables; avoid low-contrast text.
