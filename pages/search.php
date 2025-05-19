<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';
redirectIfNotLoggedIn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Search Library</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <h3>Search for Books</h3>

  <form method="GET" class="row g-3 mb-4">
    <div class="col-md-3">
      <input type="text" name="title" class="form-control" placeholder="Title">
    </div>
    <div class="col-md-3">
      <input type="text" name="author" class="form-control" placeholder="Author">
    </div>
    <div class="col-md-2">
      <input type="text" name="language" class="form-control" placeholder="Language">
    </div>
    <div class="col-md-2">
      <input type="number" name="year" class="form-control" placeholder="Year">
    </div>
    <div class="col-md-2">
      <button type="submit" class="btn btn-primary w-100">Search</button>
    </div>
  </form>

<?php
$conditions = [];
$params = [];

if (!empty($_GET['title'])) {
  $conditions[] = "nombre LIKE ?";
  $params[] = "%" . $_GET['title'] . "%";
}
if (!empty($_GET['author'])) {
  $conditions[] = "autor LIKE ?";
  $params[] = "%" . $_GET['author'] . "%";
}
if (!empty($_GET['language'])) {
  $conditions[] = "language LIKE ?";
  $params[] = "%" . $_GET['language'] . "%";
}
if (!empty($_GET['year'])) {
  $conditions[] = "fecha = ?";
  $params[] = $_GET['year'];
}

$sql = "SELECT * FROM libros";
if ($conditions) {
  $sql .= " WHERE " . implode(" AND ", $conditions);
}

$stmt = $conn->prepare($sql);
if ($params) {
  $types = str_repeat("s", count($params));
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="row">
<?php while ($book = $result->fetch_assoc()): ?>
  <div class="col-md-4">
    <div class="card mb-4 shadow-sm">
      <img src="../assets/images/<?= htmlspecialchars($book['cover_image']) ?>" class="card-img-top" style="height:250px; object-fit:cover;">
      <div class="card-body">
        <h5 class="card-title"><?= htmlspecialchars($book['nombre']) ?></h5>
        <p class="card-text"><strong>Author:</strong> <?= htmlspecialchars($book['autor']) ?><br>
        <strong>Year:</strong> <?= htmlspecialchars($book['fecha']) ?><br>
        <strong>Language:</strong> <?= htmlspecialchars($book['language']) ?></p>
        <a href="book-details.php?id=<?= $book['id'] ?>" class="btn btn-outline-primary btn-sm">Details</a>
      </div>
    </div>
  </div>
<?php endwhile; ?>
</div>
</div>
<a href="<?= $_SESSION['role'] === 'admin' ? '../admin/admin-dashboard.php' : 'user-dashboard.php' ?>" class="btn btn-outline-secondary">← Back</a>
</body>
</html>

