<?php
require_once __DIR__.'/functions.php';
if (current_user()) redirect('dashboard.php');
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') { csrf_check(); $email=strtolower(trim($_POST['email']??'')); $password=$_POST['password']??''; $s=db()->prepare('SELECT * FROM users WHERE email=?'); $s->execute([$email]); $u=$s->fetch(); if($u && password_verify($password,$u['password_hash'])) { session_regenerate_id(true); $_SESSION['user']=['id'=>(int)$u['id'],'name'=>$u['name'],'email'=>$u['email'],'role'=>$u['role'],'quota_bytes'=>(int)$u['quota_bytes']]; redirect('dashboard.php'); } $error='Email atau password salah.'; }
page_header('Login'); ?>
<section class="auth-card"><div class="eyebrow">CLOUD STORAGE MAHASISWA</div><h1>Selamat datang</h1><p class="muted">Masuk untuk mengelola file dan dokumen kuliah.</p><?php if($error): ?><div class="alert error"><?=e($error)?></div><?php endif; ?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><label>Email</label><input type="email" name="email" required autocomplete="username" placeholder="nama@email.com"><label>Password</label><input type="password" name="password" required autocomplete="current-password" placeholder="Password"><button class="primary full">Login</button></form><p class="center">Belum punya akun? <a href="register.php">Daftar mahasiswa</a></p></section>
<?php page_footer(); ?>
