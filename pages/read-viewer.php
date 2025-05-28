<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';

redirectIfNotLoggedIn();

$user = userIs();
$userId = $user['user_id'] ?? null;

if (!$userId) {
    die("⚠️ El ID del usuario no está disponible en la sesión.");
}

$file = $_GET['file'] ?? null;
$ext = '';
$relativePath = '';
$guardado = false;


if ($file) {
    $file = basename($file);
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $relativePath = realpath(__DIR__ . '/../books/' . $file);
    if (!$relativePath || !file_exists($relativePath)) {
        echo "<h2>❌ File not found</h2>";
        exit;
    }
} else {
    header("Location: upload-form.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Lectura: <?= htmlspecialchars($file) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<style>
  body {
    background-color: #f9fafb;
    font-family: 'Segoe UI', sans-serif;
  }
  .sidebar {
    width: 220px;
    background-color: #111827;
    color: #fff;
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    overflow-y: auto;
    padding: 1rem;
    z-index: 1000;
  }
  .sidebar img.logo {
    width: 36px;
    margin-bottom: 1rem;
  }
  .sidebar .nav-link {
    color: #d1d5db;
    padding: 8px 0;
    font-size: 0.9rem;
  }
  .sidebar .nav-link:hover {
    color: #fff;
  }
  .main-content {
    margin-left: 240px;
    padding: 2rem;
  }
  iframe, .epub-container {
    width: 100%;
    height: 90vh;
    border: none;
  }
</style>

</head>
<body>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <img src="../assets/images/edulibrary logo.png" alt="EduLibrary Logo" class="logo">
    <p class="mt-2 small">Welcome, <strong><?= htmlspecialchars(getCurrentUsername()) ?></strong></p>
    <ul class="nav flex-column mt-4">
      <li class="nav-item"><a href="user-dashboard.php" class="nav-link"><i class="bi bi-house-door me-2"></i>Dashboard</a></li>
      <li class="nav-item"><a href="profile-update.php" class="nav-link"><i class="bi bi-person-circle me-2"></i>Profile</a></li>
      <li class="nav-item"><a href="upload-form.php" class="nav-link"><i class="bi bi-upload me-2"></i>Upload Book</a></li>
      <li class="nav-item"><a href="save-books.php" class="nav-link"><i class="bi bi-bookmark me-2"></i>Bookmarks</a></li>
      <li class="nav-item mt-4"><a href="../logout.php" class="nav-link"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
    </ul>
  </div>

  <!-- Main content -->
  <div class="main-content">
    <div class="container mt-4">

      <!-- Botón de bookmark -->
      <div class="d-flex mb-3 gap-2">
        <form action="add-bookmark.php" method="post" class="m-0">
          <input type="hidden" name="file" value="<?= htmlspecialchars($file) ?>">
          <button type="submit" class="btn btn-primary">📌 Add to Bookmarks</button>
        </form>
      </div>

      <!-- Contenido del visor -->
      <?php if ($ext === 'pdf'): ?>
        <iframe id="pdfViewer" src="../books/<?= rawurlencode($file) ?>"></iframe>

      <?php elseif ($ext === 'epub'): ?>
        <div id="epub-reader" class="epub-container border d-flex align-items-center justify-content-center">
          <p>📘 Cargando EPUB...</p>
        </div>
        <script src="../assets/js/epub.min.js"></script>
        <script>
          const book = ePub("../books/<?= rawurlencode($file) ?>");
          const rendition = book.renderTo("epub-reader", { width: "100%", height: "90vh" });
          rendition.display();
          rendition.on("relocated", location => {
            book.locations.then(locations => {
              const currentLocation = book.locations.locationFromCfi(location.start.cfi);
              document.getElementById("pagina_actual").value = currentLocation || 0;
            });
          });
          book.ready.then(() => book.locations.generate(1000));
        </script>

      <?php else: ?>
        <div class="alert alert-warning">⚠️ Tipo de archivo no soportado: <?= htmlspecialchars($ext) ?></div>
      <?php endif; ?>
    </div>
  </div>
</body>

</body>
</html>
