<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';

redirectIfNotLoggedIn();

$user = userIs();
$email = $user['email'] ?? null;

if (!$email) {
    die("⚠️ El email del usuario no está disponible en la sesión.");
}

// Traer solo los libros guardados (bookmarks) del usuario
$sql = "
    SELECT b.book_id, b.book_name, 
           l.id, l.nombre, l.description, l.cover_image, l.autor, l.fecha, l.language, l.file_path 
    FROM bookmarks b
    JOIN libros l ON b.book_id = l.id
    WHERE b.email = ?
    ";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$savedBooks = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Your Dashboard - EduLibrary</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="user-dashboard.php">EduLibrary</a>
    <div class="d-flex">
      <span class="navbar-text text-white me-3">Welcome, <?= htmlspecialchars(getCurrentUsername()) ?>!</span>
      <a href="../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="container mt-5">
  <h3>Your Books</h3><br>
<div class="row g-4">
  <?php if ($savedBooks && $savedBooks->num_rows > 0): ?>
    <?php while ($book = $savedBooks->fetch_assoc()): ?>
      <?php $category = strtolower($book['categoria'] ?? 'unknown'); ?>
      <div class="col-sm-6 col-md-4 col-lg-3 book-card-wrapper" data-category="<?= $category ?>">
        <div class="card h-100 shadow-sm">
          <?php 
            $coverPath = $book['cover_image'] ? '../assets/images/' . htmlspecialchars($book['cover_image']) : '../assets/images/edulibrary logo.png'; 
          ?>
          <img src="<?= $coverPath ?>" class="card-img-top" alt="Book Cover">
          <div class="card-body d-flex flex-column">
            <h6 class="card-title fw-semibold text-truncate" title="<?= htmlspecialchars($book['nombre']) ?>">
              <?= htmlspecialchars($book['nombre']) ?>
            </h6>
            <p class="card-text small text-secondary flex-grow-1">
              <?= htmlspecialchars($book['description']) ?: 'No summary available.' ?>
            </p>

            <?php if (!empty($book['preview'])): ?>
              <button type="button" class="btn btn-outline-secondary btn-sm w-100 mb-2" data-bs-toggle="modal" data-bs-target="#previewModal<?= $book['id'] ?>">
                📖 Read Preview
              </button>

              <div class="modal fade" id="previewModal<?= $book['id'] ?>" tabindex="-1" aria-labelledby="previewModalLabel<?= $book['id'] ?>" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="previewModalLabel<?= $book['id'] ?>">Preview: <?= htmlspecialchars($book['nombre']) ?></h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="white-space: pre-wrap;">
                      <?= nl2br(htmlspecialchars($book['preview'])) ?>
                    </div>
                  </div>
                </div>
              </div>
            <?php endif; ?>

            <p class="text-muted small mb-1 mt-auto">By <?= htmlspecialchars($book['autor']) ?></p>
            <p class="text-muted small mb-2">
              📅 <?= htmlspecialchars($book['fecha']) ?> | 🌐 <?= htmlspecialchars($book['language']) ?>
            </p>
            <a href="read-viewer.php?file=<?= urlencode($book['file_path']) ?>" class="btn btn-primary btn-sm w-100">Read Now</a>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <div class="alert alert-info">No saved books found.</div>
  <?php endif; ?>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
