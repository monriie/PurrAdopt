<?php

require_once __DIR__ . '/auth/config.php';
require_once __DIR__ . '/auth/users.php';

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
        $upload_dir = "uploads/";
    
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
    public static function display($cat) {
        echo '<article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg">';
        echo '<img src="' . htmlspecialchars($cat['img']) . '" alt="' . htmlspecialchars($cat['name']) . '" class="w-full h-48 object-cover hover:scale-105">';
        echo '<div class="p-4">';
        echo '<header><h2 class="text-xl font-semibold text-gray-700">' . htmlspecialchars($cat['name']) . '</h2></header>';
        echo '<p class="text-gray-500 text-sm mt-2">' . htmlspecialchars($cat['description']) . '</p>';
        echo '<p class="text-lg font-bold text-black mt-4">Rp ' . number_format($cat['price'], 0, ',', '.') . '</p>';
        echo '<footer class="flex gap-2 mt-4">';
        echo '<a href="form_pengadopsian.php?cat_id=' . $cat['id'] . '" class="flex-grow text-center bg-purple-500 text-white py-2 rounded-lg hover:bg-purple-600">' . self::getButtonLabel($cat['price']) . '</a>';
        echo '</footer></div></article>';
    }

    public static function getButtonLabel($price) {
        return $price <= 10000000 ? "Segera Adopsi" : "Adopsi Eksklusif"; // Jika harga ≤ 10.000.000 "Segera Adopsi", Jika harga > 10.000.000 "Adopsi Eksklusif"
    }
}

session_start();

// login user
if (!isset($_SESSION['loggedin'])) {
    header("Location: auth/login.php");
    exit;
}

$catManager = new CatManager($conn);
$adoptionManager = new AdoptionManager($conn);

// proses form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirectUrl = $_SERVER['PHP_SELF'] . '?sort=' . ($_GET['sort'] ?? 'all');
    if (isset($_POST['add_cat'])) {
        header('Location: ' . $redirectUrl . '&status=' . ($catManager->addCat($_POST, $_FILES) ? 'added' : 'error'));
    } elseif (isset($_POST['remove_struk'])) {
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
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Daftar Adopsi Kucing - Tugas 3 Pemweb 2 Monica Amrina Rosyada">
    <title>Tugas 4 Pemweb 2 - Monica Amrina Rosyada (09021382328144)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body class="font-[poppins] bg-gray-100 px-8">
    <header class="mx-auto py-8 max-w-7xl">
        <h1 class="text-4xl font-bold text-center text-purple-700">Daftar Adopsi Kucing</h1>
    </header>

    <main class="mx-auto px-4 pb-16 max-w-7xl">
        <!-- sorting -->
        <section aria-labelledby="filter-heading" class="mb-8">
            <nav class="sort_criteria flex justify-center space-x-4">
                <a href="?sort=a-z" 
                   class="px-4 py-2 bg-purple-200 text-black rounded-full hover:bg-purple-300 transition <?php echo ($currentSort === 'a-z') ? 'bg-black text-white' : ''; ?>"
                   aria-current="<?php echo ($currentSort === 'a-z') ? 'page' : 'false'; ?>">
                    A-Z
                </a>
                <a href="?sort=termurah" 
                   class="px-4 py-2 bg-purple-200 text-black rounded-full hover:bg-purple-300 transition <?php echo ($currentSort === 'termurah') ? 'bg-black text-white' : ''; ?>"
                   aria-current="<?php echo ($currentSort === 'termurah') ? 'page' : 'false'; ?>">
                    Termurah
                </a>
                <a href="?sort=termahal" 
                   class="px-4 py-2 bg-purple-200 text-black rounded-full hover:bg-purple-300 transition <?php echo ($currentSort === 'termahal') ? 'bg-black text-white' : ''; ?>"
                   aria-current="<?php echo ($currentSort === 'termahal') ? 'page' : 'false'; ?>">
                    Termahal
                </a>
            </nav>
        </section>

        <!-- tombol tambah kucing -->
        <button id="openPopup" class="fixed top-4 right-4 bg-gray-400 text-white py-2 px-4 rounded-lg hover:bg-purple-500 transition-all z-10" aria-label="Tambah Kucing Baru" aria-haspopup="dialog">
            Tambah Kucing
        </button>
        
        <!-- popup untuk nambahin kucing baru -->
        <section id="popup" class="fixed inset-0 bg-transparent w-full h-full z-20 hidden" aria-labelledby="dialog-title" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center">
                <div class="bg-white p-6 rounded-lg shadow-md w-96 max-w-full">
                    <h2 id="dialog-title" class="text-xl font-semibold mb-4 text-purple-600">Tambah Kucing Baru</h2>
                    <form method="POST" enctype="multipart/form-data" class="flex flex-col gap-3">
                        <div class="flex flex-col">
                            <label for="cat_name" class="mb-1 font-medium">Nama Kucing :</label>
                            <input type="text" name="cat_name" id="cat_name" required class="border p-2 rounded" placeholder="contoh: Milo - Persian">
                        </div>
                        <div class="flex flex-col">
                            <label for="cat_description" class="mb-1 font-medium">Deskripsi :</label>
                            <input type="text" name="cat_description" id="cat_description" required class="border p-2 rounded" placeholder="contoh: berbulu lebat">
                        </div>
                        <div class="flex flex-col">
                            <label for="cat_price" class="mb-1 font-medium">Harga (Rp) :</label>
                            <input type="number" name="cat_price" id="cat_price" required class="border p-2 rounded" placeholder="contoh: 5000000">
                        </div>
                        <div class="flex flex-col">
                            <label for="cat_image" class="mb-1 font-medium">Gambar Kucing :</label>
                            <input type="file" name="cat_image" id="cat_image" accept="image/*" required class="border p-2 rounded">
                        </div>
                        <div class="flex justify-between mt-4">
                            <button type="button" id="closePopup" class="bg-gray-500 text-white py-2 px-4 rounded hover:bg-gray-600 transition-all">Tutup</button>
                            <button type="submit" name="add_cat" class="bg-purple-500 text-white py-2 px-4 rounded hover:bg-purple-600 transition-all">Tambah</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
        
        <!-- nampilin kucing -->
        <section aria-labelledby="cats-heading">
            <h2 id="cats-heading" class="sr-only">Daftar Kucing Tersedia</h2>
            <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($cats as $cat) { displayCardCats::display($cat); } ?>

            </div>
        </section>

        <!-- history struk -->
         <section class="mt-10">
            <h2 class="text-2xl font-bold text-purple-700">Struk Adopsi</h2>
            <div class="grid sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-4">
                <?php foreach ($adoptions as $adoption) { ?>
                    <div class="bg-white p-4 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold"><?= htmlspecialchars($adoption['name']) ?></h3>
                        <p class="text-gray-600">Email : <?= htmlspecialchars($adoption['email']) ?></p>
                        <p class="text-gray-600">No HP : <?= htmlspecialchars($adoption['phone']) ?></p>
                        <p class="text-gray-600">Jenis Kelamin : <?= htmlspecialchars($adoption['gender']) ?></p>
                        <p class="text-gray-600">Kucing : <?= htmlspecialchars($adoption['cat_name']) ?></p>
                        
                        <div class="flex space-x-2 mt-3">
                            <form action="update_struk.php" method="GET" class="inline-block">
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
                    </div>
                <?php } ?>
            </div>
        </section>
    </main>

    <footer class="mx-auto px-4 py-6 max-w-7xl text-center text-gray-600 text-sm font-semibold">
        <p>&copy; <?php echo date('Y'); ?>. Monriie</p>
    </footer>

    <script>
        const popup = document.getElementById('popup');
        const openButton = document.getElementById('openPopup');
        const closeButton = document.getElementById('closePopup');

        // buka popup
        openButton.addEventListener('click', function() {
            popup.classList.remove('hidden');
            popup.showModal();
            document.body.classList.add('overflow-hidden');
        });

        // tutup popup
        closeButton.addEventListener('click', function() {
            popup.classList.add('hidden');
            popup.close();
            document.body.classList.remove('overflow-hidden');
        });

        // Tutup popup saat mengklik di luar
        popup.addEventListener('click', function(event) {
            if (event.target === popup) {
                popup.classList.add('hidden');
                popup.close();
                document.body.classList.remove('overflow-hidden');
            }
        });
    </script>
</body>
</html>