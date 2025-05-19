<?php
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = trim($_POST["identifier"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT id, username, email, password, role FROM usuario WHERE username = ? OR email = ?");
    if (!$stmt) die("Error: " . $conn->error);

    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: ../admin/admin-dashboard.php");
            } else {
                header("Location: user-dashboard.php");
        }
            exit();
        } else {
            $message = "Invalid password.";
        }
    } else {
        $message = "User not found.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - EduLibrary</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>

    <style>
        body {
            background: linear-gradient(-45deg, #007bff, #6610f2, #6f42c1, #00c4cc);
            background-size: 400% 400%;
            animation: gradientMove 10s ease infinite;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .login-card {
            background: rgba(255, 255, 255, 0.97);
            padding: 40px 30px;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 500px;
        }

        .btn-primary {
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="login-card text-center">
    <div class="hero col-md-8" data-aos="fade-up">
        <div class="text-center mb-4">
            <dotlottie-player
                src="https://lottie.host/aa0e1b42-bfbf-4a5e-b052-63bdfc6946aa/8bLiOSOeIF.lottie"
                background="transparent"
                speed="1"
                style="width: 120px; height: 120px;"
                loop autoplay>
            </dotlottie-player>
        </div>
    </div>
    <h3 class="fw-bold text-primary mt-3">Login to Your Library Account</h3>

    <?php if ($message): ?>
        <div class="alert alert-danger mt-3"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" class="mt-4 text-start">
        <div class="mb-3">
            <label>Username or Email</label>
            <input type="text" name="identifier" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 mt-2">Login</button>
    </form>

    <p class="mt-3 small">
        Don't have an account? <a href="register.php">Register here</a>
    </p>

    <a href="index.php" class="btn btn-outline-secondary btn-sm mt-2">← Back to Home</a>
</div>

</body>
</html>
