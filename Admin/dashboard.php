<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../index.php");
    exit();
}

include "db.php";

/* FETCH DATA */
$stmt = $conn->prepare("SELECT * FROM school_profile WHERE id = 1");
$stmt->execute();
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    die("School profile not found.");
}

/* SAVE PROFILE */
$showProfileToast = false;
if (isset($_POST['save_profile'])) {
    $stmt = $conn->prepare("
        UPDATE school_profile SET
            name = :name,
            location = :location,
            email = :email,
            mobile = :mobile,
            telephone = :telephone,
            description = :description
        WHERE id = 1
    ");

    $stmt->execute([
        ':name' => $_POST['name'] ?? $data['name'],
        ':location' => $_POST['location'] ?? $data['location'],
        ':email' => $_POST['email'] ?? $data['email'],
        ':mobile' => $_POST['mobile'] ?? $data['mobile'],
        ':telephone' => $_POST['telephone'] ?? $data['telephone'],
        ':description' => $_POST['description'] ?? $data['description']
    ]);

    $showProfileToast = true;
    // Refresh $data for updated values
    $stmt = $conn->prepare("SELECT * FROM school_profile WHERE id = 1");
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
}

/* SAVE LOGO (AJAX) */
if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
    $logoName = time() . "_" . basename($_FILES['logo']['name']);
    move_uploaded_file($_FILES['logo']['tmp_name'], "../uploads/" . $logoName);

    $stmt = $conn->prepare("UPDATE school_profile SET logo = :logo WHERE id = 1");
    $stmt->execute([':logo' => $logoName]);

    echo 'success';
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        /* Sidebar */
        .sidebar {
            width: 100px;
            height: 100vh;
            background: #111;
            position: fixed;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 20px;
        }

        .sidebar img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-bottom: 20px;
            object-fit: cover;
            border: 2px solid #007bff;
        }

        .top-icons {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 30px;
        }

        .sidebar a i {
            font-size: 28px;
            color: white;
            transition: transform 0.2s, color 0.2s;
        }

        .sidebar a.active i {
            color: #0d6efd;
        }

        .sidebar a:hover i {
            transform: scale(1.2);
            color: #0d6efd;
        }

        .spacer {
            flex-grow: 1;
        }

        .logout i {
            color: red;
            font-size: 28px;
        }

        /* Main content */
        .content {
            margin-left: 120px;
            padding: 30px;
        }

        /* Cards */
        .card {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border: none;
            border-radius: 12px;
        }

        .logo-box {
            height: 180px;
            border: 2px dashed #ccc;
            border-radius: 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f1f1f1;
        }

        .form-label {
            font-weight: 500;
        }

        /* Toasts */
        .top-center-toast {
            margin-top: 20px;
        }

        /* Table Styling */
        .table thead th {
            background-color: #0d6efd;
            color: white;
            border: none;
        }

        .table tbody tr:hover {
            background-color: #e9f2ff;
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
        <a href="dashboard.php" class="active" data-bs-toggle="tooltip" data-bs-placement="right" title="Dashboard"><i class="bi bi-grid-3x2-gap-fill"></i></a>
        <a href="users.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Users"><i class="bi bi-people"></i></a>
        <a href="enrollment.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Files"><i class="bi bi-file-earmark-fill"></i></a>
        <a href="#" data-bs-toggle="tooltip" data-bs-placement="right" title="Video"><i class="bi bi-person-video3"></i></a>
        <a href="#" data-bs-toggle="tooltip" data-bs-placement="right" title="Messages"><i class="bi bi-envelope-fill"></i></a>
    </div>

    <div class="spacer"></div>

    <!-- Logout icon restored -->
    <a href="logout.php" class="logout mb-3" data-bs-toggle="tooltip" data-bs-placement="right" title="Logout"><i class="bi bi-box-arrow-left"></i></a>
</div>

<!-- Main content -->
<div class="content">
<form id="mainForm" method="POST" enctype="multipart/form-data">

    <!-- UPDATE LOGO -->
    <div class="card mb-4">
        <div class="card-body text-center">
            <h4 class="mb-3">Update School Logo</h4>
            <div class="logo-box mb-3">
                <img id="previewLogo" src="../uploads/<?= htmlspecialchars($data['logo']) ?>" height="120" class="rounded">
            </div>
            <input type="file" id="logoInput" name="logo" class="form-control mb-2">
            <button type="button" id="saveLogoBtn" class="btn btn-outline-primary">Save Logo</button>
        </div>
    </div>

    <!-- SCHOOL PROFILE -->
    <div class="card">
        <div class="card-body">
            <h4 class="text-center mb-4">School Profile</h4>

            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label">Name</label>
                <div class="col-sm-10">
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($data['name']) ?>">
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label">Location</label>
                <div class="col-sm-10">
                    <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($data['location']) ?>">
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label">Email</label>
                <div class="col-sm-10">
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($data['email']) ?>">
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label">Mobile</label>
                <div class="col-sm-10">
                    <input type="text" name="mobile" class="form-control" value="<?= htmlspecialchars($data['mobile']) ?>">
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label">Telephone</label>
                <div class="col-sm-10">
                    <input type="text" name="telephone" class="form-control" value="<?= htmlspecialchars($data['telephone']) ?>">
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label">Description</label>
                <div class="col-sm-10">
                    <input type="text" name="description" class="form-control" value="<?= htmlspecialchars($data['description']) ?>">
                </div>
            </div>

            <div class="text-center">
                <button type="submit" name="save_profile" class="btn btn-primary px-5">Save Profile</button>
            </div>
        </div>
    </div>

</form>
</div>

<!-- Toast container -->
<div class="position-fixed top-0 start-50 translate-middle-x p-3 top-center-toast" style="z-index: 1100;">
  <div id="profileToast" class="toast align-items-center text-white bg-success border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true" style="min-width: 300px; max-width: 500px;">
    <div class="d-flex">
      <div class="toast-body">School profile updated successfully!</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>

  <div id="logoToast" class="toast align-items-center text-white bg-info border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true" style="min-width: 300px; max-width: 500px;">
    <div class="d-flex">
      <div class="toast-body">Logo updated successfully!</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>

  <div id="noFileToast" class="toast align-items-center text-white bg-warning border-0" role="alert" aria-live="assertive" aria-atomic="true" style="min-width: 300px; max-width: 500px;">
    <div class="d-flex">
      <div class="toast-body">Please select a logo file first!</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const logoInput = document.getElementById('logoInput');
const saveLogoBtn = document.getElementById('saveLogoBtn');
const sidebarLogo = document.getElementById('sidebarLogo');

// Tooltips
const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
const tooltipList = [...tooltipTriggerList].map(t => new bootstrap.Tooltip(t))

<?php if($showProfileToast): ?>
new bootstrap.Toast(document.getElementById('profileToast'), { delay: 2000 }).show();
<?php endif; ?>

saveLogoBtn.addEventListener('click', function(){
    if(logoInput.files.length === 0){
        new bootstrap.Toast(document.getElementById('noFileToast'), { delay: 2000 }).show();
        return;
    }
    const formData = new FormData();
    formData.append('logo', logoInput.files[0]);
    fetch('<?= $_SERVER['PHP_SELF'] ?>', { method: 'POST', body: formData })
    .then(res => res.text())
    .then(res => {
        if(res === 'success'){
            const reader = new FileReader();
            reader.onload = function(e){
                sidebarLogo.src = e.target.result;
                document.getElementById('previewLogo').src = e.target.result;
            };
            reader.readAsDataURL(logoInput.files[0]);
            new bootstrap.Toast(document.getElementById('logoToast'), { delay: 2000 }).show();
            logoInput.value = '';
        }
    })
    .catch(err => console.error(err));
});
</script>
</body>
</html>
