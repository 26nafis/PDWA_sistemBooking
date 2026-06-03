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

  const banks = {
    bca:    { label: 'Bank BCA',    no: '1234 5678 90' },
    bri:    { label: 'Bank BRI',    no: '0987 6543 21' },
    mandiri:{ label: 'Bank Mandiri',no: '1122 3344 5566' },
    gopay:  { label: 'GoPay',       no: '0812-3456-7890' },
    ovo:    { label: 'OVO',         no: '0812-3456-7890' },
  };

  if (v === 'qris') {
    wrap.innerHTML = `
      <div class="bank-card" style="text-align:center">
        <div style="font-size:0.7rem;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:16px">Scan QR Code Berikut</div>
        <div style="background:#fff;border-radius:16px;padding:16px;display:inline-block;margin-bottom:14px">
          <svg width="160" height="160" viewBox="0 0 160 160" xmlns="http://www.w3.org/2000/svg">
            <rect width="160" height="160" fill="white"/>
            <rect x="10" y="10" width="44" height="44" rx="4" fill="#000"/>
            <rect x="18" y="18" width="28" height="28" rx="2" fill="#fff"/>
            <rect x="24" y="24" width="16" height="16" rx="1" fill="#000"/>
            <rect x="106" y="10" width="44" height="44" rx="4" fill="#000"/>
            <rect x="114" y="18" width="28" height="28" rx="2" fill="#fff"/>
            <rect x="120" y="24" width="16" height="16" rx="1" fill="#000"/>
            <rect x="10" y="106" width="44" height="44" rx="4" fill="#000"/>
            <rect x="18" y="114" width="28" height="28" rx="2" fill="#fff"/>
            <rect x="24" y="120" width="16" height="16" rx="1" fill="#000"/>
            <rect x="62" y="10" width="6" height="6" fill="#000"/><rect x="72" y="10" width="6" height="6" fill="#000"/><rect x="82" y="10" width="6" height="6" fill="#000"/>
            <rect x="62" y="20" width="6" height="6" fill="#000"/><rect x="82" y="20" width="6" height="6" fill="#000"/>
            <rect x="72" y="30" width="6" height="6" fill="#000"/><rect x="62" y="40" width="6" height="6" fill="#000"/><rect x="82" y="40" width="6" height="6" fill="#000"/>
            <rect x="62" y="50" width="6" height="6" fill="#000"/><rect x="72" y="50" width="6" height="6" fill="#000"/><rect x="82" y="50" width="6" height="6" fill="#000"/>
            <rect x="10" y="62" width="6" height="6" fill="#000"/><rect x="20" y="62" width="6" height="6" fill="#000"/><rect x="40" y="62" width="6" height="6" fill="#000"/>
            <rect x="10" y="72" width="6" height="6" fill="#000"/><rect x="30" y="72" width="6" height="6" fill="#000"/><rect x="40" y="72" width="6" height="6" fill="#000"/>
            <rect x="20" y="82" width="6" height="6" fill="#000"/><rect x="30" y="82" width="6" height="6" fill="#000"/>
            <rect x="10" y="92" width="6" height="6" fill="#000"/><rect x="40" y="92" width="6" height="6" fill="#000"/>
            <rect x="62" y="62" width="6" height="6" fill="#000"/><rect x="72" y="62" width="6" height="6" fill="#000"/><rect x="82" y="62" width="6" height="6" fill="#000"/><rect x="92" y="62" width="6" height="6" fill="#000"/>
            <rect x="62" y="72" width="6" height="6" fill="#000"/><rect x="92" y="72" width="6" height="6" fill="#000"/>
            <rect x="72" y="82" width="6" height="6" fill="#000"/><rect x="82" y="82" width="6" height="6" fill="#000"/>
            <rect x="62" y="92" width="6" height="6" fill="#000"/><rect x="72" y="92" width="6" height="6" fill="#000"/><rect x="92" y="92" width="6" height="6" fill="#000"/>
            <rect x="106" y="62" width="6" height="6" fill="#000"/><rect x="116" y="62" width="6" height="6" fill="#000"/><rect x="136" y="62" width="6" height="6" fill="#000"/>
            <rect x="106" y="72" width="6" height="6" fill="#000"/><rect x="126" y="72" width="6" height="6" fill="#000"/><rect x="144" y="72" width="6" height="6" fill="#000"/>
            <rect x="116" y="82" width="6" height="6" fill="#000"/><rect x="136" y="82" width="6" height="6" fill="#000"/>
            <rect x="106" y="92" width="6" height="6" fill="#000"/><rect x="126" y="92" width="6" height="6" fill="#000"/>
            <rect x="62" y="106" width="6" height="6" fill="#000"/><rect x="82" y="106" width="6" height="6" fill="#000"/><rect x="92" y="106" width="6" height="6" fill="#000"/>
            <rect x="72" y="116" width="6" height="6" fill="#000"/><rect x="92" y="116" width="6" height="6" fill="#000"/>
            <rect x="62" y="126" width="6" height="6" fill="#000"/><rect x="82" y="126" width="6" height="6" fill="#000"/>
            <rect x="72" y="136" width="6" height="6" fill="#000"/><rect x="62" y="144" width="6" height="6" fill="#000"/><rect x="82" y="144" width="6" height="6" fill="#000"/>
            <rect x="106" y="106" width="6" height="6" fill="#000"/><rect x="126" y="106" width="6" height="6" fill="#000"/><rect x="144" y="106" width="6" height="6" fill="#000"/>
            <rect x="116" y="116" width="6" height="6" fill="#000"/><rect x="136" y="116" width="6" height="6" fill="#000"/>
            <rect x="106" y="126" width="6" height="6" fill="#000"/><rect x="126" y="126" width="6" height="6" fill="#000"/>
            <rect x="116" y="136" width="6" height="6" fill="#000"/><rect x="144" y="136" width="6" height="6" fill="#000"/>
            <rect x="106" y="144" width="6" height="6" fill="#000"/><rect x="136" y="144" width="6" height="6" fill="#000"/>
            <!-- Center logo -->
            <rect x="68" y="68" width="24" height="24" fill="#fff" rx="3"/>
            <rect x="72" y="72" width="16" height="16" fill="#4040ff" rx="2"/>
          </svg>
        </div>
        <div style="font-size:0.75rem;color:var(--text2);margin-bottom:4px;font-weight:600">BookEase Official</div>
        <div style="font-size:0.7rem;color:var(--text3)">Scan menggunakan aplikasi e-wallet apapun</div>
        ${footer}
      </div>`;
  }
}