<?php
require_once __DIR__ . '/../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RABZ | Login & Registrasi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <div class="navbar">
            <div class="brand"><span>RABZ</span> Platform</div>
            <div>
                <a href="index.php" class="btn btn-secondary">Home</a>
            </div>
        </div>
    </header>

    <main class="container">
        <section class="hero">
            <div class="hero-copy">
                <span class="badge">One Stop Solution</span>
                <h1>Platform layanan pelanggan dan manajemen operasional.</h1>
                <p>
                    RABZ membantu perusahaan mengelola pelanggan, driver, transaksi, dan sistem approval
                    dalam satu ekosistem yang mudah dipantau dan dikelola.
                </p>
                <div>
                    <a href="#login" class="btn btn-primary">Masuk Sekarang</a>
                </div>
            </div>

            <div class="card login-panel" id="login">
                <h2>Masuk ke akun</h2>
                <form id="loginForm" class="form-grid" method="POST">
                    <div>
                        <label for="username">Username / Email</label>
                        <input id="username" name="username" type="text" placeholder="contoh: admin" required>
                    </div>

                    <div>
                        <label for="password">Password</label>
                        <input id="password" name="password" type="password" placeholder="Masukkan password" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Login</button>
                </form>

                <div id="formNotice" class="notice"></div>
            </div>
        </section>

        <section class="features">
            <div class="card feature-box">
                <h3>Customer</h3>
                <p>Melacak transaksi, menampilkan history, dan mengakses dashboard layanan pelanggan secara mudah.</p>
            </div>

            <div class="card feature-box">
                <h3>Driver</h3>
                <p>Memantau status order, lokasi, dan persiapan operasional untuk kebutuhan delivery atau transportasi.</p>
            </div>

            <div class="card feature-box">
                <h3>Owner</h3>
                <p>Mengelola data, tarif, keuangan, theme, dan sistem approval dalam satu kontrol pusat.</p>
            </div>
        </section>

        <section class="card login-panel" style="margin-top: 26px; max-width: 620px;">
            <h2>Registrasi akun baru</h2>
            <form id="registerForm" class="form-grid" method="POST">
                <div>
                    <label for="full_name">Nama Lengkap</label>
                    <input id="full_name" name="full_name" type="text" placeholder="Nama lengkap" required>
                </div>

                <div>
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" placeholder="nama@email.com" required>
                </div>

                <div>
                    <label for="reg_username">Username</label>
                    <input id="reg_username" name="username" type="text" placeholder="username" required>
                </div>

                <div>
                    <label for="reg_password">Password</label>
                    <input id="reg_password" name="password" type="password" placeholder="Password" required>
                </div>

                <div>
                    <label for="role">Role</label>
                    <select id="role" name="role">
                        <option value="customer">Customer</option>
                        <option value="driver">Driver</option>
                        <option value="admin">Admin</option>
                        <option value="owner">Owner</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Daftar</button>
            </form>
        </section>
    </main>

    <script src="../assets/js/app.js"></script>
</body>
</html>
