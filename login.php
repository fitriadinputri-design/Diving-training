<?php

session_start();

include 'config.php';

$email    = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM users
        WHERE email='$email'";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {

    $user = mysqli_fetch_assoc($result);

    if (password_verify($password, $user['password'])) {

        $_SESSION['username']
            = $user['username'];

        header("Location: daftar_peserta.php");

    } else {

        echo "Password salah!";
    }

} else {

    echo "Email tidak ditemukan!";
}

?>