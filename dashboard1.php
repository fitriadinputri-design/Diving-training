<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - DeepBlue</title>

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family: Arial, sans-serif;
        }

        body{
            background:#f4f7fb;
        }

        /* Navbar */
        .navbar{
            background: linear-gradient(to right, #0b0f6d, #0c6cb8);
            padding:20px 50px;
            display:flex;
            justify-content:space-between;
            align-items:center;
        }

        .logo{
            font-size:32px;
            font-weight:bold;
            color:white;
        }

        .logo span{
            color:#ffc93c;
        }

        .nav-menu{
            display:flex;
            gap:15px;
        }

        .nav-btn{
            background:#ffc93c;
            color:#000;
            padding:12px 25px;
            border-radius:30px;
            text-decoration:none;
            font-weight:bold;
            transition:0.3s;
        }

        .nav-btn:hover{
            background:white;
        }

        /* Container */
        .container{
            display:flex;
            justify-content:center;
            align-items:center;
            height:80vh;
        }

        .card{
            background:white;
            width:500px;
            padding:40px;
            border-radius:20px;
            text-align:center;
            box-shadow:0 5px 15px rgba(0,0,0,0.1);
        }

        .welcome{
            font-size:28px;
            color:#0b0f6d;
            margin-bottom:15px;
        }

        .username{
            color:#0c6cb8;
            font-weight:bold;
        }

        .desc{
            color:#555;
            margin-bottom:35px;
        }

        .btn-group{
            display:flex;
            justify-content:center;
            gap:20px;
            flex-wrap:wrap;
        }

        .btn{
            background:#0c6cb8;
            color:white;
            padding:14px 25px;
            border-radius:30px;
            text-decoration:none;
            transition:0.3s;
            font-weight:bold;
        }

        .btn:hover{
            background:#0b0f6d;
        }

        .logout{
            background:#dc3545;
        }

        .logout:hover{
            background:#a71d2a;
        }

    </style>
</head>
<body>

    <!-- Navbar -->
    <div class="navbar">

        <div class="logo">
            Deep<span>Blue</span>
        </div>

        <div class="nav-menu">
            <a href="daftar_peserta.php" class="nav-btn">
                Peserta
            </a>

            <a href="logout.php" class="nav-btn">
                Logout
            </a>
        </div>

    </div>

    <!-- Content -->
    <div class="container">

        <div class="card">

            <h1 class="welcome">
                Selamat Datang,
                <span class="username">
                    <?php echo $_SESSION['username']; ?>
                </span>
            </h1>

            <p class="desc">
                Anda berhasil login ke sistem pelatihan DeepBlue.
            </p>

            <div class="btn-group">

                <a href="daftar_peserta.php" class="btn">
                    Lihat Daftar Peserta
                </a>

                <a href="logout.php" class="btn logout">
                    Logout
                </a>

            </div>

        </div>

    </div>

</body>
</html>