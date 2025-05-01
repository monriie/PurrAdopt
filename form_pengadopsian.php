<?php
require_once __DIR__ . '/auth/config.php';
require_once __DIR__ . '/auth/users.php';
require 'util.php';

$config = new Config();
$conn = $config->getConnection();

if (!$conn) {
    die("Database connection failed");
}

// search kucing berdasarkan jenis kucing
$cat_name = isset($_GET['jenis_kucing']) ? htmlspecialchars($_GET['jenis_kucing']) : '';
$cat_id = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : 0;

// verifikasi berdasarkan id_kucing
if ($cat_id > 0) {
    $sql = "SELECT name FROM cats WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $cat_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $cat_name = $row['name'];
        }
        $stmt->close();
    }
}

// Ambil status dark mode
$darkMode = isset($_COOKIE['dark_mode']) && $_COOKIE['dark_mode'] === 'true';
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
        <h1 class="text-3xl font-bold text-center text-purple-700 dark:text-purple-400 mb-6">Form Pengadopsian</h1>
        
        <!-- pesan validasi jika form sudah tersubmit -->
        <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                Form pengadopsian berhasil dikirim! Kami akan menghubungi Anda segera.
            </div>
        <?php endif; ?>
        
        <form action="handle_form.php" method="POST" class="space-y-4">
            <input type="hidden" name="cat_id" value="<?php echo $cat_id; ?>">
            
            <div>
                <label for="nama" class="block font-medium text-gray-700 dark:text-gray-200">Nama:</label>
                <input type="text" id="nama" name="nama" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
            
            <div>
                <label for="email" class="block font-medium text-gray-700 dark:text-gray-200">Email:</label>
                <input type="email" id="email" name="email" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
            
            <div>
                <label for="no_hp" class="block font-medium text-gray-700 dark:text-gray-200">Nomor HP:</label>
                <input type="number" id="no_hp" name="no_hp" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
            
            <div>
                <label for="jenis_kelamin" class="block font-medium text-gray-700 dark:text-gray-200">Jenis Kelamin:</label>
                <select id="jenis_kelamin" name="jenis_kelamin" required class="w-full border border-gray-300 rounded-lg px-3 py-2 appearance-none focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="Laki-laki">Laki-laki</option>
                    <option value="Perempuan">Perempuan</option>
                </select>
            </div>
            
            <div>
                <label for="jenis_kucing" class="block font-medium text-gray-700 dark:text-gray-200">Jenis Kucing:</label>
                <input type="text" id="jenis_kucing" name="jenis_kucing" readonly value="<?php echo $cat_name; ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
            
            <div class="text-center pt-4">
                <button type="submit" class="bg-purple-500 text-white px-6 py-2 rounded-lg hover:bg-purple-600 transition duration-300">Kirim</button>
                <a href="index.php" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition duration-300 ml-2">Kembali</a>
            </div>
        </form>
    </main>
</body>
</html>