<?php

require_once __DIR__ . '/../auth/config.php';
// require_once __DIR__ . '/../public/index.php';

$config = new Config();
$conn = $config->getConnection();

// cookies dark mode
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_dark_mode'])) {
    if (isset($_COOKIE['dark_mode']) && $_COOKIE['dark_mode'] === 'true') {
        setcookie('dark_mode', 'false', time() + (86400 * 30), '/');
    } else {
        setcookie('dark_mode', 'true', time() + (86400 * 30), '/');
    }
    // Redirect untuk mencegah resubmit
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$darkMode = isset($_COOKIE['dark_mode']) && $_COOKIE['dark_mode'] === 'true';

?>