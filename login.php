<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // MySQLi prepared statement
    $stmt = $conn->prepare("SELECT * FROM admin WHERE email=? AND password=?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if ($admin) {
        $_SESSION['admin'] = $email;
        header("Location: Admin/dashboard.php");
        exit();
    } else {
        echo "<script>
            alert('Invalid admin credentials');
            window.location.href='index.php';
        </script>";
    }
}
?>
