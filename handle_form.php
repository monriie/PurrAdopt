<?php
require_once 'config.php';

function clean_input($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // get dan rapihin data dari form
    $name = clean_input($_POST['nama']);
    $email = clean_input($_POST['email']);
    $phone = clean_input($_POST['no_hp']);
    $gender = clean_input($_POST['jenis_kelamin']);
    $cat_name = clean_input($_POST['jenis_kucing']);
    $cat_id = isset($_POST['cat_id']) ? (int)$_POST['cat_id'] : 0;
    
    // search berdasarkan cat_id jika nama kosong
    if ($cat_id == 0 && !empty($cat_name)) {
        $sql = "SELECT id FROM cats WHERE name = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $cat_name);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $cat_id = $row['id'];
            }
            $stmt->close();
        }
    }
    
    // insert data pengadopsi ke database
    $sql = "INSERT INTO adoptions (cat_id, name, email, phone, gender) VALUES (?, ?, ?, ?, ?)";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("issss", $cat_id, $name, $email, $phone, $gender);
        
        if ($stmt->execute()) {
            header("Location: form_pengadopsian.php?cat_id=$cat_id&jenis_kucing=" . urlencode($cat_name) . "&status=success");
            exit;
        } else {
            echo "Error: " . $stmt->error;
        }
        
        $stmt->close();
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    header("Location: index.php");
    exit;
}