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
        <body class="font-[poppins] min-h-screen flex items-center justify-center px-6" style="background: url('../public/Sprinkle.svg') center center / cover no-repeat;">        
            <main class="relative flex w-full max-w-[1000px] h-[650px] bg-white dark:bg-gray-800 shadow-[0_5px_20px_rgba(0,0,0,0.1)] rounded-[12px] overflow-hidden">
                
                <!-- Kiri: Form -->
                <div class="flex flex-col justify-center flex-1 px-10 z-10">
                    <h1 class="text-3xl text-purple-700 dark:text-purple-400 font-semibold mb-8">Selamat Datang</h1>

                    <form action="login.php" method="POST" class="flex flex-col gap-5">
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
                            type="password"
                            id="password"
                            name="password"
                            placeholder=" "
                            required
                            class="peer w-full px-5 pt-6 pb-2 border border-gray-300 rounded-lg focus:outline-none focus:border-gray-300 transition-all bg-transparent dark:border-gray-600 dark:text-white"
                        />
                        <label for="password"
                            class="absolute left-5 top-2 text-gray-500 text-sm transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-focus:top-2 peer-focus:text-sm peer-focus:text-gray-500 dark:text-gray-300">
                            Password
                        </label>
                        </div>

                        <button type="submit" class="bg-purple-500 text-white px-6 py-2 mb-6 rounded-lg hover:bg-purple-600 transition duration-300">masuk</button>
                    </form>

                    <div class="text-center mt-6">
                        <span class="text-gray-900 dark:text-white">belum ada akun?<u><a href="register.php" class="text-purple-600 hover:text-purple-400 transition duration-300 ml-2">buat akun</a></u></span>
                    </div>
                </div>

                    <div class="relative flex-2 flex items-center justify-center overflow-hidden"></div>

                    <div class="absolute w-[750px] h-[850px] bg-purple-300 rounded-full -right-1/4 -top-1/4 z-0" ></div>

                    <img src="../public/kucing.gif" alt="Kucing GIF" class="relative w-[500px] h-[550px] object-contain" />
                </main>
            </div>
        </body>


</html>