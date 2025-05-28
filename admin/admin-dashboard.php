<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';

redirectIfNotLoggedIn();
if (!isAdmin()) {
    header("Location: ../pages/user-dashboard.php");
    exit();
}

$totalBooks = $conn->query("SELECT COUNT(*) FROM libros")->fetch_row()[0];
$totalUsers = $conn->query("SELECT COUNT(*) FROM usuario")->fetch_row()[0];
$pendingRequests = $conn->query("SELECT COUNT(*) FROM delete_requests")->fetch_row()[0];
$latestBooks = $conn->query("SELECT id, nombre, autor, fecha, created_at, description FROM libros ORDER BY created_at DESC LIMIT 5");



// Monthly statistics for charts
$booksPerMonth = $conn->query("SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS count FROM libros GROUP BY month ORDER BY month");
/*$usersPerMonth = $conn->query("SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS count FROM usuario GROUP BY month ORDER BY month");*/
$categoryStats = $conn->query("SELECT genero, COUNT(*) AS count FROM libros GROUP BY genero");

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - EduLibrary</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    body {
      background-color: #f8f9fa;
    }
    .card {
      border-radius: 12px;
    }
    .sidebar {
      width: 250px;
      background-color: #1e293b;
      color: #fff;
      min-height: 100vh;
      position: fixed;
    }
    .sidebar .nav-link {
      color: #cbd5e1;
    }
    .sidebar .nav-link:hover {
      color: #fff;
    }
    .main {
      margin-left: 250px;
    }
  </style>
</head>
<body>
<div class="d-flex">
  <!-- Sidebar -->
  <div class="sidebar p-4">
    <h4 class="mb-4">🛠 Admin Panel</h4>
    <p>Welcome, <strong><?= htmlspecialchars(getCurrentUsername()) ?></strong></p>
    <ul class="nav flex-column mt-4">
      <li class="nav-item mb-2"><a href="admin-dashboard.php" class="nav-link">📊 Dashboard</a></li>
      <li class="nav-item mb-2"><a href="manage-books.php" class="nav-link">📚 Manage Books</a></li>
      <li class="nav-item mb-2"><a href="delete-requests.php" class="nav-link">🗑 Deletion Requests</a></li>
      <li class="nav-item mt-4"><a href="../logout.php" class="nav-link">🚪 Logout</a></li>
    </ul>
  </div>

  <!-- Main Content -->
  <div class="main container-fluid p-4">
    <h2 class="fw-bold mb-4">📊 Admin Overview</h2>

    <div class="row mb-4">
      <div class="col-md-4">
        <div class="card text-center shadow-sm">
          <div class="card-body">
            <h5 class="card-title">📚 Total Books</h5>
            <p class="display-6 fw-bold text-primary"><?= $totalBooks ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card text-center shadow-sm">
          <div class="card-body">
            <h5 class="card-title">👥 Total Users</h5>
            <p class="display-6 fw-bold text-success"><?= $totalUsers ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card text-center shadow-sm">
          <div class="card-body">
            <h5 class="card-title">🗑 Pending Requests</h5>
            <p class="display-6 fw-bold text-danger"><?= $pendingRequests ?></p>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Section -->
    <h4 class="mb-3">📈 Library Stats</h4>
    <div class="row mb-5">
      <div class="col-md-6">
        <canvas id="booksChart"></canvas>
      </div>
      <div class="col-md-6">
        <canvas id="usersChart"></canvas>
      </div>
      <div class="col-md-6 mt-4">
        <canvas id="categoriesChart"></canvas>
      </div>
    </div>

    <!-- Latest Books Table -->
    <h4 class="mb-3">📕 Latest Uploaded Books</h4>
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Title</th>
            <th>Author</th>
            <th>Year</th>
            <th>Uploaded At</th>
            <th>AI Summary</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($book = $latestBooks->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($book['nombre']) ?></td>
              <td><?= htmlspecialchars($book['autor']) ?></td>
              <td><?= htmlspecialchars($book['fecha']) ?></td>
              <td><?= htmlspecialchars($book['created_at']) ?></td>
              <td>
                <?php if (!empty($book['description'])): ?>
                  <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#summaryModal<?= $book['id'] ?>">View</button>

                  <!-- Summary Modal -->
                  <div class="modal fade" id="summaryModal<?= $book['id'] ?>" tabindex="-1" aria-labelledby="summaryModalLabel<?= $book['id'] ?>" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title" id="summaryModalLabel<?= $book['id'] ?>">AI Summary - <?= htmlspecialchars($book['nombre']) ?></h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <p><?= nl2br(htmlspecialchars($book['description'])) ?></p>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php else: ?>
                  <span class="text-muted">N/A</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
// Books per month chart
const booksChartCtx = document.getElementById('booksChart').getContext('2d');
const booksChart = new Chart(booksChartCtx, {
  type: 'line',
  data: {
    labels: [<?php $booksPerMonth->data_seek(0); while($row = $booksPerMonth->fetch_assoc()) echo "'{$row['month']}',"; ?>],
    datasets: [{
      label: 'Books Uploaded',
      data: [<?php $booksPerMonth->data_seek(0); while($row = $booksPerMonth->fetch_assoc()) echo "{$row['count']},"; ?>],
      borderColor: '#0d6efd',
      backgroundColor: 'rgba(13, 110, 253, 0.2)',
      tension: 0.4
    }]
  }
});

// Users per month chart
const usersChartCtx = document.getElementById('usersChart').getContext('2d');
const usersChart = new Chart(usersChartCtx, {
  type: 'bar',
  data: {
    labels: [<?php $usersPerMonth->data_seek(0); while($row = $usersPerMonth->fetch_assoc()) echo "'{$row['month']}',"; ?>],
    datasets: [{
      label: 'Users Registered',
      data: [<?php $usersPerMonth->data_seek(0); while($row = $usersPerMonth->fetch_assoc()) echo "{$row['count']},"; ?>],
      backgroundColor: '#198754'
    }]
  }
});

// Categories pie chart
const catCtx = document.getElementById('categoriesChart').getContext('2d');
const categoriesChart = new Chart(catCtx, {
  type: 'pie',
  data: {
    labels: [<?php $categoryStats->data_seek(0); while($row = $categoryStats->fetch_assoc()) echo "'{$row['categoria']}',"; ?>],
    datasets: [{
      label: 'Book Categories',
      data: [<?php $categoryStats->data_seek(0); while($row = $categoryStats->fetch_assoc()) echo "{$row['count']},"; ?>],
      backgroundColor: ['#0d6efd', '#ffc107', '#dc3545', '#20c997', '#6610f2']
    }]
  }
});
</script>
</body>
</html>
