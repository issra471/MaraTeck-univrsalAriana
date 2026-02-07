/**
 * Dashboard Shared JavaScript
 * Handles AJAX operations, modals, and interactions
 */

// ==========================================
// UTILITY FUNCTIONS
// ==========================================

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 1rem 1.5rem;
    background: ${type === 'success' ? 'linear-gradient(135deg, #10b981, #059669)' : 'linear-gradient(135deg, #ef4444, #dc2626)'};
    color: white;
    border-radius: 0.5rem;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
    z-index: 9999;
    animation: slideIn 0.3s ease-out;
  `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// ==========================================
// MODAL FUNCTIONS
// ==========================================

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Close modal on overlay click
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-overlay')) {
        window.closeModal ? window.closeModal() : closeModal();
    }
});

// Expose to window for AJAX compatibility
window.openModal = openModal;
window.closeModal = closeModal;

// ==========================================
// AJAX HELPERS
// ==========================================

async function fetchData(url, options = {}) {
    try {
        const response = await fetch(url, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
    } catch (error) {
        console.error('Fetch error:', error);
        showNotification('Une erreur est survenue', 'error');
        throw error;
    }
}

async function postData(url, data) {
    return fetchData(url, {
        method: 'POST',
        body: JSON.stringify(data)
    });
}

// ==========================================
// FORM HANDLING
// ==========================================

function handleFormSubmit(formId, submitUrl, successCallback) {
    const form = document.getElementById(formId);
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        try {
            const result = await postData(submitUrl, data);

            if (result.success) {
                showNotification(result.message || 'Opération réussie', 'success');
                if (successCallback) successCallback(result);
                form.reset();
            } else {
                showNotification(result.message || 'Erreur', 'error');
            }
        } catch (error) {
            showNotification('Erreur de soumission', 'error');
        }
    });
}

// ==========================================
// TABLE ACTIONS
// ==========================================

function confirmDelete(id, entity, callback) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer cet élément ?`)) {
        deleteItem(id, entity, callback);
    }
}

async function deleteItem(id, entity, callback) {
    try {
        const result = await postData(`../../controller/DashboardController.php?action=crud&entity=${entity}&act=delete&id=${id}`, {});

        if (result.success !== false) {
            showNotification('Élément supprimé avec succès', 'success');
            if (callback) callback();
        }
    } catch (error) {
        showNotification('Erreur de suppression', 'error');
    }
}

// ==========================================
// FILE UPLOAD PREVIEW
// ==========================================

function setupFilePreview(inputId, previewId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);

    if (!input || !preview) return;

    input.addEventListener('change', (e) => {
        const files = e.target.files;
        preview.innerHTML = '';

        Array.from(files).forEach(file => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.cssText = 'width: 100px; height: 100px; object-fit: cover; border-radius: 0.5rem; margin: 0.5rem;';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            }
        });
    });
}

// ==========================================
// SEARCH & FILTER
// ==========================================

function setupSearch(searchInputId, tableId) {
    const searchInput = document.getElementById(searchInputId);
    const table = document.getElementById(tableId);

    if (!searchInput || !table) return;

    searchInput.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        const rows = table.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
}

function setupCategoryFilter(filterId, tableId) {
    const filter = document.getElementById(filterId);
    const table = document.getElementById(tableId);

    if (!filter || !table) return;

    filter.addEventListener('change', (e) => {
        const category = e.target.value;
        const rows = table.querySelectorAll('tbody tr');

        rows.forEach(row => {
            if (category === '' || row.dataset.category === category) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
}

function setupCardSearch(inputId, gridId) {
    const input = document.getElementById(inputId);
    const grid = document.getElementById(gridId);
    if (!input || !grid) return;

    input.addEventListener('input', () => {
        const searchTerm = input.value.toLowerCase();
        const cards = grid.querySelectorAll('.case-card');

        cards.forEach(card => {
            const title = card.dataset.title || '';
            card.style.display = title.includes(searchTerm) ? '' : 'none';
        });
    });
}

function filterCardsByCategory(category, gridId) {
    const grid = document.getElementById(gridId);
    if (!grid) return;

    const cards = grid.querySelectorAll('.case-card');
    cards.forEach(card => {
        const cardCategory = card.dataset.category?.toLowerCase();
        if (category === '' || cardCategory === category.toLowerCase()) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });

    // Update active pill UI
    document.querySelectorAll('.pill-filter').forEach(pill => {
        if (pill.textContent.toLowerCase() === (category || 'tous').toLowerCase()) {
            pill.classList.add('active');
        } else {
            pill.classList.remove('active');
        }
    });
}

// ==========================================
// PROGRESS BAR ANIMATION
// ==========================================

function animateProgressBars() {
    const progressBars = document.querySelectorAll('.progress-fill');

    progressBars.forEach(bar => {
        const targetWidth = bar.dataset.progress || '0';
        bar.style.width = '0%';

        setTimeout(() => {
            bar.style.width = targetWidth + '%';
        }, 100);
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    animateProgressBars();
    initSidebarToggle();
    initNavigation();
});

// ==========================================
// CHART HELPERS (for admin dashboard)
// ==========================================

function createLineChart(canvasId, labels, data, label) {
    const ctx = document.getElementById(canvasId);
    if (!ctx || typeof Chart === 'undefined') return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: label,
                data: data,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function createDoughnutChart(canvasId, labels, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx || typeof Chart === 'undefined') return;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: [
                    '#3b82f6', // blue-500
                    '#0ea5e9', // sky-500
                    '#f43f5e', // rose-500
                    '#10b981', // emerald-500
                    '#f59e0b'  // amber-500
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// ==========================================
// MOBILE MENU TOGGLE
// ==========================================

// ==========================================
// SIDEBAR COLLAPSE
// ==========================================

function initSidebarToggle() {
    const toggleBtn = document.getElementById('sidebarToggle');
    const container = document.querySelector('.dashboard-container');

    if (toggleBtn && container) {
        toggleBtn.addEventListener('click', () => {
            container.classList.toggle('sidebar-collapsed');
            // Save state to localStorage
            const isCollapsed = container.classList.contains('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        });

        // Restore state
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            container.classList.add('sidebar-collapsed');
        }
    }
}

// ==========================================
// AJAX NAVIGATION (SPA)
// ==========================================

function initNavigation() {
    const navItems = document.querySelectorAll('.nav-item:not(.logout)');
    const mainContent = document.querySelector('.main-content');

    navItems.forEach(item => {
        item.addEventListener('click', async (e) => {
            if (item.getAttribute('href').startsWith('../../')) return; // Logout or external

            e.preventDefault();
            const url = item.getAttribute('href');
            const view = new URLSearchParams(url.split('?')[1]).get('view');

            if (!view) return;

            // Update UI State
            navItems.forEach(i => i.classList.remove('active'));
            item.classList.add('active');

            // Load Content
            await loadView(view);

            // Update URL without reload
            window.history.pushState({ view }, '', url);
        });
    });

    // Handle back/forward buttons
    window.addEventListener('popstate', (e) => {
        if (e.state && e.state.view) {
            loadView(e.state.view, false);
            // Update active state in sidebar
            navItems.forEach(item => {
                const itemUrl = item.getAttribute('href');
                if (itemUrl.includes(`view=${e.state.view}`)) {
                    item.classList.add('active');
                } else {
                    item.classList.remove('active');
                }
            });
        }
    });
}

async function loadView(view, updateHistory = true) {
    const mainContent = document.querySelector('.main-content');
    if (!mainContent) return;

    mainContent.classList.add('page-loading');

    try {
        // Fetch view content from controller
        const currentUrl = window.location.pathname;
        const response = await fetch(`${currentUrl}?view=${view}&ajax=1`);
        const html = await response.text();

        // Smooth transition
        mainContent.style.opacity = '0';
        setTimeout(() => {
            mainContent.innerHTML = html;
            mainContent.style.opacity = '1';
            mainContent.classList.remove('page-loading');

            // Re-initialize view-specific scripts
            reinitViewScripts(view);
        }, 300);

    } catch (error) {
        console.error('Navigation error:', error);
        showNotification('Erreur de chargement', 'error');
        mainContent.classList.remove('page-loading');
    }
}

function reinitViewScripts(view) {
    animateProgressBars();

    // Admin View Re-init
    if (document.getElementById('searchCases')) {
        setupSearch('searchCases', 'casesTable');
    }

    // Donor View Re-init
    if (view === 'discover') {
        setupCardSearch('searchInput', 'casesGrid');
        window.filterByCategory = (category) => filterCardsByCategory(category, 'casesGrid');
    }

    // Force re-execution of scripts in the loaded container
    const container = document.querySelector('.main-content');
    if (container) {
        const scripts = container.querySelectorAll('script');
        scripts.forEach(oldScript => {
            if (oldScript.src) return; // Skip external scripts already loaded
            const newScript = document.createElement('script');
            Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
            newScript.appendChild(document.createTextNode(oldScript.innerHTML));
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });
    }

    // Ensure CRUD modal functions are exposed if they exist in the current scope
    // This handles the case where they are defined in the newScript above
    setTimeout(() => {
        if (typeof openCreateModal !== 'undefined') window.openCreateModal = openCreateModal;
        if (typeof editItem !== 'undefined') window.editItem = editItem;
        if (typeof renderForm !== 'undefined') window.renderForm = renderForm;
        if (typeof closeModal !== 'undefined') window.closeModal = closeModal;
    }, 50);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
  @keyframes slideIn {
    from {
      transform: translateX(100%);
      opacity: 0;
    }
    to {
      transform: translateX(0);
      opacity: 1;
    }
  }
  
  @keyframes slideOut {
    from {
      transform: translateX(0);
      opacity: 1;
    }
    to {
      transform: translateX(100%);
      opacity: 0;
    }
  }
`;
document.head.appendChild(style);
