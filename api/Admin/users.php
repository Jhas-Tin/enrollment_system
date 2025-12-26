<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../index.php");
    exit();
}

include "db.php";

/* Fetch school profile for sidebar logo */
$stmt = $conn->prepare("SELECT * FROM school_profile WHERE id = 1");
$stmt->execute();
$data = $stmt->fetch(PDO::FETCH_ASSOC);

/* Fetch all users except the logged-in admin */
$currentUser = $_SESSION['admin'];
$stmt = $conn->prepare("SELECT * FROM admin WHERE email != :currentUser ORDER BY id ASC");
$stmt->execute([':currentUser' => $currentUser]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Add new user */
if (isset($_POST['add_user'])) {
    $email = $_POST['username'] . '@admin';
    $password = $_POST['password']; // Save as plain text

    $stmt = $conn->prepare("INSERT INTO admin (email, password) VALUES (:email, :password)");
    $stmt->execute([':email' => $email, ':password' => $password]);

    header("Location: users.php");
    exit();
}

/* Edit user */
if (isset($_POST['edit_user'])) {
    $id = $_POST['edit_id'];
    $email = $_POST['edit_email'];
    $password = $_POST['edit_password'];

    if (!empty($password)) {
        $stmt = $conn->prepare("UPDATE admin SET email=:email, password=:password WHERE id=:id");
        $stmt->execute([':email'=>$email, ':password'=>$password, ':id'=>$id]);
    } else {
        $stmt = $conn->prepare("UPDATE admin SET email=:email WHERE id=:id");
        $stmt->execute([':email'=>$email, ':id'=>$id]);
    }

    header("Location: users.php");
    exit();
}

/* Delete user */
if (isset($_POST['delete_user'])) {
    $deleteId = $_POST['delete_id'];
    $stmt = $conn->prepare("DELETE FROM admin WHERE id = :id");
    $stmt->execute([':id' => $deleteId]);
    header("Location: users.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Users Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { width: 100px; height: 100vh; background: #111; position: fixed; display: flex; flex-direction: column; align-items: center; padding-top: 20px; }
        .sidebar img { width:60px;height:60px;border-radius:50%;margin-bottom:20px;border:2px solid #007bff;object-fit:cover; }
        .top-icons { display:flex; flex-direction:column; align-items:center; gap:30px; }
        .sidebar a i { font-size:28px;color:white; transition: transform 0.2s,color 0.2s; }
        .sidebar a.active i { color:#0d6efd; }
        .sidebar a:hover i { transform:scale(1.2); color:#0d6efd; }
        .spacer { flex-grow:1; }
        .logout i { color:red; font-size:28px; }
        .content { margin-left:120px; padding:30px; }
        .users-table { background:white; border-radius:12px; padding:20px; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
        .users-table thead th { border-bottom:2px solid #dee2e6; }
        .users-table tbody tr:hover { background-color: #e9f2ff; }
        .add-user-btn { float:right; margin-bottom:10px; }
        @media(max-width:768px){
            .sidebar { width:100%; height:auto; flex-direction:row; justify-content:space-around; padding:10px 0; }
            .sidebar img { width:40px; height:40px; }
            .top-icons { flex-direction:row; gap:15px; }
            .content { margin-left:0; padding:15px; }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <?php if (!empty($data['logo']) && file_exists("../uploads/".$data['logo'])): ?>
        <img id="sidebarLogo" src="../uploads/<?= htmlspecialchars($data['logo']) ?>" alt="School Logo">
    <?php else: ?>
        <img id="sidebarLogo" src="/path/to/default-logo.png" alt="Default Logo">
    <?php endif; ?>

    <div class="top-icons">
        <a href="dashboard.php"  data-bs-toggle="tooltip" data-bs-placement="right" title="Dashboard"><i class="bi bi-grid-3x2-gap-fill"></i></a>
        <a href="users.php" class="active" data-bs-toggle="tooltip" data-bs-placement="right" title="Users"><i class="bi bi-people"></i></a>
        <a href="enrollment.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Files"><i class="bi bi-file-earmark-fill"></i></a>
        <a href="#" data-bs-toggle="tooltip" data-bs-placement="right" title="Video"><i class="bi bi-person-video3"></i></a>
        <a href="#" data-bs-toggle="tooltip" data-bs-placement="right" title="Messages"><i class="bi bi-envelope-fill"></i></a>
    </div>

    <div class="spacer"></div>
    <a href="logout.php" class="logout mb-3" data-bs-toggle="tooltip" data-bs-placement="right" title="Logout"><i class="bi bi-box-arrow-left"></i></a>
</div>

<!-- Main Content -->
<div class="content">
    <h3>Users Management</h3>
    <button class="btn btn-success add-user-btn" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="bi bi-person-plus"></i></button>

    <div class="users-table table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Email</th>
                    <th>Password</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if($users): ?>
                    <?php foreach($users as $index => $user): ?>
                        <tr>
                            <td><?= $index+1 ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= str_repeat('*', strlen($user['password'])) ?></td>
                            <td>
                                <button class="btn btn-primary btn-sm edit-btn" 
                                        data-id="<?= $user['id'] ?>" 
                                        data-email="<?= htmlspecialchars($user['email']) ?>" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editUserModal">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-danger btn-sm delete-btn" 
                                        data-id="<?= $user['id'] ?>" 
                                        data-email="<?= htmlspecialchars($user['email']) ?>" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteUserModal">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">No users found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label>Email:</label>
            <div class="input-group">
                <input type="text" name="username" class="form-control" placeholder="Enter email username" required>
                <span class="input-group-text">@admin</span>
            </div>
        </div>
        <div class="mb-3">
            <label>Password:</label>
            <input type="text" name="password" class="form-control" placeholder="Enter password" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="add_user" class="btn btn-success">Confirm</button>
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="edit_id" id="edit_id">
        <div class="mb-3">
            <label>Email:</label>
            <input type="text" name="edit_email" id="edit_email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="text" name="edit_password" class="form-control" placeholder="<?= str_repeat('*', strlen($user['password'])) ?>">
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="edit_user" class="btn btn-success">Save Changes</button>
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete User Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteUserModalLabel">Delete User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete this user?</p>
        <input type="hidden" name="delete_id" id="delete_id">
        <p><strong id="delete_email"></strong></p>
      </div>
      <div class="modal-footer">
        <button type="submit" name="delete_user" class="btn btn-danger">Delete</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('edit_id').value = this.dataset.id;
        document.getElementById('edit_email').value = this.dataset.email;
    });
});

document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('delete_id').value = this.dataset.id;
        document.getElementById('delete_email').textContent = this.dataset.email;
    });
});

// Tooltips
const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
const tooltipList = [...tooltipTriggerList].map(t => new bootstrap.Tooltip(t));
</script>
</body>
</html>
