<!-- index.php -->
<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Vision Point - Home</title>
  <link rel="stylesheet" href="css/style.css">
  <script src="js/script.js" defer></script>
</head>
<body>

  <!-- Navigation Bar -->
  <nav class="navbar">
    
    <div class="logo">👁Vision Point</div>
    <ul>
      <li><a href="index.php">Home</a></li>
      <li><a href="aboutus.php">About us</a></li>
      <li><a href="doctors.php">Doctors</a></li>
      <li><a href="feedback.php">Feedback</a></li>
      <li><a href="login.php">Login</a></li>
      <li><a href="logout.php">Logout</a></li>
    </ul>
  </nav>

  <!-- Hero Section -->
  <section class="hero">
    <div class="hero-text">
      <h1>Welcome to Vision Point Eye Clinic</h1>
      <p>Leading Eye Care Clinic with Expert Doctors</p>
      <a href="register.php" class="btn">Register Now</a>
    </div>
  </section>

  <!-- About Section -->
  <section class="about">
    
    <p>
      Vision Point is a dedicated eye care clinic offering specialized treatment with modern technology since 2010.
      Trusted by thousands across the region, our Tamil-speaking doctors provide expert care with compassion.we have 100% success rate in eye surgery with people ratings.
    </p>
  </section>

  <!-- Image Gallery -->
  <section class="gallery">
    <div class="image-row">
      <img src="images/doctor-testing-patient-eyesight_23-2149230013.avif" alt="Clinic View 1">
      <img src="images/labor-union-members-working-together_23-2150995038.avif" alt="Clinic View 2">
    </div>
    <div class="image-full">
      <img src="images/eye-exam-room-with-modern-diagnostic-tools_1291600-73448.jpg" alt="Clinic Room">
    </div>
  </section>

  <!-- Footer -->
<footer class="footer">
  <div class="footer-container">
    
    <!-- Left side: Contact Info -->
    <div class="footer-left">
      <h3>Contact Us</h3>
      <p>📍 123 Vision Street, Colombo 07, Sri Lanka</p>
      <p>📞 +94 774 006 848</p>
      <p>📧 info@visionpoint.lk</p>
    </div>

    <!-- Right side: Socials -->
    <div class="footer-right">
      <h3>Follow Us</h3>
      <div class="social-icons">
      <a href="https://facebook.com"><img src="images/download.png" alt="Facebook"></a>
      <a href="https://twitter.com"><img src="images/download (1).png" alt="Twitter"></a>
      <a href="https://instagram.com"><img src="images/download (2).png" alt="Instagram"></a>
      <a href="https://tiktok.com"><img src="images/download (3).png" alt="TikTok"></a>
    </div>
    </div>

  </div>

  <div class="footer-bottom">
    <p>&copy; 2025 Vision Point Eye Clinic. All rights reserved.</p>
  </div>
</footer>

</body>
</html>