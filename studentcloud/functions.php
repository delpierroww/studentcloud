<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
    session_start();
}

function e(?string $value): string { return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function redirect(string $to): never { header('Location: ' . $to); exit; }
function csrf_token(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function csrf_check(): void { if ($_SERVER['REQUEST_METHOD'] === 'POST') { $v = $_POST['csrf'] ?? ''; if (!is_string($v) || !hash_equals(csrf_token(), $v)) { http_response_code(419); exit('Sesi formulir kedaluwarsa. Muat ulang halaman.'); } } }
function current_user(): ?array { return $_SESSION['user'] ?? null; }
function require_login(): array { $u = current_user(); if (!$u) redirect('index.php'); return $u; }
function require_admin(): array { $u = require_login(); if (($u['role'] ?? '') !== 'admin') { http_response_code(403); exit('Akses khusus admin.'); } return $u; }
function flash(string $message, string $type='success'): void { $_SESSION['flash'] = ['message'=>$message, 'type'=>$type]; }
function take_flash(): ?array { $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }
function used_bytes(int $userId): int { $s = db()->prepare('SELECT COALESCE(SUM(size_bytes),0) FROM files WHERE user_id=?'); $s->execute([$userId]); return (int)$s->fetchColumn(); }
function format_bytes(int $n): string { if ($n < 1024) return $n . ' B'; $u=['KB','MB','GB','TB']; $i=-1; do {$n/=1024; $i++;} while($n>=1024 && $i<count($u)-1); return number_format($n,1,',','.') . ' ' . $u[$i]; }
function encrypt_file(string $plain): string { $key = hash('sha256', APP_KEY, true); $iv = random_bytes(12); $tag=''; $cipher = openssl_encrypt($plain, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag); if ($cipher === false) throw new RuntimeException('Enkripsi file gagal.'); return "SC01" . $iv . $tag . $cipher; }
function decrypt_file(string $blob): string { if (strlen($blob)<32 || substr($blob,0,4)!=='SC01') throw new RuntimeException('Format file terenkripsi tidak valid.'); $key=hash('sha256',APP_KEY,true); $iv=substr($blob,4,12); $tag=substr($blob,16,16); $cipher=substr($blob,32); $plain=openssl_decrypt($cipher,'aes-256-gcm',$key,OPENSSL_RAW_DATA,$iv,$tag); if ($plain===false) throw new RuntimeException('Dekripsi gagal. Periksa APP_KEY.'); return $plain; }
function safe_filename(string $name): string { $name = preg_replace('/[\\x00-\\x1F\\x7F]/u', '', basename($name)); return $name !== '' ? $name : 'file'; }
function page_header(string $title): void { $u=current_user(); ?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?=e($title)?> - <?=e(APP_NAME)?></title><link rel="stylesheet" href="assets/style.css"></head><body><header class="topbar"><a class="brand" href="dashboard.php">☁ StudentCloud</a><nav><?php if($u): ?><a href="dashboard.php">Dashboard</a><?php if($u['role']==='admin'): ?><a href="admin.php">Admin</a><?php endif; ?><span><?=e($u['name'])?></span><a href="logout.php">Keluar</a><?php endif; ?></nav></header><main class="container">
<?php $f=take_flash(); if($f): ?><div class="alert <?=e($f['type'])?>"><?=e($f['message'])?></div><?php endif; ?>
<?php }
function page_footer(): void { ?></main><footer>StudentCloud · Penyimpanan file untuk mahasiswa</footer></body></html><?php }
