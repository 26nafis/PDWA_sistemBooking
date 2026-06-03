const S = {
  step: 1,
  svcId: null, svcName: null, svcPrice: null,
  date: null, t1: null, t2: null,
  code: null, totalPrice: null
};

/* ── SERVICE IMAGE MAPPING ── */
const SVC_IMGS = {
  'Barbershop':        'uploads/Assets/BarberShop.jpg',
  'Lapangan Futsal A': 'uploads/Assets/futsalA.jpg',
  'Lapangan Futsal B': 'uploads/Assets/FutsalB.jpg',
  'Studio Musik':      'uploads/Assets/StudioMusik.jpeg',
};
const SVC_FALLBACK = ['✂️','⚽','🏟️','🎸','🎭','📚'];

/* ── INIT ── */
document.addEventListener('DOMContentLoaded', () => {
  loadSvc();
  const t = new Date().toISOString().split('T')[0];
  document.getElementById('slot-date').value = t;
  document.getElementById('slot-date').min = t;
});

/* ── NAVIGATION ── */
function showSec(s) {
  document.getElementById('sec-booking').classList.toggle('hidden', s !== 'booking');
  document.getElementById('sec-status').classList.toggle('hidden', s !== 'status');
  document.getElementById('nav-booking').className = 'nav-btn ' + (s === 'booking' ? 'nav-active' : 'nav-inactive');
  document.getElementById('nav-status').className  = 'nav-btn ' + (s === 'status'  ? 'nav-active' : 'nav-inactive');
}

function go(n) {
  document.getElementById('s' + S.step).classList.add('hidden');
  S.step = n;
  const el = document.getElementById('s' + n);
  el.classList.remove('hidden');
  el.classList.add('fade-in');
  updSteps();
  if (n === 2) loadSlots();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}