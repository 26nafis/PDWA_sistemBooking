<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BookEase — Sistem Reservasi</title>
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<!-- Main Stylesheet -->
<link rel="stylesheet" href="css/style.css">
<link rel="icon" href="favicon.png">
</head>
<body>

<!-- ══════════════════ HEADER ══════════════════ -->
<header class="header">
  <div class="header-inner">
    <a class="logo" href="#">
      <div class="logo-icon">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
          <rect x="2" y="5" width="16" height="14" rx="3" stroke="#fff" stroke-width="1.7"/>
          <path d="M7 2v5M13 2v5M2 10h16" stroke="#fff" stroke-width="1.7" stroke-linecap="round"/>
        </svg>
      </div>
      <span class="logo-text">Book<span>Ease</span></span>
    </a>
    <nav class="nav">
      <button onclick="showSec('booking')" id="nav-booking" class="nav-btn nav-active">Booking</button>
      <button onclick="showSec('status')"  id="nav-status"  class="nav-btn nav-inactive">Cek Status</button>
      <a href="admin/login.php" class="nav-btn nav-inactive" style="color:#6666ff">Admin ↗</a>
    </nav>
  </div>
</header>

<!-- ══════════════════ BOOKING SECTION ══════════════════ -->
<div id="sec-booking" class="wrap">

  <!-- HERO -->
  <div class="hero">
    <div class="hero-pill">
      <span class="hero-pill-dot"></span>
      <span class="hero-pill-text">Sistem Reservasi Online</span>
    </div>
    <h1>Reservasi <span class="accent">Mudah</span>,<br>Jadwal <span class="accent">Teratur</span></h1>
    <p>Pilih layanan favorit Anda, tentukan jadwal, dan konfirmasi pembayaran<br>hanya dalam beberapa langkah mudah.</p>
  </div>

   <!-- STEP INDICATOR -->
  <div class="steps">
    <div class="step-item step-active" id="si-1"><div class="step-num">1</div><div class="step-lbl">Layanan</div></div>
    <div class="step-line" id="l-12"></div>
    <div class="step-item step-inactive" id="si-2"><div class="step-num">2</div><div class="step-lbl">Jadwal</div></div>
    <div class="step-line" id="l-23"></div>
    <div class="step-item step-inactive" id="si-3"><div class="step-num">3</div><div class="step-lbl">Data Diri</div></div>
    <div class="step-line" id="l-34"></div>
    <div class="step-item step-inactive" id="si-4"><div class="step-num">4</div><div class="step-lbl">Pembayaran</div></div>
  </div>

    <!-- ── STEP 1: Pilih Layanan ── -->
  <div id="s1" class="fade-in">
    <div class="section-tag">Pilih Layanan</div>
    <div class="svc-grid" id="svc-grid">
            <!-- Skeleton loading -->
      <div class="svc-card" style="opacity:.4;pointer-events:none">
        <div class="svc-icon" style="background:var(--bg3)"></div>
        <div style="flex:1">
          <div style="height:14px;background:var(--bg3);border-radius:8px;width:55%;margin-bottom:10px"></div>
          <div style="height:10px;background:var(--bg3);border-radius:6px;width:80%"></div>
        </div>
      </div>
            <div class="svc-card" style="opacity:.4;pointer-events:none">
        <div class="svc-icon" style="background:var(--bg3)"></div>
        <div style="flex:1">
          <div style="height:14px;background:var(--bg3);border-radius:8px;width:55%;margin-bottom:10px"></div>
          <div style="height:10px;background:var(--bg3);border-radius:6px;width:80%"></div>
        </div>
      </div>
    </div>
  </div>

    <!-- ── STEP 2: Pilih Jadwal ── -->
  <div id="s2" class="hidden fade-in">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px">
      <button class="btn-back" onclick="go(1)">
        <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M9 11L5 7l4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Kembali
      </button>
      <span class="badge b-blue" id="svc-badge"></span>
    </div>
        <div class="card" style="padding:24px;margin-bottom:20px">
      <label class="form-label">📅 Pilih Tanggal Reservasi</label>
      <input type="date" id="slot-date" class="form-input" style="max-width:280px"
             min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" onchange="loadSlots()">
    </div>
        <div style="display:flex;flex-wrap:wrap;gap:20px;margin-bottom:16px">
      <span style="display:flex;align-items:center;gap:8px;font-size:0.75rem;font-weight:700;color:var(--green)">
        <span style="width:10px;height:10px;border-radius:3px;background:var(--green);display:inline-block"></span>Tersedia
      </span>
      <span style="display:flex;align-items:center;gap:8px;font-size:0.75rem;font-weight:700;color:var(--red)">
        <span style="width:10px;height:10px;border-radius:3px;background:var(--red);display:inline-block;opacity:.6"></span>Dipesan
      </span>
      <span style="display:flex;align-items:center;gap:8px;font-size:0.75rem;font-weight:700;color:var(--text3)">
        <span style="width:10px;height:10px;border-radius:3px;background:var(--text3);display:inline-block;opacity:.5"></span>Sudah Lewat
      </span>
    </div>