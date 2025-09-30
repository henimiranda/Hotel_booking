<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Selamat Datang di Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Background wallpaper */
        .hero {
            background: url('images/hotel.jpg') no-repeat center center/cover;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            text-shadow: 0 3px 10px rgba(0,0,0,0.7);
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: bold;
        }

        .hero p {
            font-size: 1.3rem;
            margin-bottom: 30px;
        }

        .btn-custom {
            font-size: 1.2rem;
            padding: 12px 30px;
            border-radius: 30px;
            background: #ffc107;
            border: none;
            color: #000;
            transition: all 0.3s ease;
            font-weight: bold;
        }

        .btn-custom:hover {
            background: #ffb300;
            color: white;
            transform: translateY(-3px);
        }
    </style>
</head>
<body>

    <section class="hero">
        <div>
            <h1>Selamat Datang di Luxury Hotel</h1>
            <p>Nikmati pengalaman menginap yang tak terlupakan</p>
            <a href="login.php" class="btn btn-custom">Masuk</a>
        </div>
    </section>

</body>
</html>
