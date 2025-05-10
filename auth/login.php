<?php

session_start();

require_once 'config.php';
require_once 'users.php';
require_once __DIR__ .  '/../include/util.php';

$config = new Config();
$conn = $config->getConnection();
$user = new User($conn);
$pesan = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if ($user->login($username, $password)) {
        // berhasil login
        header("Location: ../public/index.php");
        exit;
    } else {
        // gagal login
        $pesan = "Username atau password tidak cocok";
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
    <body class="font-[poppins] bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 px-8 py-16 flex justify-center">
        <main class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-8 max-w-lg w-full">
            <h1 class="text-3xl font-bold text-center text-purple-700 dark:text-purple-400 mb-6">Login</h1> 
            
            <?php if ($pesan): ?>
                <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-700">
                    <?= $pesan ?>
                </div>
            <?php endif; ?>
            
            <form action="login.php" method="POST" class="space-y-4">
                <div>
                    <label for="username" class="block font-medium text-gray-700 dark:text-gray-200">Username: </label>
                    <input type="text" id="username" name="username" required class="w-full border border-gray-300 rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
                
                <div>
                    <label for="password" class="block font-medium text-gray-700 dark:text-gray-200">Password: </label>
                    <input type="password" id="password" name="password" required class="w-full border border-gray-300 rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
                
                <div class="grid text-center pt-4">
                    <button type="submit" class="bg-purple-500 text-white px-6 py-2 mb-6 rounded-lg hover:bg-purple-600 transition duration-300">masuk</button>
                    <span>belum ada akun?<u><a href="register.php" class="text-purple-600 hover:text-purple-400 transition duration-300 ml-2">buat akun</a></u></span>
                </div>
            </form>
        </main>
    </body>
</html>