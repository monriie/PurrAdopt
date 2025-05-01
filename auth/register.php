<?php

session_start();

require_once 'config.php';
require_once 'users.php';

$config = new Config();
$conn = $config->getConnection();
$user = new User($conn);
$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $nama = trim($_POST['nama']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Cek apakah username sudah dipakai
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $pesan = "Username sudah dipakai.";
    } else {
        // Simpan user baru
        $stmt = $conn->prepare("INSERT INTO users (username, nama, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $nama, $password);

        if ($stmt->execute()) {
            $pesan = "Registrasi berhasil. <a href='login.php'> Silahkan Kembali Login</a>";
        } else {
            $pesan = "Registrasi gagal. Silakan coba lagi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengadopsian Kucing - Monica Amrina Rosyada</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Chewy&family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body class="font-[poppins] bg-gray-100 px-8 py-16 flex justify-center">
    <main class="bg-white shadow-md rounded-lg p-8 max-w-lg w-full">
        <h1 class="text-3xl font-bold text-center text-purple-700 mb-6">Register</h1> 
        
        <?php if ($pesan): ?>
            <div class="mb-4 p-3 rounded-lg <?= strpos($pesan, 'berhasil') !== false ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                <?= $pesan ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" class="space-y-4">
            <div>
                <label for="username" class="block font-medium text-gray-700">Username: </label>
                <input type="text" id="username" name="username" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
            </div>
            
            <div>
                <label for="nama" class="block font-medium text-gray-700">Nama Lengkap: </label>
                <input type="text" id="nama" name="nama" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
            </div>
            
            <div>
                <label for="password" class="block font-medium text-gray-700">Password: </label>
                <input type="password" id="password" name="password" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
            </div>
            
            <div class="grid text-center pt-4">
                <button type="submit" class="bg-purple-500 text-white px-6 py-2 mb-6 rounded-lg hover:bg-purple-600 transition duration-300">Daftar</button>
                <span>sudah ada akun?<u><a href="login.php" class="text-purple-600 hover:text-purple-400 transition duration-300 ml-2">login sekarang</a></u></span>
            </div>
        </form>
    </main>
</body>
</html>