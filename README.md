# RABZ Project Structure

Projek ini dibuat dengan struktur modular agar mudah dikembangkan dan dipisahkan antara frontend dan backend.

## Struktur folder

- `public/` : halaman utama yang ditampilkan ke browser
- `assets/css/` : file stylesheet
- `assets/js/` : logika frontend JavaScript
- `api/` : endpoint backend PHP untuk login, register, logout
- `includes/` : konfigurasi, database, helper function
- `database/` : file schema database MySQL

## Cara menjalankan

1. Pastikan XAMPP sudah aktif (Apache + MySQL)
2. Import file `database/rabz_schema.sql` ke MySQL
3. Akses project di browser:
   - `http://localhost/rabz/public/index.php`

## Default database

- Database: `rabz_db`
- Username: `root`
- Password: kosong

## Catatan

Project ini masih merupakan starter structure yang siap dikembangkan lebih lanjut sesuai kebutuhan aplikasi RABZ.
