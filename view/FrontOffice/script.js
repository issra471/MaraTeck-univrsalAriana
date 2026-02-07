/* ==========================================
   UNIVERSAL MOTION LAYER
   Polished reveals, 3D tilt, parallax, magnetic buttons
   Motion-safe and pointer-aware
   ========================================== */

document.addEventListener('DOMContentLoaded', () => {
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const isCoarsePointer = window.matchMedia('(pointer: coarse)').matches;

  navbarScrollWatcher();
  mobileNavToggle();

  revealOnScroll(reduceMotion);

  if (!reduceMotion) {
    tiltEffect({
      selectors: ['.case-card', '.category-card', '.floating-card', '.visual-card'],
      maxDeg: 10,
      scaleOnHover: 1.02,
      pointerOnly: !isCoarsePointer
    });

    parallaxEffect({
      heroSelector: '.hero-visual',
      floatingSelector: '.floating-card',
      amplitude: 10,
      pointerOnly: !isCoarsePointer
    });

    magneticButtons({
      selectors: ['.btn-primary', '.btn-secondary', '.btn-donate', '.btn-donate-case', '.btn-submit'],
      strength: 0.35,
      pointerOnly: !isCoarsePointer
    });
  }

  textReveals('.hero-title', { stagger: 30, reduceMotion });

  // Dynamic Content Fetching
  fetchAndRenderCases();
  fetchAndRenderStats();
  fetchAndRenderEvents();

  initCategorizedFiltering();
  initEventParticipation();
  initCaseInteractions();
  initCaseSearch();
  initLoginModal();
  initRegisterModal();

  // Check if we are on the detail page or category page
  if (document.body.classList.contains('case-detail-page')) {
    initCaseDetailPage();
  } else if (document.body.classList.contains('category-detail-page')) {
    initCategoryDetailsPage();
  } else {
    initCategoryRedirection();
  }
});

/* Helper for normalization (removing accents) */
function normalizeString(str) {
  if (!str) return '';
  return str.toString().toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
}

/* Navbar scrolled state */
function navbarScrollWatcher() {
  const nav = document.querySelector('.navbar');
  if (!nav) return;
  const toggle = () => {
    const scrolled = window.scrollY > 10;
    nav.classList.toggle('scrolled', scrolled);
  };
  toggle();
  window.addEventListener('scroll', toggle, { passive: true });
}

/* Mobile nav toggle */
function mobileNavToggle() {
  const btn = document.getElementById('hamburger');
  const menu = document.getElementById('navMenu');
  if (!btn || !menu) return;

  btn.addEventListener('click', () => {
    const active = menu.classList.toggle('active');
    btn.setAttribute('aria-expanded', active ? 'true' : 'false');
  });

  // Close on link click (mobile)
  menu.querySelectorAll('a.nav-link').forEach(link => {
    link.addEventListener('click', () => {
      if (menu.classList.contains('active')) {
        menu.classList.remove('active');
        btn.setAttribute('aria-expanded', 'false');
      }
    });
  });
}

/* Reveal on scroll with IntersectionObserver */
function revealOnScroll(reduceMotion) {
  const targets = document.querySelectorAll(
    '.section-title, .section-subtitle, .stat-item, .category-card, .case-card, .event-card, .impact-stat-card, .visual-card, .hero-badge, .hero-subtitle, .hero-cta, .cta-title, .cta-subtitle, .cta-buttons, .cta-quote, .contact-title, .contact-description, .contact-item, .social-links, .contact-form-container'
  );
  if (!targets.length) return;

  targets.forEach(el => {
    el.classList.add('reveal');
    if (reduceMotion) el.classList.add('is-visible');
  });

  if (reduceMotion) return;

  const observer = new IntersectionObserver(
    entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    },
    { rootMargin: '0px 0px -10% 0px', threshold: 0.15 }
  );

  targets.forEach(el => observer.observe(el));
}

/* ==========================================
   MODAL LOGIC (Login & Register)
   ========================================== */

function initLoginModal() {
  const loginBtn = document.querySelector('.btn-login');
  const modal = document.getElementById('loginModal');
  if (!loginBtn || !modal) return;

  loginBtn.addEventListener('click', (e) => {
    e.preventDefault();
    openLoginModal();
  });
}

function openLoginModal() {
  const modal = document.getElementById('loginModal');
  if (modal) {
    modal.classList.add('active');
    document.body.style.overflow = 'hidden'; // Prevent scroll
  }
}

function closeModal() {
  const modal = document.getElementById('loginModal');
  if (modal) {
    modal.classList.remove('active');
    document.body.style.overflow = ''; // Restore scroll
  }
}

function switchLoginToRegister() {
  closeModal();
  setTimeout(openRegisterModal, 300);
}

function switchRegisterToLogin() {
  closeRegisterModal();
  setTimeout(openLoginModal, 300);
}

function initRegisterModal() {
  const modal = document.getElementById('registerModal');
  if (!modal) return;

  // Find close button or overlay clicks if needed
  modal.addEventListener('click', (e) => {
    if (e.target === modal) closeRegisterModal();
  });
}

function openRegisterModal() {
  const modal = document.getElementById('registerModal');
  if (modal) {
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
  }
}

function closeRegisterModal() {
  const modal = document.getElementById('registerModal');
  if (modal) {
    modal.classList.remove('active');
    document.body.style.overflow = '';
  }
}

function switchLoginTab(role) {
  const modal = document.getElementById('loginModal');
  const tabs = modal.querySelectorAll('.tab-btn');
  const roleInput = document.getElementById('loginRole');

  tabs.forEach(tab => {
    if (tab.getAttribute('data-role') === role) {
      tab.classList.add('active');
    } else {
      tab.classList.remove('active');
    }
  });

  if (roleInput) {
    roleInput.value = role;
  }

  // Update modal description based on role (optional polish)
  const desc = modal.querySelector('.modal-header p');
  if (desc) {
    switch (role) {
      case 'donor': desc.textContent = 'Donnez de la visibilité à l\'invisible'; break;
      case 'association': desc.textContent = 'Partagez vos causes et gérez votre impact'; break;
      case 'admin': desc.textContent = 'Espace de supervision et modération'; break;
    }
  }
}

// Ensure init runs on load
document.addEventListener('DOMContentLoaded', () => {
  initLoginModal();
  initLoginForm();
  initRegisterModal();
  initRegisterForm();
});

/* ==========================================
   REGISTER MODAL LOGIC
   ========================================== */

function initRegisterModal() {
  const openRegisterLink = document.getElementById('openRegister');
  const openLoginLink = document.getElementById('openLogin');

  if (openRegisterLink) {
    openRegisterLink.addEventListener('click', (e) => {
      e.preventDefault();
      closeModal(); // Close login modal
      openRegisterModal();
    });
  }

  if (openLoginLink) {
    openLoginLink.addEventListener('click', (e) => {
      e.preventDefault();
      closeRegisterModal(); // Close register modal
      openLoginModal();
    });
  }
}

function openRegisterModal() {
  const modal = document.getElementById('registerModal');
  if (modal) {
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
  }
}

function closeRegisterModal() {
  const modal = document.getElementById('registerModal');
  if (modal) {
    modal.classList.remove('active');
    document.body.style.overflow = '';
  }
}

function switchRegisterTab(role) {
  const modal = document.getElementById('registerModal');
  const tabs = modal.querySelectorAll('.tab-btn');
  const roleInput = document.getElementById('registerRole');
  const associationFields = document.getElementById('associationFields');
  const associationNameInput = document.getElementById('registerAssociationName');
  const descriptionInput = document.getElementById('registerDescription');

  tabs.forEach(tab => {
    if (tab.getAttribute('data-role') === role) {
      tab.classList.add('active');
    } else {
      tab.classList.remove('active');
    }
  });

  if (roleInput) {
    roleInput.value = role;
  }

  // Show/hide association-specific fields
  if (associationFields) {
    if (role === 'association') {
      associationFields.style.display = 'block';
      if (associationNameInput) associationNameInput.required = true;
      if (descriptionInput) descriptionInput.required = true;
    } else {
      associationFields.style.display = 'none';
      if (associationNameInput) associationNameInput.required = false;
      if (descriptionInput) descriptionInput.required = false;
    }
  }

  // Update modal description based on role
  const desc = modal.querySelector('.modal-header p');
  if (desc) {
    switch (role) {
      case 'donor': desc.textContent = 'Rejoignez notre communauté solidaire'; break;
      case 'association': desc.textContent = 'Partagez vos causes et gérez votre impact'; break;
      case 'admin': desc.textContent = 'Créer un compte administrateur'; break;
    }
  }
}

function initRegisterForm() {
  const form = document.getElementById('registerForm');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const submitBtn = form.querySelector('.btn-submit-login');
    const originalText = submitBtn.innerHTML;

    // Password validation
    const password = document.getElementById('registerPassword').value;
    const passwordConfirm = document.getElementById('registerPasswordConfirm').value;

    if (password !== passwordConfirm) {
      alert('Les mots de passe ne correspondent pas');
      return;
    }

    // UI Loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Inscription...</span>';

    const formData = new FormData(form);

    try {
      const response = await fetch('../../controller/AuthController.php?action=register', {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      const result = await response.json();

      if (result.success) {
        // Success notification
        submitBtn.style.background = '#10b981'; // Green
        submitBtn.innerHTML = '<i class="fas fa-check"></i><span>Compte créé!</span>';

        setTimeout(() => {
          // Redirect based on role
          switch (result.role) {
            case 'admin':
            case 'moderator':
              window.location.href = '../dashboard/admin-dashboard.php';
              break;
            case 'association':
            case 'partner':
              window.location.href = '../dashboard/association-dashboard.php';
              break;
            default:
              window.location.href = '../dashboard/donor-dashboard.php';
              break;
          }
        }, 1000);
      } else {
        // Error handling
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        alert(result.message || 'Erreur lors de l\'inscription');
      }
    } catch (error) {
      console.error('Registration error:', error);
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalText;
      alert('Une erreur est survenue. Veuillez réessayer.');
    }
  });
}

function initLoginForm() {
  const form = document.getElementById('loginForm');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const submitBtn = form.querySelector('.btn-submit-login');
    const originalText = submitBtn.innerHTML;

    // UI Loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Connexion...</span>';

    const formData = new FormData(form);

    try {
      const response = await fetch('../../controller/AuthController.php?action=login', {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      const result = await response.json();

      if (result.success) {
        // Success notification (could be a toast or subtle animation)
        submitBtn.style.background = '#10b981'; // Green
        submitBtn.innerHTML = '<i class="fas fa-check"></i><span>Succès!</span>';

        setTimeout(() => {
          // Redirect based on role returned from server
          switch (result.role) {
            case 'admin':
            case 'moderator':
              window.location.href = '../dashboard/admin-dashboard.php';
              break;
            case 'association':
            case 'partner':
              window.location.href = '../dashboard/association-dashboard.php';
              break;
            default:
              window.location.href = '../dashboard/donor-dashboard.php';
              break;
          }
        }, 1000);
      } else {
        // Error handling
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        alert(result.message || 'Erreur de connexion');
      }
    } catch (error) {
      console.error('Login error:', error);
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalText;
      alert('Une erreur est survenue. Veuillez réessayer.');
    }
  });
}


/* 3D Tilt via CSS variables (composable transforms) */
function tiltEffect({ selectors, maxDeg = 10, scaleOnHover = 1.02, pointerOnly = true }) {
  const els = document.querySelectorAll(selectors.join(','));
  if (!els.length) return;

  els.forEach(el => {
    if (pointerOnly) {
      el.addEventListener('pointerenter', () => el.style.setProperty('--cardScale', scaleOnHover));
      el.addEventListener('pointerleave', () => {
        el.style.setProperty('--tiltX', '0deg');
        el.style.setProperty('--tiltY', '0deg');
        el.style.setProperty('--cardScale', '1');
      });
      el.addEventListener('pointermove', e => {
        const rect = el.getBoundingClientRect();
        const cx = rect.left + rect.width / 2;
        const cy = rect.top + rect.height / 2;
        const x = e.clientX - cx;
        const y = e.clientY - cy;
        const rx = clamp((-y / (rect.height / 2)) * maxDeg, -maxDeg, maxDeg);
        const ry = clamp((x / (rect.width / 2)) * maxDeg, -maxDeg, maxDeg);
        el.style.setProperty('--tiltX', `${rx}deg`);
        el.style.setProperty('--tiltY', `${ry}deg`);
      });
    } else {
      el.style.setProperty('--cardScale', '1');
    }
  });
}

/* Parallax using pointer position -> CSS variables */
function parallaxEffect({ heroSelector, floatingSelector, amplitude = 10, pointerOnly = true }) {
  const hero = document.querySelector(heroSelector);
  const cards = document.querySelectorAll(floatingSelector);
  if (!hero && !cards.length) return;

  let rafId = null;
  let targetX = 0, targetY = 0;
  let curX = 0, curY = 0;
  const lerp = (a, b, t) => a + (b - a) * t;

  function onMove(e) {
    const nx = (e.clientX / window.innerWidth) - 0.5; // -0.5..0.5
    const ny = (e.clientY / window.innerHeight) - 0.5;
    targetX = clamp(nx * amplitude, -amplitude, amplitude);
    targetY = clamp(ny * amplitude, -amplitude, amplitude);
    if (!rafId) rafId = requestAnimationFrame(loop);
  }

  function loop() {
    curX = lerp(curX, targetX, 0.08);
    curY = lerp(curY, targetY, 0.08);

    if (hero) {
      hero.style.setProperty('--parallaxX', `${curX}px`);
      hero.style.setProperty('--parallaxY', `${curY}px`);
    }
    if (cards.length) {
      cards.forEach((el, i) => {
        const s = (i + 1) * 0.6; // layered speeds
        el.style.setProperty('--parallaxX', `${curX * s}px`);
        el.style.setProperty('--parallaxY', `${curY * s}px`);
      });
    }
    if (Math.abs(curX - targetX) > 0.1 || Math.abs(curY - targetY) > 0.1) {
      rafId = requestAnimationFrame(loop);
    } else {
      rafId = null;
    }
  }

  if (pointerOnly) {
    window.addEventListener('pointermove', onMove);
  } else {
    window.addEventListener('scroll', () => {
      const t = clamp(window.scrollY / 800, 0, 1);
      const sx = amplitude * t;
      const sy = amplitude * t * 0.6;
      if (hero) {
        hero.style.setProperty('--parallaxX', `${sx}px`);
        hero.style.setProperty('--parallaxY', `${sy}px`);
      }
    }, { passive: true });
  }
}

/* Magnetic buttons (CSS variables for translate) */
function magneticButtons({ selectors, strength = 0.35, pointerOnly = true }) {
  const btns = document.querySelectorAll(selectors.join(','));
  if (!btns.length) return;

  btns.forEach(btn => {
    if (!pointerOnly) return;
    btn.addEventListener('pointermove', e => {
      const rect = btn.getBoundingClientRect();
      const x = (e.clientX - (rect.left + rect.width / 2)) * strength;
      const y = (e.clientY - (rect.top + rect.height / 2)) * strength;
      btn.style.setProperty('--magnetX', `${x}px`);
      btn.style.setProperty('--magnetY', `${y}px`);
    });
    btn.addEventListener('pointerleave', () => {
      btn.style.setProperty('--magnetX', '0px');
      btn.style.setProperty('--magnetY', '0px');
    });
  });
}

/* Text reveals (char-by-char, motion-safe) */
function textReveals(selector, { stagger = 40, reduceMotion = false } = {}) {
  const el = document.querySelector(selector);
  if (!el) return;

  const text = el.textContent.trim();
  el.textContent = '';
  const frag = document.createDocumentFragment();

  text.split('').forEach((ch, i) => {
    const span = document.createElement('span');
    span.textContent = ch;
    span.setAttribute('data-char', '');
    if (!reduceMotion) {
      span.style.setProperty('--charDelay', `${i * stagger}ms`);
    }
    frag.appendChild(span);
  });
  el.appendChild(frag);

  requestAnimationFrame(() => el.classList.add('chars-revealed'));
}

/* utils */
function clamp(n, min, max) { return Math.max(min, Math.min(max, n)); }

/* ==========================================
     DYNAMIC CONTENT FETCHING
   ========================================== */

function fetchAndRenderCases() {
  fetch('../../controller/DashboardController.php?action=public_api&type=cases')
    .then(response => response.json())
    .then(apiResponse => {
      if (apiResponse.success) {
        const container = document.querySelector('.cases-grid');
        if (!container) return;

        // Keep only the first card as 'loading/template' if we wanted, but better to clear
        const data = apiResponse.data;
        if (data.length > 0) {
          container.innerHTML = ''; // Clear static mockups

          data.forEach(item => {
            // Calculate percentage
            const goal = parseFloat(item.goal_amount);
            const progress = parseFloat(item.progress_amount);
            const percent = goal > 0 ? Math.min(100, (progress / goal) * 100).toFixed(0) : 0;

            const card = document.createElement('div');
            card.className = 'case-card';
            card.dataset.category = normalizeString(item.category);

            // Handle badges (new, urgent) roughly based on some logic or hardcoded for now
            let badges = '';
            if (item.is_urgent == 1) {
              badges += `<div class="case-badge urgence">Urgent</div>`;
            }

            // Fallback image and path check
            let imgUrl = item.image_url || 'https://images.unsplash.com/photo-1631815588090-d4bfec5b1ccb?w=600';
            // If it's a relative path from dashboard, we might need to adjust it if it's not already correct
            // But our recent fix stores them as ../../public/ which is correct for FrontOffice


            card.innerHTML = `
                            ${badges}
                            <div class="case-image">
                                <img src="${imgUrl}" alt="${item.title}" loading="lazy">
                                <div class="case-overlay">
                                    <button class="btn-view-case">Voir le Cas</button>
                                </div>
                            </div>
                            <div class="case-content">
                                <div class="case-category">
                                    <i class="fas ${item.category.toLowerCase().includes('sant') ? 'fa-heartbeat' :
                item.category.toLowerCase().includes('handi') ? 'fa-wheelchair' :
                  item.category.toLowerCase().includes('educ') ? 'fa-graduation-cap' :
                    item.category.toLowerCase().includes('enfant') ? 'fa-child' : 'fa-tag'}"></i>
                                    <span>${item.category}</span>
                                </div>
                                <h3 class="case-title">${item.title}</h3>
                                <p class="case-description">
                                    ${item.description.substring(0, 100)}...
                                </p>
                                <div class="case-progress">
                                    <div class="progress-info">
                                        <span class="progress-label">Progression</span>
                                        <span class="progress-percentage">${percent}%</span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: ${percent}%"></div>
                                    </div>
                                    <div class="progress-stats">
                                        <span class="raised">${progress.toLocaleString()} DT</span>
                                        <span class="goal">sur ${goal.toLocaleString()} DT</span>
                                    </div>
                                </div>
                                <div class="case-footer">
                                    <div class="case-meta">
                                        <span><i class="far fa-clock"></i> ${new Date(item.created_at).toLocaleDateString()}</span>
                                        <span><i class="far fa-eye"></i> ${item.views} vues</span>
                                    </div>
                                    <button class="btn-donate-case" onclick="window.open('${item.cha9a9a_link}', '_blank')">
                                        <i class="fas fa-hand-holding-heart"></i>
                                        Soutenir
                                    </button>
                                </div>
                            </div>
                        `;
            card._caseData = item;
            container.appendChild(card);

            // Re-initialize tilt if needed, but the Observer might handle it if we add classes
            // But tiltEffect runs on load. We might need to manually add listeners if we want tilt on dynamic cards.
            // For now, skip tilt re-init to avoid complexity.
          });

          if (typeof applyFilterAndSearch === 'function') {
            applyFilterAndSearch(currentFilter, document.getElementById('caseSearch')?.value || '');
          }
        }
      }
    })
    .catch(err => console.error('Error loading cases:', err));
}

function fetchAndRenderStats() {
  fetch('../../controller/DashboardController.php?action=public_api&type=stats')
    .then(response => response.json())
    .then(apiResponse => {
      if (apiResponse.success) {
        const d = apiResponse.data;
        updateStat('stat-beneficiaries', d.beneficiaries);
        updateStat('stat-members', d.members);
        updateStat('stat-years', d.years_impact);
        updateStat('stat-donations', d.donations_count);
        updateStat('stat-resolved', d.cases_resolved);
        updateStat('stat-donors', d.active_donors);
      }
    })
    .catch(err => console.error('Error loading stats:', err));
}

function updateStat(id, value) {
  const el = document.getElementById(id);
  if (el) {
    el.textContent = parseInt(value).toLocaleString();
    el.setAttribute('data-target', value); // Update for any animation logic
  }
}

function fetchAndRenderEvents() {
  fetch('../../controller/DashboardController.php?action=public_api&type=events')
    .then(response => response.json())
    .then(apiResponse => {
      if (apiResponse.success) {
        const container = document.querySelector('.events-grid');
        if (!container) return;

        const data = apiResponse.data;
        if (data.length > 0) {
          container.innerHTML = '';

          data.forEach(item => {
            const dateObj = new Date(item.event_date);
            const day = dateObj.getDate();
            const month = dateObj.toLocaleString('fr-FR', { month: 'short' }).toUpperCase().replace('.', '');

            const card = document.createElement('div');
            card.className = 'event-card';
            card.innerHTML = `
                            <div class="event-date">
                                <div class="date-day">${day}</div>
                                <div class="date-month">${month}</div>
                            </div>
                            <div class="event-content">
                                <h3 class="event-title">${item.title}</h3>
                                <p class="event-description">${item.description.substring(0, 80)}...</p>
                                <div class="event-meta">
                                    <span><i class="fas fa-map-marker-alt"></i> ${item.location || 'En ligne'}</span>
                                    <span><i class="fas fa-clock"></i> ${dateObj.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</span>
                                </div>
                                <button class="btn-event">Participer</button>
                            </div>
                        `;
            container.appendChild(card);
          });
        }
      }
    });
} // End fetchAndRenderEvents

// --- Contact Form Handling ---
document.addEventListener('DOMContentLoaded', function () {
  const contactForm = document.querySelector('.contact-form form');
  if (contactForm) {
    contactForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const formData = new FormData(this);
      // Add required action parameters for the controller
      formData.append('controller', 'dashboard');
      formData.append('action', 'crud');
      formData.append('entity', 'messages');
      formData.append('act', 'create');

      const btn = this.querySelector('button[type="submit"]');
      const originalText = btn.innerHTML;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi...';
      btn.disabled = true;

      fetch('../../controller/DashboardController.php?action=crud&entity=messages&act=create', {
        method: 'POST',
        body: formData
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('Votre message a été envoyé avec succès !');
            contactForm.reset();
          } else {
            alert('Erreur: ' + (data.message || 'Une erreur est survenue'));
          }
        })
        .catch(error => {
          console.log('Submission completed via redirect potentially');
          // Optimistic success 
          alert('Votre message a été envoyé !');
          contactForm.reset();
        })
        .finally(() => {
          btn.innerHTML = originalText;
          btn.disabled = false;
        });
    });
  }
});

/* ==========================================
   CASE MODAL LOGIC (See Details)
   ========================================== */

function openCaseModal(caseData) {
  const modal = document.getElementById('caseModal');
  if (!modal) return;

  // Populate data
  const goal = parseFloat(caseData.goal_amount);
  const raised = parseFloat(caseData.progress_amount);
  const percent = goal > 0 ? Math.min(100, (raised / goal) * 100).toFixed(0) : 0;

  document.getElementById('modalCaseImage').src = caseData.image_url || 'https://source.unsplash.com/random/600x400?social';
  document.getElementById('modalCaseTitle').textContent = caseData.title;
  document.getElementById('modalCaseDescription').textContent = caseData.description;
  document.getElementById('modalCaseCategory').textContent = caseData.category;
  document.getElementById('modalCasePercent').textContent = `${percent}%`;
  document.getElementById('modalCaseRaised').textContent = `${raised.toLocaleString()} TND`;
  document.getElementById('modalCaseGoal').textContent = `${goal.toLocaleString()} TND`;
  document.getElementById('modalCaseProgressFill').style.width = `${percent}%`;
  document.getElementById('modalCaseViews').textContent = caseData.views || 0;
  document.getElementById('modalCaseDate').textContent = new Date(caseData.created_at).toLocaleDateString();

  const badge = document.getElementById('modalCaseBadge');
  if (caseData.is_urgent == 1) {
    badge.textContent = 'Urgent';
    badge.style.display = 'block';
  } else {
    badge.style.display = 'none';
  }

  const donateBtn = document.getElementById('modalDonateBtn');
  donateBtn.onclick = () => window.open(caseData.cha9a9a_link || 'https://www.cha9a9a.tn', '_blank');

  modal.classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closeCaseModal() {
  const modal = document.getElementById('caseModal');
  if (modal) {
    modal.classList.remove('active');
    document.body.style.overflow = '';
  }
}

/* ==========================================
   SEARCH & FILTER LOGIC
   ========================================== */

let currentFilter = 'all';

function initCaseSearch() {
  const input = document.getElementById('caseSearch');
  if (input) {
    input.addEventListener('input', (e) => applyFilterAndSearch(currentFilter, e.target.value));
  }
}

function applyFilterAndSearch(filter = 'all', query = '') {
  const caseCards = document.querySelectorAll('.case-card');
  const btns = document.querySelectorAll('.filter-btn');
  const q = normalizeString(query);
  const normalizedFilter = normalizeString(filter);

  btns.forEach(b => b.classList.toggle('active', normalizeString(b.getAttribute('data-filter')) === normalizedFilter));

  caseCards.forEach(card => {
    const cardCat = normalizeString(card.getAttribute('data-category'));
    const cardTitle = normalizeString(card.querySelector('.case-title').textContent);
    const matchesFilter = normalizedFilter === 'all' || cardCat === normalizedFilter;
    const matchesSearch = q === '' || cardTitle.includes(q);

    if (matchesFilter && matchesSearch) {
      card.style.display = 'block';
      setTimeout(() => (card.style.opacity = '1'), 10);
    } else {
      card.style.opacity = '0';
      setTimeout(() => (card.style.display = 'none'), 300);
    }
  });
}

function initCategorizedFiltering() {
  const filterBtns = document.querySelectorAll('.filter-btn');
  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      currentFilter = btn.getAttribute('data-filter');
      applyFilterAndSearch(currentFilter, document.getElementById('caseSearch')?.value || '');

      // Auto-scroll to grid
      const casesSection = document.getElementById('cases');
      if (casesSection) casesSection.scrollIntoView({ behavior: 'smooth' });
    });
  });

  const catCards = document.querySelectorAll('.category-card, .floating-card');
  catCards.forEach(card => {
    card.addEventListener('click', () => {
      const cat = card.getAttribute('data-category');
      if (cat) {
        currentFilter = cat;
        document.getElementById('cases')?.scrollIntoView({ behavior: 'smooth' });
        applyFilterAndSearch(currentFilter, document.getElementById('caseSearch')?.value || '');
      }
    });
  });
}

function initEventParticipation() {
  document.body.addEventListener('click', (e) => {
    if (e.target.classList.contains('btn-event')) {
      alert('Merci de votre intérêt ! Cette fonctionnalité sera disponible après connexion.');
      openLoginModal();
    }
  });
}

function initCaseInteractions() {
  document.body.addEventListener('click', (e) => {
    const viewBtn = e.target.closest('.btn-view-case');
    const overlay = e.target.closest('.case-overlay');

    if (viewBtn || overlay) {
      const target = viewBtn || overlay.querySelector('.btn-view-case');
      if (target) {
        // If it has an onclick, let it handle it (or trigger it manually if needed)
        // But better to just use window.location here for consistency
        const card = target.closest('.case-card');
        if (card && card._caseData) {
          window.location.href = `case-details.php?id=${card._caseData.id}`;
        } else if (target.hasAttribute('onclick')) {
          // If it's a dynamic card created with innerHTML, we might want to prevent default 
          // and just trigger the location change based on what's in the onclick or dataset
          const match = target.getAttribute('onclick').match(/id='([^']+)'/) || target.getAttribute('onclick').match(/id=(\d+)/);
          if (match) {
            window.location.href = `case-details.php?id=${match[1]}`;
          }
        } else {
          // Static fallback: for demo purposes, redirect to Sonia's rich view
          // We pass the category for better demo mapping
          const cat = card?.dataset.category || 'sante';
          window.location.href = `case-details.php?id=demo_${cat}`;
        }
      }
    }
  });
}

function initCaseDetailPage() {
  const params = new URLSearchParams(window.location.search);
  const caseId = params.get('id');
  if (!caseId) return;

  // Handle demo/static cases with high-impact Sonia data
  if (caseId.startsWith('demo') || caseId.startsWith('static')) {
    const catRaw = caseId.includes('_') ? caseId.split('_')[1] : 'sante';
    const catDisplay = catRaw.charAt(0).toUpperCase() + catRaw.slice(1);

    const demoData = {
      title: catRaw === 'handicap' ? "Soutien à l'autonomie de Mohamed" :
        catRaw === 'education' ? "Fournitures scolaires pour 50 élèves" :
          catRaw === 'enfants' ? "Orphelins de Sidi Bouzid - Besoins essentiels" :
            "Sonia, 8 ans, lutte contre une malformation cardiaque",
      description: "Voici un cas social urgent nécessitant votre générosité. " +
        (catRaw === 'handicap' ? "Mohamed a besoin d'un fauteuil roulant pour retrouver son autonomie." :
          catRaw === 'education' ? "Aidons ces enfants à accéder à l'éducation dans de bonnes conditions." :
            "Cette situation demande une intervention rapide pour changer une vie."),
      category: catDisplay,
      is_urgent: (catRaw === 'sante' || catRaw === 'urgence' || catRaw === 'enfants') ? 1 : 0,
      goal_amount: catRaw === 'education' ? 3000 : 20000,
      progress_amount: catRaw === 'education' ? 2550 : 13600,
      image_url: catRaw === 'handicap' ? "https://images.unsplash.com/photo-1581579438747-1dc8d17bbce4?w=1200" :
        catRaw === 'education' ? "https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=1200" :
          "https://images.unsplash.com/photo-1631815588090-d4bfec5b1ccb?w=1200",
      created_at: new Date().toISOString(),
      cha9a9a_link: "https://www.cha9a9a.tn"
    };
    populateCaseDetails(demoData);
    return;
  }

  fetch(`../../controller/DashboardController.php?action=public_api&type=cases&id=${caseId}`)
    .then(response => response.json())
    .then(apiResponse => {
      if (apiResponse.success && apiResponse.data) {
        const item = apiResponse.data;
        // ... (populate logic remains similar)
        populateCaseDetails(item);
      } else {
        console.warn('Case not found in database, using fallback or template data');
      }
    })
    .catch(err => {
      console.error('Error loading case details:', err);
      // If it's the static example, it's fine
    });
}

function populateCaseDetails(item) {
  const goal = parseFloat(item.goal_amount);
  const raised = parseFloat(item.progress_amount);
  const percent = goal > 0 ? Math.min(100, (raised / goal) * 100).toFixed(0) : 0;

  // Category Configuration
  const catLower = normalizeString(item.category);
  let catIcon = 'fa-tag';
  let catClass = 'badge-category';

  if (catLower.includes('sante') || catLower.includes('medical')) {
    catIcon = 'fa-heartbeat';
    catClass = 'badge-sante';
  } else if (catLower.includes('handicap')) {
    catIcon = 'fa-wheelchair';
    catClass = 'badge-handicap';
  } else if (catLower.includes('education') || catLower.includes('ecole')) {
    catIcon = 'fa-graduation-cap';
    catClass = 'badge-education';
  } else if (catLower.includes('enfant')) {
    catIcon = 'fa-child';
    catClass = 'badge-enfants';
  }

  // Populate fields
  const safeSet = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };

  document.title = `${item.title} | UniVersElle Ariana`;
  safeSet('caseTitle', item.title);

  const descEl = document.getElementById('caseDescription');
  if (descEl) {
    const paragraphs = item.description.split('\n').filter(p => p.trim() !== '');
    descEl.innerHTML = paragraphs.map(p => `<p>${p}</p>`).join('');
  }

  const badgesContainer = document.querySelector('.case-labels');
  if (badgesContainer) {
    let badgesHtml = '';
    if (item.is_urgent == 1) {
      badgesHtml += `<span class="badge badge-urgent"><i class="fas fa-exclamation-circle"></i> Urgent</span>`;
    }
    badgesHtml += `<span class="badge ${catClass}"><i class="fas ${catIcon}"></i> ${item.category}</span>`;
    badgesContainer.innerHTML = badgesHtml;
  }

  const techDetails = document.querySelector('.case-technical-details');
  if (techDetails) {
    techDetails.innerHTML = `
      <div class="detail-item"><span class="label">Objectif Financier</span><span class="val">${goal.toLocaleString()} TND</span></div>
      <div class="detail-item"><span class="label">Utilisation des fonds</span><span class="val">${item.category} Support</span></div>
      <div class="detail-item"><span class="label">Date de publication</span><span class="val">${new Date(item.created_at).toLocaleDateString()}</span></div>
    `;
  }

  const mainImg = document.querySelector('.main-case-img');
  if (mainImg) mainImg.src = item.image_url || 'https://images.unsplash.com/photo-1631815588090-d4bfec5b1ccb?w=1200';

  const percEl = document.querySelector('.percentage');
  if (percEl) percEl.textContent = `${percent}%`;

  const fillEl = document.querySelector('.progress-fill');
  if (fillEl) {
    fillEl.style.width = `${percent}%`;
    // Change progress fill color for non-urgent based on category if desired, but neon-red is high impact
    if (item.is_urgent == 1) {
      fillEl.className = 'progress-fill neon-red';
    } else {
      fillEl.className = 'progress-fill';
      fillEl.style.background = 'var(--primary-gradient)';
    }
  }

  const stats = document.querySelectorAll('.stat-value');
  if (stats.length >= 2) {
    stats[0].textContent = `${raised.toLocaleString()} TND`;
    stats[1].textContent = `${goal.toLocaleString()} TND`;
  }

  if (donateBtn) {
    donateBtn.onclick = () => window.open(item.cha9a9a_link || 'https://www.cha9a9a.tn', '_blank');
  }
}

/**
 * Category Details Page Initialization
 */
function initCategoryDetailsPage() {
  const params = new URLSearchParams(window.location.search);
  const category = params.get('cat');
  if (!category) return;

  // UI Configuration
  const config = {
    'sante': { title: 'Soins médicaux et traitements urgents', icon: 'fa-heartbeat' },
    'handicap': { title: 'Équipements et assistance spécialisée', icon: 'fa-wheelchair' },
    'education': { title: 'Accès à l\'éducation et fournitures', icon: 'fa-graduation-cap' },
    'enfants': { title: 'Protection et bien-être des enfants', icon: 'fa-child' },
    'renovation': { title: 'Logements et infrastructures solidaires', icon: 'fa-home' },
    'urgence': { title: 'Situations critiques immédiates', icon: 'fa-exclamation-triangle' }
  };

  const catInfo = config[category] || config['sante'];
  const titleEl = document.getElementById('categoryTitle');
  const badgeEl = document.getElementById('categoryBadge');

  if (titleEl) titleEl.textContent = catInfo.title;
  if (badgeEl) badgeEl.innerHTML = `<i class="fas ${catInfo.icon}"></i>`;
  document.title = `${catInfo.title} | UniVersElle`;

  fetch(`../../controller/DashboardController.php?action=public_api&type=cases`)
    .then(res => res.json())
    .then(apiResponse => {
      if (apiResponse.success) {
        const allCases = apiResponse.data;
        const catCases = allCases.filter(c => normalizeString(c.category).toLowerCase().includes(category.substring(0, 4).toLowerCase()));

        const activeCount = catCases.filter(c => c.status === 'active').length;
        const subEl = document.getElementById('categorySubtitle');
        if (subEl) subEl.textContent = `${activeCount || 0} cas actifs ont besoin de votre aide`;

        const actText = document.getElementById('activeCountText');
        if (actText) actText.textContent = `${activeCount || 0} personnes attendent une aide ${category === 'sante' ? 'médicale' : 'concrète'}`;

        const urgentCases = catCases.filter(c => c.is_urgent == 1).slice(0, 3);
        renderCategoryCards(urgentCases, 'urgentGrid', true);
        renderCategoryCards(catCases, 'categoryCasesGrid');
        initAdvancedFilterLogic(catCases);
      }
    });
}

function renderCategoryCards(cases, containerId, isUrgent = false) {
  const container = document.getElementById(containerId);
  if (!container) return;
  container.innerHTML = '';
  if (cases.length === 0) {
    container.innerHTML = '<div class="no-results-msg glass-card">Aucun cas trouvé dans cette catégorie pour le moment.</div>';
    return;
  }
  cases.forEach(item => {
    const goal = parseFloat(item.goal_amount);
    const raised = parseFloat(item.progress_amount);
    const percent = goal > 0 ? Math.min(100, (raised / goal) * 100).toFixed(0) : 0;
    const card = document.createElement('div');
    card.className = `case-card ${isUrgent ? 'urgent-featured' : ''}`;
    const isSensitive = item.category.toLowerCase().includes('sant') && item.is_urgent == 1;
    card.innerHTML = `
            <div class="case-image">
                <img src="${item.image_url || 'https://images.unsplash.com/photo-1631815588090-d4bfec5b1ccb?w=600'}" class="${isSensitive ? 'photo-sensitive' : ''}">
                <div class="case-overlay">
                    <button class="btn-view-case" onclick="window.location.href='case-details.php?id=${item.id}'">Voir l'histoire</button>
                </div>
            </div>
            <div class="case-content">
                <span class="badge ${item.is_urgent == 1 ? 'badge-urgent' : 'badge-category'}">${item.is_urgent == 1 ? '🔴 URGENT' : '🟡 En cours'}</span>
                <h3 class="case-title">${item.title}</h3>
                <p class="case-description">${item.description.substring(0, 80)}...</p>
                <div class="case-progress">
                    <div class="progress-info">
                        <span class="progress-percentage">${percent}%</span>
                        <span class="progress-stats">${raised.toLocaleString()} / ${goal.toLocaleString()} TND</span>
                    </div>
                    <div class="progress-bar"><div class="progress-fill ${item.is_urgent == 1 ? 'neon-red' : ''}" style="width: ${percent}%"></div></div>
                </div>
                <div class="case-card-actions" style="display:flex; gap:10px; margin-top:15px;">
                    <button class="btn-view-case" style="flex:1" onclick="window.location.href='case-details.php?id=${item.id}'">Détails</button>
                    <button class="btn-donate-case" style="flex:1" onclick="window.open('${item.cha9a9a_link || 'https://www.cha9a.tn'}', '_blank')">Soutenir</button>
                </div>
            </div>
        `;
    container.appendChild(card);
  });
}

function initAdvancedFilterLogic(data) {
  const searchInput = document.getElementById('categorySearch');
  const sortSelect = document.getElementById('sortFilter');
  const statusPills = document.querySelectorAll('.status-pills .pill');
  let currentStatus = 'all';
  const apply = () => {
    const term = searchInput.value.toLowerCase();
    const sort = sortSelect.value;
    let filtered = data.filter(c => {
      const matchesSearch = c.title.toLowerCase().includes(term) || c.description.toLowerCase().includes(term);
      const matchesStatus = currentStatus === 'all' || (currentStatus === 'urgent' && c.is_urgent == 1) || (currentStatus === 'in_progress' && c.status === 'active' && c.is_urgent == 0) || (currentStatus === 'completed' && c.status === 'completed');
      return matchesSearch && matchesStatus;
    });
    if (sort === 'amount_desc') filtered.sort((a, b) => b.goal_amount - a.goal_amount);
    if (sort === 'urgency') filtered.sort((a, b) => b.is_urgent - a.is_urgent);
    if (sort === 'date') filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    renderCategoryCards(filtered, 'categoryCasesGrid');
  };
  searchInput?.addEventListener('input', apply);
  sortSelect?.addEventListener('change', apply);
  statusPills.forEach(p => p.addEventListener('click', () => { statusPills.forEach(b => b.classList.remove('active')); p.classList.add('active'); currentStatus = p.dataset.status; apply(); }));
}

function initCategoryRedirection() {
  const cards = document.querySelectorAll('.category-card');
  cards.forEach(card => {
    card.style.cursor = 'pointer';
    card.addEventListener('click', () => {
      const cat = card.dataset.category;
      window.location.href = `category-details.php?cat=${cat}`;
    });
  });
}

function initLoadMore() {
  const btn = document.querySelector('.btn-load-more');
  if (btn) {
    btn.addEventListener('click', () => {
      const casesGrid = document.querySelector('.cases-grid');
      if (casesGrid) casesGrid.scrollIntoView({ behavior: 'smooth' });
    });
  }
}

document.addEventListener('DOMContentLoaded', initLoadMore);

/**
 * Global Toast System
 * Replaces browser alert() with high-end glassy notifications
 */
function showToast(message, type = 'info') {
  const container = document.getElementById('toast-container');
  if (!container) return;

  const toast = document.createElement('div');
  toast.className = `toast ${type}`;

  let icon = 'fa-info-circle';
  if (type === 'success') icon = 'fa-check-circle';
  if (type === 'error') icon = 'fa-exclamation-triangle';

  toast.innerHTML = `
    <i class="fas ${icon}"></i>
    <span>${message}</span>
  `;

  container.appendChild(toast);

  // Auto remove after 5s
  setTimeout(() => {
    toast.classList.add('fade-out');
    toast.addEventListener('animationend', () => toast.remove());
  }, 5000);
}

// Attach to window for global access
window.showToast = showToast;
