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