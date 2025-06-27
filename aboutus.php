<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>About Us - Vision Point Eye Clinic</title>
  <link rel="stylesheet" href="css/style.css" /> <!-- Use your existing style.css -->
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: linear-gradient(to right, #222831, #948979);
      color: #333;
      overflow-y: scroll;
    }

    /* Navbar styling */
    .navbar {
      background-color: #333;
      padding: 12px 24px;
      display: flex;
      align-items: center;
    }

    .navbar ul {
      list-style: none;
      display: flex;
      margin: 0;
      padding: 0;
    }

    .navbar li {
      margin-left: 100px;
    }

    .navbar a {
      text-decoration: none;
      color: white;
      font-weight: bold;
      font-size: 16px;
    }

    .navbar a:hover {
      color:#333;
    }

    /* Main content */
    .about-container {
      max-width: 1000px;
      margin: 40px auto;
      padding: 20px;
      background: white;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .about-container h2 {
      color: #393E46;
      margin-bottom: 10px;
      border-bottom: 2px solid #333;
      padding-bottom: 5px;
    }

    .about-container p {
      line-height: 1.7;
      margin-bottom: 20px;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .navbar {
        flex-direction: column;
        align-items: flex-start;
      }

      .navbar ul {
        flex-direction: column;
        width: 100%;
      }

      .navbar li {
        margin: 10px 0;
      }
    }
  </style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
  <ul>
    <li><a href="index.php">Home</a></li>
    <li><a href="aboutus.php">About Us</a></li>
    <li><a href="doctors.php">Doctors</a></li>
    <li><a href="feedback.php">Feedback</a></li>
    <li><a href="login.php">Login</a></li>
    <li><a href="logout.php">Logout</a></li>
  </ul>
</div>

<!-- About Us Content -->
<div class="about-container">
  <h2>Welcome to Vision Point Eye Clinic</h2>
  <p>Vision Point Eye Clinic is a leading eye care center committed to delivering expert, affordable, and compassionate treatment to our community. We prioritize patient comfort, advanced technology, and transparent healthcare services.</p>

  <h2>Our History</h2>
  <p>Founded in 2010, Vision Point Eye Clinic began with a single goal — to provide world-class eye care in a patient-friendly environment. Over the years, we’ve grown from a small local clinic into a trusted name for eye health in the region.</p>

  <h2>Why Choose Us?</h2>
  <p>We combine expert ophthalmologists, modern equipment, and personalized care. Our team is passionate about preserving and improving sight while building lifelong relationships with our patients. Your vision is our priority.</p>

  <h2>Our Vision</h2>
  <p>To be the most respected and advanced eye clinic, setting standards for compassionate and innovative eye care services in the country.</p>

  <h2>Our Mission</h2>
  <p>To enhance and restore sight through a commitment to clinical excellence, cutting-edge technology, and education while maintaining integrity and trust in every patient interaction.</p>
</div>

</body>
</html>