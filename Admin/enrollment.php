<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../index.php");
    exit();
}

include "db.php";

/* FETCH SCHOOL LOGO */
$stmt = $conn->prepare("SELECT logo FROM school_profile WHERE id = 1");
$stmt->execute();
$school = $stmt->fetch(PDO::FETCH_ASSOC);

/* ADD SCHEDULE */
if (isset($_POST['add_schedule'])) {
    $stmt = $conn->prepare("
        INSERT INTO enrollment_schedule 
        (schedule_date, start_time, end_time, slots)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $_POST['schedule_date'],
        $_POST['start_time'],
        $_POST['end_time'],
        $_POST['slots']
    ]);
}

/* DELETE SCHEDULE */
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM enrollment_schedule WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
}

/* FETCH SCHEDULES */
$schedules = $conn->prepare("
    SELECT * FROM enrollment_schedule 
    ORDER BY schedule_date ASC
");
$schedules->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Enrollment Management</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
body {
    background: #f4f6f9;
}

/* ===== SIDEBAR ===== */
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
    border: 2px solid #0d6efd;
}

.top-icons {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.sidebar a i {
    font-size: 28px;
    color: white;
    transition: transform 0.2s, color 0.2s;
}

.sidebar a:hover i,
.sidebar a.active i {
    color: #0d6efd;
    transform: scale(1.2);
}

.spacer {
    flex-grow: 1;
}

.logout i {
    color: red;
}

/* ===== CONTENT ===== */
.content {
    margin-left: 120px;
    padding: 30px;
}

.section-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.section-title {
    text-align: center;
    font-size: 26px;
    margin-bottom: 20px;
}

.table th {
    background: #f1f3f5;
    font-weight: 600;
    text-align: center;
}

.table td {
    text-align: center;
    vertical-align: middle;
}

.btn-circle {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    padding: 0;
}
</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">

    <?php if (!empty($school['logo']) && file_exists("../uploads/".$school['logo'])): ?>
        <img src="../uploads/<?= htmlspecialchars($school['logo']) ?>" alt="School Logo">
    <?php else: ?>
        <img src="../uploads/default-logo.png" alt="Default Logo">
    <?php endif; ?>

    <div class="top-icons">
        <a href="dashboard.php" title="Dashboard">
            <i class="bi bi-grid-3x2-gap-fill"></i>
        </a>
        <a href="users.php" title="Users">
            <i class="bi bi-people"></i>
        </a>
        <a href="enrollment.php" class="active" title="Enrollment">
            <i class="bi bi-file-earmark-fill"></i>
        </a>
        <a href="#" title="Video">
            <i class="bi bi-person-video3"></i>
        </a>
        <a href="#" title="Messages">
            <i class="bi bi-envelope-fill"></i>
        </a>
    </div>

    <div class="spacer"></div>

    <a href="logout.php" class="logout mb-3" title="Logout">
        <i class="bi bi-box-arrow-left"></i>
    </a>
</div>

<!-- MAIN CONTENT -->
<div class="content">

    <!-- ENROLLMENT SCHEDULING -->
    <div class="section-card">
        <div class="section-title">Enrollment Scheduling Appointment</div>

        <form method="POST">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Slots</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>

                    <!-- ADD NEW -->
                    <tr>
                        <td><input type="date" name="schedule_date" class="form-control" required></td>
                        <td><input type="time" name="start_time" class="form-control" required></td>
                        <td><input type="time" name="end_time" class="form-control" required></td>
                        <td><input type="number" name="slots" class="form-control" required></td>
                        <td>
                            <button name="add_schedule" class="btn btn-success btn-circle">
                                <i class="bi bi-plus"></i>
                            </button>
                        </td>
                    </tr>

                    <!-- EXISTING SCHEDULES -->
                    <?php while ($row = $schedules->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?= date("F d, Y | l", strtotime($row['schedule_date'])) ?></td>
                        <td><?= date("h:i A", strtotime($row['start_time'])) ?></td>
                        <td><?= date("h:i A", strtotime($row['end_time'])) ?></td>
                        <td><?= $row['slots'] ?></td>
                        <td>
                            <a href="?delete=<?= $row['id'] ?>"
                            class="btn btn-danger btn-circle"
                            onclick="return confirm('Delete this schedule?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>

                </tbody>
            </table>
        </form>
    </div>


    <!-- STUDENT APPROVAL -->
    <div class="section-card">
        <div class="section-title">Student Approval Request</div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Appointment Date</th>
                    <th>Time</th>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Course</th>
                    <th>Year</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <!-- MASTERLIST -->
    <div class="section-card">
        <div class="section-title">Masterlist</div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Course</th>
                    <th>Year</th>
                    <th>Section</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <!-- SUBJECTS -->
    <div class="section-card">
        <div class="section-title">Subjects</div>

        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Course</th>
                    <th>Instructor</th>
                    <th>Year</th>
                    <th>Hours</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="text" class="form-control" placeholder="Type subject"></td>
                    <td>
                        <select class="form-select">
                            <option>Select course</option>
                        </select>
                    </td>
                    <td><input type="text" class="form-control"></td>
                    <td>
                        <select class="form-select">
                            <option>Select</option>
                        </select>
                    </td>
                    <td><input type="number" class="form-control"></td>
                    <td>
                        <button class="btn btn-success btn-circle">
                            <i class="bi bi-plus"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
