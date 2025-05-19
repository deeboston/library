<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    header("Location: ../pages/dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Panel - EduLibrary</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
        background: linear-gradient(to right, #6a11cb, #2575fc);
        min-height: 100vh;
    }

    .admin-card {
        background: #fff;
        border-radius: 1rem;
        padding: 30px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        text-align: center;
        transition: transform 0.2s;
    }

    .admin-card:hover {
        transform: translateY(-5px);
    }

    .card-icon {
        font-size: 2rem;
    }

    .back-link {
        position: absolute;
        top: 20px;
        left: 20px;
    }
  </style>
</head>
<body class="d-flex flex-column align-items-center justify-content-center">

<a href="../pages/dashboard.php" class="btn btn-light btn-sm back-link">← Back to Dashboard</a>

<div class="container text-center mt-5">
    <h2 class="text-white fw-bold mb-4">Admin Panel</h2>

    <div class="row justify-content-center g-4">
        <div class="col-md-4">
            <div class="admin-card">
                <div class="card-icon text-primary mb-2">📚</div>
                <h5 class="fw-bold">Manage Books</h5>
                <p class="text-muted small">View, update, or delete all uploaded books.</p>
                <a href="manage-books.php" class="btn btn-outline-primary btn-sm">Go</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="admin-card">
                <div class="card-icon text-danger mb-2">🗑</div>
                <h5 class="fw-bold">Deletion Requests</h5>
                <p class="text-muted small">Review and approve book deletion requests from users.</p>
                <a href="delete-requests.php" class="btn btn-outline-danger btn-sm">Go</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
