// Scroll to top when clicking social icons
function scrollToTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Form Validation Example: Registration Form
function validateRegisterForm() {
  const name = document.getElementById("name").value.trim();
  const phone = document.getElementById("phone").value.trim();
  const address = document.getElementById("address").value.trim();
  const nic = document.getElementById("nic").value.trim();
  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value.trim();

  let errorMsg = "";

  if (name === "" || phone === "" || address === "" || nic === "" || email === "" || password === "") {
    errorMsg = "All fields are required.";
  } else if (!/^[0-9]{10}$/.test(phone)) {
    errorMsg = "Phone number must be 10 digits.";
  } else if (!/^[0-9]{9}[vVxX]$/.test(nic)) {
    errorMsg = "NIC must be in correct format (e.g., 123456789V).";
  } else if (!/^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/.test(email)) {
    errorMsg = "Enter a valid email address.";
  } else if (password.length < 6) {
    errorMsg = "Password must be at least 6 characters.";
  }

  if (errorMsg !== "") {
    alert(errorMsg);
    return false;
  }

  return true;
}

// Login Validation
function validateLoginForm() {
  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value.trim();

  if (email === "" || password === "") {
    alert("Email and password are required.");
    return false;
  }
  return true;
}