<?php
session_start();

if(!isset($_SESSION['username'])){
    header("Location: login.html");
    exit;
}

include 'config.php';

$username = $_SESSION['username'];

$query = mysqli_query($conn,"SELECT * FROM users WHERE username='$username'");
$user = mysqli_fetch_assoc($query);

if(isset($_POST['update'])){
    $password = $_POST['password'];

if(!empty($password)){

    $hash = password_hash($password, PASSWORD_DEFAULT);

    mysqli_query($conn,"
        UPDATE users
        SET password='$hash'
        WHERE username='$username'
    ");
}

    $nama  = mysqli_real_escape_string($conn,$_POST['username']);
    $email = mysqli_real_escape_string($conn,$_POST['email']);

    mysqli_query($conn,"
        UPDATE users
        SET username='$nama',
            email='$email'
        WHERE username='$username'
    ");

    $_SESSION['username'] = $nama;

    echo "<script>
        alert('Profil berhasil diperbarui');
        window.location='profile.php';
    </script>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Profil</title>

<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

body{
    font-family:'Outfit',sans-serif;
    background:#f4f8fc;
}

.container{
    max-width:700px;
    margin:50px auto;
}

.card{
    background:white;
    padding:35px;
    border-radius:20px;
    box-shadow:0 10px 30px rgba(0,0,0,.08);
}

h2{
    color:#023e8a;
    margin-bottom:25px;
}

.form-group{
    margin-bottom:18px;
}

label{
    display:block;
    margin-bottom:6px;
    font-weight:600;
}

input{
    width:100%;
    padding:12px;
    border:1px solid #ddd;
    border-radius:10px;
}

.btn{
    background:#0077b6;
    color:white;
    border:none;
    padding:12px 24px;
    border-radius:10px;
    cursor:pointer;
}

.btn:hover{
    background:#023e8a;
}

.back{
    text-decoration:none;
    color:#0077b6;
    display:inline-block;
    margin-bottom:20px;
}

.avatar{
    width:100px;
    height:100px;
    border-radius:50%;
    background:#0077b6;
    color:white;
    display:flex;
    justify-content:center;
    align-items:center;
    font-size:40px;
    margin:auto;
    margin-bottom:20px;
}

</style>
</head>

<body>

<div class="container">

<a href="dashboard.php" class="back">
← Kembali ke Dashboard
</a>

<div class="card">

<div class="avatar">
<?php echo strtoupper(substr($user['username'],0,1)); ?>
</div>

<h2>Edit Profil</h2>

<form method="POST">

<div class="form-group">
<label>Username</label>
<input type="text"
name="username"
value="<?php echo $user['username']; ?>"
required>
</div>

<div class="form-group">
<label>Email</label>
<input type="email"
name="email"
value="<?php echo $user['email']; ?>"
required>
</div>
<div class="form-group">
<label>Password Baru</label>
<input type="password" name="password">
</div>

<button type="submit"
name="update"
class="btn">
💾 Simpan Perubahan
</button>

</form>

</div>
</div>

</body>
</html>