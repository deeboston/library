<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';
redirectIfNotLoggedIn();

if (!isset($_GET['id'])) {
    echo "Book ID not provided.";
    exit;
}

$book_id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM libros WHERE id = ?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Book not found.";
    exit;
}

$book = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($book['nombre']) ?> - Details</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="row">
    <div class="col-md-4">
      <img src="../assets/images/<?= htmlspecialchars($book['cover_image']) ?>" class="img-fluid rounded shadow" alt="Book Cover">
    </div>
    <div class="col-md-8">
      <h2><?= htmlspecialchars($book['nombre']) ?></h2>
      <p><strong>Author:</strong> <?= htmlspecialchars($book['autor']) ?></p>
      <p><strong>Genre:</strong> <?= htmlspecialchars($book['genero']) ?></p>
      <p><strong>Language:</strong> <?= htmlspecialchars($book['language']) ?></p>
      <p><strong>Year:</strong> <?= htmlspecialchars($book['fecha']) ?></p>
      <a href="../books/<?= htmlspecialchars($book['file_path']) ?>" class="btn btn-primary" target="_blank">📖 Read / Download</a>
      <a href="<?= $_SESSION['role'] === 'admin' ? '../admin/admin-dashboard.php' : 'user-dashboard.php' ?>" class="btn btn-outline-secondary">← Back</a>
    </div>
  </div>
</div>

</body>
</html>
