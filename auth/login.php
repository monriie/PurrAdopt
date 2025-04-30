<?php
require_once 'config.php';
require_once 'users.php';

$config = new Config();
$conn = $config->getConnection();
$user = new User($conn);
$loginError = '';

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if ($user->login($username, $password)) {
        // Successful login redirects to index.php
        header("Location: ../index.php");
        exit;
    } else {
        // Failed login shows error message
        $loginError = "Username atau password tidak cocok";
    }
}

function valid($conn) {
    // Cek user double
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $count = $result->fetch_assoc()['count'];
    echo "<p>Number of users in database: $count</p>";
        
    // List username
    if ($count > 0) {
        $result = $conn->query("SELECT username FROM users");
        echo "<p>Usernames: ";
        $usernames = [];
        while ($row = $result->fetch_assoc()) {
        $usernames[] = htmlspecialchars($row['username']);
        }
    echo implode(", ", $usernames);
    echo "</p>";
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
            <h1 class="text-3xl font-bold text-center text-purple-700 mb-6">Login</h1> 
            
            <?php if ($loginError): ?>
                <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-700">
                    <?= $loginError ?>
                </div>
            <?php endif; ?>
            
            <form action="login.php" method="POST" class="space-y-4">
                <div>
                    <label for="username" class="block font-medium text-gray-700">Username: </label>
                    <input type="text" id="username" name="username" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
                
                <div>
                    <label for="password" class="block font-medium text-gray-700">Password: </label>
                    <input type="password" id="password" name="password" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
                
                <div class="grid text-center pt-4">
                    <button type="submit" class="bg-purple-500 text-white px-6 py-2 mb-6 rounded-lg hover:bg-purple-600 transition duration-300">masuk</button>
                    <span>belum ada akun?<u><a href="register.php" class="text-purple-600 hover:text-purple-400 transition duration-300 ml-2">buat akun</a></u></span>
                </div>
            </form>
        </main>
    </body>
</html>