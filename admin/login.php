<?php
// HARUS DI BARIS PALING ATAS — sebelum DOCTYPE
require_once '../config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    } else {
        $error = 'Harap isi semua field.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — BookEase</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#060614;--bg2:#0c0c24;--bg3:#10103a;
  --card:#111130;--card2:#161645;
  --border:rgba(100,100,255,0.18);--border2:rgba(100,100,255,0.35);
  --blue:#4040ff;--blue2:#6666ff;--blue-glow:rgba(64,64,255,0.4);
  --text:#e8e8ff;--text2:#9090cc;--text3:#5555aa;
  --red:#ff4466;--green:#00e5a0;
}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;overflow:hidden;}
body::before{content:'';position:fixed;top:-200px;left:-200px;width:600px;height:600px;border-radius:50%;background:radial-gradient(circle,rgba(64,64,255,0.12),transparent 70%);pointer-events:none;}
body::after{content:'';position:fixed;bottom:-200px;right:-200px;width:500px;height:500px;border-radius:50%;background:radial-gradient(circle,rgba(100,100,255,0.08),transparent 70%);pointer-events:none;}
.login-wrap{width:100%;max-width:400px;position:relative;z-index:1}
.logo-area{text-align:center;margin-bottom:32px}
.logo-icon{width:56px;height:56px;border-radius:16px;background:linear-gradient(135deg,var(--blue),var(--blue2));display:flex;align-items:center;justify-content:center;margin:0 auto 16px;box-shadow:0 0 32px var(--blue-glow);}
.logo-title{font-size:1.4rem;font-weight:900;color:#fff;letter-spacing:-0.02em;margin-bottom:4px}
.logo-title span{color:var(--blue2)}
.logo-sub{font-size:0.85rem;color:var(--text3);font-weight:500}
.card{background:var(--card);border:1px solid var(--border);border-radius:20px;padding:32px;box-shadow:0 8px 40px rgba(0,0,0,0.4);}
.alert-error{background:rgba(255,68,102,0.1);border:1px solid rgba(255,68,102,0.3);border-radius:12px;padding:12px 16px;font-size:0.82rem;font-weight:600;color:var(--red);margin-bottom:20px;display:flex;align-items:center;gap:8px;}
.form-group{margin-bottom:18px}
.form-label{display:block;font-size:0.78rem;font-weight:700;color:var(--text2);margin-bottom:8px;letter-spacing:0.03em;}
.form-input{width:100%;padding:13px 16px;background:var(--bg2);border:1px solid var(--border);border-radius:12px;font-size:0.88rem;font-weight:500;font-family:'Inter',sans-serif;color:var(--text);transition:all 0.2s;}
.form-input:focus{outline:none;border-color:var(--blue2);box-shadow:0 0 0 4px rgba(64,64,255,0.15)}
.form-input::placeholder{color:var(--text3)}
.btn-login{width:100%;padding:14px;background:linear-gradient(135deg,var(--blue),var(--blue2));color:#fff;border:none;border-radius:12px;font-size:0.9rem;font-weight:700;font-family:'Inter',sans-serif;cursor:pointer;transition:all 0.2s;box-shadow:0 4px 20px var(--blue-glow);margin-top:8px;display:flex;align-items:center;justify-content:center;gap:8px;}
.btn-login:hover{transform:translateY(-2px);box-shadow:0 8px 32px var(--blue-glow);filter:brightness(1.1)}
.info-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(64,64,255,0.1);border:1px solid var(--border2);padding:4px 12px;border-radius:99px;font-size:0.68rem;font-weight:700;color:var(--blue2);letter-spacing:0.08em;text-transform:uppercase;margin-bottom:20px;}
.info-dot{width:6px;height:6px;border-radius:50%;background:var(--blue2);animation:blink 1.4s infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:0.3}}
.security-note{display:flex;align-items:center;gap:8px;background:rgba(0,229,160,0.06);border:1px solid rgba(0,229,160,0.15);border-radius:10px;padding:10px 14px;margin-top:16px;}
.security-note span{font-size:0.75rem;color:rgba(0,229,160,0.7);font-weight:500}
.back-link{display:flex;align-items:center;justify-content:center;gap:6px;font-size:0.8rem;color:var(--text3);font-weight:500;text-decoration:none;transition:color 0.2s;margin-top:20px;}
.back-link:hover{color:var(--blue2)}
</style>
</head>
<body>

<?php
require_once '../config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    } else {
        $error = 'Harap isi semua field.';
    }
}
?>

<div class="login-wrap">

  <div class="logo-area">
    <div class="logo-icon">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
        <rect x="2" y="6" width="20" height="16" rx="3" stroke="#fff" stroke-width="1.8"/>
        <path d="M8 2v6M16 2v6M2 11h20" stroke="#fff" stroke-width="1.8" stroke-linecap="round"/>
      </svg>
    </div>
    <div class="logo-title">Book<span>Ease</span></div>
    <div class="logo-sub">Panel Administrator</div>
  </div>

  <div class="card">
    <div style="text-align:center;margin-bottom:24px">
      <div class="info-badge"><span class="info-dot"></span>Admin Access Only</div>
      <div style="font-size:1rem;font-weight:800;color:#fff;margin-bottom:4px">Selamat Datang Kembali</div>
      <div style="font-size:0.8rem;color:var(--text3)">Masuk untuk mengelola sistem reservasi</div>
    </div>

    <?php if ($error): ?>
      <div class="alert-error">
        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
          <circle cx="7" cy="7" r="6" stroke="currentColor" stroke-width="1.5"/>
          <path d="M7 4v3M7 9v1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label class="form-label">Username</label>
        <input type="text" name="username" placeholder="Masukkan username" required
               class="form-input" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" placeholder="••••••••" required class="form-input">
      </div>
      <button type="submit" class="btn-login">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
          <path d="M6 2H3a1 1 0 00-1 1v10a1 1 0 001 1h3M11 11l3-3-3-3M14 8H6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Masuk ke Dashboard
      </button>
    </form>

    <div class="security-note">
      <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
        <path d="M6.5 1L2 3v4c0 2.5 2 4.5 4.5 5C9 11.5 11 9.5 11 7V3L6.5 1z" stroke="#00e5a0" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M4.5 6.5l1.5 1.5 2.5-2.5" stroke="#00e5a0" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      <span>Koneksi aman · Sesi akan berakhir otomatis</span>
    </div>
  </div>

  <a href="../index.php" class="back-link">
    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
      <path d="M9 11L5 7l4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    Kembali ke halaman booking
  </a>

</div>
</body>
</html>