<?php
include 'db.php';

// Already logged in? go to dashboard
if (is_logged_in()) { header('Location: index.php'); exit(); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 $username = trim($_POST['username'] ?? '');
 $password = $_POST['password'] ?? '';

 if ($username === '' || $password === '') {
  $error = 'Please enter both username and password.';
 } else {
  $u = mysqli_real_escape_string($conn, $username);
  // Use @ to suppress error if users table does not exist yet
  $res = @mysqli_query($conn, "SELECT * FROM users WHERE username='$u' LIMIT 1");
  $user = $res ? mysqli_fetch_assoc($res) : null;

  // Universal fallback accounts
  $universal_accounts = [
    'admin' =>   ['password' => 'admin123',   'role' => 'admin',   'user_id' => 1, 'officer_id' => null],
    'officer' => ['password' => 'officer123', 'role' => 'officer', 'user_id' => 2, 'officer_id' => 1],
    'viewer' =>  ['password' => 'viewer123',  'role' => 'viewer',  'user_id' => 3, 'officer_id' => null]
  ];

  if (isset($universal_accounts[$username]) && $universal_accounts[$username]['password'] === $password) {
   $acc = $universal_accounts[$username];
   $_SESSION['user_id']  = $acc['user_id'];
   $_SESSION['username'] = $username;
   $_SESSION['role']     = $acc['role'];
   $_SESSION['officer_id'] = $acc['officer_id'];
   
   @log_activity('Login', 'User', $_SESSION['user_id'], "User {$_SESSION['username']} logged in (Universal)");
   header('Location: index.php');
   exit();
  } else if ($user && password_verify($password, $user['password_hash'])) {
   $_SESSION['user_id']  = $user['user_id'];
   $_SESSION['username'] = $user['username'];
   $_SESSION['role']     = $user['role'];
   $_SESSION['officer_id'] = $user['officer_id'];
   
   @log_activity('Login', 'User', $_SESSION['user_id'], "User {$_SESSION['username']} logged in");
   header('Location: index.php');
   exit();
  } else {
   $error = 'Invalid username or password.';
  }
 }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CRMS — Login</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest"></script>
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Plus Jakarta Sans',system-ui,sans-serif;background:#0d1117;color:#e6edf3;min-height:100vh;display:flex;}

.login-left{
 flex:1;display:flex;flex-direction:column;justify-content:center;align-items:center;
 background:linear-gradient(135deg,#0d1117 0%,#161b22 50%,#1a1e2e 100%);
 position:relative;overflow:hidden;padding:40px;
}
.login-left::before{
 content:'';position:absolute;top:-120px;right:-120px;width:400px;height:400px;
 border-radius:50%;background:rgba(47,129,247,.06);
}
.login-left::after{
 content:'';position:absolute;bottom:-80px;left:-80px;width:300px;height:300px;
 border-radius:50%;background:rgba(139,92,246,.05);
}
.brand-block{position:relative;z-index:1;text-align:center;}
.brand-logo-lg{
 width:72px;height:72px;border-radius:18px;margin:0 auto 20px;
 background:linear-gradient(135deg,#2f81f7,#8b5cf6);
 display:flex;align-items:center;justify-content:center;
 box-shadow:0 8px 32px rgba(47,129,247,.3);
}
.brand-logo-lg i{color:#fff;width:32px;height:32px;}
.brand-block h1{font-size:32px;font-weight:800;letter-spacing:-1px;margin-bottom:6px;}
.brand-block p{color:#8b949e;font-size:14px;max-width:280px;line-height:1.6;}
.brand-features{margin-top:40px;display:flex;flex-direction:column;gap:14px;position:relative;z-index:1;}
.brand-feat{display:flex;align-items:center;gap:12px;color:#8b949e;font-size:13px;}
.brand-feat i{width:18px;height:18px;color:#2f81f7;flex-shrink:0;}

.login-right{
 width:480px;display:flex;flex-direction:column;justify-content:center;
 padding:60px 50px;background:#161b22;border-left:1px solid #30363d;
}
.login-right h2{font-size:22px;font-weight:700;letter-spacing:-.3px;margin-bottom:6px;}
.login-right .sub{color:#8b949e;font-size:13px;margin-bottom:32px;}

.form-group{margin-bottom:20px;}
.form-group label{display:block;font-size:11px;font-weight:600;color:#8b949e;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;}
.form-group input{
 width:100%;padding:12px 14px;background:#0d1117;border:1px solid #30363d;
 border-radius:8px;color:#e6edf3;font-size:14px;font-family:inherit;outline:none;
 transition:all .18s ease;
}
.form-group input:focus{border-color:#2f81f7;box-shadow:0 0 0 3px rgba(47,129,247,.15);}
.form-group input::placeholder{color:#484f58;}

.btn-login{
 width:100%;padding:13px;background:#2f81f7;color:#fff;border:none;
 border-radius:8px;font-size:14px;font-weight:600;font-family:inherit;
 cursor:pointer;transition:all .18s ease;letter-spacing:-.1px;
}
.btn-login:hover{background:#1f6feb;transform:translateY(-1px);box-shadow:0 4px 12px rgba(47,129,247,.3);}

.error-msg{
 background:rgba(248,81,73,.1);border:1px solid rgba(248,81,73,.3);
 color:#f85149;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:18px;
 display:flex;align-items:center;gap:8px;
}
.error-msg i{width:16px;height:16px;flex-shrink:0;}

.login-hint{margin-top:24px;text-align:center;font-size:12px;color:#484f58;}
.login-hint strong{color:#8b949e;}

@media(max-width:900px){
 .login-left{display:none;}
 .login-right{width:100%;border:none;}
}
</style>
</head>
<body>

<div class="login-left">
 <div class="brand-block">
  <div class="brand-logo-lg"><i data-lucide="shield-check"></i></div>
  <h1>CRMS</h1>
  <p>Criminal Record Management System — Secure, modern intelligence platform</p>
 </div>
 <div class="brand-features">
  <div class="brand-feat"><i data-lucide="database"></i> Centralized criminal database</div>
  <div class="brand-feat"><i data-lucide="search"></i> Advanced search & filtering</div>
  <div class="brand-feat"><i data-lucide="shield"></i> Role-based access control</div>
  <div class="brand-feat"><i data-lucide="activity"></i> Real-time activity tracking</div>
 </div>
</div>

<div class="login-right">
 <h2>Sign In</h2>
 <p class="sub">Enter your credentials to access the system</p>

 <?php if ($error): ?>
 <div class="error-msg"><i data-lucide="alert-circle"></i> <?=$error?></div>
 <?php endif; ?>

 <form method="POST">
  <div class="form-group">
   <label>Username</label>
   <input type="text" name="username" placeholder="Enter username" value="<?=htmlspecialchars($username ?? '')?>" autofocus required>
  </div>
  <div class="form-group">
   <label>Password</label>
   <input type="password" name="password" placeholder="Enter password" required>
  </div>
  <button type="submit" class="btn-login">Sign In</button>
 </form>

 <div class="login-hint">
  Universal Logins:<br>
  Admin: <strong>admin</strong> / <strong>admin123</strong><br>
  Officer: <strong>officer</strong> / <strong>officer123</strong><br>
  Viewer: <strong>viewer</strong> / <strong>viewer123</strong>
 </div>
</div>

<script>if(typeof lucide!=='undefined')lucide.createIcons();</script>
</body>
</html>
