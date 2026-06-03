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

function updSteps() {
  for (let i = 1; i <= 4; i++) {
    const e = document.getElementById('si-' + i);
    e.className = 'step-item ' + (i < S.step ? 'step-done' : i === S.step ? 'step-active' : 'step-inactive');
  }
  for (let i = 1; i <= 3; i++) {
    const l = document.getElementById('l-' + i + (i + 1));
    S.step > i ? l.classList.add('done') : l.classList.remove('done');
  }
}

/* ── PAYMENT INFO (dinamis berdasarkan metode) ── */
function updatePayInfo() {
  const v    = document.getElementById('pay-method').value;
  const wrap = document.getElementById('pay-info-wrap');
  const amt  = S.totalPrice ? ('<span class="bank-total">' + rp(S.totalPrice) + '</span>') : '';
  const footer = S.totalPrice
    ? `<div style="height:1px;background:var(--border);margin:16px 0"></div>
       <div style="display:flex;justify-content:space-between;align-items:center">
         <span style="font-size:0.7rem;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:0.08em">Total Transfer</span>
         ${amt}
       </div>`
    : '';
  const an = `<div style="display:flex;justify-content:space-between;align-items:center;margin-top:10px">
    <span class="bank-lbl">Atas Nama</span>
    <span class="bank-val">BookEase Official</span>
  </div>`;