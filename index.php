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
        <div class="card" style="padding:24px" id="slots-wrap">
      <div style="display:flex;align-items:center;justify-content:center;padding:40px;gap:12px;color:var(--text3)">
        <div class="loader"></div><span style="font-size:0.85rem">Memuat slot waktu...</span>
      </div>
    </div>
  </div>

    <!-- ── STEP 3: Data Diri ── -->
  <div id="s3" class="hidden fade-in">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px">
      <button class="btn-back" onclick="go(2)">
        <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M9 11L5 7l4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Kembali
      </button>
    </div>
        <div class="summary">
      <div><div class="sum-label">Layanan</div><div class="sum-val" id="s-svc"></div></div>
      <div><div class="sum-label">Tanggal</div><div class="sum-val" id="s-date"></div></div>
      <div><div class="sum-label">Waktu</div><div class="sum-val" style="font-family:'JetBrains Mono',monospace" id="s-time"></div></div>
      <div><div class="sum-label">Total Bayar</div><div class="sum-price" id="s-price"></div></div>
    </div>
        <div class="card" style="padding:28px">
      <div class="section-tag">Data Pemesan</div>
      <div class="grid2">
        <div>
          <label class="form-label">Nama Lengkap<span class="req">*</span></label>
          <input type="text" id="c-name" class="form-input" placeholder="Nama lengkap Anda">
        </div>
        <div>
          <label class="form-label">No. HP / WhatsApp<span class="req">*</span></label>
          <input type="tel" id="c-phone" class="form-input" placeholder="08xxxxxxxxxx">
        </div>
                <div class="col2">
          <label class="form-label">Email<span class="opt">(opsional)</span></label>
          <input type="email" id="c-email" class="form-input" placeholder="email@contoh.com">
        </div>
        <div class="col2">
          <label class="form-label">Catatan<span class="opt">(opsional)</span></label>
          <textarea id="c-notes" class="form-input" rows="2" style="resize:none" placeholder="Permintaan khusus, dll."></textarea>
        </div>
      </div>
            <div style="margin-top:24px">
        <button id="btn-submit" class="btn btn-primary btn-full" onclick="submitBook()">
          Konfirmasi Booking
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
      </div>
      <div id="book-err" class="err-msg hidden"></div>
          </div>
  </div>

  <!-- ── STEP 4: Pembayaran ── -->
  <div id="s4" class="hidden fade-in">
        <div style="background:rgba(0,229,160,0.08);border:1px solid rgba(0,229,160,0.25);border-radius:16px;padding:20px 24px;display:flex;align-items:center;gap:14px;margin-bottom:24px">
      <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,var(--green),#00ffbb);display:flex;align-items:center;justify-content:center;flex-shrink:0">
        <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M3 9l5 5 7-7" stroke="#001a0d" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
            <div>
        <div style="font-weight:800;font-size:0.95rem;color:var(--green)">Booking Berhasil Dibuat!</div>
        <div style="font-size:0.8rem;color:rgba(0,229,160,0.7);margin-top:2px">
          Kode booking: <span style="font-family:'JetBrains Mono',monospace;font-weight:800" id="code-disp"></span>
        </div>
      </div>
    </div>

        <div class="card" style="padding:28px">
      <div class="section-tag">Upload Bukti Pembayaran</div>

      <!-- Pilih Metode -->
      <div style="margin-bottom:20px">
        <label class="form-label">Pilih Metode Pembayaran</label>
        <select id="pay-method" class="form-input" onchange="updatePayInfo()">
          <option value="bca">Transfer Bank BCA</option>
          <option value="bri">Transfer Bank BRI</option>
          <option value="mandiri">Transfer Bank Mandiri</option>
          <option value="gopay">GoPay</option>
          <option value="ovo">OVO</option>
          <option value="qris">QRIS</option>
        </select>
      </div>

      <!-- Info Pembayaran Dinamis -->
      <div id="pay-info-wrap" style="margin-bottom:24px"></div>

            <!-- Upload Bukti -->
      <div class="upload-zone" id="upload-zone" onclick="document.getElementById('proof-inp').click()">
        <input type="file" id="proof-inp" class="hidden" accept="image/*,.pdf" onchange="onFile(this)">
        <div id="up-placeholder">
          <div style="width:60px;height:60px;border-radius:18px;background:rgba(64,64,255,0.1);border:1px solid var(--border2);display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
            <svg width="26" height="26" viewBox="0 0 26 26" fill="none">
              <path d="M13 17V7m0 0L9 11m4-4l4 4" stroke="var(--blue2)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M4 19v1a3 3 0 003 3h12a3 3 0 003-3v-1" stroke="var(--blue2)" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </div>
          <div style="font-weight:800;font-size:0.95rem;color:var(--blue2);margin-bottom:6px">Klik atau drag & drop file di sini</div>
          <div style="font-size:0.78rem;color:var(--text3)">Format: JPG, PNG, PDF • Ukuran maksimal 5MB</div>
        </div>
                <div id="up-preview" class="hidden">
          <div style="width:48px;height:48px;border-radius:14px;background:rgba(0,229,160,0.12);border:1px solid rgba(0,229,160,0.3);display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
            <svg width="22" height="22" viewBox="0 0 22 22" fill="none"><path d="M4 11l5 5 9-9" stroke="var(--green)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </div>
          <div style="font-weight:800;font-size:0.88rem;color:var(--green)" id="fname"></div>
          <div style="font-size:0.72rem;color:var(--text3);margin-top:6px">Klik untuk ganti file</div>
        </div>
      </div>

            <div style="margin-top:20px">
        <button id="btn-pay" class="btn btn-primary btn-full" onclick="submitPay()">
          Kirim Bukti Pembayaran
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
      </div>
      <div id="pay-err" class="err-msg hidden"></div>
    </div>
  </div>

    <!-- ── STEP 5: Selesai ── -->
  <div id="s5" class="hidden fade-in">
    <div class="card" style="padding:64px 24px;text-align:center">
      <div class="success-icon">
        <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
          <path d="M8 20l9 9 15-15" stroke="#fff" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <h2 style="font-size:1.8rem;font-weight:900;color:#fff;margin-bottom:10px;letter-spacing:-0.02em">Pembayaran Terkirim!</h2>
      <p style="font-size:0.9rem;color:var(--text2);max-width:400px;margin:0 auto 28px;line-height:1.7">Admin akan memverifikasi dalam 1×24 jam. Catat kode booking Anda untuk mengecek status.</p>
            <div class="code-box" id="final-code"></div>
      <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
        <button onclick="toStatus()" class="btn-outline">Cek Status Booking</button>
        <button onclick="window.location.reload()" class="btn btn-primary">+ Booking Lagi</button>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════════ STATUS SECTION ══════════════════ -->
<div id="sec-status" class="wrap hidden">
  <div class="hero">
    <div class="hero-pill"><span class="hero-pill-dot"></span><span class="hero-pill-text">Lacak Reservasi</span></div>
    <h1>Cek <span class="accent">Status</span><br>Booking Anda</h1>
    <p>Masukkan kode booking untuk melihat status reservasi dan pembayaran Anda.</p>
  </div>
    <div class="card" style="padding:28px;max-width:480px;margin-bottom:24px">
    <label class="form-label">Kode Booking</label>
    <div style="display:flex;gap:10px">
      <input type="text" id="st-code" class="form-input" placeholder="BK12345678"
             style="font-family:'JetBrains Mono',monospace;font-weight:700;text-transform:uppercase;flex:1"
             oninput="this.value=this.value.toUpperCase()">
      <button onclick="fetchSt()" class="btn btn-primary" style="padding:13px 22px;white-space:nowrap">Cari</button>
    </div>
      </div>
  <div id="st-result" class="hidden fade-in"></div>
</div>