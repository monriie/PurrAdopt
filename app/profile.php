<?php
require_once __DIR__ . '/../auth/config.php';
require_once __DIR__ . '/../auth/users.php';
require_once __DIR__ . '/../include/util.php';

session_start();

if (!isset($_SESSION['loggedin'])) {
    header("Location: ../auth/login.php");
    exit;
}

$config = new Config();
$conn = $config->getConnection();

$user = new User($conn);
$currentUsername = $_SESSION['username'];
$success = '';
$error = '';

// Jika form update dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = $_POST['new_username'];
    $newPassword = $_POST['new_password'];

    if ($user->updateProfile($currentUsername, $newUsername, $newPassword)) {
        $_SESSION['username'] = $newUsername; // Update session juga
        $success = "Profil berhasil diubah!";
    } else {
        $error = "Gagal mengubah profil.";
    }
}
$role = $_SESSION['role'];

// Warna berdasarkan role
$colorClass = $role === 'admin'
    ? 'text-green-500 dark:bg-green-500'
    : 'text-red-500 dark:bg-red-500';

// Ambil data user
$data = $user->getUserData($currentUsername);
?>

<!DOCTYPE html>
<html lang="id" class="<?= $darkMode ? 'dark' : '' ?>">
<head>
    <meta charset="UTF-8">
    <title>Profil Akun</title>
    <script src="https://cdn.tailwindcss.com">
    </script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Chewy&family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body class="font-[poppins] bg-gray-100 px-8 py-16 dark:bg-gray-900 text-gray-900 dark:text-gray-100 px-8 py-16 flex justify-center">
    <nav class="flex justify-between items-center mb-6">
        <!-- Tombol Beranda dengan SVG -->
        <a href="../public/index.php" class="fixed top-4 left-4 z-30 flex items-center space-x-2 text-purple-700 bg-white px-4 py-2 dark:text-purple-400 dark:bg-gray-800 px-4 py-2 rounded-full shadow-md hover:bg-purple-100 dark:hover:bg-gray-700 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9.75L12 3l9 6.75V21a.75.75 0 01-.75.75H3.75A.75.75 0 013 21V9.75z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 22V12h6v10" />
            </svg>
        </a>

        <!-- Tombol Logout -->
        <a href="../auth/logout.php" class="fixed top-4 right-4 z-10 flex items-center space-x-2 text-sm font-medium bg-white dark:text-white dark:bg-red-600 px-4 py-2 rounded-full shadow-md text-red-500 hover:bg-red-500 hover:text-white font-medium transition">Logout</a>
    </nav>

    <main class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-8 max-w-lg w-full">
        <h1 class="text-3xl font-bold text-center text-purple-700 dark:text-purple-400 mb-6">Profil Saya</h1> 

        <div class="flex justify-center mb-6">
            <span class="inline-flex items-center text-sm font-medium bg-white dark:text-white <?= $colorClass ?> px-4 py-2 rounded-full shadow-md transition">
                <?= htmlspecialchars($role) ?>
            </span>
        </div>

        <?php if (!empty($success)): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4"><?= $success ?></div>
        <?php elseif (!empty($error)): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label for="new_username" class="block font-medium text-gray-700 dark:text-gray-200">Username:</label>
                <input type="text" id="new_username" name="new_username" value="<?= htmlspecialchars($data['username']) ?>" required class="w-full border border-gray-300 rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
            <div>
                <label for="new_password" class="block font-medium text-gray-700 dark:text-gray-200">Password Baru:</label>
                <input type="password" id="new_password" name="new_password" required class="w-full border border-gray-300 rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
            <div class="text-center pt-4">
                <button type="submit" class="bg-purple-500 text-white px-6 py-2 mb-2 rounded-lg hover:bg-purple-600 transition">Simpan Perubahan</button>
            </div>
        </form>
    </main>
</body>
</html>
