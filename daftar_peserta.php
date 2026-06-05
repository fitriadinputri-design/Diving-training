<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['username'])){

    header("Location: login.html");
    exit;
}

include 'config.php';

?>
<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'config.php';

/*
Pastikan tabel pendaftaran sudah ada

CREATE TABLE pendaftaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100),
    email VARCHAR(100),
    telepon VARCHAR(20),
    program VARCHAR(100),
    pengalaman TEXT,
    jadwal VARCHAR(50),
    pesan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

*/

$search = "";

if(isset($_GET['search'])){

    $search = mysqli_real_escape_string(
        $conn,
        $_GET['search']
    );

    $sql = "SELECT * FROM pendaftaran
            WHERE nama LIKE '%$search%'
            OR email LIKE '%$search%'
            OR program LIKE '%$search%'
            ORDER BY id DESC";

}else{

    $sql = "SELECT * FROM pendaftaran
            ORDER BY id DESC";
}

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Daftar Peserta | DeepBlue</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial, Helvetica, sans-serif;
}

body{
    background:#f4f7fb;
}

/* NAVBAR */

.navbar{

    background:linear-gradient(
        90deg,
        #03045e,
        #0077b6
    );

    padding:18px 40px;

    display:flex;
    justify-content:space-between;
    align-items:center;

    color:white;

    box-shadow:
    0 5px 15px rgba(0,0,0,0.1);
}

.logo{
    font-size:28px;
    font-weight:bold;
}

.logo span{
    color:#f7c948;
}

.navbar a{

    text-decoration:none;
    color:white;

    background:#f7c948;
    color:#03045e;

    padding:10px 18px;

    border-radius:30px;

    font-weight:bold;

    transition:0.3s;
}

.navbar a:hover{
    transform:translateY(-2px);
}

/* CONTENT */

.container{
    width:95%;
    max-width:1300px;

    margin:40px auto;
}

.title{
    margin-bottom:25px;
}

.title h1{
    color:#03045e;
    margin-bottom:10px;
}

.title p{
    color:gray;
}

/* TABLE */

.table-box{

    background:white;

    border-radius:20px;

    overflow:hidden;

    box-shadow:
    0 10px 30px rgba(0,0,0,0.08);
}

table{
    width:100%;
    border-collapse:collapse;
}

table thead{

    background:linear-gradient(
        90deg,
        #0077b6,
        #00b4d8
    );

    color:white;
}

table th,
table td{

    padding:18px;
    text-align:left;
}

table tbody tr{

    border-bottom:1px solid #eee;

    transition:0.3s;
}

table tbody tr:hover{
    background:#f5fbff;
}

.program{

    background:#caf0f8;

    color:#023e8a;

    padding:8px 14px;

    border-radius:30px;

    font-size:13px;
    font-weight:bold;

    display:inline-block;
}

/* RESPONSIVE */

@media(max-width:900px){

    .table-box{
        overflow-x:auto;
    }

    table{
        min-width:1000px;
    }
}
/* SEARCH */

.search-box{

    width:100%;

    display:flex;
    justify-content:flex-end;

    margin-bottom:25px;
}

.search-form{

    display:flex;
    align-items:center;
    gap:12px;
}

.search-input{

    width:280px;

    padding:14px 20px;

    border:none;

    border-radius:50px;

    background:white;

    box-shadow:
    0 5px 15px rgba(0,0,0,0.08);

    outline:none;

    font-size:15px;

    transition:0.3s;
}

.search-input:focus{

    box-shadow:
    0 5px 20px rgba(0,119,182,0.25);
}

.search-btn{

    border:none;

    padding:14px 24px;

    border-radius:50px;

    background:linear-gradient(
        90deg,
        #0077b6,
        #00b4d8
    );

    color:white;

    font-weight:bold;

    cursor:pointer;

    transition:0.3s;
}

.search-btn:hover{

    transform:translateY(-2px);

    box-shadow:
    0 8px 20px rgba(0,0,0,0.15);
}

</style>

</head>
<body>

<!-- NAVBAR -->

<div class="navbar">

    <div class="logo">
        Deep<span>Blue</span>
    </div>

    <a href="dashboard.php">
        Dashboard
    </a>

</div>

<!-- CONTENT -->

<div class="container">

    <div class="search-box">

    <form method="GET" class="search-form">

        <input 
            type="text"
            name="search"
            class="search-input"
            placeholder="Cari peserta..."
            value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>"
        >

        <button type="submit" class="search-btn">
            Search
        </button>

    </form>

</div>

        <h1>
            Daftar Peserta Pelatihan
        </h1>

        <p>
            Data seluruh peserta yang telah mendaftar
        </p>

    </div>

    <div class="table-box">

        <table>

            <thead>

                <tr>

                    <th>No</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Telepon</th>
                    <th>Program</th>
                    <th>Pengalaman</th>
                    <th>Jadwal</th>
                    <th>Pesan</th>
                    <th>Tanggal</th>

                </tr>

            </thead>

            <tbody>

            <?php
            $no = 1;

            while($row = mysqli_fetch_assoc($result)){
            ?>

                <tr>

                    <td>
                        <?php echo $no++; ?>
                    </td>

                    <td>
                        <?php echo $row['nama']; ?>
                    </td>

                    <td>
                        <?php echo $row['email']; ?>
                    </td>

                    <td>
                        <?php echo $row['telepon']; ?>
                    </td>

                    <td>

                        <span class="program">

                            <?php echo $row['program']; ?>

                        </span>

                    </td>

                    <td>
                        <?php echo $row['pengalaman']; ?>
                    </td>

                    <td>
                        <?php echo $row['jadwal']; ?>
                    </td>

                    <td>
                        <?php echo $row['pesan']; ?>
                    </td>

                    <td>
                        <?php echo $row['created_at']; ?>
                    </td>

                </tr>

            <?php } ?>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>