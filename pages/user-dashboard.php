<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';

redirectIfNotLoggedIn();

// Fetch all books from database
$books = $conn->query("SELECT * FROM libros ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>EduLibrary Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const searchInput = document.getElementById('searchInput');
      const categoryTags = document.querySelectorAll('.category-tags span');
      const cards = document.querySelectorAll('.row .book-card-wrapper');

      searchInput.addEventListener('input', () => {
        const query = searchInput.value.toLowerCase();
        cards.forEach(card => {
          const title = card.querySelector('.card-title').textContent.toLowerCase();
          card.style.display = title.includes(query) ? 'block' : 'none';
        });
      });

      categoryTags.forEach(tag => {
        tag.addEventListener('click', () => {
          const selected = tag.textContent.toLowerCase();
          categoryTags.forEach(t => t.classList.remove('active'));
          tag.classList.add('active');

          cards.forEach(card => {
            const category = card.getAttribute('data-category');
            card.style.display = (selected === 'all' || category === selected) ? 'block' : 'none';
          });
        });
      });
    });
  </script>
</head>
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
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div class="header-title">
        <i class="bi bi-journal-bookmark"></i>
        <h2 class="fw-bold mb-0">All Books</h2>
      </div>
      <input type="text" id="searchInput" class="form-control search-bar" placeholder="Search for books...">
    </div>

    <div class="recommended-title">Categories</div>
    <div class="category-tags mb-4">
      <span class="active">All</span><span>Fiction</span><span>Drama</span><span>Education</span><span>History</span><span>Business</span>
    </div>

    <div class="row g-4">
      <?php $books->data_seek(0); while ($book = $books->fetch_assoc()): ?>
        <?php $category = strtolower($book['categoria'] ?? 'unknown'); ?>
        <div class="col-sm-6 col-md-4 col-lg-3 book-card-wrapper" data-category="<?= $category ?>">
          <div class="book-card">
            <?php $coverPath = $book['cover_image'] ? '../assets/images/' . htmlspecialchars($book['cover_image']) : '../assets/images/edulibrary logo.png'; ?>
            <img src="<?= $coverPath ?>" class="book-cover w-100" alt="Book Cover">
            <div class="p-3">
              <h6 class="card-title fw-semibold text-truncate" title="<?= htmlspecialchars($book['nombre']) ?>">
                <?= htmlspecialchars($book['nombre']) ?>
              </h6>
              <p class="small mt-2 text-secondary">
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
              <p class="text-muted small mb-1">By <?= htmlspecialchars($book['autor']) ?></p>
              <p class="text-muted small mb-2">📅 <?= htmlspecialchars($book['fecha']) ?> | 🌐 <?= htmlspecialchars($book['language']) ?></p>
              <a href="read-viewer.php?file=<?= urlencode($book['file_path']) ?>" class="btn btn-primary btn-sm w-100">Read Now</a>
                            <!-- Espacio entre botones -->
              <div style="height:8px;"></div>

              <!-- Botón que recarga la página con ?request_delete=ID -->
              <a href="?request_delete=<?= $book['id'] ?>" class="btn btn-outline-warning btn-sm w-100 mb-2">
                Request Deletion
              </a>

              <?php if (isset($_GET['request_delete']) && $_GET['request_delete'] == $book['id']): ?>
                <form method="POST" action="submit-deletion-request.php" class="mb-3">
                  <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                  <textarea name="reason" placeholder="Reason for deletion" class="form-control form-control-sm mb-1" rows="2" required></textarea>
                  <button type="submit" class="btn btn-warning btn-sm w-100">Send Request</button>
                </form>
<?php endif; ?>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
</body>
</html>
