<?php
require_once __DIR__ . '/auth/config.php';
require_once __DIR__ . '/auth/users.php';
require 'util.php';

$config = new Config();
$conn = $config->getConnection();

if (!$conn) {
    die("Database connection failed");
}

// Fungsi clean inputan 
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

//  ngecheck id
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];

// ngammbil data adopsi
$sql = "SELECT adoptions.*, cats.name AS cat_name FROM adoptions JOIN cats ON adoptions.cat_id = cats.id WHERE adoptions.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$adoption = $result->fetch_assoc();
$stmt->close();

if (!$adoption) {
    header("Location: index.php");
    exit;
}

// form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean_input($_POST['name']);
    $email = clean_input($_POST['email']);
    $phone = clean_input($_POST['phone']);
    $gender = clean_input($_POST['gender']);

    $sql = "UPDATE adoptions SET name=?, email=?, phone=?, gender=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $name, $email, $phone, $gender, $id);

    if ($stmt->execute()) {
        header("Location: index.php?id=" . $id);
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
$darkMode = isset($_COOKIE['dark_mode']) && $_COOKIE['dark_mode'] === 'true';
?>

<!DOCTYPE html>
<html lang="id" class="scroll-smooth <?= $darkMode ? 'dark' : '' ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Mengupdate Struk Adopsi</title>
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
    <body class="font-[poppins] bg-gray-100 px-8 py-16 dark:bg-gray-900 dark:text-gray-100 flex justify-center">
        <main class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-8 max-w-lg w-full">
            <h1 class="text-3xl font-bold text-center text-purple-700 dark:text-purple-400 mb-6">Mengupdate Struk Adopsi</h1>

            <form method="POST" class="space-y-4">
                <div>
                    <label class="block font-medium text-gray-700 dark:text-gray-200">Nama :</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($adoption['name']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                </div>

                <div>
                    <label class="block font-medium text-gray-700 dark:text-gray-200">Email :</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($adoption['email']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                </div>

                <div>
                    <label class="block font-medium text-gray-700 dark:text-gray-200">No HP :</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($adoption['phone']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                </div>

                <div>
                    <label class="block font-medium text-gray-700 dark:text-gray-200">Jenis Kelamin :</label>
                    <select name="gender" class="w-full border border-gray-300 rounded-lg px-3 py-2  appearance-none focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="Laki-laki" <?= $adoption['gender'] == 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="Perempuan" <?= $adoption['gender'] == 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-gray-700 dark:text-gray-200">Jenis Kucing:</label>
                    <input type="text" readonly value="<?= htmlspecialchars($adoption['cat_name']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div class="text-center pt-4">
                    <button type="submit" class="bg-purple-500 text-white px-6 py-2 rounded-lg hover:bg-purple-600 transition duration-300">Simpan</button>
                    <a href="index.php?id=<?= $adoption['id'] ?>" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition duration-300 ml-2">Kembali</a>
                </div>
            </form>
        </main>
    </body>
</html>
