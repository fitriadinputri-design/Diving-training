<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Berhasil - DeepBlue</title>

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

        .btn-login{
            background:#ffc93c;
            color:#000;
            padding:12px 25px;
            border-radius:30px;
            text-decoration:none;
            font-weight:bold;
        }

        /* Content */
        .container{
            display:flex;
            justify-content:center;
            align-items:center;
            height:80vh;
        }

        .card{
            background:white;
            width:450px;
            padding:40px;
            border-radius:20px;
            text-align:center;
            box-shadow:0 5px 15px rgba(0,0,0,0.1);
        }

        .success-icon{
            font-size:70px;
            color:green;
            margin-bottom:20px;
        }

        h1{
            color:#0b0f6d;
            margin-bottom:15px;
        }

        p{
            color:#555;
            margin-bottom:30px;
        }

        .btn{
            background:#0c6cb8;
            color:white;
            padding:12px 25px;
            text-decoration:none;
            border-radius:30px;
            display:inline-block;
            transition:0.3s;
        }

        .btn:hover{
            background:#0b0f6d;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <div class="navbar">
        <div class="logo">
            Deep<span>Blue</span>
        </div>

        <a href="login.html" class="btn-login">
            Login
        </a>
    </div>

    <!-- Content -->
    <div class="container">
        <div class="card">

            <div class="success-icon">
                ✔
            </div>

            <h1>Registrasi Berhasil</h1>

            <p>
                Akun Anda berhasil dibuat.
                Silakan login untuk masuk ke sistem.
            </p>

            <a href="login.html" class="btn">
                Login Sekarang
            </a>

        </div>
    </div>

</body>
</html>