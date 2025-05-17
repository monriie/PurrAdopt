<?php

session_start();

require_once 'config.php';
require_once 'users.php';
require_once __DIR__ . '/../include/util.php';

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
        $role = 'pengadopsi';
        $stmt = $conn->prepare("INSERT INTO users (username, nama, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $nama, $password, $role);

        if ($stmt->execute()) {
            $pesan = "Registrasi berhasil. <a href='login.php'> Silahkan Kembali Login</a>";
        } else {
            $pesan = "Registrasi gagal. Silakan coba lagi.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id" class="scroll-smooth <?= $darkMode ? 'dark' : '' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengadopsian Kucing - Monica Amrina Rosyada</title>
    <script src="https://cdn.tailwindcss.com">
    </script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Chewy&family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body class="font-[poppins] px-8 py-16 flex justify-center" style="background: url('../public/Sprinkle.svg') center center / cover no-repeat;">
    <main class="relative flex w-full max-w-[1000px] h-[650px] bg-white dark:bg-gray-800 shadow-[0_5px_20px_rgba(0,0,0,0.1)] rounded-[12px] overflow-hidden">

        <div class="relative flex-2 flex items-center justify-center overflow-hidden">
            <div class="absolute w-[750px] h-[800px] bg-purple-300 rounded-full -left-1/2 -top-1/4 z-0"></div>
            <img src="../public/kucing.gif" alt="Kucing GIF" class="relative z-10 w-[500px] h-[550px] object-contain" />
        </div>

        <div class="flex-1 p-10 flex flex-col justify-center">
            <h1 class="text-3xl text-purple-700 dark:text-purple-400 mb-8 font-semibold">Register</h1> 

            <?php if ($pesan): ?>
                <div class="mb-4 p-3 rounded-lg <?= strpos($pesan, 'berhasil') !== false ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                    <?= $pesan ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST" class="flex flex-col gap-5">
                <div class="relative">
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder=" "
                        required
                        class="peer w-full px-5 pt-6 pb-2 border-2 text-gray-300 border-gray-300 rounded-lg focus:outline-none focus:border-gray-300 transition-all bg-transparent dark:border-gray-600 dark:text-white"
                    />
                    <label for="username"
                        class="absolute left-5 top-2 text-gray-500 text-sm transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-focus:top-2 peer-focus:text-sm peer-focus:text-gray-500 dark:text-gray-300">
                        Username
                    </label>
                </div>

                <div class="relative">
                    <input
                        type="text"
                        id="nama"
                        name="nama"
                        placeholder=" "
                        required
                        class="peer w-full px-5 pt-6 pb-2 border-2 text-gray-300 border-gray-300 rounded-lg focus:outline-none focus:border-gray-300 transition-all bg-transparent dark:border-gray-600 dark:text-white"
                    />
                    <label for="nama"
                        class="absolute left-5 top-2 text-gray-500 text-sm transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-focus:top-2 peer-focus:text-sm peer-focus:text-gray-500 dark:text-gray-300">
                        Nama Lengkap
                    </label>
                </div>

                <div class="relative">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder=" "
                        required
                        class="peer w-full px-5 pt-6 pb-2 border-2 text-gray-300 border-gray-300 rounded-lg focus:outline-none focus:border-gray-300 transition-all bg-transparent dark:border-gray-600 dark:text-white"
                    />
                    <label for="password"
                        class="absolute left-5 top-2 text-gray-500 text-sm transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-focus:top-2 peer-focus:text-sm peer-focus:text-gray-500 dark:text-gray-300">
                        Password
                    </label>
                </div>

                <div class="grid text-center pt-4">
                    <button type="submit" class="bg-purple-500 text-white px-6 py-2 mb-6 rounded-lg hover:bg-purple-600 transition duration-300">Daftar</button>
                    <span class="text-gray-900 dark:text-white">belum ada akun?<u><a href="login.php" class="text-purple-600 hover:text-purple-400 transition duration-300 ml-2">login sekarang</a></u></span>
                </div>
            </form>
        </div>
    </main>
</body>
</html>