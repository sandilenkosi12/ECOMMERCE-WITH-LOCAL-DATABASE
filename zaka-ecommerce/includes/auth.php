<?php
require_once 'config.php';

// Register new user
function registerUser($name, $email, $password, $phone = '', $address = '') {
    global $conn;
    
    // Check if email exists
    $check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
    if (mysqli_num_rows($check) > 0) {
        return ['success' => false, 'message' => 'Email already registered'];
    }
    
    // Hash password
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $query = "INSERT INTO users (full_name, email, password, phone, address, role) 
              VALUES ('$name', '$email', '$hashed', '$phone', '$address', 'customer')";
    
    if (mysqli_query($conn, $query)) {
        $userId = mysqli_insert_id($conn);
        return ['success' => true, 'user_id' => $userId];
    } else {
        return ['success' => false, 'message' => 'Registration failed: ' . mysqli_error($conn)];
    }
}

// Login user
function loginUser($email, $password) {
    global $conn;
    
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            return ['success' => true, 'user' => $user];
        }
    }
    
    return ['success' => false, 'message' => 'Invalid email or password'];
}

// Logout user
function logoutUser() {
    session_destroy();
    return true;
}
?>