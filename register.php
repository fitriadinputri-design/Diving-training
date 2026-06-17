<?php

include 'config.php';

$username = $_POST['username'];
$email    = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

$sql = "INSERT INTO users(username,email,password)
        VALUES('$username','$email','$password')";

$result = mysqli_query($conn, $sql);

if ($result) {

    header("Location: register_success.php");
exit;

    header("refresh:2;url=login.html");

} else {

    echo "Registrasi gagal : "
         . mysqli_error($conn);
}

?>