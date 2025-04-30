<?php
session_start();
require_once __DIR__ . '/auth/config.php';
require_once __DIR__ . '/auth/users.php';

if (!isset($_SESSION['loggedin'])) {
    header("Location: auth/login.php");
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

// Ambil data user
$data = $user->getUserData($currentUsername);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Akun</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Chewy&family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body class="font-[poppins] bg-gray-100 px-8 py-16 flex justify-center">
    <main class="bg-white shadow-md rounded-lg p-8 max-w-lg w-full">
        <h1 class="text-3xl font-bold text-center text-purple-700 mb-6">Profil Saya</h1> 

        <?php if (!empty($success)): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4"><?= $success ?></div>
        <?php elseif (!empty($error)): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label for="new_username" class="block font-medium text-gray-700">Username:</label>
                <input type="text" id="new_username" name="new_username" value="<?= htmlspecialchars($data['username']) ?>" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
            </div>
            <div>
                <label for="new_password" class="block font-medium text-gray-700">Password Baru:</label>
                <input type="password" id="new_password" name="new_password" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
            </div>
            <div class="text-center pt-4">
                <button type="submit" class="bg-purple-500 text-white px-6 py-2 mb-2 rounded-lg hover:bg-purple-600 transition">Simpan Perubahan</button>
            </div>
        </form>

        <div class="text-center pt-4">
            <a href="index.php" class="text-purple-600 hover:underline mr-4">Beranda</a>
            <a href="auth/logout.php" class="text-red-500 hover:underline">Logout</a>
        </div>
    </main>
</body>
</html>