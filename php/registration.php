<?php
session_start();
include("db_connection.php"); // Connect to the database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if it's a signup or login request
    if (isset($_POST['confirm_password'])) {
        // Signup Logic
        $confirm_password = $_POST['confirm_password'];

        if ($password !== $confirm_password) {
            echo "<script>
                alert('Passwords do not match!');
                window.location.href = '../login.html';
            </script>";
            exit();
        }

        // Insert new user into database
        $sql = "INSERT INTO users (email, password) VALUES ('$email', '$password')";
        if (mysqli_query($conn, $sql)) {
            echo "<script>
                alert('Signup Successful! Please login.');
                window.location.href = '../login.html';
            </script>";
            exit();
        } else {
            echo "<script>
                alert('Signup Failed! Try again.');
                window.location.href = '../login.html';
            </script>";
            exit();
        }
    } else {
        // Login Logic
        $sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            $_SESSION['email'] = $email;
            
            echo "<script>
                alert('Login Successful!');
                window.location.href = '../index.html';
            </script>";
            exit();
        } else {
            echo "<script>
                alert('Invalid email or password. Please try again.');
                window.location.href = '../login.html';
            </script>";
            exit();
        }
    }
}
?>
