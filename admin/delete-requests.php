<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';

if (!isAdmin()) {
    header("Location: ../pages/dashboard.php");
    exit();
}

// Updated query with correct table/column names
$sql = "SELECT dr.id AS request_id, dr.reason, dr.created_at,
               u.username, b.nombre AS title, b.autor AS author, b.id AS book_id
        FROM delete_requests dr
        JOIN usuario u ON dr.user_id = u.id
        JOIN libros b ON dr.book_id = b.id
        ORDER BY dr.created_at DESC";

$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Deletion Requests - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="../pages/dashboard.php">Admin Panel</a>
    <a href="<?= $_SESSION['role'] === 'admin' ? '../admin/admin-dashboard.php' : 'user-dashboard.php' ?>" class="btn btn-outline-secondary">← Back</a>
</div>
</nav>

<div class="container mt-5">
    <h3>Pending Book Deletion Requests</h3>

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered table-hover mt-3">
            <thead class="table-dark">
                <tr>
                    <th>Book Title</th>
                    <th>Author</th>
                    <th>Requested By</th>
                    <th>Reason</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= htmlspecialchars($row['author']) ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= nl2br(htmlspecialchars($row['reason'])) ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <form method="POST" action="handle-delete.php" style="display:inline;">
                            <input type="hidden" name="book_id" value="<?= $row['book_id'] ?>">
                            <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
                            <button type="submit" name="delete" class="btn btn-danger btn-sm" onclick="return confirm('Delete this book permanently?')">Delete Book</button>
                        </form>
                        <form method="POST" action="handle-delete.php" style="display:inline;">
                            <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
                            <button type="submit" name="ignore" class="btn btn-secondary btn-sm">Ignore</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-muted mt-4">No deletion requests at the moment.</p>
    <?php endif; ?>
</div>

</body>
</html>
