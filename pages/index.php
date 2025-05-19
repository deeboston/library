<?php
require_once '../includes/session.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EduLibrary - Welcome</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>

    <style>
        body {
            background: linear-gradient(-45deg, #007bff, #6610f2, #6f42c1, #00c4cc);
            background-size: 400% 400%;
            animation: gradientMove 10s ease infinite;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .hero {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 1rem;
            padding: 50px 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            text-align: center;
            animation: fadeIn 1.2s ease-out;
            position: relative;
            z-index: 1;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .btn-lg {
            padding: 12px 24px;
            font-size: 1.1rem;
        }

        .floating-shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.2;
            z-index: 0;
        }

        .shape-1 {
            width: 150px;
            height: 150px;
            background-color: #fff;
            top: 10%;
            left: -50px;
        }

        .shape-2 {
            width: 100px;
            height: 100px;
            background-color: #f0f8ff;
            bottom: 10%;
            right: -40px;
        }

        .counter {
            font-size: 1.1rem;
            margin-top: 20px;
            color: #444;
        }
    </style>
</head>
<body>

<!-- Floating background shapes -->
<div class="floating-shape shape-1"></div>
<div class="floating-shape shape-2"></div>

<div class="container d-flex align-items-center justify-content-center" style="min-height:100vh;">
    <div class="hero col-md-8" data-aos="fade-up">
        <div class="text-center mb-4">
            <div class="d-flex justify-content-center">
                <dotlottie-player
                    src="https://lottie.host/aa0e1b42-bfbf-4a5e-b052-63bdfc6946aa/8bLiOSOeIF.lottie"
                    background="transparent"
                    speed="1"
                    style="width: 150px; height: 150px;"
                    loop autoplay>
                </dotlottie-player>
            </div>
        </div>
        <h1 class="display-4 fw-bold text-primary mb-3">EduLibrary</h1>
        <p class="lead text-muted">Explore, upload, and manage your favorite books — all in one place.</p>
        <div class="mt-4">
            <a href="login.php" class="btn btn-primary btn-lg me-3">Login</a>
            <a href="register.php" class="btn btn-outline-primary btn-lg">Register</a>
        </div>
        <div class="counter" id="bookCounter">📚 Books uploaded: loading...</div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>AOS.init();</script>
<script>
    fetch('../api/book-count.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('bookCounter').textContent = `📚 Books uploaded: ${data.count}`;
        })
        .catch(() => {
            document.getElementById('bookCounter').textContent = '📚 Books uploaded: N/A';
        });
</script>

</body>
</html>

