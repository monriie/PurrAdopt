<?php

function handleThemeToggle() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['theme'])) {
        $newTheme = $_POST['theme'] === 'dark' ? 'dark' : 'light';
        setcookie('theme', $newTheme, time() + (86400 * 30), '/');
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

function getThemeSettings() {
    $theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';
    $isDark = $theme === 'dark';
    
    return [
        'theme' => $theme,
        'isDark' => $isDark,
        'themeClass' => $isDark ? 'dark' : '',
        'iconVisible' => $isDark ? 'hidden' : '',
        'iconHidden' => $isDark ? '' : 'hidden',
        'nextTheme' => $isDark ? 'light' : 'dark',
    ];
}
