<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';
require_once __DIR__ . '/../vendor/autoload.php';
use Smalot\PdfParser\Parser;

redirectIfNotLoggedIn();

$skippedBooks = []; // Libros ya existentes

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $files = $_FILES['book_files'];
    $cover = $_FILES['cover_image'] ?? null;

    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }

        $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            continue; // Skip non-PDFs
        }

        $title = $_POST['nombre'];
        $author = $_POST['autor'];
        $year = $_POST['date'];
        $genero = $_POST['genero'];
        $language = $_POST['language'];
        $description = $_POST['description'];

        // Verificar si ya existe el libro
        $checkStmt = $conn->prepare("SELECT id FROM libros WHERE nombre = ? AND autor = ?");
        $checkStmt->bind_param("ss", $title, $author);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $skippedBooks[] = $title;
            continue;
        }
        $checkStmt->close();

        $newFileName = uniqid() . '-' . basename($files['name'][$i]);
        $destPath = "../books/" . $newFileName;

        if (!move_uploaded_file($files['tmp_name'][$i], $destPath)) {
            continue;
        }

        $coverName = '';
        if ($cover && $cover['error'] === UPLOAD_ERR_OK) {
            $coverName = uniqid() . '-' . basename($cover['name']);
            move_uploaded_file($cover['tmp_name'], "../assets/images/" . $coverName);
        }

        $stmt = $conn->prepare("INSERT INTO libros (nombre, autor, description, genero, fecha, language, file_path, cover_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $title, $author, $description, $genero, $year, $language, $newFileName, $coverName);
        $stmt->execute();
        $stmt->close();
    }

    // Redirigir con mensajes de error si hay libros omitidos
    if (!empty($skippedBooks)) {
        $skippedParam = urlencode(implode(', ', $skippedBooks));
        header("Location: upload-form.php?upload=partial&skipped={$skippedParam}");
    } else {
        header("Location: upload-form.php?upload=success");
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Bulk Upload PDF Books</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
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
    .book-card {
      border-radius: 14px;
      overflow: hidden;
      background: #fff;
      box-shadow: 0 2px 12px rgba(0,0,0,0.08);
      transition: all 0.3s ease-in-out;
    }
    .book-card:hover {
      transform: translateY(-5px) scale(1.01);
      box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }
    .book-cover {
      height: 160px;
      object-fit: cover;
      border-bottom: 1px solid #eee;
    }
    .search-bar {
      max-width: 400px;
    }
    .header-title {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .header-title i {
      font-size: 1.4rem;
      color: #3b82f6;
    }
    .recommended-title {
      margin-top: 2rem;
      font-weight: 600;
      font-size: 1.2rem;
    }
    .category-tags {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-bottom: 1rem;
    }
    .category-tags span {
      background-color: #e0e7ff;
      color: #1e40af;
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      cursor: pointer;
    }
    .category-tags span.active {
      background-color: #3b82f6;
      color: #fff;
    }
  </style>
  
    <!-----User Dashboard------>
 <div class="sidebar">
    <img src="../assets/images/edulibrary logo.png" alt="EduLibrary Logo" class="logo">
    <p class="mt-2 small">Welcome, <strong><?= htmlspecialchars(getCurrentUsername()) ?></strong></p>
    <ul class="nav flex-column mt-4">
      <li class="nav-item"><a href="user-dashboard.php" class="nav-link"><i class="bi bi-house-door me-2"></i>Dashboard</a></li>
      <li class="nav-item"><a href="profile-update.php" class="nav-link"><i class="bi bi-person-circle me-2"></i>Profile</a></li>
      <li class="nav-item"><a href="upload-form.php" class="nav-link"><i class="bi bi-upload me-2"></i>Upload Book</a></li>
      <li class="nav-item"><a href="#" class="nav-link"><i class="bi bi-bookmark me-2"></i>Bookmarks</a></li>
      <li class="nav-item mt-4"><a href="../logout.php" class="nav-link"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
    </ul>
  </div>
     
<div class="main-content d-flex justify-content-center align-items-center" style="min-height: 100vh;">
  <div class="w-100" style="max-width: 600px;">
    <?php if (isset($_GET['upload'])): ?>
      <?php if ($_GET['upload'] === 'success'): ?>
          <div class="alert alert-success">✅ Libros subidos exitosamente.</div>
      <?php elseif ($_GET['upload'] === 'partial' && isset($_GET['skipped'])): ?>
          <div class="alert alert-warning">
              ⚠️ Los siguientes libros ya habían sido subidos y fueron omitidos: <strong><?= htmlspecialchars($_GET['skipped']) ?></strong>.
          </div>
      <?php endif; ?>
    <?php endif; ?>

    <h2 class="mb-4 text-center"><i class="bi bi-upload"></i> Bulk Upload PDF Books</h2>

    <form action="upload-form.php" method="POST" enctype="multipart/form-data" class="bg-white p-4 rounded shadow-sm">
      <div class="mb-3">
        <label for="nombre" class="form-label"><i class="bi bi-book me-1"></i>Nombre del libro</label>
        <input type="text" name="nombre" id="nombre" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="autor" class="form-label"><i class="bi bi-person me-1"></i>Autor</label>
        <input type="text" name="autor" id="autor" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="language" class="form-label"><i class="bi bi-translate me-1"></i>Idioma</label>
        <input type="text" name="language" id="language" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="date" class="form-label"><i class="bi bi-calendar me-1"></i>Año de publicación</label>
        <input type="number" name="date" id="date" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="genero" class="form-label"><i class="bi bi-tags me-1"></i>Género</label>
        <select name="genero" id="genero" class="form-select" required>
          <option value="fiction">Fiction</option>
          <option value="drama">Drama</option>
          <option value="education">Education</option>
          <option value="history">History</option>
          <option value="business">Business</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="book_files" class="form-label"><i class="bi bi-file-earmark-pdf me-1"></i>Seleccionar archivos PDF</label>
        <input type="file" name="book_files[]" id="book_files" class="form-control" multiple accept=".pdf" required>
      </div>

      <div class="mb-3">
        <label for="description" class="form-label"><i class="bi bi-align-left me-1"></i>Descripción del libro</label>
        <textarea name="description" id="description" class="form-control" rows="4" placeholder="Escribe aquí la descripción del libro..." required></textarea>
      </div>

      <div class="d-grid">
        <button type="submit" class="btn btn-primary"><i class="bi bi-cloud-arrow-up me-1"></i>Subir libro</button>
      </div>
    </form>
  </div>
</div>
</body>
</html>
