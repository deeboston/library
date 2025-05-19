<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    header("Location: ../pages/dashboard.php");
    exit();
}

$result = $conn->query("SELECT b.*, u.username FROM libros b LEFT JOIN usuario u ON b.uploaded_by = u.id ORDER BY b.fecha DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage All Books - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="delete-requests.php">Admin Panel</a>
    <a href="<?= $_SESSION['role'] === 'admin' ? '../admin/admin-dashboard.php' : 'user-dashboard.php' ?>" class="btn btn-outline-secondary">← Back</a>
  </div>
</nav>

<div class="container mt-5">
    <h3>📚 All Uploaded Books</h3>

    <?php if ($result && $result->num_rows > 0): ?>
        <table class="table table-hover table-bordered mt-3">
            <thead class="table-dark">
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Year</th>
                    <th>Language</th>
                    <th>Category</th>
                    <th>Summary</th>
                    <th>Uploaded By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($book = $result->fetch_assoc()): ?>
                    <tr data-id="<?= $book['id'] ?>">
                        <td contenteditable="true" class="editable" data-field="nombre"><?= htmlspecialchars($book['nombre']) ?></td>
                        <td contenteditable="true" class="editable" data-field="autor"><?= htmlspecialchars($book['autor']) ?></td>
                        <td contenteditable="true" class="editable" data-field="fecha"><?= htmlspecialchars($book['fecha']) ?></td>
                        <td contenteditable="true" class="editable" data-field="language"><?= htmlspecialchars($book['language']) ?></td>
                        <td contenteditable="true" class="editable" data-field="categoria"><?= htmlspecialchars($book['categoria']) ?></td>
                        <td contenteditable="true" class="editable" data-field="resumen"><?= htmlspecialchars($book['resumen']) ?></td>
                        <td><?= htmlspecialchars($book['username'] ?? 'Unknown') ?></td>
                        <td>
                            <form method="POST" action="handle-delete.php" style="display:inline;">
                                <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                                <input type="hidden" name="request_id" value="0">
                                <button type="submit" name="delete" class="btn btn-sm btn-danger" onclick="return confirm('Delete this book permanently?')">Delete</button>
                            </form>
                            <button class="btn btn-sm btn-info mt-1 generate-summary" data-id="<?= $book['id'] ?>">AI Summary</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-muted mt-4">No books found in the library.</p>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    $('.editable').on('blur', function() {
        var row = $(this).closest('tr');
        var bookId = row.data('id');
        var field = $(this).data('field');
        var value = $(this).text();

        $.ajax({
            url: 'update-book-field.php',
            method: 'POST',
            data: {
                id: bookId,
                field: field,
                value: value
            },
            success: function(response) {
                console.log('Update success:', response);
            },
            error: function() {
                alert('Error updating the book.');
            }
        });
    });

    $('.generate-summary').on('click', function() {
        var bookId = $(this).data('id');

        $.ajax({
            url: 'generate-summary.php',
            method: 'POST',
            data: { id: bookId },
            success: function(response) {
                alert('Summary generated!');
                location.reload();
            },
            error: function() {
                alert('Failed to generate summary.');
            }
        });
    });
});
</script>
</body>
</html>
