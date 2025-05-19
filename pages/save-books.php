<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';

redirectIfNotLoggedIn();

$message = "";
$bookData = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['epub_file'])) {
    $file = $_FILES['epub_file'];
    $filename = basename($file['name']);
    $targetPath = '../uploads/' . uniqid() . '_' . $filename;

    if (!file_exists('../uploads')) {
        mkdir('../uploads', 0777, true);
    }

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        try {
            $zip = new ZipArchive;
            if ($zip->open($targetPath)) {
                $content = $zip->getFromName('META-INF/container.xml');
                if ($content !== false) {
                    $xml = simplexml_load_string($content);
                    $opfPath = (string) $xml->rootfiles->rootfile['full-path'];
                    $opfContent = $zip->getFromName($opfPath);
                    if ($opfContent !== false) {
                        $opfXml = simplexml_load_string($opfContent);
                        $opfXml->registerXPathNamespace('dc', 'http://purl.org/dc/elements/1.1/');
                        $bookData = [
                            'title'    => (string) $opfXml->xpath('//dc:title')[0],
                            'author'   => (string) $opfXml->xpath('//dc:creator')[0],
                            'language' => (string) $opfXml->xpath('//dc:language')[0],
                            'year'     => substr((string) $opfXml->xpath('//dc:date')[0], 0, 4),
                            'path'     => $targetPath
                        ];
                    }
                }
                $zip->close();
            }
        } catch (Exception $e) {
            $message = "Error reading EPUB metadata.";
        }
    } else {
        $message = "Failed to upload file.";
    }
}
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
    <a class="navbar-brand" href="#">EduLibrary</a>
    <div class="d-flex">
      <span class="navbar-text text-white me-3">Welcome, <?= htmlspecialchars(getCurrentUsername()) ?>!</span>
      <a href="../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="container mt-5">
  <h3>Your Books</h3>

  <form method="POST" enctype="multipart/form-data" class="mb-4">
    <div class="row g-2 align-items-center">
      <div class="col-auto">
        <label for="epub_file" class="form-label">Upload EPUB</label>
        <input type="file" class="form-control" name="epub_file" id="epub_file" accept=".epub" required>
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-primary mt-4">Extract Metadata</button>
      </div>
    </div>
  </form>

  <?php if ($bookData): ?>
    <form action="upload-form.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="auto_uploaded" value="<?= htmlspecialchars($bookData['path']) ?>">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Book Title</label>
          <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($bookData['title']) ?>" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Author</label>
          <input type="text" class="form-control" name="author" value="<?= htmlspecialchars($bookData['author']) ?>" required>
        </div>

        <div class="col-md-4">
          <label class="form-label">Year</label>
          <input type="number" class="form-control" name="fecha" value="<?= htmlspecialchars($bookData['year']) ?>" required>
        </div>

        <div class="col-md-4">
          <label class="form-label">Genre</label>
          <input type="text" class="form-control" name="genre">
        </div>

        <div class="col-md-4">
          <label class="form-label">Language</label>
          <input type="text" class="form-control" name="language" value="<?= htmlspecialchars($bookData['language']) ?>">
        </div>

        <div class="col-md-6">
          <label class="form-label">Cover Image</label>
          <input type="file" class="form-control" name="cover" accept="image/*" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Category</label>
          <select name="categoria" class="form-select" required>
            <option value="General">General</option>
            <option value="Spiritual">Spiritual</option>
            <option value="Education">Education</option>
            <option value="Inspirational">Inspirational</option>
          </select>
        </div>
      </div>
      <div class="mt-4 d-flex justify-content-between">
        <a href="<?= $_SESSION['role'] === 'admin' ? '../admin/admin-dashboard.php' : 'user-dashboard.php' ?>" class="btn btn-outline-secondary">← Back</a>
        <button type="submit" name="upload" class="btn btn-success">Save Book</button>
      </div>
    </form>
  <?php elseif ($message): ?>
    <div class="alert alert-warning mt-3"> <?= htmlspecialchars($message) ?> </div>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
