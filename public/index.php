<?php

require_once __DIR__ . '/../auth/config.php';
require_once __DIR__ . '/../auth/users.php';
require_once __DIR__ . '/../include/util.php';

$config = new Config();
$conn = $config->getConnection();

class catManager {
    private $conn;

    public function __construct(mysqli $conn) {
        $this->conn = $conn;
    }

    public function getCats($sort = null) {
        if (!$this->conn) {
            return []; // ga terkoneksi database, maka fungsi akan mengembalikan array kosong
        }
    
        $sql = "SELECT * FROM cats"; // query utk ambil semua data dari tabel cats
        if ($sort === 'a-z') {
            $sql .= " ORDER BY name ASC";
        } elseif ($sort === 'termurah') {
            $sql .= " ORDER BY price ASC";
        } elseif ($sort === 'termahal') {
            $sql .= " ORDER BY price DESC";
        }
    
        $result = mysqli_query($this->conn, $sql);
        return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : []; // ngembaliin hasil dalam bentuk array asosiatif atau array kosong kalo query gagal
    }
    
    public function addCat($data, $file) {
        if (!$this->conn) return false;
    
        $name = Validator::cleanInput($data['cat_name']); // ambil dan bersiin nama kucing
        $description = Validator::cleanInput($data['cat_description']); //ambil dan bersiin deskripsi
        $price = (int)$data['cat_price']; //konvert harga ke int
        
        // check upload file berhasil ga nya
        if ($file['cat_image']['error'] !== 0) {
            return false; 
        }
        
        // validasi tipe file upload
        $allowed = array(
            "jpg"  => "image/jpeg",
            "jpeg" => "image/jpeg",
            "png"  => "image/png",
            "gif"  => "image/gif"
        );
        
        // get info file
        $filename = $file['cat_image']['name'];
        $filetype = $file['cat_image']['type'];
        $filesize = $file['cat_image']['size'];
        
        // extens file untuk huruf kecil semua
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        // validasi tipe file dan sizenya (max 5mb) 
        if (!array_key_exists($ext, $allowed) || 
            !in_array($filetype, $allowed) || 
            $filesize > 5 * 1024 * 1024) {
            return false;
        }
        
        // buat hashing fotonya/nama yang unik
        $newfilename = uniqid() . "." . $ext;
        $upload_dir = __DIR__ . "../uploads/";
    
        // jaga2 buat uploads directory kalo ga ada
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $destination = $upload_dir . $newfilename;
        
        // hapus foto asli dari folder uploads
        if (!move_uploaded_file($file['cat_image']['tmp_name'], $destination)) {
            return false; // tidak berhasil uploads
        }
        
        // store path nya foto hashing
        $image_path = $upload_dir . $newfilename;
        
        // masukin data inputan ke database
        $sql = "INSERT INTO cats (img, name, description, price) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
    
        $stmt->bind_param("sssi", $image_path, $name, $description, $price);
        // sssi buat parameter string string string int
        $success = $stmt->execute();
        $stmt->close();
    
        return $success;
    }
}

class adoptionManager {
    private $conn;

    public function __construct(mysqli $conn) {
        $this->conn = $conn;
    }

    public function getAdoptions() {
        if (!$this->conn) return [];
    
        // query JOIN utk info gabungan adoption + cat
        $sql = "SELECT adoptions.id, adoptions.name, adoptions.email, adoptions.phone, adoptions.gender, cats.name AS cat_name FROM adoptions JOIN cats ON adoptions.cat_id = cats.id";
        $result = mysqli_query($this->conn, $sql);
        return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
    }

    public function removeStruk($id) {
        $sql = "DELETE FROM adoptions WHERE id = ?";
        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param("i", $id); // i = indikasi paramter int
            if ($stmt->execute()) {
                return true; 
            } else {
                echo "Error: " . $stmt->error; // pesan output error 
            }
            $stmt->close();
        } else {
            echo "Prepare statement failed: " . $this->conn->error; // pesan output error
        }
        return false;
    }
}

class Validator {
    public static function cleanInput($data) {
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');//bersiin input dari user(trim=spasi awal+end, strip_tags=ilangin tag html+php, htmlspecialchars=convert karakter ke html)
    }
}

class displayCardCats {
    public static function getButtonLabel($price) {
        return $price <= 10000000 ? "Segera Adopsi" : "Adopsi Eksklusif"; // Jika harga ≤ 10.000.000 "Segera Adopsi", Jika harga > 10.000.000 "Adopsi Eksklusif"
    }
}

session_start();

// login user
if (!isset($_SESSION['loggedin'])) {
    header("Location: ../auth/login.php");
    exit;
}

$catManager = new CatManager($conn);
$adoptionManager = new AdoptionManager($conn);

// proses form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirectUrl = $_SERVER['PHP_SELF'] . '?sort=' . ($_GET['sort'] ?? 'all');
    // Tambahkan pengecekan role
    $role = $_SESSION['role'] ?? '';

    if (isset($_POST['add_cat']) && $role === 'admin') {
        header('Location: ' . $redirectUrl . '&status=' . ($catManager->addCat($_POST, $_FILES) ? 'added' : 'error'));
    } elseif (isset($_POST['remove_struk']) && $role === 'admin') {
        header('Location: ' . $redirectUrl . '&status=' . ($adoptionManager->removeStruk((int)$_POST['id']) ? 'removed' : 'error_remove'));
    }
    exit;
}

// ambil data kucing
$currentSort = $_GET['sort'] ?? 'all';
$cats = $catManager->getCats($currentSort);
$adoptions = $adoptionManager->getAdoptions();
?>


<!DOCTYPE html>
<html lang="id" class="scroll-smooth <?= $darkMode ? 'dark' : '' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Daftar Adopsi Kucing - Tugas 3 Pemweb 2 Monica Amrina Rosyada">
    <title>Tugas 4 Pemweb 2 - Monica Amrina Rosyada (09021382328144)</title>
    <script src="https://cdn.tailwindcss.com">
    </script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body class="font-[poppins] <?= $darkMode ? 'bg-gray-900' : 'bg-gray-100' ?>">
    <header class="mx-auto mt-10 py-8 max-w-7xl">
        <h1 class="text-4xl font-bold text-center text-purple-700 dark:text-purple-400">Daftar Adopsi Kucing</h1>
    </header>

    <main class="mx-auto px-4 pb-16 max-w-7xl">
        <!-- sorting -->
        <section aria-labelledby="filter-heading" class="mb-8">
            <nav class="sort_criteria flex justify-center space-x-4">
                <!--search JQuery-->
                <input type="text" id="searchInput" placeholder="Cari kucing..." class="border border-gray-300 rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">

                <a href="?sort=a-z" 
                   class="px-4 py-2 dark:bg-purple-900 bg-purple-200 text-black dark:text-white rounded-full hover:bg-purple-300 dark:hover:bg-purple-800 transition <?php echo ($currentSort === 'a-z') ? 'bg-black dark:bg-purple-600 text-white' : ''; ?>"
                   aria-current="<?php echo ($currentSort === 'a-z') ? 'page' : 'false'; ?>">
                    A-Z
                </a>
                <a href="?sort=termurah" 
                   class="px-4 py-2 dark:bg-purple-900 bg-purple-200 text-black dark:text-white rounded-full hover:bg-purple-300 dark:hover:bg-purple-800 transition <?php echo ($currentSort === 'termurah') ? 'bg-black dark:bg-purple-600 text-white' : ''; ?>"
                   aria-current="<?php echo ($currentSort === 'termurah') ? 'page' : 'false'; ?>">
                    Termurah
                </a>
                <a href="?sort=termahal" 
                   class="px-4 py-2 dark:bg-purple-900 bg-purple-200 text-black dark:text-white rounded-full hover:bg-purple-300 dark:hover:bg-purple-800 transition <?php echo ($currentSort === 'termahal') ? 'bg-black dark:bg-purple-600 text-white' : ''; ?>"
                   aria-current="<?php echo ($currentSort === 'termahal') ? 'page' : 'false'; ?>">
                    Termahal
                </a>
            </nav>
        </section>

        <!-- pembeda fitur -->
        <?php if ($_SESSION['role'] === 'admin') : ?>
            <!-- tombol tambah kucing -->
            <button id="openPopup" class="fixed top-4 right-4 z-10 flex items-center space-x-2 text-sm font-medium text-purple-700 dark:text-purple-400 bg-white dark:bg-gray-800 px-4 py-2 rounded-full shadow-md hover:bg-purple-100 dark:hover:bg-gray-700 transition" aria-label="Tambah Kucing Baru" aria-haspopup="dialog">
                Tambah Kucing
            </button>
        <?php endif; ?>

        <!-- Profil user -->
        <a href="../app/profile.php" class="fixed top-4 left-4 z-30 flex items-center space-x-2 bg-white dark:text-purple-400 bg-white dark:bg-gray-800 px-4 py-2 rounded-full shadow-md hover:bg-purple-100 dark:hover:bg-gray-700 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9.003 9.003 0 0112 15c2.485 0 4.735.998 6.364 2.636M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="text-sm font-medium text-purple-700 dark:text-purple-400"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
        </a>

        <!-- button dark/light mode -->
        <form method="POST" class="ml-auto">
            <button  type="submit" name="toggle_dark_mode" class="fixed bottom-4 right-8 z-30 flex items-center space-x-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-purple-300 p-3 rounded-full shadow-md hover:shadow-lg transition">
            <?= $darkMode
                ? '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 " fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1111.21 3a7 7 0 0010.79 9.79z"/></svg>'
                : '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M16.96 16.96l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M16.96 7.04l1.42-1.42"/></svg>'
            ?>
            </button>
        </form>
        

        <?php if ($_SESSION['role'] === 'admin') : ?>
            <!-- popup untuk nambahin kucing baru -->
            <section id="popup" class="fixed inset-0 bg-transparent w-full h-full z-20 hidden" aria-labelledby="dialog-title" aria-modal="true">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md w-96 max-w-full">
                        <h2 id="dialog-title" class="text-xl font-semibold mb-4 text-purple-600 dark:text-purple-400">Tambah Kucing Baru</h2>
                        <form method="POST" enctype="multipart/form-data" class="flex flex-col gap-3">
                            <div class="flex flex-col">
                                <label for="cat_name" class="mb-1 font-medium dark:text-gray-200">Nama Kucing :</label>
                                <input type="text" name="cat_name" id="cat_name" required class="border border-gray-300 rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="contoh: Milo - Persian">
                            </div>
                            <div class="flex flex-col">
                                <label for="cat_description" class="mb-1 font-medium dark:text-gray-200">Deskripsi :</label>
                                <input type="text" name="cat_description" id="cat_description" required class="border border-gray-300 rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="contoh: berbulu lebat">
                            </div>
                            <div class="flex flex-col">
                                <label for="cat_price" class="mb-1 font-medium dark:text-gray-200">Harga (Rp) :</label>
                                <input type="number" name="cat_price" id="cat_price" required class="border border-gray-300 rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="contoh: 5000000">
                            </div>
                            <div class="flex flex-col">
                                <label for="cat_image" class="mb-1 font-medium dark:text-gray-200">Gambar Kucing :</label>
                                <input type="file" name="cat_image" id="cat_image" accept="image/*" required class="border border-gray-300 rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div class="flex justify-between mt-4">
                                <button type="button" id="closePopup" class="bg-gray-500 text-white py-2 px-4 rounded hover:bg-gray-600 transition-all">Tutup</button>
                                <button type="submit" name="add_cat" class="bg-purple-500 text-white py-2 px-4 rounded hover:bg-purple-600 transition-all">Tambah</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        <?php endif; ?>
        
        <!-- nampilin kucing -->
        <section aria-labelledby="cats-heading">
            <h2 id="cats-heading" class="sr-only">Daftar Kucing Tersedia</h2>
            <div id="catList"class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach ($cats as $cat): ?>
                    <article 
                        class="cat-item bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-lg" 
                        data-name="<?= htmlspecialchars(strtolower($cat['name'])) ?>">
                        <img src="../<?= htmlspecialchars($cat['img']); ?>" 
                            alt="<?= htmlspecialchars($cat['name']) ?>" 
                            class="w-full h-48 object-cover hover:scale-105">
                        <div class="p-4">
                            <header><h2 class="text-xl font-semibold text-gray-700 dark:text-white"><?= htmlspecialchars($cat['name']) ?></h2></header>
                            <p class="text-gray-500 dark:text-gray-300 text-sm mt-2"><?= htmlspecialchars($cat['description']) ?></p>
                            <p class="text-lg font-bold text-black dark:text-white mt-4">Rp <?= number_format($cat['price'], 0, ',', '.') ?></p>
                            <footer class="flex gap-2 mt-4">
                                <a href="../app/form_pengadopsian.php?cat_id=<?= $cat['id'] ?>" 
                                class="flex-grow text-center bg-purple-500 text-white py-2 rounded-lg hover:bg-purple-600">
                                <?= displayCardCats::getButtonLabel($cat['price']) ?>
                                </a>
                            </footer>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <p id="noResult" class="col-span-full text-center text-gray-500 dark:text-gray-300 hidden">
                   Tidak ada kucing yang sesuai dengan pencarian
            </p>
        </section>

        <!-- history struk -->
         <section class="mt-10">
            <h2 class="text-2xl font-bold text-purple-700 dark:text-purple-400">Struk Adopsi</h2>
            <div class="grid sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-4">
                <?php foreach ($adoptions as $adoption) { ?>
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold dark:text-white"><?= htmlspecialchars($adoption['name']) ?></h3>
                        <p class="text-gray-600 dark:text-gray-300">Email : <?= htmlspecialchars($adoption['email']) ?></p>
                        <p class="text-gray-600 dark:text-gray-300">No HP : <?= htmlspecialchars($adoption['phone']) ?></p>
                        <p class="text-gray-600 dark:text-gray-300">Jenis Kelamin : <?= htmlspecialchars($adoption['gender']) ?></p>
                        <p class="text-gray-600 dark:text-gray-300">Kucing : <?= htmlspecialchars($adoption['cat_name']) ?></p>
                        
                        <?php if ($_SESSION['role'] === 'admin') : ?>
                            <div class="flex space-x-2 mt-3">
                                <form action="../app/update_struk.php" method="GET" class="inline-block">
                                    <input type="hidden" name="id" value="<?= $adoption['id'] ?>">
                                    <button type="submit" class="bg-purple-500 text-white py-2 px-4 rounded-lg hover:bg-purple-600">
                                        Edit Struk
                                    </button>
                                </form>
                                <form method="POST" class="inline-block">
                                    <input type="hidden" name="id" value="<?= $adoption['id'] ?>">
                                    <button type="submit" name="remove_struk" class="bg-red-500 text-white py-2 px-3 rounded-lg hover:bg-red-600 transition duration-300">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>

                    </div>
                <?php } ?>
            </div>
        </section>
    </main>

    <footer class="mx-auto px-4 py-6 max-w-7xl text-center text-gray-600 dark:text-gray-400 text-sm font-semibold">
        <p>&copy; <?php echo date('Y'); ?>. Monriie</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="script.js"></script>
</body>
</html>