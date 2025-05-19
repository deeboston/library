<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';

redirectIfNotLoggedIn();

$file = $_GET['file'] ?? null;
$ext = '';
$relativePath = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fileUpload = $_FILES['book_file'] ?? null;
    $cover = $_FILES['cover_image'] ?? null;

    if (!$fileUpload || $fileUpload['error'] !== UPLOAD_ERR_OK) {
        exit('❌ Book upload failed.');
    }

    $ext = strtolower(pathinfo($fileUpload['name'], PATHINFO_EXTENSION));
    $newFileName = uniqid() . '-' . basename($fileUpload['name']);
    $destPath = "../books/" . $newFileName;

    if (!move_uploaded_file($fileUpload['tmp_name'], $destPath)) {
        exit('❌ Failed to save uploaded book.');
    }

    $title = $_POST['nombre'] ?? pathinfo($fileUpload['name'], PATHINFO_FILENAME);
    $author = $_POST['autor'] ?? 'Unknown';
    $year = $_POST['fecha'] ?? date('Y');
    $language = $_POST['language'] ?? 'Unknown';
    $coverName = '';

    if ($cover && $cover['error'] === UPLOAD_ERR_OK) {
        $coverName = uniqid() . '-' . basename($cover['name']);
        move_uploaded_file($cover['tmp_name'], "../assets/images/" . $coverName);
    }

    // Try to extract metadata from EPUB
    if ($ext === 'epub') {
        $zip = new ZipArchive();
        if ($zip->open($destPath) === TRUE) {
            $containerXml = $zip->getFromName("META-INF/container.xml");
            if ($containerXml) {
                $container = new SimpleXMLElement($containerXml);
                $opfPath = (string) $container->rootfiles->rootfile['full-path'];
                $opfContent = $zip->getFromName($opfPath);
                if ($opfContent) {
                    $opf = new SimpleXMLElement($opfContent);
                    $dc = $opf->children('http://www.idpf.org/2007/opf')->metadata->children('http://purl.org/dc/elements/1.1/');
                    $title = (string) ($dc->title ?? $title);
                    $author = (string) ($dc->creator ?? $author);
                    $language = (string) ($dc->language ?? $language);
                }
            }
            $zip->close();
        }
    }

    $stmt = $conn->prepare("INSERT INTO libros (nombre, autor, fecha, language, file_path, cover_image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $title, $author, $year, $language, $newFileName, $coverName);
    $stmt->execute();

    header('Location: user-dashboard.php?upload=success');
    exit;
} elseif ($file) {
    $file = basename($file);
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $relativePath = realpath(__DIR__ . '/../books/' . $file);
    if (!$relativePath || !file_exists($relativePath)) {
        echo "<h2>❌ File not found</h2>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= $file ? 'Reading ' . htmlspecialchars($file) : 'Upload Book' ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    iframe, .epub-container, .text-viewer {
      width: 100%;
      height: 90vh;
      border: none;
    }
  </style>
</head>
<body>
<div class="container mt-4">
  <?php if (!$file): ?>
    <h4 class="mb-3">📤 Upload a New Book</h4>
    <form action="" method="POST" enctype="multipart/form-data" class="border p-4 bg-light rounded shadow">
      <div class="mb-3"><label class="form-label">Title</label><input type="text" name="nombre" class="form-control" required></div>
      <div class="mb-3"><label class="form-label">Author</label><input type="text" name="autor" class="form-control" required></div>
      <div class="mb-3"><label class="form-label">Year</label><input type="text" name="fecha" class="form-control" required></div>
      <div class="mb-3"><label class="form-label">Language</label><input type="text" name="language" class="form-control" required></div>
      <div class="mb-3"><label class="form-label">Book File (.epub, .pdf, .txt)</label><input type="file" name="book_file" accept=".epub,.pdf,.txt" class="form-control" required></div>
      <div class="mb-3"><label class="form-label">Cover Image (optional)</label><input type="file" name="cover_image" accept="image/*" class="form-control"></div>
      <button type="submit" class="btn btn-primary w-100">Upload</button>
    </form>
  <?php else: ?>
    <h4 class="mb-3">📖 Reading: <?= htmlspecialchars($file) ?></h4>

    <?php if ($ext === 'pdf'): ?>
      <iframe src="<?= '../books/' . rawurlencode($file) ?>" width="100%" height="90vh"></iframe>
      <p class="text-danger mt-2">⚠️ Your browser does not support embedded PDFs. 
      <a href="<?= '../books/' . rawurlencode($file) ?>" target="_blank">Download it here</a>.</p>

    <?php elseif ($ext === 'txt'): ?>
      <div class="text-viewer border p-3 bg-light overflow-auto">
        <pre><?= htmlspecialchars(file_get_contents($relativePath)) ?></pre>
      </div>

    <?php elseif ($ext === 'epub'): ?>
      <div id="epub-reader" class="epub-container border d-flex align-items-center justify-content-center">
        <p>📘 Loading EPUB...</p>
      </div>
      <script src="../assets/js/epub.min.js"></script>
      <script>
        const book = ePub("../books/<?= rawurlencode($file) ?>");
        const rendition = book.renderTo("epub-reader", { width: "100%", height: "90vh" });
        rendition.display();
        book.ready.then(() => {
          console.log("✅ Book ready");
          return book.loaded.navigation;
        }).then(nav => {
          console.log("📚 TOC:", nav.toc);
        }).catch(err => {
          console.error("❌ EPUB Load Error:", err);
          document.getElementById("epub-reader").innerHTML = "<p class='text-danger'>⚠️ Failed to load EPUB</p>";
        });
      </script>

    <?php else: ?>
      <div class="alert alert-warning">⚠️ Unsupported file type: <?= htmlspecialchars($ext) ?></div>
    <?php endif; ?>
  <?php endif; ?>
</div>
</body>
</html>
