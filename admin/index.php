<?php
require_once '../config.php';
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

$message = '';
$msgType = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();
    $act = $_POST['act'] ?? '';

    if ($act === 'update_booking_status') {
        $id = (int)$_POST['booking_id'];
        $status = in_array($_POST['status'], ['pending','confirmed','canceled']) ? $_POST['status'] : 'pending';
        $db->prepare("UPDATE bookings SET status=? WHERE id=?")->execute([$status, $id]);
        if ($status === 'canceled') {
            $booking = $db->prepare("SELECT * FROM bookings WHERE id=?");
            $booking->execute([$id]);
            $b = $booking->fetch();
            if ($b) {
                $db->prepare("UPDATE time_slots SET status='available' WHERE service_id=? AND slot_date=? AND start_time=?")
                   ->execute([$b['service_id'], $b['slot_date'], $b['start_time']]);
            }
        }
        $message = 'Status booking berhasil diperbarui.';
        $msgType = 'success';
    }

    if ($act === 'update_payment_status') {
        $id = (int)$_POST['payment_id'];
        $status = in_array($_POST['status'], ['pending','verified','rejected']) ? $_POST['status'] : 'pending';
        $verifiedAt = $status === 'verified' ? date('Y-m-d H:i:s') : null;
        $verifiedBy = $status === 'verified' ? $_SESSION['admin_name'] : null;
        $db->prepare("UPDATE payments SET status=?, verified_at=?, verified_by=? WHERE id=?")
           ->execute([$status, $verifiedAt, $verifiedBy, $id]);
        if ($status === 'verified') {
            $p = $db->prepare("SELECT booking_id FROM payments WHERE id=?");
            $p->execute([$id]);
            $pay = $p->fetch();
            if ($pay) {
                $db->prepare("UPDATE bookings SET status='confirmed' WHERE id=?")->execute([$pay['booking_id']]);
            }
        }
        $message = 'Status pembayaran berhasil diperbarui.';
        $msgType = 'success';
    }
}

$db = getDB();
$stats = [
    'total'       => $db->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
    'pending'     => $db->query("SELECT COUNT(*) FROM bookings WHERE status='pending'")->fetchColumn(),
    'confirmed'   => $db->query("SELECT COUNT(*) FROM bookings WHERE status='confirmed'")->fetchColumn(),
    'canceled'    => $db->query("SELECT COUNT(*) FROM bookings WHERE status='canceled'")->fetchColumn(),
    'revenue'     => $db->query("SELECT SUM(b.total_price) FROM bookings b JOIN payments p ON p.booking_id=b.id WHERE p.status='verified'")->fetchColumn() ?? 0,
    'pay_pending' => $db->query("SELECT COUNT(*) FROM payments WHERE status='pending' AND payment_proof IS NOT NULL")->fetchColumn(),
];
$bookings = $db->query("
    SELECT b.*, c.name as cust_name, c.phone, s.name as svc_name, p.status as pay_status
    FROM bookings b
    JOIN customers c ON c.id=b.customer_id
    JOIN services s ON s.id=b.service_id
    LEFT JOIN payments p ON p.booking_id=b.id
    ORDER BY b.created_at DESC LIMIT 100
")->fetchAll();
$pendingPayments = $db->query("
    SELECT p.*, b.booking_code, b.total_price, b.slot_date, b.start_time, b.end_time,
           c.name as cust_name, c.phone, s.name as svc_name
    FROM payments p
    JOIN bookings b ON b.id=p.booking_id
    JOIN customers c ON c.id=b.customer_id
    JOIN services s ON s.id=b.service_id
    WHERE p.payment_proof IS NOT NULL
    ORDER BY p.created_at DESC LIMIT 50
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — BookEase Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#060614;--bg2:#0c0c24;--bg3:#10103a;
  --card:#111130;--card2:#161645;
  --border:rgba(100,100,255,0.18);--border2:rgba(100,100,255,0.35);
  --blue:#4040ff;--blue2:#6666ff;--blue-glow:rgba(64,64,255,0.4);
  --text:#e8e8ff;--text2:#9090cc;--text3:#5555aa;
  --green:#00e5a0;--red:#ff4466;--yellow:#ffcc00;
}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;flex-direction:column;}

/* SIDEBAR */
.layout{display:flex;min-height:100vh}
.sidebar{width:240px;background:var(--bg2);border-right:1px solid var(--border);display:flex;flex-direction:column;padding:24px 16px;position:fixed;top:0;left:0;height:100vh;z-index:50;overflow-y:auto;}
.sidebar-logo{display:flex;align-items:center;gap:10px;margin-bottom:32px;padding:0 8px}
.sidebar-logo-icon{width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,var(--blue),var(--blue2));display:flex;align-items:center;justify-content:center;box-shadow:0 0 16px var(--blue-glow);flex-shrink:0}
.sidebar-logo-text{font-size:1rem;font-weight:800;color:#fff;letter-spacing:-0.02em}
.sidebar-logo-text span{color:var(--blue2)}
.nav-section{font-size:0.62rem;font-weight:800;color:var(--text3);text-transform:uppercase;letter-spacing:0.12em;padding:0 10px;margin-bottom:8px}
.nav-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:12px;font-size:0.82rem;font-weight:600;color:var(--text2);transition:all 0.15s;cursor:pointer;border:none;background:transparent;width:100%;text-align:left;text-decoration:none;margin-bottom:2px}
.nav-item:hover{background:rgba(100,100,255,0.08);color:var(--text)}
.nav-item.active{background:rgba(64,64,255,0.15);color:var(--blue2);border:1px solid rgba(64,64,255,0.2)}
.nav-item svg{flex-shrink:0;opacity:0.7}
.nav-item.active svg{opacity:1}
.nav-badge{margin-left:auto;background:var(--red);color:#fff;border-radius:99px;font-size:0.6rem;font-weight:800;padding:2px 7px;font-family:'JetBrains Mono',monospace}
.sidebar-footer{margin-top:auto;padding-top:16px;border-top:1px solid var(--border)}
.sidebar-user{padding:10px 12px;margin-bottom:4px}
.sidebar-user-name{font-size:0.78rem;font-weight:700;color:var(--text);margin-bottom:2px}
.sidebar-user-role{font-size:0.68rem;color:var(--text3)}
.nav-item-danger{color:var(--red)!important}
.nav-item-danger:hover{background:rgba(255,68,102,0.08)!important;color:var(--red)!important}

/* MAIN */
.main{margin-left:240px;flex:1;display:flex;flex-direction:column;min-height:100vh}
.topbar{height:60px;background:rgba(6,6,20,0.8);backdrop-filter:blur(20px);border-bottom:1px solid var(--border);display:flex;align-items:center;padding:0 28px;position:sticky;top:0;z-index:40;gap:16px}
.topbar-title{font-size:0.95rem;font-weight:800;color:#fff}
.topbar-sub{font-size:0.75rem;color:var(--text3);margin-left:2px}
.content{padding:28px;flex:1}

/* SECTION */
.section{display:none}
.section.active{display:block}
.fade-in{animation:fadeUp 0.25s ease}
@keyframes fadeUp{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}

/* PAGE HEADER */
.page-header{margin-bottom:24px}
.page-title{font-size:1.3rem;font-weight:900;color:#fff;letter-spacing:-0.02em;margin-bottom:4px}
.page-sub{font-size:0.82rem;color:var(--text3)}

/* STATS */
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px}
.stat-card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:20px;position:relative;overflow:hidden}
.stat-card::before{content:'';position:absolute;top:-20px;right:-20px;width:60px;height:60px;border-radius:50%;background:radial-gradient(circle,rgba(64,64,255,0.08),transparent 70%)}
.stat-label{font-size:0.68rem;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:8px}
.stat-val{font-family:'JetBrains Mono',monospace;font-size:1.6rem;font-weight:800;color:#fff}
.stat-val.green{color:var(--green)}
.stat-val.yellow{color:var(--yellow)}
.stat-val.red{color:var(--red)}
.stat-val.blue{color:var(--blue2)}
.stat-icon{position:absolute;top:18px;right:18px;width:32px;height:32px;border-radius:10px;display:flex;align-items:center;justify-content:center}

/* ALERT */
.alert{border-radius:14px;padding:16px 20px;margin-bottom:20px;display:flex;align-items:center;gap:14px}
.alert-warn{background:rgba(255,204,0,0.08);border:1px solid rgba(255,204,0,0.2)}
.alert-success{background:rgba(0,229,160,0.08);border:1px solid rgba(0,229,160,0.2)}
.alert-icon{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0}

/* CARD */
.card{background:var(--card);border:1px solid var(--border);border-radius:16px}
.card-header{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-title{font-size:0.85rem;font-weight:800;color:#fff}

/* TABLE */
table{width:100%;border-collapse:collapse}
thead tr{background:var(--bg3)}
th{padding:10px 16px;font-size:0.65rem;font-weight:800;color:var(--text3);text-transform:uppercase;letter-spacing:0.1em;text-align:left}
td{padding:12px 16px;font-size:0.82rem;border-bottom:1px solid var(--border);color:var(--text)}
tr:hover td{background:rgba(100,100,255,0.04)}
tr:last-child td{border-bottom:none}
.overflow-x{overflow-x:auto}

/* BADGE */
.badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:99px;font-size:0.65rem;font-weight:800;letter-spacing:0.07em;text-transform:uppercase;font-family:'JetBrains Mono',monospace}
.b-pending{background:rgba(255,204,0,0.12);color:var(--yellow);border:1px solid rgba(255,204,0,0.25)}
.b-confirmed{background:rgba(0,229,160,0.12);color:var(--green);border:1px solid rgba(0,229,160,0.25)}
.b-canceled{background:rgba(255,68,102,0.12);color:var(--red);border:1px solid rgba(255,68,102,0.25)}
.b-verified{background:rgba(0,229,160,0.12);color:var(--green);border:1px solid rgba(0,229,160,0.25)}
.b-rejected{background:rgba(255,68,102,0.12);color:var(--red);border:1px solid rgba(255,68,102,0.25)}

/* FORM INPUTS */
.form-input{background:var(--bg2);border:1px solid var(--border);border-radius:10px;padding:9px 14px;font-size:0.8rem;font-family:'Inter',sans-serif;color:var(--text);transition:all 0.2s}
.form-input:focus{outline:none;border-color:var(--blue2);box-shadow:0 0 0 3px rgba(64,64,255,0.12)}
.form-input::placeholder{color:var(--text3)}
.form-input option{background:var(--bg2);color:var(--text)}
.select-status{background:var(--bg3);border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:0.75rem;font-family:'JetBrains Mono',monospace;color:var(--text);cursor:pointer;transition:all 0.2s}
.select-status:focus{outline:none;border-color:var(--blue2)}

/* SEARCH BAR */
.search-wrap{display:flex;gap:10px;flex-wrap:wrap}

/* PAYMENT CARD */
.pay-card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:20px;transition:all 0.2s}
.pay-card:hover{border-color:var(--border2);box-shadow:0 8px 32px rgba(64,64,255,0.1)}
.pay-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
.pay-info{background:var(--bg3);border-radius:12px;padding:14px;margin-bottom:16px}
.pay-info-row{display:flex;justify-content:space-between;align-items:center;font-size:0.75rem;margin-bottom:6px}
.pay-info-row:last-child{margin-bottom:0}
.pay-info-label{color:var(--text3);font-weight:600}
.pay-info-val{color:var(--text);font-weight:700;font-family:'JetBrains Mono',monospace}
.pay-info-val.green{color:var(--green)}

/* PROOF IMAGE */
.proof-img{width:100%;max-height:160px;object-fit:cover;border-radius:10px;border:1px solid var(--border);transition:opacity 0.2s}
.proof-img:hover{opacity:0.85}

/* BUTTONS */
.btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:9px 18px;border-radius:10px;font-size:0.78rem;font-weight:700;cursor:pointer;border:none;transition:all 0.2s;font-family:'Inter',sans-serif}
.btn-verify{background:linear-gradient(135deg,var(--green),#00ffbb);color:#001a0d;flex:1}
.btn-verify:hover{filter:brightness(1.1);transform:translateY(-1px)}
.btn-reject{background:rgba(255,68,102,0.1);color:var(--red);border:1px solid rgba(255,68,102,0.25);flex:1}
.btn-reject:hover{background:rgba(255,68,102,0.2)}
.btn-reset{background:var(--bg3);color:var(--text2);border:1px solid var(--border);flex:1}
.btn-reset:hover{border-color:var(--border2);color:var(--text)}
.btn-sm{padding:7px 14px;font-size:0.72rem}
.btn-ghost{background:transparent;color:var(--text3);font-size:0.75rem;font-weight:600;cursor:pointer;border:none;font-family:'Inter',sans-serif;transition:color 0.15s}
.btn-ghost:hover{color:var(--blue2)}

/* EMPTY STATE */
.empty-state{text-align:center;padding:60px 20px;color:var(--text3)}
.empty-icon{width:48px;height:48px;margin:0 auto 16px;opacity:0.3}

/* VERIFIED INFO */
.verified-note{font-size:0.7rem;color:var(--text3);text-align:center;margin-top:10px}

/* MOBILE */
.mobile-topbar{display:none;background:var(--bg2);border-bottom:1px solid var(--border);padding:12px 16px;gap:8px;overflow-x:auto;position:sticky;top:0;z-index:50}
@media(max-width:768px){
  .sidebar{display:none}
  .main{margin-left:0}
  .mobile-topbar{display:flex}
  .stats-grid{grid-template-columns:1fr 1fr}
  .pay-grid{grid-template-columns:1fr}
  .content{padding:16px}
  .topbar{display:none}
}
</style>
</head>
<body>

<!-- Mobile Top Nav -->
<div class="mobile-topbar">
  <button onclick="showSection('dashboard')" class="nav-item active text-xs whitespace-nowrap">Dashboard</button>
  <button onclick="showSection('bookings')" class="nav-item text-xs whitespace-nowrap">Booking</button>
  <button onclick="showSection('payments')" class="nav-item text-xs whitespace-nowrap">
    Pembayaran
    <?php if($stats['pay_pending']>0): ?><span class="nav-badge"><?=$stats['pay_pending']?></span><?php endif; ?>
  </button>
  <a href="logout.php" class="nav-item nav-item-danger text-xs whitespace-nowrap">Keluar</a>
</div>

<div class="layout">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="sidebar-logo-icon">
        <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
          <rect x="1.5" y="4" width="15" height="13" rx="2.5" stroke="#fff" stroke-width="1.6"/>
          <path d="M6 2v4M12 2v4M1.5 9h15" stroke="#fff" stroke-width="1.6" stroke-linecap="round"/>
        </svg>
      </div>
      <span class="sidebar-logo-text">Book<span>Ease</span></span>
    </div>

    <div class="nav-section" style="margin-top:0">Menu</div>
    <nav style="flex:1">
      <button onclick="showSection('dashboard')" id="nav-dashboard" class="nav-item active">
        <svg width="15" height="15" viewBox="0 0 15 15" fill="none"><rect x="1" y="1" width="5.5" height="5.5" rx="1.5" stroke="currentColor" stroke-width="1.3"/><rect x="8.5" y="1" width="5.5" height="5.5" rx="1.5" stroke="currentColor" stroke-width="1.3"/><rect x="1" y="8.5" width="5.5" height="5.5" rx="1.5" stroke="currentColor" stroke-width="1.3"/><rect x="8.5" y="8.5" width="5.5" height="5.5" rx="1.5" stroke="currentColor" stroke-width="1.3"/></svg>
        Overview
      </button>
      <button onclick="showSection('bookings')" id="nav-bookings" class="nav-item">
        <svg width="15" height="15" viewBox="0 0 15 15" fill="none"><rect x="1.5" y="2.5" width="12" height="11" rx="2" stroke="currentColor" stroke-width="1.3"/><path d="M5 1v3M10 1v3M1.5 7h12" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>
        Semua Booking
      </button>
      <button onclick="showSection('payments')" id="nav-payments" class="nav-item">
        <svg width="15" height="15" viewBox="0 0 15 15" fill="none"><rect x="1" y="3.5" width="13" height="9" rx="2" stroke="currentColor" stroke-width="1.3"/><path d="M1 7h13M4.5 10h3" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>
        Verifikasi Pembayaran
        <?php if($stats['pay_pending']>0): ?><span class="nav-badge"><?=$stats['pay_pending']?></span><?php endif; ?>
      </button>
    </nav>

    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="sidebar-user-name"><?= htmlspecialchars($_SESSION['admin_name']) ?></div>
        <div class="sidebar-user-role">Administrator</div>
      </div>
      <a href="logout.php" class="nav-item nav-item-danger">
        <svg width="15" height="15" viewBox="0 0 15 15" fill="none"><path d="M6 2H3a1 1 0 00-1 1v9a1 1 0 001 1h3M10 10l3-3-3-3M13 7H6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Keluar
      </a>
    </div>
  </aside>

  <!-- MAIN -->
  <div class="main">

    <!-- Topbar -->
    <div class="topbar">
      <div>
        <div class="topbar-title" id="topbar-title">Dashboard</div>
        <div class="topbar-sub">BookEase Admin Panel</div>
      </div>
      <?php if($message): ?>
        <div style="margin-left:auto;background:<?=$msgType==='success'?'rgba(0,229,160,0.1)':'rgba(255,68,102,0.1)'?>;border:1px solid <?=$msgType==='success'?'rgba(0,229,160,0.25)':'rgba(255,68,102,0.25)'?>;border-radius:10px;padding:7px 14px;font-size:0.78rem;font-weight:600;color:<?=$msgType==='success'?'var(--green)':'var(--red)'?>">
          <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="content">

      <!-- ══ DASHBOARD ══ -->
      <div id="section-dashboard" class="section active fade-in">
        <div class="page-header">
          <div class="page-title">Selamat Datang, <?= htmlspecialchars($_SESSION['admin_name']) ?> 👋</div>
          <div class="page-sub">Ringkasan aktivitas sistem reservasi BookEase</div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon" style="background:rgba(64,64,255,0.1)">
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="2" width="14" height="13" rx="2" stroke="var(--blue2)" stroke-width="1.4"/><path d="M1 7h14M5 1v3M11 1v3" stroke="var(--blue2)" stroke-width="1.4" stroke-linecap="round"/></svg>
            </div>
            <div class="stat-label">Total Booking</div>
            <div class="stat-val blue"><?= $stats['total'] ?></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background:rgba(255,204,0,0.1)">
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="var(--yellow)" stroke-width="1.4"/><path d="M8 5v3.5L10 10" stroke="var(--yellow)" stroke-width="1.4" stroke-linecap="round"/></svg>
            </div>
            <div class="stat-label">Menunggu</div>
            <div class="stat-val yellow"><?= $stats['pending'] ?></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background:rgba(0,229,160,0.1)">
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M2.5 8l4 4 7-7" stroke="var(--green)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <div class="stat-label">Dikonfirmasi</div>
            <div class="stat-val green"><?= $stats['confirmed'] ?></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background:rgba(0,229,160,0.1)">
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="4" width="14" height="10" rx="2" stroke="var(--green)" stroke-width="1.4"/><path d="M1 8h14M5 8v6" stroke="var(--green)" stroke-width="1.4" stroke-linecap="round"/></svg>
            </div>
            <div class="stat-label">Pendapatan</div>
            <div class="stat-val green" style="font-size:1rem"><?= formatRupiah((float)$stats['revenue']) ?></div>
          </div>
        </div>

        <!-- Alert pembayaran pending -->
        <?php if($stats['pay_pending']>0): ?>
          <div class="alert alert-warn" style="margin-bottom:20px">
            <div class="alert-icon" style="background:rgba(255,204,0,0.15)">
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 2v6M8 10v1" stroke="var(--yellow)" stroke-width="1.8" stroke-linecap="round"/><circle cx="8" cy="8" r="7" stroke="var(--yellow)" stroke-width="1.3"/></svg>
            </div>
            <div style="flex:1">
              <div style="font-size:0.85rem;font-weight:800;color:var(--yellow);margin-bottom:2px"><?= $stats['pay_pending'] ?> pembayaran menunggu verifikasi</div>
              <div style="font-size:0.75rem;color:rgba(255,204,0,0.6)">Segera periksa dan verifikasi bukti transfer pelanggan</div>
            </div>
            <button onclick="showSection('payments')" style="background:rgba(255,204,0,0.15);border:1px solid rgba(255,204,0,0.3);color:var(--yellow);padding:7px 14px;border-radius:8px;font-size:0.75rem;font-weight:700;cursor:pointer;white-space:nowrap">Lihat →</button>
          </div>
        <?php endif; ?>

        <!-- Recent Bookings Table -->
        <div class="card">
          <div class="card-header">
            <div class="card-title">Booking Terbaru</div>
            <button onclick="showSection('bookings')" class="btn-ghost">Lihat semua →</button>
          </div>
          <div class="overflow-x">
            <table>
              <thead><tr>
                <th>Kode</th><th>Pelanggan</th><th>Layanan</th><th>Jadwal</th><th>Status</th><th>Pembayaran</th>
              </tr></thead>
              <tbody>
                <?php foreach(array_slice($bookings,0,8) as $b): ?>
                  <tr>
                    <td><span style="font-family:'JetBrains Mono',monospace;font-size:0.75rem;font-weight:700;color:var(--blue2)"><?= htmlspecialchars($b['booking_code']) ?></span></td>
                    <td>
                      <div style="font-weight:700;font-size:0.8rem"><?= htmlspecialchars($b['cust_name']) ?></div>
                      <div style="font-size:0.72rem;color:var(--text3)"><?= htmlspecialchars($b['phone']) ?></div>
                    </td>
                    <td style="color:var(--text2);font-size:0.78rem"><?= htmlspecialchars($b['svc_name']) ?></td>
                    <td style="font-family:'JetBrains Mono',monospace;font-size:0.75rem">
                      <?= date('d/m/y',strtotime($b['slot_date'])) ?><br>
                      <span style="color:var(--text3)"><?= substr($b['start_time'],0,5) ?></span>
                    </td>
                    <td><span class="badge b-<?= $b['status'] ?>"><?= $b['status'] ?></span></td>
                    <td><span class="badge b-<?= $b['pay_status']??'pending' ?>"><?= $b['pay_status']??'—' ?></span></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ══ BOOKINGS ══ -->
      <div id="section-bookings" class="section fade-in">
        <div class="page-header">
          <div class="page-title">Semua Booking</div>
          <div class="page-sub"><?= count($bookings) ?> total reservasi terdaftar</div>
        </div>

        <div class="search-wrap" style="margin-bottom:16px">
          <input type="text" id="search-booking" placeholder="🔍 Cari nama atau kode booking..." oninput="filterBookings()" class="form-input" style="min-width:240px">
          <select id="filter-status" onchange="filterBookings()" class="form-input">
            <option value="">Semua Status</option>
            <option value="pending">Pending</option>
            <option value="confirmed">Confirmed</option>
            <option value="canceled">Canceled</option>
          </select>
        </div>

        <div class="card overflow-hidden">
          <div class="overflow-x">
            <table>
              <thead><tr>
                <th>Kode</th><th>Pelanggan</th><th>Layanan</th><th>Jadwal</th><th>Total</th><th>Status</th><th>Pembayaran</th><th>Aksi</th>
              </tr></thead>
              <tbody id="bookings-tbody">
                <?php foreach($bookings as $b): ?>
                  <tr class="booking-row"
                      data-name="<?= strtolower(htmlspecialchars($b['cust_name'])) ?>"
                      data-code="<?= strtolower(htmlspecialchars($b['booking_code'])) ?>"
                      data-status="<?= $b['status'] ?>">
                    <td><span style="font-family:'JetBrains Mono',monospace;font-size:0.75rem;font-weight:700;color:var(--blue2)"><?= htmlspecialchars($b['booking_code']) ?></span></td>
                    <td>
                      <div style="font-weight:700;font-size:0.8rem"><?= htmlspecialchars($b['cust_name']) ?></div>
                      <div style="font-size:0.72rem;color:var(--text3)"><?= htmlspecialchars($b['phone']) ?></div>
                    </td>
                    <td style="color:var(--text2);font-size:0.78rem"><?= htmlspecialchars($b['svc_name']) ?></td>
                    <td style="font-family:'JetBrains Mono',monospace;font-size:0.75rem">
                      <?= date('d/m/y',strtotime($b['slot_date'])) ?><br>
                      <span style="color:var(--text3)"><?= substr($b['start_time'],0,5) ?></span>
                    </td>
                    <td style="font-family:'JetBrains Mono',monospace;font-size:0.78rem;font-weight:700;color:var(--green)"><?= formatRupiah($b['total_price']) ?></td>
                    <td><span class="badge b-<?= $b['status'] ?>"><?= $b['status'] ?></span></td>
                    <td><span class="badge b-<?= $b['pay_status']??'pending' ?>"><?= $b['pay_status']??'—' ?></span></td>
                    <td>
                      <form method="POST">
                        <input type="hidden" name="act" value="update_booking_status">
                        <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                        <select name="status" onchange="this.form.submit()" class="select-status">
                          <?php foreach(['pending','confirmed','canceled'] as $st): ?>
                            <option value="<?=$st?>" <?=$b['status']===$st?'selected':''?>><?=ucfirst($st)?></option>
                          <?php endforeach; ?>
                        </select>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ══ PAYMENTS ══ -->
      <div id="section-payments" class="section fade-in">
        <div class="page-header">
          <div class="page-title">Verifikasi Pembayaran</div>
          <div class="page-sub">Periksa dan validasi bukti transfer dari pelanggan</div>
        </div>

        <?php if(empty($pendingPayments)): ?>
          <div class="card">
            <div class="empty-state">
              <svg class="empty-icon" viewBox="0 0 48 48" fill="none">
                <rect x="4" y="10" width="40" height="28" rx="4" stroke="currentColor" stroke-width="2"/>
                <path d="M4 20h40M14 30h10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
              <div style="font-weight:700;color:var(--text2);margin-bottom:4px">Tidak ada pembayaran</div>
              <div style="font-size:0.8rem">Belum ada bukti pembayaran yang perlu diverifikasi</div>
            </div>
          </div>
        <?php else: ?>
          <div class="pay-grid">
            <?php foreach($pendingPayments as $p): ?>
              <div class="pay-card">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:14px">
                  <div>
                    <div style="font-family:'JetBrains Mono',monospace;font-size:1rem;font-weight:800;color:var(--blue2)"><?= htmlspecialchars($p['booking_code']) ?></div>
                    <div style="font-size:0.75rem;color:var(--text3);margin-top:3px"><?= htmlspecialchars($p['cust_name']) ?> · <?= htmlspecialchars($p['phone']) ?></div>
                  </div>
                  <span class="badge b-<?= $p['status'] ?>"><?= $p['status'] ?></span>
                </div>

                <div class="pay-info">
                  <div class="pay-info-row"><span class="pay-info-label">Layanan</span><span class="pay-info-val" style="font-family:'Inter',sans-serif"><?= htmlspecialchars($p['svc_name']) ?></span></div>
                  <div class="pay-info-row"><span class="pay-info-label">Tanggal</span><span class="pay-info-val"><?= date('d/m/Y',strtotime($p['slot_date'])) ?></span></div>
                  <div class="pay-info-row"><span class="pay-info-label">Waktu</span><span class="pay-info-val"><?= substr($p['start_time'],0,5) ?> – <?= substr($p['end_time'],0,5) ?></span></div>
                  <div class="pay-info-row" style="margin-top:8px;padding-top:8px;border-top:1px solid var(--border)">
                    <span class="pay-info-label">Total</span>
                    <span class="pay-info-val green"><?= formatRupiah($p['total_price']) ?></span>
                  </div>
                </div>

                <?php if($p['payment_proof']): ?>
                  <div style="margin-bottom:14px">
                    <div style="font-size:0.7rem;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:8px">
                      Bukti Transfer <?php if($p['payment_method']): ?>(<?= htmlspecialchars($p['payment_method']) ?>)<?php endif; ?>
                    </div>
                    <?php
                      $ext = strtolower(pathinfo($p['payment_proof'], PATHINFO_EXTENSION));
                      $proofUrl = '../' . UPLOAD_URL . $p['payment_proof'];
                    ?>
                    <?php if(in_array($ext,['jpg','jpeg','png','gif','webp'])): ?>
                      <a href="<?= htmlspecialchars($proofUrl) ?>" target="_blank">
                        <img src="<?= htmlspecialchars($proofUrl) ?>" alt="Bukti" class="proof-img">
                      </a>
                    <?php else: ?>
                      <a href="<?= htmlspecialchars($proofUrl) ?>" target="_blank"
                         style="display:inline-flex;align-items:center;gap:6px;font-size:0.78rem;font-weight:600;color:var(--blue2);text-decoration:none">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 2h7l3 3v7H2V2z" stroke="currentColor" stroke-width="1.3"/><path d="M9 2v3h3" stroke="currentColor" stroke-width="1.3"/></svg>
                        Lihat File PDF
                      </a>
                    <?php endif; ?>
                  </div>
                <?php else: ?>
                  <div style="background:rgba(255,204,0,0.08);border:1px solid rgba(255,204,0,0.2);border-radius:10px;padding:10px 14px;font-size:0.75rem;color:var(--yellow);font-weight:600;margin-bottom:14px">
                    ⚠ Belum ada bukti pembayaran diunggah
                  </div>
                <?php endif; ?>

                <form method="POST">
                  <input type="hidden" name="act" value="update_payment_status">
                  <input type="hidden" name="payment_id" value="<?= $p['id'] ?>">
                  <div style="display:flex;gap:8px">
                    <?php if($p['status']!=='verified'): ?>
                      <button type="submit" name="status" value="verified" class="btn btn-verify">✓ Verifikasi</button>
                    <?php endif; ?>
                    <?php if($p['status']!=='rejected'): ?>
                      <button type="submit" name="status" value="rejected" class="btn btn-reject">✕ Tolak</button>
                    <?php endif; ?>
                    <?php if($p['status']!=='pending'): ?>
                      <button type="submit" name="status" value="pending" class="btn btn-reset">Reset</button>
                    <?php endif; ?>
                  </div>
                </form>

                <?php if($p['verified_at']): ?>
                  <div class="verified-note">
                    Diverifikasi <?= date('d/m/Y H:i',strtotime($p['verified_at'])) ?>
                    <?php if($p['verified_by']): ?> · <?= htmlspecialchars($p['verified_by']) ?><?php endif; ?>
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

    </div><!-- /content -->
  </div><!-- /main -->
</div><!-- /layout -->

<script>
const titles = { dashboard:'Dashboard', bookings:'Semua Booking', payments:'Verifikasi Pembayaran' };

function showSection(s) {
  document.querySelectorAll('.section').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('[id^="nav-"]').forEach(el => el.classList.remove('active'));
  document.getElementById('section-' + s).classList.add('active');
  document.getElementById('section-' + s).classList.add('fade-in');
  if (document.getElementById('nav-' + s)) document.getElementById('nav-' + s).classList.add('active');
  if (document.getElementById('topbar-title')) document.getElementById('topbar-title').textContent = titles[s] || s;
}

function filterBookings() {
  const q  = document.getElementById('search-booking').value.toLowerCase();
  const st = document.getElementById('filter-status').value;
  document.querySelectorAll('.booking-row').forEach(row => {
    const matchQ = !q || row.dataset.name.includes(q) || row.dataset.code.includes(q);
    const matchS = !st || row.dataset.status === st;
    row.style.display = matchQ && matchS ? '' : 'none';
  });
}
</script>
</body>
</html>