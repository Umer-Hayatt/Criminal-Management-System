<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "criminal_record_db");
if (!$conn) {
 die("<div style='font-family:sans-serif;padding:40px;text-align:center;'>
 <h2 style='color:red'> Database Connection Failed</h2>
 <p>" . mysqli_connect_error() . "</p>
 <p>Make sure XAMPP MySQL is running!</p>
 </div>");
}

// ── Auth Helpers ──────────────────────────────
function is_logged_in(): bool {
 return isset($_SESSION['user_id']);
}

function require_login(): void {
 if (!is_logged_in()) {
  header('Location: login.php');
  exit();
 }
}

function can(string $action): bool {
 $role = $_SESSION['role'] ?? 'viewer';
 return match($action) {
  'delete' => $role === 'admin',
  'edit'   => in_array($role, ['admin','officer']),
  'view'   => true,
  'admin'  => $role === 'admin',
  default  => false
 };
}

function current_user(): array {
 return [
  'user_id'  => $_SESSION['user_id'] ?? 0,
  'username' => $_SESSION['username'] ?? '',
  'role'     => $_SESSION['role'] ?? 'viewer',
 ];
}

// ── Flash Messages ───────────────────────────
function set_flash(string $type, string $msg): void {
 $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function get_flash(): ?array {
 if (isset($_SESSION['flash'])) {
  $flash = $_SESSION['flash'];
  unset($_SESSION['flash']);
  return $flash;
 }
 return null;
}

// ── Activity Logging ─────────────────────────
function log_activity(string $action, string $entity_type,
                      int $entity_id = 0, string $detail = ''): void {
 global $conn;
 $uid = $_SESSION['user_id'] ?? 0;
 $ip  = $_SERVER['REMOTE_ADDR'] ?? '';
 $action = mysqli_real_escape_string($conn, $action);
 $entity_type = mysqli_real_escape_string($conn, $entity_type);
 $detail = mysqli_real_escape_string($conn, $detail);
 $ip = mysqli_real_escape_string($conn, $ip);
 mysqli_query($conn,
  "INSERT INTO Activity_Log(user_id,action,entity_type,entity_id,detail,ip_address)
   VALUES($uid,'$action','$entity_type',$entity_id,'$detail','$ip')");
}

// ── Date Formatting ──────────────────────────
function fmt_date(?string $date): string {
 if (!$date || $date === '0000-00-00') return '—';
 return date('d M Y', strtotime($date));
}

// ── Escape Helper ────────────────────────────
function esc($conn, $v) {
 return mysqli_real_escape_string($conn, trim($v));
}

// ── Avatar HTML ───────────────────────────────
// Returns <img> if photo exists, else initials circle
function avatar_html(string $first, string $last, ?string $photo, int $size=40, string $extra_class=''): string {
 $initials = strtoupper(substr($first,0,1).substr($last,0,1));
 // pick color from name hash
 $colors = ['#2f81f7','#3fb950','#d29922','#f85149','#8b5cf6','#ec4899','#06b6d4'];
 $color  = $colors[abs(crc32($first.$last)) % count($colors)];
 $r = intval($size); $fs = intval($size*0.38);
 if ($photo && file_exists($photo)) {
  return "<img src=\"".htmlspecialchars($photo)."\" style=\"width:{$r}px;height:{$r}px;border-radius:50%;object-fit:cover;border:2px solid var(--border);flex-shrink:0\" class=\"{$extra_class}\" onerror=\"this.style.display='none';this.nextSibling.style.display='flex'\" alt=\"\"><span style=\"display:none;width:{$r}px;height:{$r}px;border-radius:50%;background:{$color};color:#fff;font-size:{$fs}px;font-weight:700;align-items:center;justify-content:center;flex-shrink:0;border:2px solid var(--border)\">{$initials}</span>";
 }
 return "<span style=\"display:inline-flex;width:{$r}px;height:{$r}px;border-radius:50%;background:{$color};color:#fff;font-size:{$fs}px;font-weight:700;align-items:center;justify-content:center;flex-shrink:0;border:2px solid var(--border)\" class=\"{$extra_class}\">{$initials}</span>";
}

// ── AJAX JSON Response ────────────────────────
function json_response(bool $ok, string $msg='', array $data=[]): never {
 header('Content-Type: application/json');
 echo json_encode(['ok'=>$ok,'msg'=>$msg,'data'=>$data]);
 exit();
}

// ── Convenience DB row fetch ──────────────────
function db_row($conn, string $sql): ?array {
 $r = mysqli_query($conn, $sql);
 return $r ? mysqli_fetch_assoc($r) : null;
}
?>
