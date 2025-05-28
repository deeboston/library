<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';

redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];

// Obtener datos actuales del usuario
$stmt = $conn->prepare("SELECT username, email, role FROM usuario WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email, $role);
$stmt->fetch();
$stmt->close();

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);
    $new_password = trim($_POST['password']);

    if (!empty($new_username) && filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE usuario SET username = ?, email = ?, password = ? WHERE id = ?");
            $update->bind_param("sssi", $new_username, $new_email, $hashed_password, $user_id);
        } else {
            $update = $conn->prepare("UPDATE usuario SET username = ?, email = ? WHERE id = ?");
            $update->bind_param("ssi", $new_username, $new_email, $user_id);
        }

        if ($update->execute()) {
            $_SESSION['username'] = $new_username;
            $success = "Perfil actualizado correctamente.";
            $username = $new_username;
            $email = $new_email;
        } else {
            $error = "Error al actualizar el perfil.";
        }

        $update->close();
    } else {
        $error = "Nombre de usuario o email inválido.";
    }
}




// Mostrar confirmación si el usuario ha presionado "Eliminar Cuenta"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $confirm_prompt = true;
}

// Eliminar cuenta si el usuario lo confirma
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    $delete_stmt = $conn->prepare("DELETE FROM usuario WHERE id = ?");
    $delete_stmt->bind_param("i", $user_id);

    if ($delete_stmt->execute()) {
        session_destroy();
        header("Location: ../index.php");
        exit();
    } else {
        $error = "No se pudo eliminar la cuenta.";
    }
}


?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Perfil</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
  </style>
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
      <li class="nav-item"><a href="read-section.php" class="nav-link"><i class="bi bi-book-half me-2"></i>Read</a></li>
      <li class="nav-item"><a href="save-books.php" class="nav-link"><i class="bi bi-bookmark me-2"></i>Bookmarks</a></li>
      <li class="nav-item mt-4"><a href="../logout.php" class="nav-link"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
    </ul>
  </div>

  <!-- Main content -->
<div class="main-content">
  <h2 class="fw-bold mb-4 text-center"><i class="bi bi-person-circle me-2"></i>Editar Perfil</h2>

  <?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= $success ?></div>
  <?php endif; ?>
  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php endif; ?>
  <?php if (!empty($confirm_prompt)): ?>
  <div class="alert alert-warning text-center">
    <p><strong>¿Estás seguro de que deseas eliminar tu cuenta?</strong> Esta acción es irreversible.</p>
    <form method="POST" class="d-inline">
      <input type="hidden" name="delete_account" value="1">
      <button type="submit" class="btn btn-danger">Sí, eliminar</button>
    </form>
    <a href="profile-update.php" class="btn btn-secondary ms-2">Cancelar</a>
  </div>
<?php endif; ?>


  <div class="d-flex justify-content-center">
    <form method="POST" class="w-100" style="max-width: 600px;">
      <div class="mb-3">
        <label for="username" class="form-label">Nombre de usuario</label>
        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($username) ?>" required>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Correo electrónico</label>
        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Rol</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($role) ?>" disabled>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Nueva contraseña (opcional)</label>
        <input type="password" name="password" class="form-control" placeholder="Dejar en blanco para mantener la actual">
      </div>
      <button type="submit" class="btn btn-primary">Actualizar Perfil</button>
      <a href="user-dashboard.php" class="btn btn-secondary">Cancelar</a>
      <hr class="my-4">
      <div class="text-center">
        <form method="POST">
          <input type="hidden" name="confirm_delete" value="1">
          <button type="submit" class="btn btn-outline-danger">Eliminar Cuenta</button>
        </form>
      </div>

      
    </form>
  </div>
</div>

</body>
</html>

