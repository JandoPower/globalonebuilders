/* ============================================================
   Global One Builders LLC — Site Script
   ============================================================ */

// ---------- Header scroll state ----------
const header = document.getElementById('site-header');
window.addEventListener('scroll', () => {
  header.classList.toggle('scrolled', window.scrollY > 60);
});

// ---------- Mobile nav ----------
function toggleNav() {
  document.getElementById('main-nav').classList.toggle('open');
}
window.toggleNav = toggleNav;

document.querySelectorAll('#main-nav a').forEach(a => {
  a.addEventListener('click', () => document.getElementById('main-nav').classList.remove('open'));
});

// ---------- Scroll reveal animations ----------
const observer = new IntersectionObserver((entries) => {
  entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
}, { threshold: 0.12 });

document.querySelectorAll('.fade-up, .stagger').forEach(el => observer.observe(el));

// ---------- Bilingual (ES/EN) toggle ----------
let currentLang = 'es';

const placeholders = {
  'f-fname':   { en: 'John',                                                                es: 'Juan' },
  'f-lname':   { en: 'Smith',                                                               es: 'García' },
  'f-email':   { en: 'john@email.com',                                                      es: 'juan@correo.com' },
  'f-details': { en: 'Describe your project, location, and any specific requirements...',   es: 'Describa su proyecto, ubicación y cualquier requisito específico...' },
};

function setLang(lang) {
  currentLang = lang;
  document.getElementById('btn-en').classList.toggle('active', lang === 'en');
  document.getElementById('btn-es').classList.toggle('active', lang === 'es');
  document.documentElement.lang = lang;

  document.querySelectorAll('[data-en][data-es]').forEach(el => {
    el.innerHTML = el.getAttribute('data-' + lang);
  });
  Object.entries(placeholders).forEach(([id, texts]) => {
    const el = document.getElementById(id);
    if (el) el.placeholder = texts[lang];
  });

  document.title = lang === 'es'
    ? 'Global One Builders LLC | Servicios de Construcción Premium'
    : 'Global One Builders LLC | Premium Construction Services';
}
window.setLang = setLang;

document.getElementById('btn-es').classList.add('active');

// ---------- Contact form → Web3Forms ----------
const WEB3FORMS_KEY = '9b6334a7-142a-468b-9ee2-ef2b8d31bcaf';

document.getElementById('submit-btn').addEventListener('click', async function () {
  const btn = this;
  const statusEl = document.getElementById('form-status');
  const fname   = document.getElementById('f-fname').value.trim();
  const lname   = document.getElementById('f-lname').value.trim();
  const email   = document.getElementById('f-email').value.trim();
  const phone   = document.getElementById('f-phone').value.trim();
  const service = document.getElementById('f-service').value;
  const details = document.getElementById('f-details').value.trim();

  if (!fname || !lname || !email || !details) {
    statusEl.className = 'form-status error';
    statusEl.textContent = currentLang === 'es'
      ? '⚠ Por favor complete los campos obligatorios (nombre, apellido, correo y detalles).'
      : '⚠ Please fill in the required fields (name, email, and project details).';
    return;
  }

  btn.disabled = true;
  btn.textContent = currentLang === 'es' ? 'Enviando...' : 'Sending...';
  statusEl.className = 'form-status';
  statusEl.textContent = '';

  try {
    const res = await fetch('https://api.web3forms.com/submit', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({
        access_key: WEB3FORMS_KEY,
        subject: 'New Estimate Request from ' + fname + ' ' + lname,
        from_name: 'Global One Builders Website',
        name: fname + ' ' + lname,
        email: email,
        phone: phone || '(not provided)',
        service: service || '(not selected)',
        message: details,
        botcheck: ''
      })
    });
    const data = await res.json();
    if (data.success) {
      statusEl.className = 'form-status success';
      statusEl.textContent = currentLang === 'es'
        ? '✓ ¡Solicitud enviada! Le contactaremos pronto.'
        : "✓ Request sent! We'll be in touch soon.";
      ['f-fname','f-lname','f-email','f-phone','f-details'].forEach(id => {
        document.getElementById(id).value = '';
      });
      document.getElementById('f-service').selectedIndex = 0;
    } else {
      throw new Error(data.message || 'Submission failed');
    }
  } catch (err) {
    console.error(err);
    statusEl.className = 'form-status error';
    statusEl.textContent = currentLang === 'es'
      ? '⚠ Error al enviar. Por favor llámenos al +1 574-386-8817.'
      : '⚠ Send failed. Please call us at +1 574-386-8817.';
  } finally {
    btn.disabled = false;
    btn.textContent = currentLang === 'es' ? 'Enviar Solicitud →' : 'Submit Request →';
  }
});
