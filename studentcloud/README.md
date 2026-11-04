# StudentCloud — PHP + XAMPP

Versi ini dibuat untuk tugas yang mewajibkan XAMPP. Stack: PHP Native, Apache, MySQL/MariaDB, PDO, HTML/CSS. Tidak memakai Docker atau Python.

## Fitur
- Registrasi mahasiswa dan login dengan `password_hash()` / `password_verify()`.
- Dashboard mahasiswa, pembuatan folder, upload dan download file.
- Enkripsi file di server menggunakan AES-256-GCM (OpenSSL).
- Kuota penyimpanan default 100 MB per mahasiswa; batas upload 20 MB per file.
- Link berbagi file dengan masa berlaku 7 hari.
- Dashboard admin, daftar pengguna, dan pengaturan kuota mahasiswa.
- Prepared statements PDO, session regeneration, dan token CSRF pada form POST.

## Instalasi Windows + XAMPP
1. Instal dan buka XAMPP Control Panel.
2. Jalankan **Apache** dan **MySQL**.
3. Ekstrak folder `studentcloud_php` ke `C:\xampp\htdocs\studentcloud`.
4. Buka `http://localhost/phpmyadmin`.
5. Pilih tab **Import**, pilih file `database.sql` dari folder project, lalu klik **Import/Go**. Script akan membuat database `studentcloud` beserta tabel.
6. Buka `config.php`. Default XAMPP biasanya `DB_USER='root'`, `DB_PASS=''`. Sesuaikan jika MySQL kamu memakai password.
7. Ganti `APP_KEY` dengan string acak panjang. Contoh untuk membuatnya: buka PowerShell dan jalankan `php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"` jika PHP ada di PATH; atau gunakan PHP Shell dari folder `C:\xampp\php` dengan `php.exe -r "echo bin2hex(random_bytes(32)), PHP_EOL;"`. Salin hasilnya ke `APP_KEY` di config.php. Simpan cadangan kunci dengan aman. Jangan mengganti APP_KEY setelah ada file terenkripsi kecuali kamu punya rencana migrasi/dekripsi.
8. Pastikan folder `storage` ada. File terenkripsi disimpan di sana dan akses HTTP langsung diblokir oleh `.htaccess`.
9. Buka `http://localhost/studentcloud`.
10. Daftarkan akun melalui **Daftar mahasiswa**.

## Membuat akun admin pertama
Setelah registrasi akun yang akan dijadikan admin, buka phpMyAdmin → database `studentcloud` → tab SQL, jalankan query berikut dengan email akunmu:

```sql
UPDATE users SET role='admin' WHERE email='admin@kampus.ac.id';
```

Ganti email contoh tersebut dengan email yang benar. Logout lalu login kembali. Jangan pernah menyediakan pilihan role admin di halaman registrasi publik.

## Menggunakan dari komputer lain di LAN
1. Pastikan komputer server dan klien berada di jaringan kampus yang sama dan penggunaan server telah diizinkan.
2. Jalankan `ipconfig` di komputer yang menjalankan XAMPP dan catat IPv4 Address.
3. Dari perangkat lain buka `http://IP-KOMPUTER/studentcloud`.
4. Atur Windows Firewall agar Apache diizinkan hanya pada jaringan kampus yang disetujui. Jangan membuka aplikasi ke internet publik tanpa HTTPS dan pengamanan tambahan.

## Konfigurasi PHP yang disarankan
Di `C:\xampp\php\php.ini`, pastikan ekstensi `pdo_mysql`, `openssl`, dan `fileinfo` aktif (barisnya tidak diawali `;`). Atur `upload_max_filesize=20M` dan `post_max_size=22M`, lalu restart Apache. Batas aplikasi tetap 20 MB.

## Keamanan dan batasan MVP
- Enkripsi ini adalah **server-side encryption at rest**, bukan end-to-end encryption. Server memegang APP_KEY.
- Simpan `APP_KEY` di luar repositori/backup aman. Jika hilang atau berubah, file lama tidak bisa didekripsi.
- Link berbagi adalah bearer link: siapa pun yang memilikinya bisa mengunduh sampai kedaluwarsa.
- Untuk produksi: pindahkan storage ke luar `htdocs`, gunakan HTTPS, backup database dan storage, antivirus scanning, rate limiting, audit log, reset password/email verification, dan uji keamanan.
- Pastikan `mod_rewrite` tidak diperlukan; proteksi storage memakai `.htaccess` dan membutuhkan Apache `AllowOverride` aktif. Untuk instalasi produksi, simpan storage di luar document root.
- Jangan jalankan `DROP DATABASE` atau menghapus folder storage jika ada data penting.
