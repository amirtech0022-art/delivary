const menuToggle = document.querySelector('.menu-toggle');
const navLinks = document.getElementById('navLinks');

menuToggle?.addEventListener('click', () => {
  const isOpen = navLinks.classList.toggle('open');
  menuToggle.setAttribute('aria-expanded', String(isOpen));
});

document.querySelectorAll('.nav-links a').forEach((link) => {
  link.addEventListener('click', () => {
    navLinks.classList.remove('open');
    menuToggle?.setAttribute('aria-expanded', 'false');
  });
});

const sections = document.querySelectorAll('main section[id]');
const navItems = document.querySelectorAll('.nav-links a');

const setActiveLink = () => {
  let currentId = '';
  sections.forEach((section) => {
    const rect = section.getBoundingClientRect();
    if (rect.top <= 140 && rect.bottom > 140) {
      currentId = section.id;
    }
  });
  navItems.forEach((item) => {
    item.classList.toggle('active', item.getAttribute('href') === `#${currentId}`);
  });
};

window.addEventListener('scroll', setActiveLink, { passive: true });
window.addEventListener('load', setActiveLink);

const revealItems = document.querySelectorAll('.reveal');
const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      entry.target.classList.add('visible');
      revealObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.16 });

revealItems.forEach((item) => revealObserver.observe(item));

const filterButtons = document.querySelectorAll('.filter-button');
const portfolioCards = document.querySelectorAll('.portfolio-card');

filterButtons.forEach((button) => {
  button.addEventListener('click', () => {
    filterButtons.forEach((btn) => btn.classList.remove('active'));
    button.classList.add('active');
    const filter = button.dataset.filter;
    portfolioCards.forEach((card) => {
      const matches = filter === 'all' || card.dataset.category === filter;
      card.style.display = matches ? 'flex' : 'none';
    });
  });
});

const lightbox = document.getElementById('lightbox');
const lightboxImage = document.getElementById('lightboxImage');
const lightboxClose = document.getElementById('lightboxClose');

document.querySelectorAll('.portfolio-card img').forEach((img) => {
  img.addEventListener('click', () => {
    lightboxImage.src = img.src;
    lightbox.classList.add('open');
    document.body.style.overflow = 'hidden';
  });
});

const closeLightbox = () => {
  lightbox.classList.remove('open');
  document.body.style.overflow = '';
};

lightboxClose?.addEventListener('click', closeLightbox);
lightbox?.addEventListener('click', (event) => {
  if (event.target === lightbox) closeLightbox();
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape') closeLightbox();
});

const counters = document.querySelectorAll('.number');
const counterObserver = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      const element = entry.target;
      const target = Number(element.dataset.target || '0');
      const duration = 1300;
      const startTime = performance.now();
      const step = (now) => {
        const progress = Math.min((now - startTime) / duration, 1);
        const value = Math.floor(progress * target);
        element.textContent = `${value}${target >= 100 ? '+' : ''}`;
        if (progress < 1) requestAnimationFrame(step);
      };
      requestAnimationFrame(step);
      counterObserver.unobserve(element);
    }
  });
}, { threshold: 0.6 });

counters.forEach((counter) => counterObserver.observe(counter));

// Theme (dark / light) toggle
let themeToggle = document.getElementById('themeToggle');
let themeIcon = document.getElementById('themeIcon');

const applyTheme = (theme) => {
  if (theme === 'dark') {
    document.documentElement.setAttribute('data-theme', 'dark');
    if (themeIcon) themeIcon.textContent = '🌙';
    if (themeToggle) themeToggle.setAttribute('aria-pressed', 'true');
  } else {
    document.documentElement.removeAttribute('data-theme');
    if (themeIcon) themeIcon.textContent = '☀️';
    if (themeToggle) themeToggle.setAttribute('aria-pressed', 'false');
  }
};

const stored = localStorage.getItem('theme');
console.debug('theme: stored preference =', stored);
if (stored) {
  console.debug('theme: applying stored ->', stored);
  applyTheme(stored);
} else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
  console.debug('theme: system prefers dark');
  applyTheme('dark');
} else {
  console.debug('theme: defaulting to light');
  applyTheme('light');
}

// Prefer direct listener if element exists, but also support delegated clicks
const doToggle = () => {
  console.debug('theme: toggle requested');
  const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
  const next = isDark ? 'light' : 'dark';
  console.debug('theme: switching to', next);
  applyTheme(next);
  try { localStorage.setItem('theme', next); } catch (e) { console.warn('theme: failed to persist', e); }
};

if (themeToggle) themeToggle.addEventListener('click', doToggle);

// Delegated handler: works even if the toggle is added later or replaced.
document.addEventListener('click', (ev) => {
  const btn = ev.target.closest && ev.target.closest('#themeToggle');
  if (btn) {
    // refresh references in case button was re-rendered
    themeToggle = document.getElementById('themeToggle');
    themeIcon = document.getElementById('themeIcon');
    doToggle();
  }
});
