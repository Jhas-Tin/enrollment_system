<?php
session_start();
$siteName = "Smart School System";
require "Admin/db.php"; // PDO connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- CHECK DUPLICATE EMAIL ---
    $emailCheck = $_POST['email'];
    $stmtCheck = $conn->prepare("SELECT * FROM studentportal WHERE email = ?");
    $stmtCheck->execute([$emailCheck]);
    if($stmtCheck->rowCount() > 0){
        echo "<script>alert('Email already exists!');window.history.back();</script>";
        exit;
    }

    // --- 1. Student Portal (plain text password) ---
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password']; // store plain text

    $stmt = $conn->prepare("INSERT INTO studentportal (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $password]);
    $student_id = $conn->lastInsertId();

    // --- 2. Educational Attainment ---
    $elementary = $_POST['elementary_school'];
    $elem_year = $_POST['elementary_year'];
    $junior = $_POST['junior_high_school'];
    $junior_year = $_POST['junior_high_year'];
    $senior = $_POST['senior_high_school'];
    $senior_year = $_POST['senior_high_year'];

    $stmt2 = $conn->prepare("
        INSERT INTO educational_attainment 
        (student_id, elementary_school, elementary_year, junior_high_school, junior_high_year, senior_high_school, senior_high_year) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt2->execute([$student_id, $elementary, $elem_year, $junior, $junior_year, $senior, $senior_year]);

    // --- 3. Appointment Schedule ---
    if(!isset($_POST['schedule'])){
        echo "<script>alert('Please select an appointment schedule.');window.history.back();</script>";
        exit;
    }
    $appointment_schedule = $_POST['schedule'];

    // Check if slots are still available
    $stmtCheckSlot = $conn->prepare("SELECT slots FROM enrollment_schedule WHERE id = ?");
    $stmtCheckSlot->execute([$appointment_schedule]);
    $slotData = $stmtCheckSlot->fetch(PDO::FETCH_ASSOC);
    if($slotData['slots'] <= 0){
        echo "<script>alert('Selected schedule is full. Please choose another.');window.history.back();</script>";
        exit;
    }

    // Decrease slot count
    $stmtSlot = $conn->prepare("UPDATE enrollment_schedule SET slots = slots - 1 WHERE id = ?");
    $stmtSlot->execute([$appointment_schedule]);

    // --- 4. Enrollment Form ---
    $last_name = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $sex = $_POST['sex'];
    $dob = $_POST['dob'];
    $contact = $_POST['contact'];
    $home = $_POST['home_address'];
    $guardian_name = $_POST['guardian_name'];
    $guardian_contact = $_POST['guardian_contact'];
    $guardian_address = $_POST['guardian_address'];
    $course = $_POST['course'];
    $year_level = $_POST['year_level'];

    $stmt3 = $conn->prepare("
        INSERT INTO enrollment_form
        (student_id, last_name, first_name, middle_name, sex, dob, contact_number, home_address, guardian_name, guardian_contact, guardian_address, course, year_level, appointment_schedule, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')
    ");
    $stmt3->execute([
        $student_id, $last_name, $first_name, $middle_name, $sex, $dob, $contact, $home,
        $guardian_name, $guardian_contact, $guardian_address, $course, $year_level, $appointment_schedule
    ]);

    echo "<script>alert('Enrollment submitted successfully!');window.location='index.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= $siteName ?> - Enrollment</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Bootstrap & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
body {
    font-family: system-ui, sans-serif;
    background: #f1f5f9;
    margin: 0;
}
/* Navbar */
.navbar { background: #fff; box-shadow:0 2px 8px rgba(0,0,0,0.1);}
.navbar-brand { font-weight: 600; }
.navbar-nav .nav-link { color: #333; font-weight: 500; margin-right: 20px;}
.navbar-nav .nav-link:hover { color:#007bff; }

/* Form Card */
.form-card { background:white;border-radius:10px;padding:25px;margin-bottom:25px;box-shadow:0 2px 6px rgba(0,0,0,0.08);}
.section-title{font-size:20px;font-weight:600;margin-bottom:18px;}
label{font-weight:500;margin-bottom:4px;}
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light shadow-sm px-4">
  <a class="navbar-brand" href="#"><?= $siteName ?></a>
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
    <ul class="navbar-nav">
      <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
      <li class="nav-item"><a class="nav-link" href="#">Contact Us</a></li>
      <li class="nav-item"><a class="nav-link" href="enroll.php">Enroll Now</a></li>
    </ul>
    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
  </div>
</nav>

<div class="container py-4">
<form method="POST">

<!-- Account Creation -->
<div class="form-card">
<h5 class="section-title">I. Create Your Student Portal Account</h5>
<div class="row g-3">
<div class="col-md-8"><label>Username</label><input name="username" type="text" class="form-control" required></div>
<div class="col-md-4"><label>Email Suffix</label><input type="text" class="form-control" value="@student" disabled></div>
<div class="col-12"><label>Email Address</label><input name="email" type="email" class="form-control" placeholder="name@example.com" required></div>
<div class="col-12"><label>Password</label><input name="password" type="text" class="form-control" required></div>
</div>
</div>

<!-- Educational Attainment -->
<div class="form-card">
<h5 class="section-title">II. Educational Attainment</h5>
<div class="row g-3 mb-2">
<div class="col-md-8"><label>Elementary</label><input name="elementary_school" class="form-control"></div>
<div class="col-md-4"><label>Graduation Year</label><input name="elementary_year" class="form-control"></div>
</div>
<div class="row g-3 mb-2">
<div class="col-md-8"><label>Junior High School</label><input name="junior_high_school" class="form-control"></div>
<div class="col-md-4"><label>Graduation Year</label><input name="junior_high_year" class="form-control"></div>
</div>
<div class="row g-3">
<div class="col-md-8"><label>Senior High School</label><input name="senior_high_school" class="form-control"></div>
<div class="col-md-4"><label>Graduation Year</label><input name="senior_high_year" class="form-control"></div>
</div>
</div>

<!-- Appointment -->
<div class="form-card">
<h5 class="section-title">Select your appointment schedule</h5>
<table class="table table-bordered text-center align-middle">
<thead class="table-light">
<tr><th>Date</th><th>Time</th><th>Slots</th><th>Action</th></tr>
</thead>
<tbody>
<?php
$stmt = $conn->prepare("
SELECT * FROM enrollment_schedule 
WHERE slots > 0 AND CONCAT(schedule_date, ' ', end_time) > NOW()
ORDER BY schedule_date ASC, start_time ASC
");
$stmt->execute();
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(count($schedules) > 0):
foreach($schedules as $row):
$date = date("F d, Y | l", strtotime($row['schedule_date']));
$time = date("h:i A", strtotime($row['start_time'])) . " – " . date("h:i A", strtotime($row['end_time']));
$badge = '<span class="badge bg-success">'.$row['slots'].' Available</span>';
?>
<tr>
<td><?= $date ?></td>
<td><?= $time ?></td>
<td><?= $badge ?></td>
<td><input type="radio" name="schedule" class="form-check-input" value="<?= $row['id'] ?>" required></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="4">No schedules available</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>

<!-- Enrollment Form -->
<div class="form-card">
<h5 class="section-title">III. Enrollment Form</h5>
<div class="row g-3 mb-3">
<div class="col-md-4"><label>Last Name</label><input name="last_name" class="form-control"></div>
<div class="col-md-4"><label>First Name</label><input name="first_name" class="form-control"></div>
<div class="col-md-4"><label>Middle Name</label><input name="middle_name" class="form-control"></div>
<div class="col-md-6"><label>Sex</label><select name="sex" class="form-control"><option disabled selected>Select</option><option>Male</option><option>Female</option></select></div>
<div class="col-md-6"><label>Date of Birth</label><input name="dob" type="date" class="form-control"></div>
<div class="col-md-6"><label>Contact Number</label><input name="contact" class="form-control"></div>
<div class="col-md-6"><label>Home Address</label><input name="home_address" class="form-control"></div>
<div class="col-md-6"><label>Guardian Full Name</label><input name="guardian_name" class="form-control"></div>
<div class="col-md-6"><label>Guardian Contact Number</label><input name="guardian_contact" class="form-control"></div>
<div class="col-12"><label>Guardian Address</label><input name="guardian_address" class="form-control"></div>
<div class="col-md-6"><label>Course</label><select name="course" class="form-control"><option>Select Course</option></select></div>
<div class="col-md-6"><label>Year Level</label><select name="year_level" class="form-control"><option>Select Year Level</option></select></div>
</div>
</div>

<!-- Data Privacy -->
    <div class="form-card">
        <h5 class="fw-bold">Data Privacy Notice</h5>
        <p>Before you submit any personal information to our website, please take a moment to read this data privacy notice.</p>
        <h6 class="fw-bold">What personal information do we collect?</h6>
        <p>We may collect personal information such as your name, email address, phone number, and educational history.</p>
        <h6 class="fw-bold">How do we use your personal information?</h6>
        <p>We use your information to provide services you request and improve system operations.</p>
        <h6 class="fw-bold">Do we share your personal information?</h6>
        <p>No information is sold or transferred unless required by law.</p>
        <h6 class="fw-bold">How do we protect your data?</h6>
        <p>We use encryption and security measures to ensure protection.</p>
        <h6 class="fw-bold">Your rights</h6>
        <p>You may request access, correction or deletion of your data anytime.</p>
        <h6 class="fw-bold">Changes & Contact</h6>
        <p>Updates may occur. Contact us through this <a href="#">link</a>.</p>
        <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" id="agree" required>
            <label class="form-check-label" for="agree">I have read and agree to the Data Privacy Notice</label>
        </div>
    </div>

<button class="btn btn-primary mb-5">Submit</button>
</form>
</div>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-4">
      <div class="text-center mb-3">
        <div style="width:px;height:80px;background:#007bff;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:auto;">
            <i class="bi bi-person-fill text-white"></i>
        </div>
      </div>
      <form method="POST" action="login.php">
        <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div>
        <div class="mb-3"><label>Password</label><input type="password" name="password" class="form-control" required></div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
