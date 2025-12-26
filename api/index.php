<?php session_start(); $siteName="Smart School System"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= $siteName ?></title>
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
.navbar {
    background: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.navbar-brand {
    font-weight: 600;
}
.navbar-nav .nav-link {
    color: #333;
    font-weight: 500;
    margin-right: 20px;
}
.navbar-nav .nav-link:hover {
    color: #007bff;
}

/* Hero Section */
section.hero {
    background: #3ac7f2;
    color: #fff;
    padding: 80px 20px;
    text-align: center;
}
section.hero h1 {
    font-size: 2.5rem;
    margin-bottom: 15px;
}
section.hero p {
    font-size: 1.1rem;
    margin-bottom: 20px;
}
section.hero button {
    border: 1px solid #fff;
    color: #fff;
    background: transparent;
    padding: 8px 20px;
    border-radius: 5px;
}
section.hero button:hover {
    background: #fff;
    color: #3ac7f2;
}

/* Login Modal */
.modal-content {
    border-radius: 12px;
    padding: 30px;
}
.modal .bi-person-fill {
    font-size: 2rem;
}
.modal input {
    border-radius: 8px;
}
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
      <li class="nav-item"><a class="nav-link active" href="#">Home</a></li>
      <li class="nav-item"><a class="nav-link" href="#">Contact Us</a></li>
      <li class="nav-item"><a class="nav-link" href="enroll.php">Enroll Now</a></li>
    </ul>
    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
  </div>
</nav>

<!-- Hero Section -->
<section class="hero">
    <h1>Welcome</h1>
    <p>Your webpage is ready to be set up. If you are the admin, click Start to configure the website.</p>
    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#loginModal">Get Started</button>
</section>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-4">
      <div class="text-center mb-3">
        <div style="width:80px;height:80px;background:#007bff;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:auto;">
            <i class="bi bi-person-fill text-white"></i>
        </div>
      </div>
      <form method="POST" action="login.php">
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
