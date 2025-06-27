// js/script.js

document.addEventListener('DOMContentLoaded', function() {
    const doctorCards = document.querySelectorAll('.doctor-card');
    const doctorModal = document.getElementById('doctorModal'); // Ensure this ID exists in your HTML
    const closeButton = document.querySelector('.close-button'); // Ensure this class exists in your HTML
    const modalDoctorImage = document.getElementById('modal-doctor-image'); // Ensure this ID exists in your HTML
    const modalDoctorName = document.getElementById('modal-doctor-name'); // Ensure this ID exists in your HTML
    const modalDoctorSpecialty = document.getElementById('modal-doctor-specialty'); // Ensure this ID exists in your HTML
    const modalDoctorBio = document.getElementById('modal-doctor-bio'); // Ensure this ID exists in your HTML
    const modalDoctorSchedule = document.getElementById('modal-doctor-schedule'); // Ensure this ID exists in your HTML (likely a tbody or similar)
    const modalDoctorContact = document.getElementById('modal-doctor-contact'); // Ensure this ID exists in your HTML

    // Function to open the modal and load doctor details
    function openDoctorDetails(doctorId) {
        // FIX: Use backticks for template literal in fetch URL
        // Also, adjust the path to get_doctor_details.php based on its location relative to this script.
        // If script.js is in 'js/' and get_doctor_details.php is in the root, use '../get_doctor_details.php'
        fetch(`../get_doctor_details.php?doctor_id=${doctorId}`) // Assuming get_doctor_details.php is one level up from js/
            .then(response => {
                if (!response.ok) { // Check for HTTP errors
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.doctor) {
                    const doctor = data.doctor;

                    modalDoctorImage.src = doctor.image_path; // Ensure image_path from PHP is the full, correct URL
                    modalDoctorImage.alt = `Dr. ${doctor.name}`;
                    modalDoctorName.textContent = doctor.name;
                    modalDoctorSpecialty.textContent = doctor.specialty;
                    modalDoctorBio.textContent = doctor.bio || 'No detailed biography available.';

                    // Populate schedule table
                    modalDoctorSchedule.innerHTML = ''; // Clear previous schedule
                    const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                    let hasSchedule = false;

                    days.forEach(day => {
                        const scheduleKey = `${day}_schedule`; // e.g., 'monday_schedule'
                        if (doctor[scheduleKey] && doctor[scheduleKey] !== 'N/A' && doctor[scheduleKey].trim() !== '') {
                            hasSchedule = true;
                            const row = document.createElement('tr');
                            const dayCell = document.createElement('td');
                            const timeCell = document.createElement('td');
                            // Capitalize the first letter of the day name for display
                            dayCell.textContent = day.charAt(0).toUpperCase() + day.slice(1);
                            timeCell.textContent = doctor[scheduleKey];
                            row.appendChild(dayCell);
                            row.appendChild(timeCell);
                            modalDoctorSchedule.appendChild(row);
                        }
                    });

                    // Display message if no schedule is found
                    if (!hasSchedule) {
                        const row = document.createElement('tr');
                        const cell = document.createElement('td');
                        cell.setAttribute('colspan', '2'); // Span across both columns (Day and Time)
                        cell.textContent = 'No specific schedule available. Please call the clinic to inquire.';
                        row.appendChild(cell);
                        modalDoctorSchedule.appendChild(row);
                    }

                    // Populate contact information
                    let contactInfo = [];
                    if (doctor.email) contactInfo.push(`Email: ${doctor.email}`);
                    if (doctor.phone) contactInfo.push(`Phone: ${doctor.phone}`);
                    modalDoctorContact.textContent = contactInfo.join(' | ') || 'Contact information not available.';

                    doctorModal.style.display = 'flex'; // Use flex to center the modal
                } else {
                    alert(data.message || 'Failed to retrieve doctor details.');
                }
            })
            .catch(error => {
                console.error('Error fetching doctor details:', error);
                alert('Could not load doctor details. Please try again later.');
            });
    }

    // Add click event listeners to each doctor card
    doctorCards.forEach(card => {
        card.addEventListener('click', function() {
            // Get doctor ID from data attribute. Ensure your HTML has data-doctor-id="..." on each .doctor-card
            const doctorId = this.dataset.doctorId; 
            if (doctorId) {
                openDoctorDetails(doctorId);
            }
        });
    });

    // Close the modal when the close button is clicked
    closeButton.addEventListener('click', function() {
        doctorModal.style.display = 'none';
    });

    // Close the modal when clicking outside of the modal content
    window.addEventListener('click', function(event) {
        if (event.target == doctorModal) {
            doctorModal.style.display = 'none';
        }
    });
});