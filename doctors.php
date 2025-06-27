<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Doctors - Smart Clinic</title>
    <link rel="stylesheet" href="css/doctors.css">
</head>
<body>
    <header>
        <h1>Our Expert Doctors</h1>
        </header>

    <main class="doctors-container">
        <?php
        require_once 'php/db_connect.php'; // Include your database connection

        // Fetch up to 10 doctors (or all if less than 10)
        // Changed doctor_id to id to match your table
        $sql = "SELECT id, name, specialty, image_path FROM vision_doctors ORDER BY name ASC LIMIT 10";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Output data for each doctor
            while($row = $result->fetch_assoc()) {
                // Changed doctor_id to id for data attribute
                echo '<div class="doctor-card" data-doctor-id="' . htmlspecialchars($row["id"]) . '">';
                echo '    <img src="' . htmlspecialchars($row["image_path"]) . '" alt="Dr. ' . htmlspecialchars($row["name"]) . '">';
                echo '    <h3 class="doctor-name">' . htmlspecialchars($row["name"]) . '</h3>';
                echo '    <p class="doctor-specialty">' . htmlspecialchars($row["specialty"]) . '</p>';
                echo '</div>';
            }
        } else {
            echo '<p>No doctors found at the moment.</p>';
        }
        $conn->close();
        ?>
    </main>

    <div id="doctorModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <div id="modal-body-content">
                <img id="modal-doctor-image" src="" alt="Doctor Image">
                <h2 id="modal-doctor-name"></h2>
                <p id="modal-doctor-specialty"></p>
                <p id="modal-doctor-bio"></p>
                <div class="doctor-schedule">
                    <h3>Availability:</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Day</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody id="modal-doctor-schedule">
                            </tbody>
                    </table>
                </div>
                <p id="modal-doctor-contact"></p>
                </div>
        </div>
    </div>

    <script src="js/doctors.js"></script>
</body>
</html>