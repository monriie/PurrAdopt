<?php
class User {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function register($username, $nama, $password) {
        try {
            // cek username dulu
            $checkStmt = $this->conn->prepare("SELECT username FROM users WHERE username = ?");
            $checkStmt->bind_param("s", $username);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                // username sudah ada
                return false;
            }
            
            // hashing password
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            
            // buat user
            $stmt = $this->conn->prepare("INSERT INTO users (username, nama, password) VALUES (?, ?, ?)");
            if (!$stmt) {
                error_log("Prepare failed: " . $this->conn->error);
                return false;
            }
            
            $stmt->bind_param("sss", $username, $nama, $hashed);
            $result = $stmt->execute();
            
            if (!$result) {
                error_log("Execute failed: " . $stmt->error);
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return false;
        }
    }

    public function login($username, $password) {
        try {
            // Debug info
            error_log("Attempting login for username: $username");
            
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
            if (!$stmt) {
                error_log("Prepare failed: " . $this->conn->error);
                return false;
            }
            
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                error_log("User found: " . print_r($user, true));
                
                if (isset($user['password']) && password_verify($password, $user['password'])) {
                    session_start();
                    $_SESSION['loggedin'] = true;
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role']; // admin
                    return true;
                } else {
                    error_log("Password verification failed");
                }
            } else {
                error_log("User not found");
            }
            return false;
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getUserData($username) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function updateProfile($oldUsername, $newUsername, $newPassword) {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("UPDATE users SET username = ?, password = ? WHERE username = ?");
        $stmt->bind_param("sss", $newUsername, $hashed, $oldUsername);
        return $stmt->execute();
    }
}
?>